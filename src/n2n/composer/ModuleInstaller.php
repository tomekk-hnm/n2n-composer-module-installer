<?php
namespace n2n\composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\Package;

class ModuleInstaller extends LibraryInstaller {
	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::supports()
	 */
	public function supports($packageType) {
		return $packageType == self::N2N_MODULE_TYPE;
	}

	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::install()
	 */
	public function install(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) {
		parent::install($repo, $package);
		
		$this->io->write('INSTALL HOLERADIO');
		$this->removeResources($package);
		$this->installResources($package);
	}
	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::update()
	 */
	public function update(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $initial, 
			\Composer\Package\PackageInterface $target) {
		parent::update($repo, $initial, $target);
		
		$this->io->write('UPDATE HOLERADIO');
		$this->removeResources($initial);
		$this->removeResources($target);
		$this->installResources($target);
	}

	/**
	 * {@inheritDoc}
	 * @see \Composer\Installer\InstallerInterface::uninstall()
	 */
	public function uninstall(\Composer\Repository\InstalledRepositoryInterface $repo, \Composer\Package\PackageInterface $package) {
		parent::uninstall($repo, $package);

		$this->io->write('UNINSTALL HOLERADIO');
		$this->removeResources($target);
	}
	
	const N2N_MODULE_TYPE = 'n2n-module';
	const VAR_ORIG_DIR = 'src' . DIRECTORY_SEPARATOR . 'var';
	const VAR_DEST_DIR = '..' . DIRECTORY_SEPARATOR . 'var';
	const ETC_DIR = 'etc';
	const PUBLIC_ORIG_DIR = 'src' . DIRECTORY_SEPARATOR . 'public';
	const PUBLIC_DEST_DIR = '..' . DIRECTORY_SEPARATOR . 'public';
	const ASSETS_DIR = 'assets';
	
	private function getModuleName(Package $package) {
		return pathinfo($this->getInstallPath($package), PATHINFO_BASENAME);
	}
	
	private function getVarOrigDirPath(Package $package) {
		return $this->filesystem->normalizePath($this->getInstallPath($package) . DIRECTORY_SEPARATOR 
				. self::VAR_ORIG_DIR);
	}
	
	private function getVarDestDirPath() {
		return $this->filesystem->normalizePath($this->vendorDir . DIRECTORY_SEPARATOR . self::VAR_DEST_DIR);
	}
	
	private function getRelEtcDirPath(Package $package) {
		return DIRECTORY_SEPARATOR . self::ETC_DIR . DIRECTORY_SEPARATOR . $this->getModuleName($package);
	}
	
	private function getPublicOrigDirPath(Package $package) {
		return $this->filesystem->normalizePath($this->getInstallPath($package) . DIRECTORY_SEPARATOR 
				. self::PUBLIC_ORIG_DIR);
	}
	
	private function getPublicDestDirPath() {
		return $this->filesystem->normalizePath($this->vendorDir . DIRECTORY_SEPARATOR . self::PUBLIC_DEST_DIR);
	}

	private function getRelAssetsDirPath(Package $package) {
		return DIRECTORY_SEPARATOR . self::ASSETS_DIR . DIRECTORY_SEPARATOR . $this->getModuleName($package);
	}
	
	private function removeResources(Package $package) {
		$mdlEtcDestDirPath = $this->getVarDestDirPath() . $this->getRelEtcDirPath($package);
		if (is_dir($mdlEtcDestDirPath)) {
			$this->filesystem->removeDirectory($mdlEtcDestDirPath);	
		}
		
		$mdlAssetsDestDirPath = $this->getPublicDestDirPath() . $this->getRelAssetsDirPath($package);
		if (is_dir($mdlAssetsDestDirPath)) {
			$this->filesystem->removeDirectory($mdlAssetsDestDirPath);
		}
	}
	
	private function installResources(Package $package) {
		$this->moveEtc($package);
		$this->moveAssets($package);
	}
	
	private function moveEtc(Package $package) {
		$varOrigDirPath = $this->getVarOrigDirPath($package);
		$varDestDirPath = $this->getVarDestDirPath();
	
		$this->valOrigDirPath($varOrigDirPath, $package);
	
		$relEtcDirPath = $this->getRelEtcDirPath($package);
		$mdlEtcOrigDirPath = $varOrigDirPath . $relEtcDirPath;
		$mdlEtcDestDirPath = $varDestDirPath . $relEtcDirPath;
	
		if (!is_dir($mdlEtcOrigDirPath)) {
			return;
		}
	
		if ($this->valDestDirPath($varDestDirPath, $package)) {
			$this->filesystem->copyThenRemove($mdlEtcOrigDirPath, $mdlEtcDestDirPath);
		}
	}
	
	private function moveAssets(Package $package) {
		$publicOrigDirPath = $this->getPublicOrigDirPath($package);
		$publicDestDirPath = $this->getPublicDestDirPath();
	
		$this->valOrigDirPath($publicOrigDirPath, $package);
	
		$relAssetsDirPath = $this->getRelAssetsDirPath($package);
		$mdlAssetsOrigDirPath = $publicOrigDirPath . $relAssetsDirPath;
		$mdlAssetsDestDirPath = $publicDestDirPath . $relAssetsDirPath;
	
		if (!is_dir($mdlAssetsOrigDirPath)) {
			return;
		}
	
		if ($this->valDestDirPath($publicDestDirPath, $package)) {
			$this->filesystem->copyThenRemove($mdlAssetsOrigDirPath, $mdlAssetsDestDirPath);
		}
	}
	
	private function valOrigDirPath($origDirPath, Package $package) {
		if (is_dir($origDirPath)) return;
	
		$dirName = pathinfo($origDirPath, PATHINFO_BASENAME);
		throw new CorruptedN2nModuleException($package->getPrettyName() . ' has type \'' . $self::N2N_MODULE_TYPE
				. '\' but contains no ' . $dirName . ' directory: ' . $origDirPath);
	}
	
	private function valDestDirPath($destDirPath, Package $package) {
		if (is_dir($destDirPath)) return true;
	
		$dirName = pathinfo($destDirPath, PATHINFO_BASENAME);
	
		$question = $package->getPrettyName() . ' is an ' . self::N2N_MODULE_TYPE
		. ' and requires a ' . $dirName . ' directory (' . $destDirPath
		. '). Do you want to skip the installation of the ' . $dirName . ' files? [y,n] (default: y): ';
		if ($this->io->askConfirmation($question)) return false;
	
		throw new N2nModuleInstallationException('Failed to install ' . self::N2N_MODULE_TYPE . ' '
				. $package->getPrettyName() . '. Reason: ' . $dirName . ' directory missing: ' . $destDirPath);
	}
	
}

class CorruptedN2nModuleException extends \RuntimeException {
	
}

class N2nModuleInstallationException extends \RuntimeException {
	
}