<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\Element;

use Contao\ContentElement;
use Contao\Controller;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree\ToggleableFileTreeBuilder;
use function basename;
use function count;
use function dirname;
use function preg_match;
use function preg_replace;
use function strpos;
use function trim;

/**
 * Class ContentRecursiveDownloadFolder
 *
 * Front end content element "hofff_recursive-download-folder".
 *
 * @property mixed recursiveDownloadFolderHideEmptyFolders
 * @property mixed recursiveDownloadFolderAllowFileSearch
 * @property mixed recursiveDownloadFolderShowAllLevels
 * @property mixed recursiveDownloadFolderTpl
 */
class RecursiveDownloadFolderElement extends ContentElement
{
    /**
     * Files object
     *
     * @var FilesModel
     */
    protected $objFolder;

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_recursive-download-folder';


    /**
     * Return if there are no files
     */
    public function generate() : string
    {
        // Use the home directory of the current user as file source
        if ($this->useHomeDir && FE_USER_LOGGED_IN) {
            $this->import('FrontendUser', 'User');

            if ($this->User->assignDir && $this->User->homeDir) {
                $this->folderSRC = $this->User->homeDir;
            }
        }

        // Return if there is no folder defined
        if (empty($this->folderSRC)) {
            return '';
        }

        // Get the folders from the database
        $this->objFolder = FilesModel::findByUuid($this->folderSRC);

        if ($this->objFolder === null) {
            if (! Validator::isUuid($this->folderSRC[0])) {
                return '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['version2format'] . '</p>';
            }

            return '';
        }

        $file = Input::get('file', true);

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ($file !== '' && $file !== null && ! preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file))) {
            if (strpos(dirname($file), $this->objFolder->path) !== false) {
                Controller::sendFileToBrowser($file);
            }
        }

        return parent::generate();
    }


    /**
     * Generate the content element
     */
    protected function compile() : void
    {
        $treeBuilder = new ToggleableFileTreeBuilder();

        if ($this->recursiveDownloadFolderAllowFileSearch) {
            $treeBuilder->allowFileSearch();
        }

        if (!$this->recursiveDownloadFolderHideEmptyFolders) {
            $treeBuilder->showEmptyFolders();
        }

        if ($this->recursiveDownloadFolderShowAllLevels) {
            $treeBuilder->showAllLevels();
        }

        $fileTree = $treeBuilder->build(StringUtil::deserialize($this->folderSRC, true));

        if ($fileTree) {
            $fileTree = $fileTree[0];

            $this->Template->fileTree = $fileTree;
            $this->Template->elements = $fileTree['elements_rendered'];
            $this->Template->count    = count($fileTree['elements']);
        } else {
            $this->Template->count = 0;
        }

        $this->Template->searchable = $this->recursiveDownloadFolderAllowFileSearch;

        if ($this->recursiveDownloadFolderAllowFileSearch) {
            $this->Template->action       = preg_replace('/&(amp;)?/i', '&amp;', Environment::get('indexFreeRequest'));
            $this->Template->keyword      = trim((string) Input::get('keyword'));
            $this->Template->noResults    = sprintf($GLOBALS['TL_LANG']['MSC']['sEmpty'], $this->Template->keyword);
            $this->Template->keywordLabel = StringUtil::specialchars(
                $GLOBALS['TL_LANG']['MSC']['recursiveDownloadFolderKeywordLabel']
            );
            $this->Template->searchLabel  = StringUtil::specialchars(
                $GLOBALS['TL_LANG']['MSC']['recursiveDownloadFolderSearchLabel']
            );
            $this->Template->resetUrl     = $this->addToUrl('keyword=');
            $this->Template->resetLabel   = StringUtil::specialchars(
                $GLOBALS['TL_LANG']['MSC']['recursiveDownloadFolderResetLabel']
            );
        }

        if (TL_MODE !== 'BE') {
            return;
        }

        // only load the default JS and CSS in backend, for frontend this will be done in template
        $GLOBALS['TL_CSS']['recursive-download-folder.css'] =
            'bundles/hofffcontaorecursivedownloadfolder/css/recursive-download-folder.min.css||static';

        $GLOBALS['TL_JAVASCRIPT']['recursive-download-folder.js'] =
            'bundles/hofffcontaorecursivedownloadfolde/js/recursive-download-folder.min.js';
    }
}
