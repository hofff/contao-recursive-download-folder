<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package Hofff_recursive-download-folder
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Hofff',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Elements
	'Hofff\Contao\RecursiveDownloadFolder\ContentRecursiveDownloadFolder' => 'system/modules/hofff_recursive-download-folder/elements/ContentRecursiveDownloadFolder.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'ce_recursive-download-folder'      => 'system/modules/hofff_recursive-download-folder/templates/elements',
	'recursive-download-folder_default' => 'system/modules/hofff_recursive-download-folder/templates/recursive-download-folder',
));
