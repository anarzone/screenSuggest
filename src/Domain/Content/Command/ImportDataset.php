<?php

namespace App\Domain\Content\Command;

use App\Domain\Content\Service\CSVConverterService;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-dataset',
    description: 'Import movie dataset from Kaggle'
)]
class ImportDataset extends Command
{
    public function __construct(
        private readonly CSVConverterService $csvConverterService
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Importing movie dataset from Kaggle')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force reload from CSV file even if staging data exists');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($input->getOption('force')) {
                $forceReload = $input->getOption('force');
                $io->info('Force reloading from CSV...');
                $this->csvConverterService->importMovies($forceReload);
            } else {
                $io->info('Processing data from staging table only...');
                // You would need to add a method to process staging only
                $this->csvConverterService->importMovies();
            }

            $io->success('Import completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Import failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

    }
}
