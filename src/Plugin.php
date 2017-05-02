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
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\DependencyResolver\Operation\InstallOperation;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var array noted package updates.
     */
    private $_packageUpdates = [];
    
    private $_packageInstalls = [];
    
    private $_vendorDir = null;
    
    protected $io;
    
    protected $composer;
    
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->_vendorDir = rtrim($composer->getConfig()->get('vendor-dir'), '/');
        
        $io->write('LUYA Composer Plugin INIT: ' . $this->_vendorDir);
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
            symlink($this->_vendorDir . DIRECTORY_SEPARATOR . 'luyadev/luya-core/bin/luya', 'luya');   
        }
    }
    
    public function postUpdatePackage(PackageEvent $event)
    {
        $operation = $event->getOperation();
        if ($operation instanceof UpdateOperation) {
            $this->_packageUpdates[$operation->getInitialPackage()->getName()] = [
                'from' => $operation->getInitialPackage()->getVersion(),
                'fromPretty' => $operation->getInitialPackage()->getPrettyVersion(),
                'to' => $operation->getTargetPackage()->getVersion(),
                'toPretty' => $operation->getTargetPackage()->getPrettyVersion(),
                'direction' => $event->getPolicy()->versionCompare(
                    $operation->getInitialPackage(),
                    $operation->getTargetPackage(),
                    '<'
                    ) ? 'up' : 'down',
            ];
        }
        
        if ($operation instanceof InstallOperation) {
            $this->_packageInstalls[] = $operation->getPackage()->getName();
        }
    }
}