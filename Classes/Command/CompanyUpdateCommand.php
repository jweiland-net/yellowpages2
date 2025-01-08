<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Command;

use JWeiland\Yellowpages2\Service\CompanyUpdateService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CompanyUpdateCommand extends Command
{
    public function __construct(
        private readonly CompanyUpdateService $companyUpdateService
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Hides companies older than 13 months and informs users about entries older than 12 months.')
            ->setHelp('This command processes companies, hides old ones, and sends notifications to users and admins.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting company update process...</info>');

        $this->companyUpdateService->updateCompanies();

        $output->writeln('Company update process completed.');
        return Command::SUCCESS;
    }
}
