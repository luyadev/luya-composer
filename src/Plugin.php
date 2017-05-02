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
            ScriptEvents::POST_UPDATE_CMD => 'postUpdateScript',
            PackageEvents::POST_PACKAGE_INSTALL => 'postUpdatePackage',
            PackageEvents::POST_PACKAGE_UPDATE => 'postUpdatePackage',
        ];
    }
    
    public function postUpdateScript(Event $event)
    {
        $event->getIO()->write('Update Script');
        $this->io->write('Update Script 2');
    }
    
    public function postUpdatePackage(PackageEvent $event)
    {
        $event->getIO()->write('Package Script');
        $this->io->write('Package Script 2');
        $operation = $event->getOperation();
        if ($operation instanceof UpdateOperation) {
            var_dump($operation->getInitialPackage());
        }
    }
}