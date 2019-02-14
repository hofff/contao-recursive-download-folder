<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\Element;

use Contao\ContentElement;
use Contao\Controller;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use function array_map;
use function array_merge;
use function array_values;
use function basename;
use function count;
use function dirname;
use function explode;
use function in_array;
use function ksort;
use function preg_match;
use function preg_replace;
use function stripos;
use function strpos;
use function strtolower;
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
        if ($file !== '' && ! preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file))) {
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
        $objPage = $GLOBALS['objPage'];

        $elements = $this->getElements($this->objFolder, $objPage);
        $fileTree = [
            'type'              => $this->objFolder->type,
            'data'              => $this->getFolderData($this->objFolder, $objPage),
            'elements'          => $elements,
            'elements_rendered' => $this->getElementsRendered($elements),
        ];

        $this->Template->fileTree   = $fileTree;
        $this->Template->elements   = $fileTree['elements_rendered'];
        $this->Template->searchable = $this->recursiveDownloadFolderAllowFileSearch;
        if ($this->recursiveDownloadFolderAllowFileSearch) {
            $this->Template->action       = preg_replace('/&(amp;)?/i', '&amp;', Environment::get('indexFreeRequest'));
            $this->Template->keyword      = trim(Input::get('keyword'));
            $this->Template->keywordLabel = StringUtil::specialchars(
                $GLOBALS['TL_LANG']['MSC']['recursiveDownloadFolderKeywordLabel']
            );
            $this->Template->searchLabel  = StringUtil::specialchars(
                $GLOBALS['TL_LANG']['MSC']['recursiveDownloadFolderSearchLabel']
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

    /** @return mixed[][] */
    private function getElements(FilesModel $objParentFolder, PageModel $objPage, int $level = 1) : array
    {
        $allowedDownload = array_map('trim', explode(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload'])));

        $arrElements = [];
        $arrFolders  = [];
        $arrFiles    = [];

        $objElements = FilesModel::findByPid($objParentFolder->uuid);

        if ($objElements === null) {
            return $arrElements;
        }

        while ($objElements->next()) {
            if ($objElements->type === 'folder') {
                $elements = $this->getElements($objElements, $objPage, $level + 1);

                if (($this->recursiveDownloadFolderHideEmptyFolders && ! empty($elements))
                    || ! $this->recursiveDownloadFolderHideEmptyFolders
                ) {
                    $strCssClass = 'folder';
                    if ($this->recursiveDownloadFolderShowAllLevels) {
                        $strCssClass .= ' folder-open';
                    }
                    if (empty($elements)) {
                        $strCssClass .= ' folder-empty';
                    }

                    $arrFolders[$objElements->name] = [
                        'type'              => $objElements->type,
                        'data'              => $this->getFolderData($objElements, $objPage),
                        'elements'          => $elements,
                        'elements_rendered' => $this->getElementsRendered($elements, $level + 1),
                        'is_empty'          => empty($elements),
                        'css_class'         => $strCssClass,
                    ];
                }
            } else {
                $objFile = new File($objElements->path);

                if (in_array($objFile->extension, $allowedDownload, true) && ! preg_match(
                    '/^meta(_[a-z]{2})?\.txt$/',
                    $objFile->basename
                )) {
                    $arrFileData = $this->getFileData($objFile, $objElements, $objPage);

                    $fileMatches = true;
                    if ($this->recursiveDownloadFolderAllowFileSearch && ! empty(trim(Input::get('keyword')))) {
                        $visibleFileName = $arrFileData['name'];
                        if (! empty($arrFileData['link'])) {
                            $visibleFileName = $arrFileData['link'];
                        }
                        // use exact, case insensitive string search
                        $fileMatches = (stripos($visibleFileName, trim(Input::get('keyword'))) !== false);
                    }

                    if ($fileMatches) {
                        $strCssClass = 'file file-' . $arrFileData['extension'];

                        $arrFiles[$objFile->basename] = [
                            'type'      => $objElements->type,
                            'data'      => $arrFileData,
                            'css_class' => $strCssClass,
                        ];
                    }
                }
            }
        }

        // sort the folders and files alphabetically by their name
        ksort($arrFolders);
        ksort($arrFiles);

        // merge folders and files into one array (foders at first, files afterwards)
        $arrElements = array_values($arrFolders);
        $arrElements = array_merge($arrElements, array_values($arrFiles));

        return $arrElements;
    }

    /**
     * Get all data for a file
     *
     * @return mixed[]
     */
    private function getFileData(File $objFile, Collection $objElements, PageModel $objPage) : array
    {
        $meta = self::getMetaData($objElements->meta, $objPage->language);

        // Use the file name as title if none is given
        if ($meta['title'] === '') {
            $meta['title'] = StringUtil::specialchars($objFile->basename);
        }

        // Use the title as link if none is given
        if ($meta['link'] === '') {
            $meta['link'] = $meta['title'];
        }

        $strHref = Environment::get('request');

        // Remove an existing file parameter (see #5683)
        if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
            $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
        }

        $strHref .= ($GLOBALS['TL_CONFIG']['disableAlias'] || strpos(
            $strHref,
            '?'
        ) !== false ? '&amp;' : '?') . 'file=' . System::urlEncode($objElements->path);

        return [
            'id'        => $objFile->id,
            'uuid'      => $objFile->uuid,
            'name'      => $objFile->basename,
            'title'     => $meta['title'],
            'link'      => $meta['link'],
            'caption'   => $meta['caption'],
            'href'      => $strHref,
            'filesize'  => self::getReadableSize($objFile->filesize, 1),
            'icon'      => TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon,
            'mime'      => $objFile->mime,
            'meta'      => $meta,
            'extension' => $objFile->extension,
            'path'      => $objFile->dirname,
        ];
    }

    /**
     * Get all data for a folder
     *
     * @return mixed[]
     */
    private function getFolderData(FilesModel $objFolder, PageModel $objPage) : array
    {
        $arrMeta = self::getMetaData($objFolder->meta, $objPage->language);

        // Use the folder name as title if none is given
        if ($arrMeta['title'] === '') {
            $arrMeta['title'] = $objFolder->name;
        }

        // Use the title as link if none is given
        if ($arrMeta['link'] === '') {
            $arrMeta['link'] = $arrMeta['title'];
        }

        return [
            'id'    => $objFolder->id,
            'uuid'  => $objFolder->uuid,
            'name'  => $objFolder->name,
            'title' => $arrMeta['title'],
            'link'  => $arrMeta['link'],
            'meta'  => $arrMeta,
            'path'  => $objFolder->path,
        ];
    }

    /** @param mixed[][] $elements */
    private function getElementsRendered(array $elements, int $level = 1) : string
    {
        // Layout template fallback
        if ($this->recursiveDownloadFolderTpl === '') {
            $this->recursiveDownloadFolderTpl = 'recursive-download-folder_default';
        }

        if (count($elements) === 0) {
            return '';
        }

        $template = new FrontendTemplate($this->recursiveDownloadFolderTpl);
        $template->setData(
            [
                'level'    => 'level_' . $level,
                'elements' => $elements,
            ]
        );

        return $template->parse();
    }
}
