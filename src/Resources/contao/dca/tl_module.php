<?php

declare(strict_types=1);

use Contao\Controller;

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_recursive-download-folder']
    = '{type_legend},name,headline,type'
    . ';{source_legend},folderSRC,useHomeDir'
    . ';{recursive-download-folder_legend},recursiveDownloadFolderMode,recursiveDownloadFolderHideEmptyFolders'
    . ',recursiveDownloadFolderShowAllLevels,recursiveDownloadFolderAllowFileSearch,recursiveDownloadFolderVisibleRoot'
    . ',recursiveDownloadFolderAllowAll'
    . ';{template_legend:hide},recursiveDownloadFolderTpl,customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID,space'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['fields']['folderSRC'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['folderSRC'],
    'exclude'   => true,
    'inputType' => 'fileTree',
    'eval'      => ['fieldType' => 'checkbox', 'multiple' => true, 'mandatory' => true, 'tl_class' => 'clr'],
    'sql'       => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['recursiveDownloadFolderMode'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderMode'],
    'exclude'   => true,
    'default'   => 'toggleable',
    'inputType' => 'select',
    'options'   => ['toggleable', 'breadcrumb'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderModes'],
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "varchar(12) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['recursiveDownloadFolderHideEmptyFolders'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderHideEmptyFolders'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50 m12'],
    'sql'       => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['recursiveDownloadFolderShowAllLevels'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderShowAllLevels'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['recursiveDownloadFolderAllowFileSearch'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderAllowFileSearch'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['recursiveDownloadFolderTpl'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderTpl'],
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => Controller::getTemplateGroup('recursive-download-folder_'),
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['recursiveDownloadFolderVisibleRoot'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderVisibleRoot'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['recursiveDownloadFolderAllowAll'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['recursiveDownloadFolderAllowAll'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];
