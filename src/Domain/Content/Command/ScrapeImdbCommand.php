<?php

namespace App\Domain\Content\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:scrape-imdb')]
class ScrapeImdbCommand extends Command
{
    public function __construct(private HttpClientInterface $httpClient)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Scrape IMDB for titles and ratings')
            ->setHelp('This command allows you to scrape IMDB for titles and ratings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = 'https://www.imdb.com/chart/top/';
        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() !== 200){
            $output->writeln('Failed to fetch the page');
            return Command::FAILURE;
        }

        $htmlContent = $response->getContent();

        $crawler = new Crawler($htmlContent);

        $movies = $crawler->filter('div.ipc-page-content-container.ipc-page-content-container--center > section > div > div.ipc-page-grid.ipc-page-grid--bias-left > div > ul')->each(function (Crawler $node) {
            $title = $node->filter('a.ipc-title-link-wrapper > h3.ipc-title__text')->text();
//            $rating = $node->filter('.imdbRating strong')->text();
            return [
                'title' => $title,
//                'rating' => $rating,
            ];
        });


        // Output the results
        foreach ($movies as $movie) {
            $output->writeln($movie['title']);
        }

        return Command::SUCCESS;
    }
}