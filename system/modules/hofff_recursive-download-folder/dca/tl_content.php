<?php

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_recursive-download-folder']
	= '{type_legend},type,headline'
	. ';{source_legend},folderSRC,useHomeDir'
	. ';{recursive-download-folder_legend},recursiveDownloadFolderHideEmptyFolders'
	. ';{template_legend:hide},recursiveDownloadFolderTpl,customTpl'
	. ';{protected_legend:hide},protected'
	. ';{expert_legend:hide},guests,cssID,space'
	. ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['folderSRC'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['tl_content']['folderSRC'],
	'exclude'	=> true,
	'inputType'	=> 'fileTree',
	'eval'	=> array('fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
	'sql'	=> "binary(16) NULL"
);
$GLOBALS['TL_DCA']['tl_content']['fields']['recursiveDownloadFolderHideEmptyFolders'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderHideEmptyFolders'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'	=> array('tl_class'=>'w50'),
	'sql'	=> "char(1) NOT NULL default ''" 
);
$GLOBALS['TL_DCA']['tl_content']['fields']['recursiveDownloadFolderTpl'] = array
(
	'label'		=> &$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderTpl'],
	'exclude'	=> true,
	'inputType'	=> 'select',
	'options'	=> \Controller::getTemplateGroup('recursive-download-folder_'),
	'eval'	=> array('tl_class'=>'w50'),
	'sql'	=> "varchar(64) NOT NULL default ''"
);
