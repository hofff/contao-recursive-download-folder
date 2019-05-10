<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\Element;

use Contao\ContentElement;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\RecursiveDownloadFolderTrait;

/**
 * Class ContentRecursiveDownloadFolder
 *
 * Front end content element "hofff_recursive-download-folder".
 *
 * @property mixed recursiveDownloadFolderHideEmptyFolders
 * @property mixed recursiveDownloadFolderAllowFileSearch
 * @property mixed recursiveDownloadFolderShowAllLevels
 * @property mixed recursiveDownloadFolderTpl
 * @property mixed recursiveDownloadFolderMode
 * @property mixed recursiveDownloadFolderVisibleRoot
 */
class RecursiveDownloadFolderElement extends ContentElement
{
    use RecursiveDownloadFolderTrait;

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_recursive-download-folder';
}
