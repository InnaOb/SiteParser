<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function save(Page $page): void
    {
        $this->_em->persist($page);
        $this->_em->flush();
    }

    public function findAllSortedByImagesCount(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.imagesCount', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
