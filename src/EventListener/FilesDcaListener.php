<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

use function is_dir;

final class FilesDcaListener
{
    public function __construct(private readonly string $projectDir)
    {
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    #[AsCallback(table: 'tl_files', target: 'config.onload', priority: -1)]
    public function adjustPalettes(DataContainer $dataContainer): void
    {
        if (! $dataContainer->id || ! is_dir($this->projectDir . '/' . $dataContainer->id)) {
            return;
        }

        // Add meta field for directories again. Contao removes it since https://github.com/contao/contao/pull/1055
        $GLOBALS['TL_DCA']['tl_files']['palettes']['default'] .= ';meta';
    }
}
