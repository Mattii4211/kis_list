<?php

namespace App\Controller;

use App\DTO\BookDTO;
use App\DTO\CreateBookDTO;
use App\Entity\Book;
use App\Factory\BookFactory;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'Books', description: 'Operations related to book inventory')]
class BookController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private BookRepository $bookRepository,
    ) {
    }

    #[Route('/api/books', name: 'book_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/books',
        summary: 'List all books',
        tags: ['Books'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A list of books',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: BookDTO::class))
                )
            ),
        ]
    )]
    public function list(SerializerInterface $serializer, int $lastId = 0): JsonResponse
    {
        $books = [];

        foreach ($this->bookRepository->getBooksAfterId($lastId) as $book) {
            $books[] = BookFactory::create($book);
        }

        return new JsonResponse(
            $serializer->serialize($books, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/books', name: 'book_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/books',
        summary: 'Create a new book',
        tags: ['Books'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateBookDTO::class))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Book created successfully',
                content: new OA\JsonContent(ref: new Model(type: BookDTO::class))
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
        ]
    )]
    public function create(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        try {
            $bookDto = $serializer->deserialize($request->getContent(), CreateBookDTO::class, 'json');
        } catch (NotEncodableValueException $exception) {
            return new JsonResponse(['error' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($book = new Book($bookDto->serialNumber, $bookDto->title, $bookDto->author));
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getPropertyPath().': '.$error->getMessage();
            }

            return new JsonResponse(['errors' => $messages], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($book);
        $this->em->flush();

        return new JsonResponse(
            $serializer->serialize(BookFactory::create($book), 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/api/books/{serialNumber}', name: 'book_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/books/{serialNumber}',
        summary: 'Delete a book by serial number',
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'serialNumber', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Book deleted successfully'),
            new OA\Response(response: 404, description: 'Book not found'),
        ]
    )]
    public function delete(string $serialNumber): JsonResponse
    {
        $book = $this->em->getRepository(Book::class)->findOneBy(['serialNumber' => $serialNumber]);
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found.'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($book);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/books/{serialNumber}/borrow', name: 'book_borrow', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/books/{serialNumber}/borrow',
        summary: 'Borrow a book',
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'serialNumber', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'borrowerCardNumber', type: 'string', example: '999999'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Book borrowed successfully',
                content: new OA\JsonContent(ref: new Model(type: BookDTO::class))
            ),
            new OA\Response(response: 400, description: 'Invalid payload'),
            new OA\Response(response: 404, description: 'Book or reader not found'),
            new OA\Response(response: 409, description: 'Book already borrowed'),
        ]
    )]
    public function borrow(string $serialNumber, Request $request, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $book = $this->em->getRepository(Book::class)->findOneBy(['serialNumber' => $serialNumber]);
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !isset($data['borrowerCardNumber'])) {
            return new JsonResponse(['error' => 'borrowerCardNumber is required.'], Response::HTTP_BAD_REQUEST);
        }

        if ($book->isBorrowed()) {
            return new JsonResponse(['error' => 'Book is already borrowed.'], Response::HTTP_CONFLICT);
        }

        $reader = $this->em->getRepository('App\Entity\Readers')->findOneBy(['borrowerCardNumber' => $data['borrowerCardNumber']]);
        if (!$reader) {
            return new JsonResponse(['error' => 'Reader not found.'], Response::HTTP_NOT_FOUND);
        }

        $book->setReader($reader);
        $book->setBorrowedAt(new \DateTimeImmutable());

        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getPropertyPath().': '.$error->getMessage();
            }

            return new JsonResponse(['errors' => $messages], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return new JsonResponse(
            $serializer->serialize(BookFactory::create($book), 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/api/books/{serialNumber}/return', name: 'book_return', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/books/{serialNumber}/return',
        summary: 'Return a borrowed book',
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'serialNumber', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Book returned successfully',
                content: new OA\JsonContent(ref: new Model(type: BookDTO::class))
            ),
            new OA\Response(response: 400, description: 'Book is not currently borrowed'),
            new OA\Response(response: 404, description: 'Book not found'),
        ]
    )]
    public function returnBook(string $serialNumber, SerializerInterface $serializer): JsonResponse
    {
        $book = $this->em->getRepository(Book::class)->findOneBy(['serialNumber' => $serialNumber]);
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found.'], Response::HTTP_NOT_FOUND);
        }

        if (!$book->isBorrowed()) {
            return new JsonResponse(['error' => 'Book is not currently borrowed.'], Response::HTTP_BAD_REQUEST);
        }

        $book->setReader(null);
        $book->setBorrowedAt(null);

        $this->em->flush();

        return new JsonResponse(
            $serializer->serialize(BookFactory::create($book), 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }
}
