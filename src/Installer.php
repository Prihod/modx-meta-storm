<?php

namespace MetaStorm;

class Installer
{
    public static function postInstall()
    {
        echo "Running MetaStorm post-install script...\n";
        $projectRoot = dirname(__DIR__, 4);

        if (!file_exists($projectRoot . '/core/components')) {
            echo "MODX structure not detected, skipping initial generation.\n";
            return;
        }

        echo "MODX structure detected, generating .meta-storm.xml files...\n";

        $templatePath = dirname(__DIR__) . '/templates/meta-storm.xml.tpl';

        MetaGenerator::generate($projectRoot, $templatePath, true, true);

        echo "MetaStorm post-install process completed.\n";
    }
}