<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codingStyle: true,
        typeDeclarations: true,
        instanceOf: true,
        earlyReturn: true,
    )
    ->withPhpSets(php81: true)
    ->withComposerBased(symfony: true, doctrine: true)
    ->withAttributesSets(symfony: true)
    ->withSets([
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
    ])
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
    ]);
