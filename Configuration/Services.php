<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->private()
        ->autowire()
        ->autoconfigure();

    $services->load('Bmack\\SiteImporter\\', __DIR__ . '/../src/');

    $services->set(\Bmack\SiteImporter\Command\SiteImportCommand::class)
        ->tag('console.command', [
            'command' => 'siteimport:fromfile',
            'schedulable' => false
        ]);
};
