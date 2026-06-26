<?php

namespace App\Factory;

use App\DTO\BookDTO;
use App\Entity\Book;

final class BookFactory
{
    public static function create(Book $book): BookDTO
    {
        return new BookDTO(
            $book->getSerialNumber(),
            $book->getTitle(),
            $book->getAuthor(),
            $book->getReader()?->getBorrowerCardNumber(),
            $book->getBorrowedAt() ? $book->getBorrowedAt()->format('Y-m-d H:i:s') : null
        );
    }
}
