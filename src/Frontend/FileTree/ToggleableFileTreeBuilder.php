<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree;

use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use function array_map;
use function array_merge;
use function array_values;
use function explode;
use function in_array;
use function ksort;
use function preg_match;
use function preg_replace;
use function sprintf;
use function stripos;
use function strpos;
use function strtolower;
use function trim;

final class ToggleableFileTreeBuilder
{
    private $templateName = 'recursive-download-folder_default';

    private $hideEmptyFolders = true;

    private $showAllLevels = false;

    private $allowFileSearch = false;

    public function showEmptyFolders() : self
    {
        $this->hideEmptyFolders = false;

        return $this;
    }

    public function showAllLevels() : self
    {
        $this->showAllLevels = true;

        return $this;
    }

    public function allowFileSearch() : self
    {
        $this->allowFileSearch = true;

        return $this;
    }

    public function build(array $uuids) : array
    {
        $folders = FilesModel::findMultipleByUuids($uuids);
        if ($folders === null) {
            return [];

        }

        $tree = [];

        foreach ($folders as $folder) {
            $elements = $this->getElements($folder);
            $tree[] = [
                'type'              => $folder->type,
                'data'              => $this->getFolderData($folder),
                'elements'          => $elements,
                'elements_rendered' => $this->getElementsRendered($elements),
            ];
        }

        return $tree;
    }

    private function getElements(FilesModel $objParentFolder, int $level = 1) : array
    {
        $allowedDownload = array_map('trim', explode(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload'])));

        $elements = [];
        $folders  = [];
        $files    = [];

        $objElements = FilesModel::findByPid($objParentFolder->uuid);

        if ($objElements === null) {
            return $elements;
        }

        foreach ($objElements as $objElement) {
            if ($objElement->type === 'folder') {
                $elements = $this->getElements($objElement, $level + 1);

                if (($this->hideEmptyFolders && ! empty($elements)) || ! $this->hideEmptyFolders) {
                    $strCssClass = 'folder';
                    if ($this->showAllLevels) {
                        $strCssClass .= ' folder-open';
                    }
                    if (empty($elements)) {
                        $strCssClass .= ' folder-empty';
                    }

                    $folders[$objElement->name] = [
                        'type'              => $objElement->type,
                        'data'              => $this->getFolderData($objElement),
                        'elements'          => $elements,
                        'elements_rendered' => $this->getElementsRendered($elements, $level + 1),
                        'is_empty'          => empty($elements),
                        'css_class'         => $strCssClass,
                    ];
                }
            } else {
                $objFile = new File($objElement->path);

                if (in_array($objFile->extension, $allowedDownload, true) && ! preg_match(
                        '/^meta(_[a-z]{2})?\.txt$/',
                        $objFile->basename
                    )) {
                    $arrFileData = $this->getFileData($objFile, $objElement);
                    $fileMatches = true;

                    if ($this->allowFileSearch && ! empty(trim((string) Input::get('keyword')))) {
                        $visibleFileName = $arrFileData['name'];
                        if (! empty($arrFileData['link'])) {
                            $visibleFileName = $arrFileData['link'];
                        }
                        // use exact, case insensitive string search
                        $fileMatches = (stripos($visibleFileName, trim(Input::get('keyword'))) !== false);
                    }

                    if ($fileMatches) {
                        $strCssClass = 'file file-' . $arrFileData['extension'] . ' ext-' . $arrFileData['extension'];

                        $files[$objFile->basename] = [
                            'type'      => $objElement->type,
                            'data'      => $arrFileData,
                            'css_class' => $strCssClass,
                        ];
                    }
                }
            }
        }

        // sort the folders and files alphabetically by their name
        ksort($folders);
        ksort($files);

        // merge folders and files into one array (foders at first, files afterwards)
        $elements = array_values($folders);
        $elements = array_merge($elements, array_values($files));

        return $elements;
    }

    /**
     * Get all data for a file
     *
     * @return mixed[]
     */
    private function getFileData(File $objFile, FilesModel $fileModel) : array
    {
        $meta = Frontend::getMetaData($fileModel->meta, $GLOBALS['TL_LANGUAGE']);

        // Use the file name as title if none is given
        if (!isset($meta['title']) || $meta['title'] == '') {
            $meta['title'] = StringUtil::specialchars($objFile->basename);
        }

        $strHref = Environment::get('request');

        // Remove an existing file parameter (see #5683)
        if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
            $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
        }

        $strHref .= ($GLOBALS['TL_CONFIG']['disableAlias'] || strpos(
                $strHref,
                '?'
            ) !== false ? '&amp;' : '?') . 'file=' . System::urlEncode($fileModel->path);

        return [
            'id'        => $objFile->id,
            'uuid'      => $objFile->uuid,
            'name'      => $objFile->basename,
            'title'     => StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['download'], $objFile->basename)),
            'link'      => $meta['title'],
            'caption'   => $meta['caption'],
            'href'      => $strHref,
            'filesize'  => Frontend::getReadableSize($objFile->filesize),
            'mime'      => $objFile->mime,
            'meta'      => $meta,
            'extension' => $objFile->extension,
            'path'      => $objFile->dirname,
            'ctime'     => $objFile->ctime,
            'mtime'     => $objFile->mtime,
            'atime'     => $objFile->atime
        ];
    }

    /**
     * Get all data for a folder
     *
     * @return mixed[]
     */
    private function getFolderData(FilesModel $objFolder) : array
    {
        $meta = Frontend::getMetaData($objFolder->meta, $GLOBALS['TL_LANGUAGE']);

        // Use the folder name as title if none is given
        if (!isset($meta['title']) || $meta['title'] == '') {
            $meta['title'] = StringUtil::specialchars($objFolder->name);
        }

        return [
            'id'    => $objFolder->id,
            'uuid'  => $objFolder->uuid,
            'name'  => $objFolder->name,
            'title' => $objFolder->name,
            'link'  => $meta['title'],
            'meta'  => $meta,
            'path'  => $objFolder->path,
        ];
    }

    /** @param mixed[][] $elements */
    private function getElementsRendered(array $elements, int $level = 1) : string
    {
        if (count($elements) === 0) {
            return '';
        }

        $template = new FrontendTemplate($this->templateName);
        $template->setData(
            [
                'level'    => 'level_' . $level,
                'elements' => $elements,
            ]
        );

        return $template->parse();
    }
}
