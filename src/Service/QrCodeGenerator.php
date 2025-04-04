<?php

namespace App\Service;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeGenerator
{
    /**
     * Generate a QR code and save it to the given file path.
     *
     * @param string $data     The data to encode.
     * @param string $filePath The absolute file path where the QR code image will be saved.
     * @param int    $size     The size of the QR code (default 150).
     * @param int    $margin   The margin around the QR code (default 10).
     */
    public function generateAndSave(string $data, string $filePath, int $size = 150, int $margin = 10): void
    {
        $builder = new Builder();
        $result = $builder->build(
            writer: new PngWriter(),
            data: $data,
            size: $size,
            margin: $margin
        );

        $result->saveToFile($filePath);
    }
}
