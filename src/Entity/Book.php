<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'books')]
#[ORM\Entity(repositoryClass: BookRepository::class)]
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

    #[ORM\ManyToOne(targetEntity: Readers::class, inversedBy: 'books')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Readers $reader = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $borrowedAt = null;

    public function __construct(
        string $serialNumber,
        string $title,
        string $author)
    {
        $this->serialNumber = $serialNumber;
        $this->title = $title;
        $this->author = $author;
    }

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

    public function getReader(): ?Readers
    {
        return $this->reader;
    }

    public function setReader(?Readers $reader): self
    {
        $this->reader = $reader;

        return $this;
    }

    public function isBorrowed(): bool
    {
        return null !== $this->reader;
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
