<?php
namespace app\n2n\composer;

use Composer\Plugin\PluginInterface;
use Composer\IO\IOInterface;
use Composer\Installer\InstallerInterface;
use Composer\Installer\LibraryInstaller;

class ModulePlugin implements PluginInterface {
	
	public function activate(\Composer\Composer $composer, IOInterface $io) {
		$installer = new ModuleInstaller($io, $composer);
		$composer->getInstallationManager()->addInstaller($installer);
	}
}

class ModuleInstaller extends LibraryInstaller {
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \Composer\Installer\InstallerInterface::supports()
// 	 */
// 	public function supports($packageType) {
// 		return $packageType == 'n2n-module';
// 	}

	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::install()
	 */
	public function install(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) {
		parent::install($repo, $package);
		
		$this->io->write('INSTALL HOLERADIO');
	}

	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::update()
	 */
	public function update(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $initial, 
			\Composer\Package\PackageInterface $target) {
		parent::update($repo, $initial, $target);
		
		$this->io->write('UPDATE HOLERADIO');
	}

	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::uninstall()
	 */
	public function uninstall(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) {
		parent::uninstall($repo, $package);
		
		$this->io->write('UNINSTALL HOLERADIO');
	}
}

