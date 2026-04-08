<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree;

use Contao\FilesModel;
use Override;

final class ToggleableFileTreeBuilder extends BaseFileTreeBuilder
{
    /** @inheritDoc */
    #[Override]
    protected function getChildren(FilesModel $objElement, int $level): array
    {
        return $this->getElements($objElement, $level);
    }

    /** @inheritDoc */
    #[Override]
    protected function generateLink(array $element): string
    {
        return '#';
    }
}
