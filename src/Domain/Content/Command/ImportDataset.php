<?php

namespace App\Domain\Content\Command;

use App\Domain\Content\Service\TSVConverterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-dataset',
    description: 'Import movie dataset from Kaggle'
)]
class ImportContentDataset extends Command
{
    public function __construct(private readonly TSVConverterService $tsvConverterService)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Importing movie dataset from Kaggle');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Start importing movie dataset');

        $dir = dirname(__DIR__, 3) . '/Domain/Content/dataset/';
        $contents = fopen($dir . 'TMDB_all_movies.csv', 'r');
        dd($contents);
        $data = [];
        while (false !== ($line = fgets($contents))) {
            // Process $line, e.g split it into values since it is CSV.
            $values = explode("\t", trim($line, "\n"));
            $data[] = $values;
            print_r($data);
        }

        fclose($contents);


        return Command::SUCCESS;
    }
}
