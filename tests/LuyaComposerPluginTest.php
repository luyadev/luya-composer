<?php

namespace luya\composer\tests;

use Composer\TestCase;
use luya\composer\Plugin;
use Composer\Installer\PackageEvent;
use Composer\Script\Event;

class LuyaComposerPluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    protected $plugin;
    /**
     * @var Composer
     */
    protected $composer;
    /**
     * @var IOInterface
     */
    protected $io;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $package;
    
    protected function setUp()
    {
        $this->plugin = new Plugin();
        $this->composer = $this->getMockBuilder('Composer\Composer')->getMock();
        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();
        $this->package = $this->getMockBuilder('Composer\Package\RootPackageInterface')->getMock();
    }
    
    protected function tearDown()
    {
        $this->plugin = null;
        $this->composer = null;
        $this->io = null;
    }
    
    public function testSubscribeEvents()
    {
        $this->plugin->activate($this->composer, $this->io);
        
        $this->assertCount(3, $this->plugin->getSubscribedEvents());
        
        $packageEvent = $this->getMockBuilder(PackageEvent::class)->disableOriginalConstructor()->getMock();
        
        $this->plugin->findCoreRepo($packageEvent);
        
        $scriptEvent = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
        
        $this->plugin->postUpdateScript($scriptEvent);
    }
}
