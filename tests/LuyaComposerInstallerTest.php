<?php

namespace luya\composer\tests;

use Composer\Package\Package;
use luya\composer\Installer;
use Composer\Installer\PackageEvent;
use Composer\Script\Event;

class LuyaComposerInstallerTest extends TestCase
{
    /**
     * @var Installer
     */
    protected $installer;
    
    protected function setUp()
    {
        parent::setUp();
        $this->installer = new Installer($this->io, $this->composer);
    }
    
    protected function tearDown()
    {
        $this->installer = null;
        parent::tearDown();
    }
    
    public function testEnsureConfig()
    {
        $package = new Package('archivertest/archivertest', 'master', 'master');
        $package->setExtra([
            Installer::LUYA_EXTRA => [
                'blocks' => [
                    'my/extension/blocks'
                ],
                'themes' => [
                    'themes/testTheme'
                ]
            ],
        ]);
        
        /**
         * @var $config array
         * @see Installer::getPackageExtraData()
         */
        $config = $this->invokeMethod($this->installer, 'getPackageExtraData', [$package]);
    
        /**
         * @var $packageConfig array
         * @see Installer::ensureConfig()
         */
        $packageConfig = $this->invokeMethod($this->installer, 'ensureConfig', [$package, $config]);
        
        $expectedConfig = [
            'package' => [
                'isDev' => false,
                'name' => $package->getName(),
                'prettyName' => $package->getPrettyName(),
                'version' => $package->getVersion(),
                'targetDir' => null,
                'installSource' => null,
                'sourceUrl' => null,
                'packageFolder' => $package->getPrettyName(),
            ],
            'blocks' => [
                'data/tmp/vendor/archivertest/archivertest/my/extension/blocks'
            ],
            'bootstrap' => [],
            'themes' => [
                'data/tmp/vendor/archivertest/archivertest/themes/testTheme'
            ],
        ];
        
        $this->assertSame(array_keys($expectedConfig), array_keys($packageConfig), 'Invalid configuration entries.');
        $this->assertSame($expectedConfig['package'], $packageConfig['package'], 'Invalid package configuration.');
        $this->assertSame($expectedConfig['blocks'], $packageConfig['blocks'], 'Invalid block configuration.');
        $this->assertSame($expectedConfig['bootstrap'], $packageConfig['bootstrap'], 'Invalid bootstrap configuration.');
        $this->assertSame($expectedConfig['themes'], $packageConfig['themes'], 'Invalid theme configuration.');
    }

    public function testSupports()
    {
        $this->assertTrue($this->installer->supports(Installer::LUYA_TYPE_CORE));
        $this->assertTrue($this->installer->supports(Installer::LUYA_TYPE_EXTENSION));
        $this->assertTrue($this->installer->supports(Installer::LUYA_TYPE_MODULE));
        $this->assertTrue($this->installer->supports(Installer::LUYA_TYPE_THEME));
        $this->assertFalse($this->installer->supports('package'));
        $this->assertFalse($this->installer->supports('project'));
    }
}
