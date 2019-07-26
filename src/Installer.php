<?php

namespace luya\composer;

use Composer\Installer\LibraryInstaller;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Package\PackageInterface;

/**
 * LUYA Package Installer.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.1
 */
class Installer extends LibraryInstaller
{
    const LUYA_EXTRA = 'luya';
    
    const LUYA_FILE = 'luyadev/installer.php';
    
    private $_relativeVendorDir;
    
    public function supports($packageType)
    {
        return $packageType == 'luya-core' || $packageType == 'luya-extension' || $packageType == 'luya-module';
    }
    
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        // install the package the normal composer way
        parent::install($repo, $package);
        
        $this->addPackage($package);
    }
    
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        
        $this->removePackage($initial);
        $this->addPackage($target);
    }
    
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::uninstall($repo, $package);
        
        $this->removePackage($package);
    }
    
    protected function addPackage(PackageInterface $package)
    {
        $this->writeInstaller($this->addConfig($package));
    }
    
    protected function removePackage(PackageInterface $package)
    {
        $this->writeInstaller($this->removeConfig($package));
    }
    
    protected function getInstallers()
    {
        $file = $this->vendorDir . DIRECTORY_SEPARATOR . self::LUYA_FILE;
        
        if (!file_exists($file)) {
            return ['configs' => [], 'timestamp' => time()];
        }
        
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($file, true);
        }
        
        $data = require($file);
        $data['timestamp'] = time();
        return $data;
    }
    
    protected function ensureConfig(PackageInterface $package, array $config)
    {
        $packageConfig = [
            'package' => ['name' => $package->getName(), 'prettyName' => $package->getPrettyName(), 'version' => $package->getVersion()],
            'blocks' => [],
            'bootstrap' => (isset($config['bootstrap'])) ? $config['bootstrap'] : [],
            'themes' => [],
        ];
        
        $blocks = (isset($config['blocks'])) ? $config['blocks'] : [];
    
        foreach ($blocks as $blockFolder) {
            $packageConfig['blocks'][] = $this->getRelativeVendorDir() . DIRECTORY_SEPARATOR . $package->getPrettyName() . DIRECTORY_SEPARATOR . ltrim($blockFolder, '/');
        }
    
        $themes = (isset($config['themes'])) ? $config['themes'] : [];
    
        foreach ($themes as $themeFolder) {
            $packageConfig['themes'][] = $this->getRelativeVendorDir() . DIRECTORY_SEPARATOR . $package->getPrettyName() . DIRECTORY_SEPARATOR . ltrim($themeFolder, '/');
        }
        
        return $packageConfig;
    }
        
    protected function removeConfig(PackageInterface $package)
    {
        $data = $this->getInstallers();
        
        if (isset($data['configs'][$package->getName()])) {
            unset($data['configs'][$package->getName()]);
        }
        
        return $data;
    }
    
    protected function getPackageExtraData(PackageInterface $package)
    {
        if (empty($package->getExtra())) {
            return [];
        }
        
        return isset($package->getExtra()[self::LUYA_EXTRA]) ? $package->getExtra()[self::LUYA_EXTRA] : [];
    }
    
    protected function addConfig(PackageInterface $package)
    {
        $data = $this->getInstallers();
        $data['configs'][$package->getName()] = $this->ensureConfig($package, $this->getPackageExtraData($package));
        
        return $data;
    }
    
    protected function writeInstaller(array $data)
    {
        $file = $this->vendorDir . DIRECTORY_SEPARATOR . self::LUYA_FILE;
        
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        
        $array = str_replace("'<vendor-dir>", '$vendorDir . \'', var_export($data, true));
        if (file_put_contents($file, "<?php\n\n\$vendorDir = dirname(__DIR__);\n\nreturn $array;\n") === false) {
            $this->io->writeError("Unable to create luya installer file.");
        }
        
        // Invalidate opcache of plugins.php if it exists
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($file, true);
        }
    }
    
    /**
     * Read the relative vendor-dir from composer config.
     *
     * @return string
     * @since 1.0.4
     */
    public function getRelativeVendorDir()
    {
        if ($this->_relativeVendorDir === null) {
            $this->_relativeVendorDir = rtrim($this->composer->getConfig()->get('vendor-dir', \Composer\Config::RELATIVE_PATHS), '/');
        }
        
        return $this->_relativeVendorDir;
    }
    
    
}
