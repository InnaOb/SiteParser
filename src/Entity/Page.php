<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 500)]
    private string $url;

    #[ORM\Column(type: 'integer')]
    private int $imagesCount;

    #[ORM\Column(type: 'float')]
    private float $processingTime;

    public function __construct(string $url, int $imagesCount, float $processingTime)
    {
        $this->url = $url;
        $this->imagesCount = $imagesCount;
        $this->processingTime = $processingTime;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImagesCount(): int
    {
        return $this->imagesCount;
    }

    public function getProcessingTime(): float
    {
        return $this->processingTime;
    }
}
