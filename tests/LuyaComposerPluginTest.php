<?php

namespace luya\composer\tests;

use luya\composer\Plugin;
use Composer\Installer\PackageEvent;
use Composer\Script\Event;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use luya\composer\Installer;
use Composer\DependencyResolver\DefaultPolicy;

class LuyaComposerPluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    protected $plugin;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->plugin = new Plugin();
        $this->plugin->linkPath = __DIR__ . '/data/luya';
    
        @unlink($this->plugin->linkPath);
        @mkdir(__DIR__ . '/data/tmp/vendor/luyadev/luya-core/bin', 0755, true);
        touch(__DIR__ . '/data/tmp/vendor/luyadev/luya-core/bin/luya');
    }
    
    protected function tearDown()
    {
        @unlink($this->plugin->linkPath);
        @unlink(__DIR__ . '/data/tmp/vendor/luyadev/luya-core/bin/luya');
    
        $this->plugin = null;
        parent::tearDown();
    }
    
    public function testSubscribeEvents()
    {
        $this->plugin->activate($this->composer, $this->io);
        
        $this->assertCount(4, $this->plugin->getSubscribedEvents());
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
    
        $this->assertNotFalse(is_link(__DIR__ . '/data/luya'), 'Luya file should be a link.');
        
        $luyaLinkTarget = @readlink(__DIR__ . '/data/luya');
        $this->assertNotFalse($luyaLinkTarget, 'Luya file link missing.');
        $this->assertStringStartsNotWith('/', $luyaLinkTarget, 'Link target should not be a absolute path.');
    }

    public function testIgnoreOptionForSymlink()
    {
        $plugin = new Plugin($this->io, $this->composer);

        $root = new RootPackage('archivertest/archivertest', 'master', 'master');
        $root->setExtra(['foo' => 'bar']);
        $this->assertFalse($plugin->ensureLuyaExtraSectionSymlinkIsDisabled($root));
        

        /*
        $this->composer->setPackage($root);

        $scriptEvent = new Event('post-update', $this->composer, $this->io);
        $this->plugin->postUpdateScript($scriptEvent);
        */

        

        $luya = new RootPackage('archivertest/archivertest', 'master', 'master');
        $luya->setExtra([Installer::LUYA_EXTRA => [
            Plugin::LUYA_SYMLINK => false,
        ]]);
        $this->assertTrue($plugin->ensureLuyaExtraSectionSymlinkIsDisabled($luya));


        $luyaTrue = new RootPackage('archivertest/archivertest', 'master', 'master');
        $luyaTrue->setExtra([Installer::LUYA_EXTRA => [
            Plugin::LUYA_SYMLINK => true,
        ]]);
        $this->assertFalse($plugin->ensureLuyaExtraSectionSymlinkIsDisabled($luyaTrue));
    }

    public function testPackageHasDisabledSymlink()
    {
        $scriptEvent = new Event('post-update', $this->composer, $this->io);
       
        $plugin = new Plugin();
        $plugin->packageHasDisabledSymlink = true;
        $this->assertNull($plugin->postUpdateScript($scriptEvent)); // well... yes
    }
}
