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

abstract class AbstractFileTreeBuilder implements FileTreeBuilder
{
    protected $templateName = 'recursive-download-folder_default';

    protected $hideEmptyFolders = false;

    protected $showAllLevels = false;

    protected $allowFileSearch = false;

    public function hideEmptyFolders() : FileTreeBuilder
    {
        $this->hideEmptyFolders = true;

        return $this;
    }

    public function showAllLevels() : FileTreeBuilder
    {
        $this->showAllLevels = true;

        return $this;
    }

    public function allowFileSearch() : FileTreeBuilder
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

    abstract protected function getElements(FilesModel $folder) : array;

    /**
     * Get all data for a file
     *
     * @return mixed[]
     */
    protected function getFileData(File $objFile, FilesModel $fileModel) : array
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
    protected function getFolderData(FilesModel $objFolder) : array
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
    protected function getElementsRendered(array $elements, int $level = 1) : string
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
