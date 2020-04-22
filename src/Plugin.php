<?php

namespace luya\composer;

use Composer\Plugin\PluginInterface;
use Composer\IO\IOInterface;
use Composer\Config;
use Composer\Composer;
use Composer\Script\ScriptEvents;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\InstallerEvent;
use Composer\Installer\InstallerEvents;
use Composer\Installer\PackageEvents;
use Composer\Package\PackageInterface;

/**
 * LUYA Composer Plugin.
 *
 * The main plugin acticates the Installer, which can observe every package installation. The Plugin class
 * is responsible for controlling the `composer.json` file itself and installes the luya binary file.
 * 
 * Events: https://getcomposer.org/doc/articles/scripts.md#event-names
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var string The option key in luya section which determines whether symlink is disabled or not.
     */
    const LUYA_SYMLINK = 'symlink';

    /**
     * @var string Filename of the symlink with target to the `vendor/bin/luya`.
     * @since 1.0.4
     */
    public $linkPath = 'luya';

    /**
     * @var boolean This property can be turned trued by any package while isntalling in order to do stop creating luya binary.
     * @since 1.0.4
     */
    public $packageHasDisabledSymlink = false;
    
    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // register the installer which extras luya specific config data from extras
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'postUpdateScript',
            ScriptEvents::POST_UPDATE_CMD => 'postUpdateScript',
            ScriptEvents::POST_CREATE_PROJECT_CMD => 'postUpdateScript',
            PackageEvents::POST_PACKAGE_INSTALL => 'findCoreRepo',
        ];
    }

    private $_packageInstalls = [];

    /**
     * Find repositories and store into package installs array.
     *
     * @param PackageEvent $event
     */
    public function findCoreRepo(PackageEvent $event)
    {
        $operation = $event->getOperation();
        
        if ($operation instanceof InstallOperation) {
            // any package with symlink option disable can prevent the symlink creating.
            if (!$this->packageHasDisabledSymlink) {
                $this->packageHasDisabledSymlink = $this->ensureLuyaExtraSectionSymlinkIsDisabled($operation->getPackage());
            }

            $this->_packageInstalls[] = $operation->getPackage()->getName();
        }
    }
    
    /**
     * Create the symlink binary.
     *
     * @param Event $event
     * @return void
     */
    public function postUpdateScript(Event $event)
    {
        // any package which has been installed disabled the symlink command, therefore skip this step.
        if ($this->packageHasDisabledSymlink) {
            return;
        }

        if ($event->getComposer()->getPackage()) {
            if ($this->ensureLuyaExtraSectionSymlinkIsDisabled($event->getComposer()->getPackage())) {
                // disable continue due to symlink disable option
                return;
            }
        }

        
        if (in_array('luyadev/luya-core', $this->_packageInstalls)) {
            if (!is_link($this->linkPath) && !is_file($this->linkPath)) {
                // oppress exception for windows system (https://github.com/luyadev/luya/issues/1694)
                @symlink($this->getRelativeVendorDir($event->getComposer()) . DIRECTORY_SEPARATOR . 'luyadev'.DIRECTORY_SEPARATOR.'luya-core'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'luya', $this->linkPath);
            }
        }
    }
    
    /**
     * Check if the root package configuration contains the luya section with symlink false/0.
     *
     * This means the symlinking for luya binary is diabled for postUpdateScript() event.
     *
     * @param PackageInterface $package
     * @return boolean
     * @since 1.0.4
     */
    public function ensureLuyaExtraSectionSymlinkIsDisabled(PackageInterface $package)
    {
        $extra = $package->getExtra();

        if (!is_array($extra) || !array_key_exists(Installer::LUYA_EXTRA, $extra)) {
            return false;
        }

        //
        $extra = $extra[Installer::LUYA_EXTRA];

        if (isset($extra[self::LUYA_SYMLINK])) {
            $symlink = $extra[self::LUYA_SYMLINK];
            if ($symlink == false || $symlink == 0) {
                return true;
            }
        }

        return false;
    }

    private $_relativeVendorDir;
    
    /**
     * Read the relative vendor-dir from composer config.
     *
     * @return string
     * @since 1.0.4
     */
    public function getRelativeVendorDir(Composer $composer)
    {
        if ($this->_relativeVendorDir === null) {
            $this->_relativeVendorDir = rtrim($composer->getConfig()->get('vendor-dir', Config::RELATIVE_PATHS), DIRECTORY_SEPARATOR);
        }
    
        return $this->_relativeVendorDir;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {  
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {   
    }
}
