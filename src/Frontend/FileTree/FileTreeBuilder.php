<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree;

interface FileTreeBuilder
{
    public function hideEmptyFolders() : self;

    public function showAllLevels() : self;

    public function allowFileSearch() : self;

    public function alwaysShowRoot() : self;

    public function ignoreAllowedDownloads() : self;

    public function build(array $uuids) : array;
}
