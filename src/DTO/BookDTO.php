<?php

namespace App\DTO;

final readonly class BookDTO
{
    public function __construct(
        public readonly string $serialNumber,
        public readonly string $title,
        public readonly string $author,
        public readonly ?string $readerCardNumber = null,
        public readonly ?string $borrowedAt = null,
        public readonly bool $borrowed = false,
    ) {
    }
}
