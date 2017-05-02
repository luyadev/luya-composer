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

class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected $io;
    protected $composer;
    
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        
        $io->write('LUYA Composer Plugin INIT');
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
        $event->getIO()->write('Scripts has been Updated/Installed.');
    }
    
    public function postUpdatePackage(PackageEvent $event)
    {
        $event->getIO()->write('Package Event');
        $operation = $event->getOperation();
        if ($operation instanceof UpdateOperation) {
            $this->io->write($operation->getInitialPackage());
        }
    }
}