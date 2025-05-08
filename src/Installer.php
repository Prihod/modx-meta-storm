<?php
namespace MetaStorm;

class Installer
{
    public static function postInstall()
    {
        $projectRoot = getcwd();
        $schemaPath = "{$projectRoot}/core/model/schema";

        if(file_exists($schemaPath)) {
            $force = getenv('FORCE') === 'true';
            $templatePath = __DIR__ . '/../templates/meta-storm.xml.tpl';

            MetaGenerator::generate($schemaPath, $templatePath, $force);
        }
    }
}
