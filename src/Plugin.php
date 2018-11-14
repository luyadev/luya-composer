<?php

namespace luya\composer;

use Composer\Plugin\PluginInterface;
use Composer\IO\IOInterface;
use Composer\Composer;
use Composer\Script\ScriptEvents;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\PackageEvents;

/**
 * LUYA Composer Plugin.
 *
 * Events: https://getcomposer.org/doc/articles/scripts.md#event-names
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    private $_packageInstalls = [];
    
    private $_relativeVendorDir = null;
    
    protected $io;
    
    protected $composer;
    
    /**
     * Filename of the symlink with target to the `vendor/bin/luya`.
     *
     * @since 1.0.4
     */
    public $linkPath = 'luya';
    
    /**
     * Read the relative vendor-dir from composer config.
     *
     * @return string
     * @since 1.0.4
     */
    public function getRelativeVendorDir(Composer $composer)
    {
        if ($this->_relativeVendorDir === null) {
            $this->_relativeVendorDir = rtrim($composer->getConfig()->get('vendor-dir', \Composer\Config::RELATIVE_PATHS), '/');
        }
    
        return $this->_relativeVendorDir;
    }
    
    public function activate(Composer $composer, IOInterface $io)
    {
        // register the installer which extras luya specific config data from extras
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'postUpdateScript',
            ScriptEvents::POST_UPDATE_CMD => 'postUpdateScript',
            ScriptEvents::POST_CREATE_PROJECT_CMD => 'postUpdateScript',
            PackageEvents::POST_PACKAGE_INSTALL => 'findCoreRepo',
        ];
    }
    
    public function postUpdateScript(Event $event)
    {
        if (in_array('luyadev/luya-core', $this->_packageInstalls)) {
            if (!is_link($this->linkPath) && !is_file($this->linkPath)) {
                // oppress exception for windows system (https://github.com/luyadev/luya/issues/1694)
                @symlink($this->getRelativeVendorDir($event->getComposer()) . DIRECTORY_SEPARATOR . 'luyadev/luya-core/bin/luya', $this->linkPath);
            }
        }
    }
    
    public function findCoreRepo(PackageEvent $event)
    {
        $operation = $event->getOperation();
        
        if ($operation instanceof InstallOperation) {
            $this->_packageInstalls[] = $operation->getPackage()->getName();
        }
    }
}
