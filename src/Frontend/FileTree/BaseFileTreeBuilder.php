<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree;

use Contao\Config;
use Contao\CoreBundle\Image\Studio\FigureBuilder;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use Exception;

use function array_map;
use function array_merge;
use function array_values;
use function assert;
use function explode;
use function in_array;
use function ksort;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_contains;
use function stripos;
use function strtolower;
use function trim;

/**
 * @psalm-type TFileMetaData = array{
 *   id: string|int,
 *   uuid: string|null,
 *   name: string,
 *   title: string,
 *   link: string,
 *   caption: string,
 *   href: string|null,
 *   filesize: string,
 *   mime: string,
 *   meta: array<string,mixed>,
 *   extension: string,
 *   path: string,
 *   ctime: int,
 *   mtime: int,
 *   atime: int,
 * }
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class BaseFileTreeBuilder implements FileTreeBuilder
{
    protected string $templateName = 'recursive-download-folder_default';

    protected bool $hideEmptyFolders = false;

    protected bool $showAllLevels = false;

    protected bool $allowFileSearch = false;

    protected bool $showAllSearchResults = false;

    protected bool $alwaysShowRoot = false;

    protected bool $ignoreAllowedDownloads = false;

    protected bool $allowFolderDownload = false;

    /** @var array<int,string>|null */
    protected array|null $thumbnailSize = null;

    /** @var array<string,array<string,TFileMetaData>> */
    private array $metaDataCache = [];

    private readonly Studio $studio;

    public function __construct(Studio|null $studio = null)
    {
        /** @psalm-suppress PropertyTypeCoercion */
        $this->studio = $studio ?? System::getContainer()->get('contao.image.studio');
    }

    public function withTemplate(string $templateName): FileTreeBuilder
    {
        $this->templateName = $templateName;

        return $this;
    }

    /** @param array<int,string> $thumbnailSize */
    public function withThumbnailSize(array $thumbnailSize): FileTreeBuilder
    {
        $this->thumbnailSize = $thumbnailSize;

        return $this;
    }

    public function hideEmptyFolders(): FileTreeBuilder
    {
        $this->hideEmptyFolders = true;

        return $this;
    }

    public function showAllLevels(): FileTreeBuilder
    {
        $this->showAllLevels = true;

        return $this;
    }

    public function allowFileSearch(): FileTreeBuilder
    {
        $this->allowFileSearch = true;

        return $this;
    }

    public function showAllSearchResults(): FileTreeBuilder
    {
        $this->showAllSearchResults = true;

        return $this;
    }

    public function alwaysShowRoot(): FileTreeBuilder
    {
        $this->alwaysShowRoot = true;

        return $this;
    }

    public function ignoreAllowedDownloads(): FileTreeBuilder
    {
        $this->ignoreAllowedDownloads = true;

        return $this;
    }

    public function allowFolderDownload(): FileTreeBuilder
    {
        $this->allowFolderDownload = true;

        return $this;
    }

    /** @inheritDoc */
    public function build(array $uuids): array
    {
        $folders = FilesModel::findMultipleByUuids($uuids);
        if (! $folders instanceof Collection) {
            return [];
        }

        /** @psalm-suppress PossiblyInvalidCast */
        if (
            $this->allowFileSearch && $this->showAllSearchResults &&
            trim((string) Input::get('keyword')) !== ''
        ) {
            $this->showAllLevels();
        }

        $tree  = [];
        $level = $folders->count() > 1 || $this->alwaysShowRoot ? 2 : 1;

        foreach ($folders as $folder) {
            $elements = $this->getElements($folder, $level);
            $tree[]   = [
                'type'              => $folder->type,
                'href'              => $this->generateDownloadLink($folder),
                'model'             => $folder,
                'data'              => $this->getFolderData($folder),
                'elements'          => $elements,
                'elements_rendered' => $this->getElementsRendered($elements, $level),
                'css_class'         => '',
                'is_empty'          => $elements === [],
            ];
        }

        if ($level === 2) {
            $tree = [
                'type'              => 'folder',
                'data'              => ['name' => '/'],
                'elements'          => $tree,
                'elements_rendered' => $this->getElementsRendered($tree, $level),
                'css_class'         => '',
                'is_empty'          => $tree === [],
            ];
        } else {
            $tree = $tree[0];
        }

        return [
            'breadcrumb' => [],
            'tree'       => $tree,
        ];
    }

    /**
     * @return mixed[][]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getElements(FilesModel $objParentFolder, int $level = 1): array
    {
        $elements = [];
        $folders  = [];
        $files    = [];

        $objElements = FilesModel::findByPid($objParentFolder->uuid);

        if (! $objElements instanceof Collection) {
            return $elements;
        }

        foreach ($objElements as $objElement) {
            if ($objElement->type === 'folder') {
                $elements    = $this->getChildren($objElement, $level + 1);
                $hasChildren = $this->hasChildren($objElement, $level + 1);

                if (($this->hideEmptyFolders && $hasChildren) || ! $this->hideEmptyFolders) {
                    $strCssClass = 'folder';
                    if ($this->showAllLevels) {
                        $strCssClass .= ' folder-open';
                    }

                    if (! $hasChildren) {
                        $strCssClass .= ' folder-empty';
                    }

                    $folders[$objElement->name] = [
                        'type'              => $objElement->type,
                        'model'             => $objElement,
                        'data'              => $this->getFolderData($objElement),
                        'elements'          => $elements,
                        'elements_rendered' => $this->getElementsRendered($elements, $level + 1),
                        'is_empty'          => ! $hasChildren,
                        'css_class'         => $strCssClass,
                    ];
                }
            } elseif ($this->isAllowed($objElement->extension)) {
                $arrFileData = $this->getMetaData($objElement);

                if ($this->matches($arrFileData)) {
                    $strCssClass = 'file file-' . $arrFileData['extension'] . ' ext-' . $arrFileData['extension'];

                    $files[$arrFileData['name']] = [
                        'type'      => $objElement->type,
                        'model'     => $objElement,
                        'data'      => $arrFileData,
                        'is_empty'  => false,
                        'css_class' => $strCssClass,
                    ];
                }
            }
        }

        // sort the folders and files alphabetically by their name
        ksort($folders);
        ksort($files);

        // merge folders and files into one array (foders at first, files afterwards)
        $elements = array_values($folders);

        return array_merge($elements, array_values($files));
    }

    /**
     * @return array<string,mixed>
     * @psalm-return TFileMetaData
     *
     * @throws Exception
     */
    protected function getMetaData(FilesModel $file): array
    {
        /** @psalm-suppress DeprecatedMethod */
        return $this->getFileData(new File($file->path), $file);
    }

    /**
     * Get all data for a file.
     *
     * @deprecated Use BaseFileTreeBuilder::getMetaData() instead
     *
     * @return array<string,mixed>
     * @psalm-return TFileMetaData
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getFileData(File $objFile, FilesModel $fileModel): array
    {
        if (isset($this->metaDataCache[$GLOBALS['TL_LANGUAGE']][$fileModel->path])) {
            return $this->metaDataCache[$GLOBALS['TL_LANGUAGE']][$fileModel->path];
        }

        /**
         * @psalm-suppress PossiblyInvalidArgument
         * @psalm-var array<string,mixed> $meta
         */
        $meta = Frontend::getMetaData($fileModel->meta, $GLOBALS['TL_LANGUAGE']);

        // Use the file name as title if none is given
        if (! isset($meta['title']) || $meta['title'] === '') {
            $meta['title'] = StringUtil::specialchars($objFile->basename);
        }

        $metadata = [
            'id'        => $fileModel->id,
            'uuid'      => $fileModel->uuid,
            'name'      => $objFile->basename,
            'title'     => StringUtil::specialchars(
                sprintf($GLOBALS['TL_LANG']['MSC']['download'], $objFile->basename),
            ),
            'link'      => (string) $meta['title'],
            'caption'   => (string) $meta['caption'],
            'href'      => $this->generateDownloadLink($fileModel),
            'filesize'  => Frontend::getReadableSize($objFile->filesize),
            'mime'      => $objFile->mime,
            'meta'      => $meta,
            'extension' => $objFile->extension,
            'path'      => $objFile->dirname,
            'ctime'     => (int) $objFile->ctime,
            'mtime'     => (int) $objFile->mtime,
            'atime'     => (int) $objFile->atime,
        ];

        $this->metaDataCache[$GLOBALS['TL_LANGUAGE']][$fileModel->path] = $metadata;

        return $metadata;
    }

    /**
     * Get all data for a folder
     *
     * @return mixed[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getFolderData(FilesModel $objFolder): array
    {
        /** @psalm-suppress PossiblyInvalidArgument */
        $meta = Frontend::getMetaData($objFolder->meta, $GLOBALS['TL_LANGUAGE']);

        // Use the folder name as title if none is given
        if (! isset($meta['title']) || $meta['title'] === '') {
            $meta['title'] = StringUtil::specialchars($objFolder->name);
        }

        return [
            'id'    => $objFolder->id,
            'href'  => $this->generateDownloadLink($objFolder),
            'uuid'  => $objFolder->uuid,
            'name'  => $objFolder->name,
            'title' => $objFolder->name,
            'link'  => $meta['title'],
            'meta'  => $meta,
            'path'  => $objFolder->path,
        ];
    }

    /** @param mixed[][] $elements */
    protected function getElementsRendered(array $elements, int $level = 1): string
    {
        if ($elements === []) {
            return '';
        }

        $template = new FrontendTemplate($this->templateName);
        $template->setData(
            [
                'level'    => 'level_' . $level,
                'elements' => $elements,
                'generateLink' => fn (array $element): string => $this->generateLink($element),
                'generateThumbnail' => fn (array $element): string => $this->generateThumbnail($element),
            ],
        );

        return $template->parse();
    }

    protected function isAllowed(string $extension): bool
    {
        static $allowedDownloads = null;

        if ($this->ignoreAllowedDownloads) {
            return true;
        }

        if ($allowedDownloads === null) {
            $allowedDownloads = array_map('trim', explode(',', strtolower((string) Config::get('allowedDownload'))));
        }

        return in_array($extension, $allowedDownloads, true);
    }

    /** @param TFileMetaData $metadata */
    protected function matches(array $metadata): bool
    {
        /** @psalm-suppress PossiblyInvalidCast */
        $keyword = trim((string) Input::get('keyword'));
        if (! $this->allowFileSearch || $keyword === '') {
            return true;
        }

        $visibleFileName = $metadata['name'];
        if (! empty($metadata['link'])) {
            $visibleFileName = $metadata['link'];
        }

        // use exact, case-insensitive string search
        return stripos($visibleFileName, $keyword) !== false;
    }

    /**
     * Will return all visible children of an element.
     *
     * @return mixed[][]
     */
    abstract protected function getChildren(FilesModel $objElement, int $level): array;

    /**
     * Will count if an element has children. It should also contain true even if the children are not visible.
     */
    protected function hasChildren(FilesModel $element, int $level): bool
    {
        $children = FilesModel::findByPid($element->uuid);

        if (! $children instanceof Collection) {
            return false;
        }

        foreach ($children as $child) {
            if ($child->type === 'folder') {
                if ($this->hasChildren($child, $level + 1)) {
                    return true;
                }
            } elseif ($this->isAllowed($child->extension)) {
                $arrFileData = $this->getMetaData($child);

                if ($this->matches($arrFileData)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @param mixed[] $element */
    abstract protected function generateLink(array $element): string;

    protected function generateDownloadLink(FilesModel $model): string|null
    {
        if ($model->type === 'folder' && ! $this->allowFolderDownload) {
            return null;
        }

        $strHref = Environment::get('request');

        // Remove an existing file parameter (see #5683)
        if (preg_match('/(&(amp;)?|\?)file=/', (string) $strHref)) {
            $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', (string) $strHref);
        }

        return $strHref . ((Config::get('disableAlias') || str_contains((string) $strHref, '?') ? '&amp;' : '?')
            . 'file='
            . System::urlEncode($model->path));
    }

    /** @param array<string,mixed> $element */
    protected function generateThumbnail(array $element): string
    {
        $model = $element['model'];
        assert($model instanceof FilesModel);

        if ($model->type !== 'file') {
            return '';
        }

        $imageExtensions = StringUtil::trimsplit(',', Config::get('validImageTypes'));
        if (! in_array($model->extension, $imageExtensions)) {
            return '';
        }

        $template = new FrontendTemplate('image');
        /**
         * @psalm-suppress PossiblyNullReference
         * @psalm-var FigureBuilder $figureBuilder
         */
        $figureBuilder = $this->studio->createFigureBuilder();
        $figureBuilder->fromFilesModel($model)
            ->setSize($this->thumbnailSize)
            ->build()
            ->applyLegacyTemplateData($template);

        return $template->parse();
    }
}
