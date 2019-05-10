<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree;

use Contao\FilesModel;

final class ToggleableFileTreeBuilder extends BaseFileTreeBuilder
{
    /** @inheritDoc */
    protected function getChildren(FilesModel $objElement, int $level) : array
    {
        return $this->getElements($objElement, $level);
    }

    /** @inheritDoc */
    protected function generateLink(array $element) : string
    {
        return '#';
    }
}
