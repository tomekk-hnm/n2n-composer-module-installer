<?php
namespace n2n\composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\Package;

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
		
		$this->io->write('INSTALL HOLERADIO - ' . $package->getName() . ' - ' . $package->getPrettyName() . ' - ' . implode(';', $package->getNames()) 
				. ' - ' . $package->getId() . ' - ' . $package->getInstallationSource() . ' - ' . $package->getPrettyString());
		
		
		$this->move($package);
		
	}
	
	const N2N_MODULE_TYPE = 'n2n-module';
	const VAR_DIR_NAME = 'var';
	const ETC_DIR_NAME = 'etc';
	const ASSETS_PUBLIC_DIR_NAME = 'public';
	const ASSETS_DIR_NAME = 'assets';
	
	private function move(Package $package) {
		$installDirPath = $this->getInstallPath($package);
		$moduleName = pathinfo($installDirPath, PATHINFO_BASENAME);
		
		$varOrigDirPath = $this->filesystem->normalizePath($installDirPath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'var'); 
		$varDestDirPath = $this->filesystem->normalizePath($this->vendorDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'var');

		if (!is_dir($varOrigDirPath)) {
			throw new CorruptedN2nModuleException($package->getPrettyName() . ' has type \'' . $self::N2N_MODULE_TYPE 
					. '\' but contains no directory var directory: ' . $varDestDirPath);
		}
		
		$relEtcPath = DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . $moduleName;
		$mdlEtcOrigDirPath = $varOrigDirPath . $relEtcPath;
		$mdlEtcDestDirPath = $varDestDirPath . $relEtcPath;
		
		if (!is_dir($mdlEtcOrigDirPath)) {
			return;
		}
		
		if (!is_dir($varDestDirPath)) {
			$question = $package->getPrettyName() . ' is an ' . $self::N2N_MODULE_TYPE 
					. ' and requires a var directory (' . $varDestDirPath 
					. '). Do you want to skip the installation of the var files?';
			if (!$this->io->askConfirmation($question)) {
				throw new N2nModuleInstallationException('Failed to install ' . self::N2N_MODULE_TYPE . ' ' 
						. $package->getPrettyName() . '. Reason: Var directory missing: ' . $varDestDirPath);
			}
		}
		
		$this->filesystem->copyThenRemove($mdlEtcOrigDirPath, $mdlEtcDestDirPath);
	}

	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::update()
	 */
	public function update(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $initial, 
			\Composer\Package\PackageInterface $target) {
		parent::update($repo, $initial, $target);

		$this->io->write('UPDATE HOLERADIO ' . $this->vendorDir . ' ' . $this->binDir . ' ' . $target->getTargetDir());
		
	}

	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::uninstall()
	 */
	public function uninstall(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) {
		parent::uninstall($repo, $package);

		$this->io->write('UNINSTALL HOLERADIO' . $this->vendorDir . ' ' . $this->binDir . ' ' . $package->getTargetDir());
	}
}

class CorruptedN2nModuleException extends \RuntimeException {
	
}

class N2nModuleInstallationException extends \RuntimeException {
	
}