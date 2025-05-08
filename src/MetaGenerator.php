<?php

namespace MetaStorm;

class MetaGenerator
{
    public static function generate(string $projectRoot, string $templatePath, bool $force = false, bool $verbose = false): void
    {
        if ($verbose) {
            echo "Starting MetaStorm generator\n";
            echo "Project root: $projectRoot\n";
            echo "Template path: $templatePath\n";
            echo "Force mode: " . ($force ? "enabled" : "disabled") . "\n";
        }

        if (!file_exists($templatePath)) {
            echo "Template 'meta-storm.xml.tpl' not found at: $templatePath\n";
            exit(1);
        }

        $corePath = $projectRoot . '/core/components';

        if ($verbose) {
            echo "Scanning directory: $corePath\n";
        }

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($corePath));

        $directories = [];
        $schemaCount = 0;

        foreach ($rii as $file) {
            if (!$file->isFile() || !preg_match('/\.mysql\.schema\.xml$/', $file->getFilename())) {
                continue;
            }

            $schemaCount++;
            $dir = $file->getPath();
            if (!isset($directories[$dir])) {
                $directories[$dir] = [];
            }
            $directories[$dir][] = $file;

            if ($verbose) {
                echo "Found schema file: " . $file->getPathname() . "\n";
            }
        }

        if ($verbose) {
            echo "Total schema files found: $schemaCount\n";
            echo "Processing schemas...\n";
        }

        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($directories as $dir => $files) {
            foreach ($files as $file) {
                $metaFilePath = $dir . '/' . basename($file->getFilename(), '.mysql.schema.xml');

                if (count($files) > 1) {
                    $metaFilePath .= '.' . preg_replace('/\.mysql\.schema\.xml$/', '', $file->getFilename()) . '.meta-storm.xml';
                } else {
                    $metaFilePath .= '.meta-storm.xml';
                }

                if (!$force && file_exists($metaFilePath)) {
                    echo "Skipped (already exists): $metaFilePath\n";
                    $skippedCount++;
                    continue;
                }

                $schemaPath = $file->getRealPath();
                $relativePath = str_replace('\\', '/', str_replace($projectRoot . '/', '', $schemaPath));
                $schemaName = preg_replace('/\.mysql\.schema\.xml$/', '', $file->getFilename());

                if ($verbose) {
                    echo "Processing schema: $schemaName\n";
                    echo "  - Schema path: $schemaPath\n";
                    echo "  - Relative path: $relativePath\n";
                    echo "  - Output file: $metaFilePath\n";
                }

                $template = file_get_contents($templatePath);
                $xml = str_replace(['{{SCHEMA_NAME}}', '{{RELATIVE_PATH}}'], [$schemaName, $relativePath], $template);

                file_put_contents($metaFilePath, $xml);
                $generatedCount++;

                echo "Generated: $metaFilePath" . ($force ? " (overwritten)" : "") . "\n";
            }
        }

        if ($verbose) {
            echo "\nGeneration complete!\n";
            echo "Generated files: $generatedCount\n";
            echo "Skipped files: $skippedCount\n";
        }
    }
}