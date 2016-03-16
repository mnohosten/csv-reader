<?php

include __DIR__ . "/../vendor/autoload.php";

$csv = new \Mnohosten\CsvReader(
    __DIR__ . "/data/example.csv",
    null,
    ';'
);
$csv->initHeader(true);

foreach ($csv as $row) {
    var_dump($row);
}
