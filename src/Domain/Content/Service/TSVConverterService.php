<?php

namespace App\Domain\Content\Service;

class TSVConverterService
{
    public function convert(string $tsv): array
    {

        $rows = explode("\n", $tsv);
        $header = explode("\t", array_shift($rows));
        $data = [];

        foreach ($rows as $row) {
            $data[] = array_combine($header, explode("\t", $row));
        }

        return $data;
    }
}