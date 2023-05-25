<?php

declare(strict_types=1);

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title'            => 'bmack/site-importer',
    'description'      => 'Imports records from a Yaml file structure into the TYPO3 database',
    'category'         => 'misc',
    'author'           => 'Benni Mack',
    'author_email'     => 'benni@typo3.org',
    'state'            => 'stable',
    'version'          => '2',
    'constraints'      => [
        'depends'   => [],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
