<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

use function is_dir;

final class FilesDcaListener
{
    /** @var string */
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @Callback(table="tl_files", target="config.onload", priority=-1)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function adjustPalettes(DataContainer $dataContainer): void
    {
        if (! $dataContainer->id || ! is_dir($this->projectDir . '/' . $dataContainer->id)) {
            return;
        }

        // Add meta field for directories again. Contao removes it since https://github.com/contao/contao/pull/1055
        $GLOBALS['TL_DCA']['tl_files']['palettes']['default'] .= ';meta';
    }
}
