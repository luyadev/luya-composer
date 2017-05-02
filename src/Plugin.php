<?php

namespace luya\composer;

use Composer\Plugin\PluginInterface;
use Composer\IO\IOInterface;
use Composer\Composer;
use Composer\Script\ScriptEvents;
use Composer\Script\Event;
use Composer\Installer\PackageEvents;
use Composer\Installer\PackageEvent;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\DependencyResolver\Operation\InstallOperation;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private $_packageInstalls = [];
    
    private $_vendorDir = null;
    
    protected $io;
    
    protected $composer;
    
    public function activate(Composer $composer, IOInterface $io)
    {
        if ($composer->getConfig()) {
            $this->_vendorDir = rtrim($composer->getConfig()->get('vendor-dir'), '/');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'postUpdateScript',
            ScriptEvents::POST_UPDATE_CMD => 'postUpdateScript',
            PackageEvents::POST_PACKAGE_INSTALL => 'postUpdatePackage',
            PackageEvents::POST_PACKAGE_UPDATE => 'postUpdatePackage',
        ];
    }
    
    public function postUpdateScript(Event $event)
    {
        if (in_array('luyadev/luya-core', $this->_packageInstalls)) {
            if (!is_link('luya') && !is_file('luya')) {
                symlink($this->_vendorDir . DIRECTORY_SEPARATOR . 'luyadev/luya-core/bin/luya', 'luya');
            }
        }
    }
    
    public function postUpdatePackage(PackageEvent $event)
    {
        $operation = $event->getOperation();
        
        if ($operation instanceof InstallOperation) {
            $this->_packageInstalls[] = $operation->getPackage()->getName();
        }
    }
}