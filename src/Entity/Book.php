<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'books')]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 6, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 6)]
    #[Assert\Regex(pattern: '/^[0-9]{6}$/', message: 'Serial number must be exactly 6 digits.')]
    private string $serialNumber;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $author;

    #[ORM\Column(type: 'boolean')]
    private bool $isBorrowed = false;

    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    #[Assert\Length(min: 6, max: 6)]
    #[Assert\Regex(pattern: '/^[0-9]{6}$/', message: 'Card number must be exactly 6 digits.')]
    private ?string $borrowerCardNumber = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $borrowedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function isBorrowed(): bool
    {
        return $this->isBorrowed;
    }

    public function setIsBorrowed(bool $isBorrowed): self
    {
        $this->isBorrowed = $isBorrowed;

        return $this;
    }

    public function getBorrowerCardNumber(): ?string
    {
        return $this->borrowerCardNumber;
    }

    public function setBorrowerCardNumber(?string $borrowerCardNumber): self
    {
        $this->borrowerCardNumber = $borrowerCardNumber;

        return $this;
    }

    public function getBorrowedAt(): ?\DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function setBorrowedAt(?\DateTimeImmutable $borrowedAt): self
    {
        $this->borrowedAt = $borrowedAt;

        return $this;
    }
}
