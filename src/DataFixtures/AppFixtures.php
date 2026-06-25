<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $books = [
            ['serialNumber' => '123456', 'title' => 'Clean Code', 'author' => 'Robert C. Martin'],
            ['serialNumber' => '234567', 'title' => 'The Pragmatic Programmer', 'author' => 'Andrew Hunt'],
            ['serialNumber' => '345678', 'title' => 'Domain-Driven Design', 'author' => 'Eric Evans'],
        ];

        foreach ($books as $data) {
            $book = new Book();
            $book->setSerialNumber($data['serialNumber']);
            $book->setTitle($data['title']);
            $book->setAuthor($data['author']);
            $manager->persist($book);
        }

        $manager->flush();
    }
}
