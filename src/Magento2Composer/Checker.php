<?php

namespace Elgentos\Magento2Composer;

use Composer\Factory as ComposerFactory;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;

class Checker
{
    /** @var IOInterface */
    private $io;

    /** @var Filesystem */
    private $fileSystem;

    /**
     * Checker constructor.
     * @param IOInterface $io
     * @param Filesystem $fileSystem
     */
    public function __construct(IOInterface $io, Filesystem $fileSystem)
    {
        $this->projectRootPath = dirname(ComposerFactory::getComposerFile());
        $this->io = $io;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return void
     */
    public function check($vendorDir, array $ignoreModules = [])
    {
        $this->io->write('Checking for Magento 2 modules in config.php that are not present in codebase');

        // Find registration files in codebase
        $registrationFiles = $this->getRegistrationFiles($vendorDir);

        // Parse Magento module names from registration files
        $magentoModuleNames = $this->parseModuleNamesFromRegistrationFiles($registrationFiles);

        // Get module names that are defined in config.php
        $definedModuleNames = $this->getDefinedModuleNames();

        // If modules are listed to be ignored, remove them from the array of defined module names
        if (count($ignoreModules)) {
            array_map(function ($moduleName) {
                $this->io->write('- Ignoring ' . $moduleName);
            }, $ignoreModules);

            $definedModuleNames = array_diff($definedModuleNames, $ignoreModules);
        }

        // Find modules that are defined in config.php but not present in codebase
        $definedYetNotFound = array_diff($definedModuleNames, $magentoModuleNames);

        // If found, show errors and exit with non-zero exit code
        if ($definedYetNotFound) {
            array_map(function ($extension) {
                $this->io->writeError('- Module defined in config.php but not found: ' . $extension);
            }, $definedYetNotFound);

            exit(1);
        }
    }

    /**
     * @param $vendorDir
     * @return array
     */
    protected function getRegistrationFiles($vendorDir)
    {
        $registrationFiles = [];

        // Find local modules
        $pattern = str_repeat('*/', 2) . 'registration.php';
        $registrationFiles = array_merge($registrationFiles, glob('app/code/' . $pattern));

        // Find vendor modules
        for ($level = 2; $level <= 5; $level++) {
            $pattern = str_repeat('*/', $level) . 'registration.php';
            $registrationFiles = array_merge($registrationFiles, glob($vendorDir . '/' . $pattern));
        }

        return $registrationFiles;
    }

    /**
     * @param array $registrationFiles
     * @return array
     */
    protected function parseModuleNamesFromRegistrationFiles(array $registrationFiles)
    {
        return array_map(function ($file) {
            $fileContents = file_get_contents($file);
            preg_match('#(\'(.*)_(.*)\')#', $fileContents, $matches);

            if (isset($matches[2]) && isset($matches[3])) {
                return $matches[2] . '_' . $matches[3];
            }

            return false;
        }, $registrationFiles);
    }

    /**
     * @return array
     */
    protected function getDefinedModuleNames()
    {
        $config = include('app/etc/config.php');
        return array_keys($config['modules']);
    }
}