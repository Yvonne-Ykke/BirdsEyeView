<?php

namespace App\Console\Commands\Support\Actions;

use Illuminate\Support\Facades\Storage;

class DeCompressFiles
{
    public function __invoke(array $files, bool $replaceFiles = false): void
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
            $outputFile = $this->getOutputFile($gzFile);

            if (is_array($outputFile)) {
                continue;
            }

            if ($this->checkOutputFileExists($file, $outputFile, $replaceFiles)) {
                continue;
            }

            $this->processFile($gzFile, $outputFile);

            echo("File '{$file}' decompressed to '{$outputFile}'.\n");
        }

    }

    private function checkOutputFileExists(string $file, string $outputFile, bool $replaceFiles): bool
    {
        if (file_exists($outputFile)) {
            if (!$replaceFiles) {
                echo("Output file '{$outputFile}' already exists. Skipping decompression.\n");
                return true;
            }

            Storage::delete($outputFile);
            echo 'Deleted ' . $file . ' to be replaced';
        }

        return false;
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

    private function getOutputFile(string $gzFile): array|string
    {
        return str_replace(
            search: 'compressed',
            replace: 'decompressed',
            subject: str_replace('.gz', '', $gzFile)
        );
    }
}
