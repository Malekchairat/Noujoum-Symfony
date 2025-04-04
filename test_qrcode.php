<?php

require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

$builder = new Builder();
$result = $builder->build(
    writer: new PngWriter(),
    data: 'Test QR Code',
    size: 150,
    margin: 10
);

$outputFile = __DIR__ . '/qrcode_test.png';
$result->saveToFile($outputFile);

echo "QR code generated and saved to $outputFile\n";
