<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database\Result;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\Input;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree\BreadcrumbFileTreeBuilder;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree\FileTreeBuilder;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree\ToggleableFileTreeBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ZipArchive;

use function assert;
use function basename;
use function count;
use function defined;
use function dirname;
use function end;
use function file_exists;
use function in_array;
use function is_array;
use function mb_convert_encoding;
use function mb_strtoupper;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function sprintf;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function sys_get_temp_dir;
use function tempnam;
use function trim;

trait RecursiveDownloadFolderTrait
{
    /**
     * Files object
     *
     * @var FilesModel|Collection|null
     */
    protected $objFolder;

    /**
     * @param Model|Result $objElement
     */
    public function __construct($objElement, string $strColumn = 'main')
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        parent::__construct($objElement, $strColumn);

        $this->folderSRC = StringUtil::deserialize($this->folderSRC, true);
    }

    /**
     * Return if there are no files
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function generate(): string
    {
        if (defined('TL_MODE') && TL_MODE === 'BE') {
            $template           = new BackendTemplate('be_wildcard');
            $template->wildcard = sprintf(
                '### %s ###',
                mb_strtoupper($GLOBALS['TL_LANG']['FMD']['hofff_recursive-download-folder'][0], 'UTF-8')
            );
            /** @psalm-suppress UndefinedThisPropertyFetch */
            $template->title = $this->headline ?: $this->name;

            return $template->parse();
        }

        // Use the home directory of the current user as file source
        if ($this->useHomeDir && FE_USER_LOGGED_IN) {
            $this->import('FrontendUser', 'User');

            /** @psalm-suppress UndefinedThisPropertyFetch */
            if ($this->User->assignDir && $this->User->homeDir) {
                $this->folderSRC = [$this->User->homeDir];
            }
        }

        // Return if there is no folder defined
        if (empty($this->folderSRC)) {
            return '';
        }

        // Get the folders from the database
        $collection = FilesModel::findMultipleByUuids($this->folderSRC);
        if (! $collection instanceof Collection) {
            return '';
        }

        $this->objFolder = $collection;
        $file            = Input::get('file', true);

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ($file !== '' && $file !== null) {
            foreach ($this->objFolder as $folder) {
                if (strpos(dirname($file), $folder->path) !== 0) {
                    continue;
                }

                $this->downloadAsFile($file);
            }
        }

        return parent::generate();
    }

    /**
     * Generate the content element
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function compile(): void
    {
        $treeBuilder = $this->createTreeBuilder();
        $fileTree    = $treeBuilder->build($this->folderSRC);

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->cssID = [
            $this->cssID[0],
            trim(
                $this->cssID[1]
                . ' hofff-recursive-download-folder-'
                . ($this->recursiveDownloadFolderMode ?: 'toggleable')
            ),
        ];

        $this->Template->generateBreadcrumbLink = static function (string $path): string {
            $url = '';

            if (isset($GLOBALS['objPage'])) {
                $url = $GLOBALS['objPage']->getFrontendUrl();
            }

            $url .= '?path=' . $path;

            return $url;
        };

        if ($fileTree['tree']) {
            $this->Template->breadcrumb    = $fileTree['breadcrumb'];
            $this->Template->showRootLevel = $this->recursiveDownloadFolderVisibleRoot || count($this->folderSRC) > 1;
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

        if (defined('TL_MODE') && TL_MODE !== 'BE') {
            return;
        }

        // only load the default JS and CSS in backend, for frontend this will be done in template
        $GLOBALS['TL_CSS']['recursive-download-folder.css'] =
            'bundles/hofffcontaorecursivedownloadfolder/css/recursive-download-folder.min.css||static';

        $GLOBALS['TL_JAVASCRIPT']['recursive-download-folder.js'] =
            'bundles/hofffcontaorecursivedownloadfolde/js/recursive-download-folder.min.js';
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function createTreeBuilder(): FileTreeBuilder
    {
        if ($this->recursiveDownloadFolderMode === 'breadcrumb') {
            $treeBuilder = new BreadcrumbFileTreeBuilder();
        } else {
            $treeBuilder = new ToggleableFileTreeBuilder();
        }

        $treeBuilder->withThumbnailSize(StringUtil::deserialize($this->size, true));

        if ($this->recursiveDownloadFolderTpl) {
            $treeBuilder->withTemplate($this->recursiveDownloadFolderTpl);
        }

        if ($this->recursiveDownloadFolderAllowFileSearch) {
            $treeBuilder->allowFileSearch();

            if ($this->recursiveDownloadFolderShowAllSearchResults) {
                $treeBuilder->showAllSearchResults();
            }
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

        if ($this->recursiveDownloadFolderAllowAll) {
            $treeBuilder->ignoreAllowedDownloads();
        }

        if ($this->recursiveDownloadFolderZipDownload) {
            $treeBuilder->allowFolderDownload();
        }

        return $treeBuilder;
    }

    protected function downloadAsFile(string $path): void
    {
        $file = FilesModel::findOneByPath($path);
        if (! $file instanceof FilesModel) {
            throw new BadRequestException();
        }

        if ($file->type === 'file') {
            $this->sendFile($file->path);

            return;
        }

        if (! $this->recursiveDownloadFolderZipDownload) {
            throw new NotFoundHttpException();
        }

        $treeBuilder = $this->createTreeBuilder();

        $data = $treeBuilder->build([$file->uuid]);

        $zipFile    = tempnam(sys_get_temp_dir(), 'hofff-recursive-download-zip') . '.zip';
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipFile, ZipArchive::CREATE);

        $this->addElementsToZip($data['tree']['elements'], $zipArchive, $file->path);
        $zipArchive->close();

        $response = new BinaryFileResponse($zipFile);
        $response->setPrivate(); // public by default
        $response->setAutoEtag();

        $filename = basename($file->path) . '.zip';
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
            mb_convert_encoding($filename, 'UTF-8', 'ASCII')
        );
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->set('Connection', 'close');
        $response->headers->set('Content-Type', 'application/zip');

        throw new ResponseException($response);
    }

    /** @param array<int,array<string,mixed>> $elements */
    protected function addElementsToZip(array $elements, ZipArchive $zipArchive, string $rootPath): void
    {
        assert(defined('TL_ROOT'));

        foreach ($elements as $element) {
            switch ($element['type']) {
                case 'file':
                    $zipArchive->addFile(
                        TL_ROOT . '/' . $element['model']->path,
                        substr($element['model']->path, strlen($rootPath))
                    );
                    break;

                case 'folder':
                    $this->addElementsToZip($element['elements'], $zipArchive, $rootPath);
                    break;
            }
        }
    }

    /**
     * Send a file to the browser so the "save as …" dialogue opens
     *
     * @param string $strFile The file path
     *
     * @throws AccessDeniedException
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function sendFile(string $strFile): void
    {
        assert(defined('TL_ROOT'));

        // Make sure there are no attempts to hack the file system
        if (preg_match('@^\.+@', $strFile) || preg_match('@\.+/@', $strFile) || preg_match('@(://)+@', $strFile)) {
            throw new PageNotFoundException('Invalid file name');
        }

        // Limit downloads to the files directory
        if (! preg_match('@^' . preg_quote(Config::get('uploadPath'), '@') . '@i', $strFile)) {
            throw new PageNotFoundException('Invalid path');
        }

        // Check whether the file exists
        if (! file_exists(TL_ROOT . '/' . $strFile)) {
            throw new PageNotFoundException('File not found');
        }

        $objFile         = new File($strFile);
        $arrAllowedTypes = StringUtil::trimsplit(',', strtolower(Config::get('allowedDownload')));

        // Check whether the file type is allowed to be downloaded
        if (! $this->recursiveDownloadFolderAllowAll && ! in_array($objFile->extension, $arrAllowedTypes)) {
            throw new AccessDeniedException(sprintf('File type "%s" is not allowed', $objFile->extension));
        }

        // HOOK: post download callback
        if (isset($GLOBALS['TL_HOOKS']['postDownload']) && is_array($GLOBALS['TL_HOOKS']['postDownload'])) {
            foreach ($GLOBALS['TL_HOOKS']['postDownload'] as $callback) {
                static::importStatic($callback[0])->{$callback[1]}($strFile);
            }
        }

        // Send the file (will stop the script execution)
        $objFile->sendToBrowser();
    }
}
