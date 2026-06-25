<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class BookController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('/api/books', name: 'book_list', methods: ['GET'])]
    public function list(SerializerInterface $serializer): JsonResponse
    {
        $books = $this->em->getRepository(Book::class)->findAll();
        $payload = $serializer->serialize($books, 'json');

        return new JsonResponse($payload, Response::HTTP_OK, [], true);
    }

    #[Route('/api/books', name: 'book_create', methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        try {
            $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        } catch (NotEncodableValueException $exception) {
            return new JsonResponse(['error' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getPropertyPath().': '.$error->getMessage();
            }

            return new JsonResponse(['errors' => $messages], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($book);
        $this->em->flush();

        $payload = $serializer->serialize($book, 'json');
        return new JsonResponse($payload, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/books/{serialNumber}', name: 'book_delete', methods: ['DELETE'])]
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

        $book->setIsBorrowed(true);
        $book->setBorrowerCardNumber((string) $data['borrowerCardNumber']);
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
        $payload = $serializer->serialize($book, 'json', ['groups' => ['book:read']]);

        return new JsonResponse($payload, Response::HTTP_OK, [], true);
    }

    #[Route('/api/books/{serialNumber}/return', name: 'book_return', methods: ['PATCH'])]
    public function returnBook(string $serialNumber, SerializerInterface $serializer): JsonResponse
    {
        $book = $this->em->getRepository(Book::class)->findOneBy(['serialNumber' => $serialNumber]);
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found.'], Response::HTTP_NOT_FOUND);
        }

        if (!$book->isBorrowed()) {
            return new JsonResponse(['error' => 'Book is not currently borrowed.'], Response::HTTP_BAD_REQUEST);
        }

        $book->setIsBorrowed(false);
        $book->setBorrowerCardNumber(null);
        $book->setBorrowedAt(null);

        $this->em->flush();
        $payload = $serializer->serialize($book, 'json');

        return new JsonResponse($payload, Response::HTTP_OK, [], true);
    }
}
