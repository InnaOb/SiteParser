<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Page;
use App\Repository\PageRepository;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SiteParser
{
    private string $baseUrl;
    private int $maxDepth;
    private int $timeout;
    private int $limit;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly PageRepository      $pageRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function setBaseUrl(string $baseUrl): void
    {
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid URL provided');
        }

        $this->baseUrl = $baseUrl;
    }

    public function setMaxDepth(int $maxDepth): void
    {
        $this->maxDepth = $maxDepth;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function parseSite(): void
    {
        if (!isset($this->baseUrl)) {
            throw new InvalidArgumentException('Base URL is not set');
        }

        $this->logger->info('Starting site parsing', ['baseUrl' => $this->baseUrl]);

        $visitedPages = [];
        $queue = new \SplQueue();
        $queue->enqueue([$this->baseUrl, 0]);

        while (!$queue->isEmpty()) {
            [$url, $depth] = $queue->dequeue();

            if ($depth > $this->maxDepth) {
                continue;
            }

            if (isset($visitedPages[$url])) {
                continue;
            }

            $visitedPages[$url] = true;

            $this->logger->info('Parsing page', ['url' => $url]);

            $startTime = microtime(true);

            try {
                $response = $this->httpClient->request('GET', $url, [
                    'timeout' => $this->timeout,
                ]);
            } catch (\Throwable $e) {
                $this->logger->error('Error requesting page', ['url' => $url, 'error' => $e->getMessage()]);
                continue;
            }
            $processingTime = microtime(true) - $startTime;
            $contentType = $response->getHeaders()['content-type'][0] ?? '';

            if (strpos($contentType, 'text/html') === false) {
                $this->logger->warning('Skipping non-HTML page', ['url' => $url]);
                continue;
            }

            $content = $response->getContent();
            $imagesCount = substr_count($content, '<img ');

            $this->logger->info('Page parsed', ['url' => $url, 'imagesCount' => $imagesCount]);

            $page = new Page(substr($url, 0, 255), $imagesCount, $processingTime);
            $this->pageRepository->save($page);

            if (count($visitedPages) >= $this->limit) {
                break;
            }

            if ($depth < $this->maxDepth) {
                foreach ($this->getPageLinks($url, $content) as $link) {
                    $queue->enqueue([$link, $depth + 1]);
                }
            }
        }

        $this->logger->info('Site parsing completed', ['baseUrl'=> $this->baseUrl]);
    }

    public function getPageLinks(string $url, string $content): \Generator
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($content);

        $xpath = new \DOMXPath($dom);
        $hrefs = $xpath->evaluate('/html/body//a');

        foreach ($hrefs as $href) {
            $hrefUrl = $href->getAttribute('href');

            if ($hrefUrl === '#' || stripos($hrefUrl, 'javascript:') === 0) {
                continue;
            }

            $hrefUrlParts = parse_url($hrefUrl);

            if (!is_array($hrefUrlParts) || !isset($hrefUrlParts['host'])) {
                continue;
            }

            if (!isset($hrefUrlParts['host'])) {
                $hrefUrl = rtrim($url, '/') . '/' . ltrim($hrefUrl, '/');
            }

            if ($hrefUrlParts['host'] !== parse_url($this->baseUrl)['host'] && strpos($hrefUrlParts['host'], parse_url($this->baseUrl)['host']) === false) {
                continue;
            }

            yield $hrefUrl;
        }
    }
}
