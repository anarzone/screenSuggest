<?php

namespace App\Domain\Content\Service;

use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Panther\Client;

#[Deprecated("This service is not yet implemented")]
readonly class ScrapingService
{
    public function __construct(
//        private Client $client
        private KernelInterface $appKernel
    ) {
    }

    public function scrape(): string
    {
        $rootDir = $this->appKernel->getProjectDir();

        $client = Client::createChromeClient($rootDir.'/drivers/chromedriver');
        $api_key = "3ESG9L48QM7HSKZDCR7GVK8NNWAVJTV0OQ9NFG61CAK3F39J5RCRK5ZQ23SSQML9T0KMIU1VC3YV9G1D";
        $url = "https://app.scrapingbee.com/api/v1/?api_key=$api_key&url=https://imdb.com/chart/top";
        $response = $client
            ->get($url)
            ->getResponse();

        // @Todo: make this service work. For now we continue with downloading the tsv files from imdb directly
    }
}