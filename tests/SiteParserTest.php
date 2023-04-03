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

    public function testSetBaseUrl(): void
    {
        $url = 'https://example.com';

        $this->siteParser->setBaseUrl($url);

        $this->assertSame($url, $this->getPrivatePropertyValue($this->siteParser, 'baseUrl'));
    }

    public function testSetBaseUrlThrowsExceptionForInvalidUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL provided');

        $this->siteParser->setBaseUrl('invalid_url');
    }

    public function testSetMaxDepth(): void
    {
        $depth = 3;

        $this->siteParser->setMaxDepth($depth);

        $this->assertSame($depth, $this->getPrivatePropertyValue($this->siteParser, 'maxDepth'));
    }

    public function testSetTimeout(): void
    {
        $timeout = 10;

        $this->siteParser->setTimeout($timeout);

        $this->assertSame($timeout, $this->getPrivatePropertyValue($this->siteParser, 'timeout'));
    }

    public function testSetLimit(): void
    {
        $limit = 100;

        $this->siteParser->setLimit($limit);

        $this->assertSame($limit, $this->getPrivatePropertyValue($this->siteParser, 'limit'));
    }

    public function testParseSiteThrowsExceptionForUnsetBaseUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Base URL is not set');

        $this->siteParser->parseSite();
    }

    /**
     * @throws ReflectionException
     */
    private function getPrivatePropertyValue(object $object, string $property)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        return $property->getValue($object);
    }
}
