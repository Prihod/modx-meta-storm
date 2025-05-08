<?php

namespace MetaStorm;

class MetaGenerator
{
    public static function generate(string $path, string $templatePath, bool $force = false, bool $verbose = false): void
    {
        $corePos = strpos($path, 'core/');
        if ($corePos === false) {
            $projectRoot = $path;
        } else {
            $projectRoot = substr($path, 0, $corePos);
        }

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

        if ($verbose) {
            echo "Scanning directory: $path\n";
        }

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

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
                $metaFilePath = $dir . '/';

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
                $relativePath = str_replace('\\', '/', str_replace($projectRoot, '', $schemaPath));
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