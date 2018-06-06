<?php

namespace luya\composer\tests;

use luya\composer\Plugin;
use Composer\Installer\PackageEvent;
use Composer\Script\Event;

class LuyaComposerPluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    protected $plugin;
    
    protected function setUp()
    {
        $this->plugin = new Plugin();
        $this->plugin->linkPath = __DIR__ . '/data/luya';
    
        return parent::setUp();
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
        
        $this->assertCount(4, $this->plugin->getSubscribedEvents());
        
        $packageEvent = $this->getMockBuilder(PackageEvent::class)->disableOriginalConstructor()->getMock();
    
        // @todo: own test
        $this->plugin->findCoreRepo($packageEvent);
    }
    
    public function testPostUpdateScript()
    {
        $this->invokeSetProperty($this->plugin, '_packageInstalls', [
            'luyadev/luya-module-admin',
            'luyadev/luya-core',
            'luyadev/luya-foo',
        ]);
    
        $scriptEvent = new Event('post-update', $this->composer, $this->io);
    
        $this->plugin->postUpdateScript($scriptEvent);
    
        $luyaLinkTarget = @readlink(__DIR__ . '/data/luya');
        $this->assertNotFalse($luyaLinkTarget, 'Luya file link missing.');
        $this->assertStringStartsNotWith('/', $luyaLinkTarget, 'Link target should not be a absolute path.');
    }
    
}
