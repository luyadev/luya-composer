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
    
    private $_vendorDir = null;
    
    protected $io;
    
    protected $composer;
    
    public function activate(Composer $composer, IOInterface $io)
    {
        // register the installer which extras luya specific config data from extras
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
        
        if ($composer->getConfig()) {
            $this->_vendorDir = rtrim($composer->getConfig()->get('vendor-dir'), '/');
        }
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
            if (!is_link('luya') && !is_file('luya')) {
                // oppress exception for windows system (https://github.com/luyadev/luya/issues/1694) 
                @symlink($this->_vendorDir . DIRECTORY_SEPARATOR . 'luyadev/luya-core/bin/luya', 'luya');
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
