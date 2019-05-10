<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend;

use Contao\Controller;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\StringUtil;
use function dirname;
use function end;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree\BreadcrumbFileTreeBuilder;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree\FileTreeBuilder;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree\ToggleableFileTreeBuilder;
use function preg_replace;
use function sprintf;
use function strpos;
use function trim;

trait RecursiveDownloadFolderTrait
{
    /**
     * Files object
     *
     * @var FilesModel
     */
    protected $objFolder;

    public function __construct($objElement, $strColumn = 'main')
    {
        parent::__construct($objElement, $strColumn);

        $this->folderSRC = StringUtil::deserialize($this->folderSRC, true);
    }

    /**
     * Return if there are no files
     */
    public function generate() : string
    {
        // Use the home directory of the current user as file source
        if ($this->useHomeDir && FE_USER_LOGGED_IN) {
            $this->import('FrontendUser', 'User');

            if ($this->User->assignDir && $this->User->homeDir) {
                $this->folderSRC = [$this->User->homeDir];
            }
        }

        // Return if there is no folder defined
        if (empty($this->folderSRC)) {
            return '';
        }

        // Get the folders from the database
        $this->objFolder = FilesModel::findMultipleByUuids($this->folderSRC);

        if ($this->objFolder === null) {
            return '';
        }

        $file = Input::get('file', true);

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ($file !== '' && $file !== null) {
            foreach ($this->objFolder as $folder) {
                if (strpos(dirname($file), $folder->path) === 0) {
                    Controller::sendFileToBrowser($file);
                }
            }
        }

        return parent::generate();
    }


    /**
     * Generate the content element
     */
    protected function compile() : void
    {
        $treeBuilder = $this->createTreeBuilder();
        $fileTree    = $treeBuilder->build($this->folderSRC);

        $this->Template->generateBreadcrumbLink = function (string $path) : string {
            $url = '';

            if (isset($GLOBALS['objPage'])) {
                $url = $GLOBALS['objPage']->getFrontendUrl();
            }

            $url .= '?path=' . $path;

            return $url;
        };

        if ($fileTree['tree']) {
            $this->Template->breadcrumb    = $fileTree['breadcrumb'];
            $this->Template->showRootLevel = $this->recursiveDownloadFolderVisibleRoot || count($this->folderSRC) > 1 ;
            $this->Template->activeFolder  = end($fileTree['breadcrumb']);
            $this->Template->fileTree      = $fileTree['tree'];
            $this->Template->elements      = $fileTree['tree']['elements_rendered'];
            $this->Template->count         = count($fileTree['tree']['elements']);
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
            $this->Template->resetUrl     = self::addToUrl('keyword=');
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

    protected function createTreeBuilder() : FileTreeBuilder
    {
        if ($this->recursiveDownloadFolderMode === 'breadcrumb') {
            $treeBuilder = new BreadcrumbFileTreeBuilder();
        } else {
            $treeBuilder = new ToggleableFileTreeBuilder();
        }

        if ($this->recursiveDownloadFolderAllowFileSearch) {
            $treeBuilder->allowFileSearch();
        }

        if ($this->recursiveDownloadFolderHideEmptyFolders) {
            $treeBuilder->hideEmptyFolders();
        }

        if ($this->recursiveDownloadFolderShowAllLevels) {
            $treeBuilder->showAllLevels();
        }

        if ($this->recursiveDownloadFolderVisibleRoot) {
            $treeBuilder->alwaysShowRoot();
        }

        return $treeBuilder;
    }
}
