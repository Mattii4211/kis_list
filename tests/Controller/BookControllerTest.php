<?php

namespace App\Tests\Controller;

use App\Entity\Book;
use App\Entity\Readers;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }

    public function testCreateBook(): void
    {
        $this->client->request(
            'POST',
            '/api/books',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'serialNumber' => '123456',
                'title' => 'Clean Code',
                'author' => 'Robert Martin',
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('123456', $data['serialNumber']);
        $this->assertEquals('Clean Code', $data['title']);
        $this->assertEquals('Robert Martin', $data['author']);
    }

    public function testListBooks(): void
    {
        $book = new Book(
            '111111',
            'DDD',
            'Eric Evans'
        );

        $this->em->persist($book);
        $this->em->flush();

        $this->client->request('GET', '/api/books');

        $this->assertResponseIsSuccessful();

        $books = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $books);
        $this->assertEquals('111111', $books[0]['serialNumber']);
    }

    public function testDeleteBook(): void
    {
        $book = new Book(
            '222222',
            'Symfony',
            'Fabien'
        );

        $this->em->persist($book);
        $this->em->flush();

        $this->client->request(
            'DELETE',
            '/api/books/222222'
        );

        $this->assertResponseStatusCodeSame(204);

        $deleted = $this->em
            ->getRepository(Book::class)
            ->findOneBy([
                'serialNumber' => '222222',
            ]);

        $this->assertNull($deleted);
    }

    public function testBorrowBook(): void
    {
        $book = new Book(
            '333333',
            'PHP',
            'Rasmus'
        );

        $reader = new Readers(
            'Jan Kowalski',
            'jan@test.pl',
            '999999'
        );

        $this->em->persist($book);
        $this->em->persist($reader);
        $this->em->flush();

        $this->client->request(
            'PATCH',
            '/api/books/333333/borrow',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'borrowerCardNumber' => '999999',
            ])
        );

        $this->assertResponseIsSuccessful();

        $this->em->refresh($book);

        $this->assertNotNull($book->getReader());
        $this->assertEquals(
            '999999',
            $book->getReader()->getBorrowerCardNumber()
        );
    }

    public function testReturnBook(): void
    {
        $reader = new Readers(
            'Jan',
            'jan@test.pl',
            '123123'
        );

        $book = new Book(
            '444444',
            'Refactoring',
            'Martin Fowler'
        );

        $book->setReader($reader);
        $book->setBorrowedAt(new \DateTimeImmutable());

        $this->em->persist($reader);
        $this->em->persist($book);
        $this->em->flush();

        $this->client->request(
            'PATCH',
            '/api/books/444444/return'
        );

        $this->assertResponseIsSuccessful();

        $this->em->refresh($book);

        $this->assertNull($book->getReader());
        $this->assertNull($book->getBorrowedAt());
    }

    public function testBorrowAlreadyBorrowedBook(): void
    {
        $reader1 = new Readers(
            'Jan',
            'jan@test.pl',
            '111111'
        );

        $reader2 = new Readers(
            'Adam',
            'adam@test.pl',
            '222222'
        );

        $book = new Book(
            '555555',
            'DDD',
            'Evans'
        );

        $book->setReader($reader1);

        $this->em->persist($reader1);
        $this->em->persist($reader2);
        $this->em->persist($book);
        $this->em->flush();

        $this->client->request(
            'PATCH',
            '/api/books/555555/borrow',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'borrowerCardNumber' => '222222',
            ])
        );

        $this->assertResponseStatusCodeSame(409);
    }

    public function testBorrowBookReaderNotFound(): void
    {
        $book = new Book(
            '666666',
            'Laravel',
            'Taylor'
        );

        $this->em->persist($book);
        $this->em->flush();

        $this->client->request(
            'PATCH',
            '/api/books/666666/borrow',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'borrowerCardNumber' => '999999',
            ])
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
