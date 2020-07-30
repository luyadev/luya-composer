<?php

namespace luya\composer;

use Composer\Config;
use Composer\Installer\LibraryInstaller;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Package\PackageInterface;
use React\Promise\PromiseInterface;

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

    const LUYA_TYPE_CORE = 'luya-core';

    const LUYA_TYPE_EXTENSION = 'luya-extension';

    const LUYA_TYPE_MODULE = 'luya-module';

    const LUYA_TYPE_THEME = 'luya-theme';
    
    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType == self::LUYA_TYPE_CORE || 
            $packageType == self::LUYA_TYPE_EXTENSION || 
            $packageType == self::LUYA_TYPE_MODULE || 
            $packageType == self::LUYA_TYPE_THEME;
    }
    
    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        // install the package the normal composer way
        $promise = parent::install($repo, $package);
        $this->addPackage($package);
        
        // Composer v2 might return a promise here
        if ($promise instanceof PromiseInterface) {
            return $promise->then();
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $promise = parent::update($repo, $initial, $target);
        $this->removePackage($initial);
        $this->addPackage($target);
        
        // Composer v2 might return a promise here
        if ($promise instanceof PromiseInterface) {
            return $promise->then();
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $promise = parent::uninstall($repo, $package);
        $this->removePackage($package);
        
        // Composer v2 might return a promise here
        if ($promise instanceof PromiseInterface) {
            return $promise->then();
        }
    }
    
    /**
     * Add a package to installer
     *
     * @param PackageInterface $package
     * @return void
     */
    protected function addPackage(PackageInterface $package)
    {
        if (!$this->isPackageInComposerConfig($package)) {
            return;
        }
        
        $this->writeInstaller($this->addConfig($package));
    }
    
    /**
     * Remove a package from installer
     *
     * @param PackageInterface $package
     * @return void
     */
    protected function removePackage(PackageInterface $package)
    {
        $this->writeInstaller($this->removeConfig($package));
    }
    
    /**
     * Get the installer array
     *
     * @return array
     */
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
    
    /**
     * Check if the package is in the require or require-dev config.
     * 
     * If the package is not in the "root" composer.json available, the installer should not add those packages.
     *
     * @param PackageInterface $package
     * @return boolean
     * @since 1.1.0
     */
    public function isPackageInComposerConfig(PackageInterface $package)
    {
        if (array_key_exists($package->getName(), $this->composer->getPackage()->getRequires())) {
            return true;
        }

        if (array_key_exists($package->getName(), $this->composer->getPackage()->getDevRequires())) {
            return true;
        }

        $this->io->write("Package {$package->getName()} will be ignored by luyadev installer as its not part of the composer.json requirements.");

        return false;
    }

    /**
     * Ensure a config for a package.
     *
     * @param PackageInterface $package
     * @param array $config
     * @return void
     */
    protected function ensureConfig(PackageInterface $package, array $config)
    {

        // generate the package folder, which is actually the name but with os based directory seperator
        $packageFolder = str_replace("/", DIRECTORY_SEPARATOR, $package->getPrettyName());
        
        $packageConfig = [
            'package' => [
                'isDev' => $package->isDev(),
                'name' => $package->getName(),
                'prettyName' => $package->getPrettyName(),
                'version' => $package->getVersion(),
                'targetDir' => $package->getTargetDir(),
                'installSource' => $package->getInstallationSource(),
                'sourceUrl' => $package->getSourceUrl(),
                'packageFolder' => $packageFolder,
            ],
            'blocks' => [],
            'bootstrap' => (isset($config['bootstrap'])) ? ComposerHelper::parseDirectorySeperator($config['bootstrap']) : [],
            'themes' => [],
        ];
        
        $blocks = isset($config['blocks']) ? $config['blocks'] : [];
    
        foreach ($blocks as $blockFolder) {
            $packageConfig['blocks'][] = $this->getRelativeVendorDir() . DIRECTORY_SEPARATOR . $packageFolder . DIRECTORY_SEPARATOR . ComposerHelper::parseDirectorySeperator(ltrim($blockFolder, DIRECTORY_SEPARATOR));
        }
    
        $themes = isset($config['themes']) ? $config['themes'] : [];
    
        foreach ($themes as $themeFolder) {
            $packageConfig['themes'][] = $this->getRelativeVendorDir() . DIRECTORY_SEPARATOR . $package->getPrettyName() . DIRECTORY_SEPARATOR . ComposerHelper::parseDirectorySeperator(ltrim($themeFolder, '/'));
        }
        
        return $packageConfig;
    }
     
    /**
     * Remove a package from the config
     *
     * @param PackageInterface $package
     * @return array
     */
    protected function removeConfig(PackageInterface $package)
    {
        $data = $this->getInstallers();
        
        if (isset($data['configs'][$package->getName()])) {
            unset($data['configs'][$package->getName()]);
        }
        
        return $data;
    }
    
    /**
     * Get the LUYA extra binary data.
     *
     * @param PackageInterface $package
     * @return array
     */
    protected function getPackageExtraData(PackageInterface $package)
    {
        if (empty($package->getExtra())) {
            return [];
        }
        
        return isset($package->getExtra()[self::LUYA_EXTRA]) ? $package->getExtra()[self::LUYA_EXTRA] : [];
    }
    
    /**
     * Add a package to the config
     *
     * @param PackageInterface $package
     * @return array
     */
    protected function addConfig(PackageInterface $package)
    {
        $data = $this->getInstallers();
        $data['configs'][$package->getName()] = $this->ensureConfig($package, $this->getPackageExtraData($package));
        
        return $data;
    }
    
    /**
     * Write the installer.php file in vendor folder
     *
     * @param array $data
     * @return void
     */
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

    private $_relativeVendorDir;
    
    /**
     * Read the relative vendor-dir from composer config.
     *
     * @return string
     * @since 1.0.4
     */
    public function getRelativeVendorDir()
    {
        if ($this->_relativeVendorDir === null) {
            $this->_relativeVendorDir = rtrim($this->composer->getConfig()->get('vendor-dir', Config::RELATIVE_PATHS), DIRECTORY_SEPARATOR);
        }
        
        return $this->_relativeVendorDir;
    }
}
