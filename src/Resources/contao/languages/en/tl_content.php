<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_content']['folderSRC']                               = [
    'Source folder',
    'Please select a folder from the file tree, whose content shall be recursively available for download.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderHideEmptyFolders'] = [
    'Hide empty folders',
    'Choose this option if empty folders should not be visible in front end.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderShowAllLevels']    = [
    'Show all levels',
    'Choose this option if the folder tree initially should be expanded in the front end.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderAllowFileSearch']  = [
    'Allow file search',
    'Choose this option if searching for files should be allowed in the front end.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderTpl']              = [
    'Template for recursive download folder',
    'Please select the template for the recursive download folder.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderMode']              = [
    'Display mode',
    'Please choose the mode how the recursive module should work.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderVisibleRoot']              = [
    'Render root folder',
    'Force rendering of root folder (Always rendered if more than one folder is selected).',
];

$GLOBALS['TL_LANG']['tl_content']['recursive-download-folder_legend'] = 'Recursive download folder settings';

$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderModes']['breadcrumb'][0] = 'Folder navigation with breadcrumb';
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderModes']['breadcrumb'][1] = 'Folder navigation with breadcrumb.';
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderModes']['toggleable'][0] = 'Toggleable folder tree';
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderModes']['toggleable'][1] = 'All folders are toggleable without a page reload.';
