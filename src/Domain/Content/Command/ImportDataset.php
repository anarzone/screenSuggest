<?php

namespace App\Domain\Content\Command;

use App\Domain\Content\Service\CSVConverterService;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-dataset',
    description: 'Import movie dataset from Kaggle'
)]
class ImportDataset extends Command
{
    public function __construct(
        private readonly CSVConverterService $tsvConverterService
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Importing movie dataset from Kaggle');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Start importing movie dataset');

        $this->tsvConverterService->bulkImportMoviesWithRelations();

        $output->writeln("<info>Imported movies successfully.</info>");

        return Command::SUCCESS;
    }
}
