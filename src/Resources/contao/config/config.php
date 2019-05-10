<?php

declare(strict_types=1);

use Hofff\Contao\RecursiveDownloadFolder\Frontend\Element\RecursiveDownloadFolderElement;
use Hofff\Contao\RecursiveDownloadFolder\Frontend\Module\RecursiveDownloadFolderModule;

/*
 * Content elements
 */

$GLOBALS['TL_CTE']['files']['hofff_recursive-download-folder']    = RecursiveDownloadFolderElement::class;
$GLOBALS['FE_MOD']['application']['hofff_recursive-download-folder'] = RecursiveDownloadFolderModule::class;
