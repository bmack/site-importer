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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Simple command to import records defined in a config yaml file into the database
 */
class SiteImportCommand extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Imports database entries into the database, based on Yaml configuration');
        $this->addArgument('file', InputArgument::REQUIRED, 'The file to import');
    }

    /**
     * Imports database entries into the database, based on Yaml configuration
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');

        $contents = Yaml::parse(file_get_contents($file));
        $contents = $this->loadImports($contents);
        foreach ($contents as $config) {
            $mode = $config['mode'] ?? 'append';
            $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($config['table']);
            if ($mode === 'replace') {
                $conn->truncate($config['table']);
                $output->writeln('Emptied database table "'.$config['table'].'"');
            }
            foreach ($config['entries'] ?? [] as $entry) {
                if ($mode === 'update' && isset($entry['uid'])) {
                    $identifiers = ['uid' => $entry['uid']];
                    if ($conn->count('uid', $config['table'], $identifiers)) {
                        $conn->update($config['table'], $entry, $identifiers);
                        $output->writeln('Updated (mode=update, entry has uid): '.json_encode($entry).' to database table '.$config['table']);
                    } else {
                        $conn->insert($config['table'], $entry);
                        $output->writeln('Added (mode=update, entry does not have uid): '.json_encode($entry).' to database table '.$config['table']);
                    }
                } else {
                    $conn->insert($config['table'], $entry);
                    $output->writeln('Added '.json_encode($entry).' to database table '.$config['table']);
                }
            }
        }

        return self::SUCCESS;
    }

    /**
     * Load recursively import files declared in yml files
     *
     * imports:
     *    - { resource: 'base.site-importer.yml' }
     *
     * @param array $contents
     */
    private function loadImports(array $contents): array
    {
        if ( ! empty($contents['imports'])) {
            foreach ($contents['imports'] as $import) {
                $importedContent = Yaml::parseFile($import['resource']);
                if ( ! empty($importedContent) && is_array($importedContent)) {
                    $importedContent = $this->loadImports($importedContent);
                    $contents = array_merge($contents, $importedContent);
                }
            }
            unset($contents['imports']);
        }

        return $contents;
    }
}
