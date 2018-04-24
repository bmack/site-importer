<?php
declare(strict_types=1);
namespace Bmack\SiteImporter\Command;

/*
 * This file is part of the Site Importer package.
 *
 * (c) Benni Mack <benni@typo3.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\Typo3Console\Mvc\Controller\CommandController;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Simple command to import records defined in a config yaml file into the database
 */
class SiteImportCommandController extends CommandController
{

    /**
     * Imports database entries into the database, based on Yaml configuration
     *
     * @param string $file
     */
    public function fromFileCommand($file)
    {
        $contents = Yaml::parseFile($file);
        foreach ($contents as $type => $config) {
            $mode = $config['mode'] ?? 'append';
            if (class_exists(ConnectionPool::class)) {
                $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($config['table']);
                if ($mode === 'replace') {
                    $conn->truncate($config['table']);
                    $this->outputLine('Emptied database table "' . $config['table'] . '"');
                }
                foreach ($config['entries'] as $entry) {
                    $conn->insert($config['table'], $entry);
                    $this->outputLine('Added ' . json_encode($entry) . ' to database table ' . $config['table']);
                }
            } else {
                if ($mode === 'replace') {
                    $GLOBALS['TYPO3_DB']->exec_TRUNCATEquery($config['table']);
                    $this->outputLine('Emptied database table "' . $config['table'] . '"');
                }
                foreach ($config['entries'] as $entry) {
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery($config['table'], $entry);
                    $this->outputLine('Added ' . json_encode($entry) . ' to database table ' . $config['table']);
                }

            }
        }
    }
}
