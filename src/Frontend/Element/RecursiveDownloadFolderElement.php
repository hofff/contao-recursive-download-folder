<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\Element;

use Contao\ContentElement;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\RecursiveDownloadFolderTrait;

/**
 * Front end content element "hofff_recursive-download-folder".
 *
 * @property mixed $recursiveDownloadFolderHideEmptyFolders
 * @property mixed $recursiveDownloadFolderAllowFileSearch
 * @property mixed $recursiveDownloadFolderShowAllLevels
 * @property mixed $recursiveDownloadFolderTpl
 * @property mixed $recursiveDownloadFolderMode
 * @property mixed $recursiveDownloadFolderVisibleRoot
 * @property mixed $recursiveDownloadFolderAllowAll
 * @property mixed $recursiveDownloadFolderZipDownload
 * @property mixed $recursiveDownloadFolderShowAllSearchResults
 * @property mixed $folderSRC
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RecursiveDownloadFolderElement extends ContentElement
{
    use RecursiveDownloadFolderTrait;

    /**
     * Template
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    protected $strTemplate = 'ce_recursive-download-folder';
}
