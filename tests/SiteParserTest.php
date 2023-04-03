<?php

declare(strict_types=1);

namespace App\Tests;

use App\Repository\PageRepository;
use App\Service\SiteParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SiteParserTest extends TestCase
{
    private SiteParser $siteParser;

    protected function setUp(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $pageRepositoryMock = $this->createMock(PageRepository::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $this->siteParser = new SiteParser(
            $httpClientMock,
            $pageRepositoryMock,
            $loggerMock
        );
    }

    public function testSetBaseUrlThrowsExceptionForInvalidUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL provided');

        $this->siteParser->setBaseUrl('invalid_url');
    }


    public function testParseSiteThrowsExceptionForUnsetBaseUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Base URL is not set');

        $this->siteParser->parseSite();
    }

}
