<?php

namespace App\Controller;

use App\Entity\Readers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ReaderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/readers', name: 'reader_list', methods: ['GET'])]
    public function list(SerializerInterface $serializer): JsonResponse
    {
        $readers = $this->em->getRepository(Readers::class)->findAll();

        return new JsonResponse(
            $serializer->serialize($readers, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
