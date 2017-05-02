<?php

namespace luya\composer;

use Composer\Plugin\PluginInterface;
use Composer\IO\IOInterface;
use Composer\Composer;
use Composer\Script\ScriptEvents;
use Composer\Script\Event;
use Composer\Installer\PackageEvents;
use Composer\Installer\PackageEvent;

class Plugin implements PluginInterface
{
    protected $io;
    protected $composer;
    
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        
        $io->write('hi');
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_UPDATE_CMD => 'postUpdateScript',
            PackageEvents::POST_PACKAGE_INSTALL => 'postUpdatePackage'
        ];
    }
    
    public function postUpdateScript(Event $event)
    {
        $this->io->write('Hello World');
        var_dumP('postUpdateScript');
    }
    
    public function postUpdatePackage(PackageEvent $event)
    {
        $operation = $event->getOperation();
        if ($operation instanceof UpdateOperation) {
            var_dump($operation->getInitialPackage());
        }
    }
}