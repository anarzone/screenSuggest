<?php

namespace App\Domain\Content\Command;

use App\Domain\Content\Service\TSVConverterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:convert-tsv',
    description: 'Convert TSV file to array'
)]
class ConvertTSVCommand extends Command
{
    public function __construct(private readonly TSVConverterService $tsvConverterService)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Convert TSV file to array');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Convert TSV file to array');

        $dir = dirname(__DIR__, 3) . '/Domain/Content/data/imdb-dataset/';
        $contents = fopen($dir . 'title.ratings.tsv', 'r');

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