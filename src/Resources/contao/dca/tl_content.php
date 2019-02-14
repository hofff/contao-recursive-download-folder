<?php

declare(strict_types=1);

use Contao\Controller;

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_recursive-download-folder']
    = '{type_legend},type,headline'
    . ';{source_legend},folderSRC,useHomeDir'
    . ';{recursive-download-folder_legend},recursiveDownloadFolderHideEmptyFolders'
    . ',recursiveDownloadFolderShowAllLevels,recursiveDownloadFolderAllowFileSearch'
    . ';{template_legend:hide},recursiveDownloadFolderTpl,customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID,space'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['folderSRC']                               = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['folderSRC'],
    'exclude'   => true,
    'inputType' => 'fileTree',
    'eval'      => ['fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'],
    'sql'       => 'binary(16) NULL',
];
$GLOBALS['TL_DCA']['tl_content']['fields']['recursiveDownloadFolderHideEmptyFolders'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderHideEmptyFolders'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['recursiveDownloadFolderShowAllLevels']    = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderShowAllLevels'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['recursiveDownloadFolderAllowFileSearch']  = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderAllowFileSearch'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['recursiveDownloadFolderTpl']              = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderTpl'],
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => Controller::getTemplateGroup('recursive-download-folder_'),
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "varchar(64) NOT NULL default ''",
];
