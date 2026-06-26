<?php

namespace App\DTO;

final readonly class CreateBookDTO
{
    public function __construct(
        public readonly string $serialNumber,
        public readonly string $title,
        public readonly string $author,
    ) {
    }
}
