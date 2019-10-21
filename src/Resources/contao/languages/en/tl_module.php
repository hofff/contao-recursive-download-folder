<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_module']['folderSRC']                               = [
    'Source folder',
    'Please select a folder from the file tree, whose content shall be recursively available for download.',
];
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderHideEmptyFolders'] = [
    'Hide empty folders',
    'Choose this option if empty folders should not be visible in front end.',
];
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderShowAllLevels']    = [
    'Show all levels',
    'Choose this option if the folder tree initially should be expanded in the front end.',
];
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderAllowFileSearch']  = [
    'Allow file search',
    'Choose this option if searching for files should be allowed in the front end.',
];
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderTpl']              = [
    'Template for recursive download folder',
    'Please select the template for the recursive download folder.',
];
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderMode']             = [
    'Display mode',
    'Please choose the mode how the recursive module should work.',
];
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderVisibleRoot']      = [
    'Render root folder',
    'Force rendering of root folder (Always rendered if more than one folder is selected).',
];
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderAllowAll']         = [
    'Ignore setting allowed downloads',
    'List all files no matter if they are defined in the ignore download setting',
];

$GLOBALS['TL_LANG']['tl_module']['recursive-download-folder_legend'] = 'Recursive download folder settings';

$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderModes']['breadcrumb'][0] = 'Folder navigation with breadcrumb';
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderModes']['breadcrumb'][1] = 'Folder navigation with breadcrumb.';
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderModes']['toggleable'][0] = 'Toggleable folder tree';
$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderModes']['toggleable'][1] = 'All folders are toggleable without a page reload.';
