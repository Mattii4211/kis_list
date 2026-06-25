<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Readers;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $readers = [
            ['name' => 'John Doe', 'email' => 'john.doe@example.com', 'borrowerCardNumber' => '123456'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@example.com', 'borrowerCardNumber' => '234567'],
        ];

        $books = [
            ['serialNumber' => '123456', 'title' => 'Clean Code', 'author' => 'Robert C. Martin'],
            ['serialNumber' => '234567', 'title' => 'The Pragmatic Programmer', 'author' => 'Andrew Hunt'],
            ['serialNumber' => '345678', 'title' => 'Domain-Driven Design', 'author' => 'Eric Evans'],
            ['serialNumber' => '456789', 'title' => 'Refactoring', 'author' => 'Martin Fowler'],
            ['serialNumber' => '567890', 'title' => 'Design Patterns', 'author' => 'Erich Gamma'],
            ['serialNumber' => '678901', 'title' => 'Test-Driven Development', 'author' => 'Kent Beck'],
            ['serialNumber' => '789012', 'title' => 'Continuous Delivery', 'author' => 'Jez Humble'],
            ['serialNumber' => '890123', 'title' => 'Working Effectively with Legacy Code', 'author' => 'Michael Feathers'],
            ['serialNumber' => '901234', 'title' => 'The Art of Unit Testing', 'author' => 'Roy Osherove'],
            ['serialNumber' => '012345', 'title' => 'Agile Software Development, Principles, Patterns, and Practices', 'author' => 'Robert C. Martin'],
        ];

        foreach ($books as $key => $data) {
            $book = new Book($data['serialNumber'], $data['title'], $data['author']);

            if (isset($readers[$key])) {
                $reader = new Readers($readers[$key]['name'], $readers[$key]['email'], $readers[$key]['borrowerCardNumber']);
                $manager->persist($reader);
                $book->setReader($reader);
                $book->setBorrowedAt(new \DateTimeImmutable());
            }

            $manager->persist($book);
        }

        $manager->flush();
    }
}
