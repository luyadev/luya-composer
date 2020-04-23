<?php

namespace luya\composer\tests;

use Composer\Autoload\AutoloadGenerator;
use Composer\Composer;
use Composer\Config;
use Composer\Package\CompletePackage;
use Composer\Plugin\PluginManager;
use Composer\Util\Filesystem;
use Composer\Util\HttpDownloader;
use Composer\Util\Loop;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public $directory = 'data/tmp';
    
    /**
     * @var Composer
     */
    protected $composer;
    
    /**
     * @var PluginManager
     */
    protected $pm;
    
    /**
     * @var AutoloadGenerator
     */
    protected $autoloadGenerator;
    
    /**
     * @var CompletePackage[]
     */
    protected $packages;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $im;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $io;
    
    protected function setUp()
    {
        $this->packages = [];
        
        $dm = $this->getMockBuilder('Composer\Downloader\DownloadManager')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->repository = $this->getMockBuilder('Composer\Repository\InstalledRepositoryInterface')->getMock();
        
        $rm = $this->getMockBuilder('Composer\Repository\RepositoryManager')
            ->disableOriginalConstructor()
            ->getMock();
        $rm->expects($this->any())
            ->method('getLocalRepository')
            ->will($this->returnValue($this->repository));
        


        $config = new Config();
        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();

        $downloader = new HttpDownloader($this->io, $config);
        $im = $this->getMockBuilder('Composer\Installer\InstallationManager')->setConstructorArgs([new Loop($downloader), $this->io])->getMock();
        $im->expects($this->any())
            ->method('getInstallPath')
            ->will($this->returnCallback(function ($package) {
                return __DIR__.'/data/'.$package->getPrettyName();
            }));
        
        
        $dispatcher = $this->getMockBuilder('Composer\EventDispatcher\EventDispatcher')->disableOriginalConstructor()->getMock();
        $this->autoloadGenerator = new AutoloadGenerator($dispatcher);
        
        $this->composer = new Composer();
        $this->composer->setConfig($config);
        $this->composer->setDownloadManager($dm);
        $this->composer->setRepositoryManager($rm);
        $this->composer->setInstallationManager($im);
        $this->composer->setAutoloadGenerator($this->autoloadGenerator);
        
        $this->pm = new PluginManager($this->io, $this->composer);
        $this->composer->setPluginManager($this->pm);
        
        $config->merge(array(
            'config' => array(
                'vendor-dir' => $this->directory . '/vendor',
                'home' => $this->directory,
                'bin-dir' => $this->directory . '/bin',
            ),
        ));
    }
    
    protected function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->removeDirectory($this->directory);
        
        $this->composer = null;
        $this->io = null;
        $this->pm = null;
        $this->autoloadGenerator = null;
        $this->repository = null;
        $this->packages = null;
        
        parent::tearDown();
    }
    
    /**
     * Invokes a inaccessible method.
     * @param $object
     * @param $method
     * @param array $args
     * @param bool $revoke whether to make method inaccessible after execution
     * @return mixed
     */
    protected function invokeMethod($object, $method, $args = [], $revoke = true)
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);
        if ($revoke) {
            $method->setAccessible(false);
        }
        return $result;
    }
    
    /**
     * Invokes a inaccessible method.
     * @param $object
     * @param $property
     * @param mixed $value
     * @param bool $revoke whether to make method inaccessible after execution
     * @return mixed
     */
    protected function invokeSetProperty($object, $property, $value, $revoke = true)
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $result = $property->setValue($object, $value);
        if ($revoke) {
            $property->setAccessible(false);
        }
        return $result;
    }
}
