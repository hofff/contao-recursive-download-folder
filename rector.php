<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoLevelSetList;
use Contao\Rector\Set\ContaoSetList;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codingStyle: true,
        typeDeclarations: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
    )
    ->withPhpSets(php81: true)
    ->withAttributesSets(symfony: true)
    ->withSets([
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::DOCTRINE_DBAL_30,
        SymfonySetList::SYMFONY_54,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        ContaoLevelSetList::UP_TO_CONTAO_51,
        ContaoSetList::CONTAO_53,
        __DIR__ . '/vendor/contao/contao-rector/config/sets/contao/level/up-to-contao-53.php',
        ContaoSetList::ANNOTATIONS_TO_ATTRIBUTES,
        ContaoSetList::FQCN,
    ])
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
    ]);
