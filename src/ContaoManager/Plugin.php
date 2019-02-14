<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Hofff\Contao\RecursiveDownloadFolder\HofffContaoRecursiveDownloadFolderBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(HofffContaoRecursiveDownloadFolderBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace('hofff_recursive-download-folder')
        ];
    }
}
