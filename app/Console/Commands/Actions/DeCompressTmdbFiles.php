<?php

namespace App\Console\Commands\Actions;

use Illuminate\Support\Facades\Storage;

class DeCompressTmdbFiles
{
    public function __invoke(array $files): void
    {

        foreach ($files as $file) {
            // Check if the file exists
            if (!Storage::exists($file)) {
                echo 'file doesnt exist\n';
                return;
            }

            // Check if the file has .gz extension
            if (!preg_match('/\.gz$/', $file)) {
                echo("File '{$file}' is not a .gz file.\n");
                return;
            }

            $gzFile = Storage::path($file);
            $outputFile = str_replace('.gz', '', $gzFile);
            $outputFile = str_replace('compressed', 'decompressed', $outputFile);

            if (file_exists($outputFile)) {
                echo("Output file '{$outputFile}' already exists. Skipping decompression.\n");
                return;
            }

            $this->processFile($gzFile, $outputFile);

            echo("File '{$file}' decompressed to '{$outputFile}'.\n");
        }

    }

    private function processFile($gzFile, $outputFile): void
    {

        $gz = gzopen($gzFile, 'rb');
        $output = fopen($outputFile, 'wb');

        while (!gzeof($gz)) {
            fwrite($output, gzread($gz, 4096));
        }

        gzclose($gz);
        fclose($output);
    }
}
