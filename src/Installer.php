<?php

namespace MetaStorm;

use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\Event;


class Installer implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            'post-install-cmd' => 'onPostInstall',
            'post-update-cmd' => 'onPostInstall',
        ];
    }

    public function onPostInstall(Event $event): void
    {
        $io = $event->getIO();
        Installer::install($io->isVerbose());
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {

    }

    public static function install(bool $verbose = false): void
    {
        if ($verbose) {
            echo "Running MetaStorm post-install script...\n";
        }

        $projectRoot = dirname(__DIR__, 4);
        $modxSchemaPath = $projectRoot . '/core/model/schema';

        if (!file_exists($modxSchemaPath)) {
            if ($verbose) {
                echo "MODX structure not detected, skipping initial generation.\n";
            }
            return;
        }

        if ($verbose) {
            echo "MODX structure detected, generating .meta-storm.xml files...\n";
        }

        $templatePath = dirname(__DIR__) . '/templates/meta-storm.xml.tpl';

        MetaGenerator::generate($modxSchemaPath, $templatePath, true, $verbose);

        if ($verbose) {
            echo "MetaStorm post-install process completed.\n";
        }
    }
}