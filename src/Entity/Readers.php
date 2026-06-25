<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'readers')]
class Readers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $email;

    #[ORM\Column(type: 'string', length: 6, nullable: false)]
    #[Assert\Length(min: 6, max: 6)]
    #[Assert\Regex(pattern: '/^[0-9]{6}$/', message: 'Card number must be exactly 6 digits.')]
    private string $borrowerCardNumber;

    #[ORM\OneToMany(mappedBy: 'reader', targetEntity: Book::class)]
    private Collection $books;

    public function __construct(string $name, string $email, string $borrowerCardNumber)
    {
        $this->name = $name;
        $this->email = $email;
        $this->borrowerCardNumber = $borrowerCardNumber;
        $this->books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    #[Ignore]
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->setReader($this);
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        if ($this->books->removeElement($book)) {
            if ($book->getReader() === $this) {
                $book->setReader(null);
            }
        }

        return $this;
    }
}