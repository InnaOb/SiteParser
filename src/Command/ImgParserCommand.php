<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\SiteParser;
use App\Service\Validation\InputValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'img-parser',
    description: 'Parse images on a website'
)]
class ImgParserCommand extends Command
{

    public function __construct(
        private readonly SiteParser      $siteParser,
        private readonly LoggerInterface $logger,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('url', InputArgument::REQUIRED, 'The URL of the site to parse');
        $this->addArgument('depth', InputArgument::OPTIONAL, 'The maximum depth to parse (default: 3)', 3);
        $this->addArgument('timeout', InputArgument::OPTIONAL, 'The timeout for each page request in seconds (default: 10)', 10);
        $this->addArgument('limit', InputArgument::OPTIONAL, 'The maximum number of pages to parse (default: 1000)', 1000);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $inputValidator = new InputValidator($input);
            $inputValidator->validate();
        } catch (\Exception $e) {
            $this->logger->error('Invalid input', ['error' => $e->getMessage()]);
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
        $this->siteParser->setBaseUrl($input->getArgument('url'));
        $this->siteParser->setMaxDepth((int)$input->getArgument('depth'));
        $this->siteParser->setTimeout((int)$input->getArgument('timeout'));
        $this->siteParser->setLimit((int)$input->getArgument('limit'));

        $this->logger->info('Starting site parsing', ['baseUrl' => $input->getArgument('url')]);
        $this->siteParser->parseSite();
        $this->logger->info('Site parsing completed', ['baseUrl' => $input->getArgument('url')]);

        $output->writeln('<info>Site parsing completed</info>');

        return Command::SUCCESS;
    }
}
