<?php

namespace MetaStorm;

use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\Event;


class Installer implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $io->write("MetaStorm plugin activated!");
    }

    public static function getSubscribedEvents()
    {
        return [
            'post-install-cmd' => 'onPostInstall',
            'post-update-cmd' => 'onPostInstall',
        ];
    }

    public function onPostInstall(Event $event)
    {
        Installer::postInstall();
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {

    }

    public static function postInstall()
    {
        echo "Running MetaStorm post-install script...\n";
        $projectRoot = dirname(__DIR__, 4);
        $modxSchemaPath = $projectRoot . '/core/model/schema';

        if (!file_exists($modxSchemaPath)) {
            echo "MODX structure not detected, skipping initial generation.\n";
            return;
        }

        echo "MODX structure detected, generating .meta-storm.xml files...\n";

        $templatePath = dirname(__DIR__) . '/templates/meta-storm.xml.tpl';

        MetaGenerator::generate($modxSchemaPath, $templatePath, true, true);

        echo "MetaStorm post-install process completed.\n";
    }
}