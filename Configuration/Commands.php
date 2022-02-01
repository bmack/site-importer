<?php

declare(strict_types=1);

return [
    'siteimport:fromfile' => [
        'class' => \Bmack\SiteImporter\Command\SiteImportCommand::class,
        'schedulable' => false,
        'runLevel' => \Helhum\Typo3Console\Core\Booting\RunLevel::LEVEL_MINIMAL,
        'bootingSteps' => [],
    ],
];
