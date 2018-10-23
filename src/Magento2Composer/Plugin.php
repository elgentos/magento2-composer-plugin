<?php

namespace Elgentos\Magento2Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {

    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'check',
        ];
    }

    /**
     * @param Event $event
     */
    public function check(Event $event)
    {
        if (!file_exists('app/etc/config.php')) {
            $event->getIO()->writeError('Magento 2 composer plugin: Cannot read app/etc/config.php');
            return;
        }

        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $extra = $event->getComposer()->getPackage()->getExtra();
        $ignoreModules = isset($extra['magento2-ignore-extensions']) ? $extra['magento2-ignore-extensions'] : (array)$event->getComposer()->getConfig()->get('magento2-ignore-extensions');
        $fileSystem = new Filesystem(new ProcessExecutor($event->getIO()));
        $checker = new Checker($event->getIO(), $fileSystem);
        $checker->check($vendorDir, $ignoreModules);
    }
}