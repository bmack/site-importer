<?php
return [
    'controllers' => [
        \Bmack\SiteImporter\Command\SiteImportCommandController::class,
    ],
    'runLevels' => [
        'bmack/site-importer:siteimport:*' => \Helhum\Typo3Console\Core\Booting\RunLevel::LEVEL_MINIMAL,
    ],
    'bootingSteps' => [],
];
