<?php

declare(strict_types=1);

namespace Hofff\Contao\RecursiveDownloadFolder\Frontend\FileTree;

use Contao\FilesModel;
use Contao\Input;
use Contao\StringUtil;
use function array_unshift;
use function end;
use function in_array;
use function urldecode;

final class BreadcrumbFileTreeBuilder extends BaseFileTreeBuilder
{
    /** @var FilesModel[] */
    private $breadcrumb = [];

    /** @inheritDoc */
    public function build(array $uuids) : array
    {
        $this->buildBreadcrumb($uuids);

        $data               = parent::build($uuids);
        $data['breadcrumb'] = $this->breadcrumb;

        return $data;
    }

    /** @inheritDoc */
    protected function getChildren(FilesModel $objElement, int $level) : array
    {
        return [];
    }

    /** @inheritDoc */
    protected function generateLink(array $element) : string
    {
        $url = '';

        if (isset($GLOBALS['objPage'])) {
            $url = $GLOBALS['objPage']->getFrontendUrl();
        }

        $url .= '?path=' . urlencode($element['data']['path']);

        return $url;
    }

    /** @param string[] $rootIds */
    private function buildBreadcrumb(array &$rootIds) : void
    {
        $this->breadcrumb = [];

        $path = Input::get('path');
        if (! $path || $path === '/') {
            if (count ($rootIds) === 1) {
                $folder = FilesModel::findByUuid($rootIds[0]);

                if ($folder) {
                    array_unshift($this->breadcrumb, $folder);
                }
            }

            return;
        }

        $path   = urldecode($path);
        $folder = FilesModel::findByPath($path);
        $safe   = false;

        while ($folder && $folder->type === 'folder') {
            array_unshift($this->breadcrumb, $folder);
            $safe = in_array($folder->uuid, $rootIds, true);

            if ($folder->pid === null || $safe) {
                break;
            }

            $folder = FilesModel::findByUuid($folder->pid);
        }

        // Breadcrumb is in defined root folders.
        if (! $safe) {
            $this->breadcrumb = [];
        } elseif ($this->breadcrumb) {
            $last    = end($this->breadcrumb);
            $rootIds = [$last->uuid];
        }
    }

    protected function getElements(FilesModel $objParentFolder, int $level = 1): array
    {
        if ($level !== 1 && !$this->breadcrumb) {
            return [];
        }

        return parent::getElements($objParentFolder, $level);
    }
}
