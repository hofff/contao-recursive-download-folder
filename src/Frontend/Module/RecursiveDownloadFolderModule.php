<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\Module;

use Contao\Module;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\RecursiveDownloadFolderTrait;

/**
 * Class RecursiveDownloadFolderModule
 *
 * Front end content module "hofff_recursive-download-folder".
 *
 * @property mixed recursiveDownloadFolderHideEmptyFolders
 * @property mixed recursiveDownloadFolderAllowFileSearch
 * @property mixed recursiveDownloadFolderShowAllLevels
 * @property mixed recursiveDownloadFolderTpl
 * @property mixed recursiveDownloadFolderMode
 * @property mixed recursiveDownloadFolderVisibleRoot
 */
class RecursiveDownloadFolderModule extends Module
{
    use RecursiveDownloadFolderTrait;

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'mod_hofff_recursive-download-folder';
}
