<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return Book[]
     */
    public function getBooksAfterId(int $lastId): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.reader', 'r')
            ->addSelect('r')
            ->where('b.id > :lastId')
            ->setParameter('lastId', $lastId)
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
