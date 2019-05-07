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

final class ToggleableFileTreeBuilder extends AbstractFileTreeBuilder
{
    protected function getElements(FilesModel $objParentFolder, int $level = 1) : array
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
}
