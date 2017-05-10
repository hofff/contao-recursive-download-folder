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
 * Run in a custom namespace, so the class can be replaced
 */
namespace Hofff\Contao\RecursiveDownloadFolder;


/**
 * Class ContentRecursiveDownloadFolder
 *
 * Front end content element "hofff_recursive-download-folder".
 * @copyright  Hofff.com 2015-2015
 * @author     Cliff Parnitzky <cliff@hofff.com> 
 * @package    Hofff_recursive-download-folder
 */
class ContentRecursiveDownloadFolder extends \Contao\ContentElement
{

	/**
	 * Files object
	 * @var \FilesModel
	 */
	protected $objFolder;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_recursive-download-folder';


	/**
	 * Return if there are no files
	 * @return string
	 */
	public function generate()
	{
		// Use the home directory of the current user as file source
		if ($this->useHomeDir && FE_USER_LOGGED_IN)
		{
			$this->import('FrontendUser', 'User');

			if ($this->User->assignDir && $this->User->homeDir)
			{
				$this->folderSRC = $this->User->homeDir;
			}
		}

		// Return if there is no folder defined
		if (empty($this->folderSRC))
		{
			return '';
		}

		// Get the folders from the database
		$this->objFolder = \FilesModel::findByUuid($this->folderSRC);
		
		if ($this->objFolder === null)
		{
			if (!\Validator::isUuid($this->folderSRC[0]))
			{
				return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
			}

			return '';
		}

		$file = \Input::get('file', true);

		// Send the file to the browser and do not send a 404 header (see #4632)
		if ($file != '' && !preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file)))
		{
			if (strpos(dirname($file), $this->objFolder->path) !== FALSE)
			{
				\Controller::sendFileToBrowser($file);
			}
		}

		return parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		global $objPage;

		$elements = $this->getElements($this->objFolder, $objPage);
		$fileTree = array
		(
			'type'              => $this->objFolder->type,
			'data'              => $this->getFolderData($this->objFolder, $objPage),
			'elements'          => $elements,
			'elements_rendered' => $this->getElementsRendered($elements)
		);
		
		$this->Template->fileTree   = $fileTree;
		$this->Template->elements   = $fileTree['elements_rendered'];
		$this->Template->searchable = $this->recursiveDownloadFolderAllowFileSearch;
		if ($this->recursiveDownloadFolderAllowFileSearch)
		{
			$this->Template->action = ampersand(\Environment::get('indexFreeRequest')); 
			$this->Template->keyword = trim(\Input::get('keyword')); 
			$this->Template->keywordLabel = specialchars($GLOBALS['TL_LANG']['MSC']['recursiveDownloadFolderKeywordLabel']);
			$this->Template->searchLabel = specialchars($GLOBALS['TL_LANG']['MSC']['recursiveDownloadFolderSearchLabel']);
		}
		
		if (TL_MODE == 'BE')
		{
			// only load the default JS and CSS in backend, for frontend this will be done in template
			$GLOBALS['TL_CSS']['recursive-download-folder.css'] = 'system/modules/hofff_recursive-download-folder/assets/css/recursive-download-folder.min.css||static';
			$GLOBALS['TL_JAVASCRIPT']['recursive-download-folder.js'] = 'system/modules/hofff_recursive-download-folder/assets/js/recursive-download-folder.min.js';
		}
	}
	
	private function getElements ($objParentFolder, $objPage, $level=1)
	{
		$allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));
		
		$arrElements = array();
		$arrFolders = array();
		$arrFiles = array();
		
		$objElements = \FilesModel::findByPid($objParentFolder->uuid);

		if ($objElements === null)
		{
			return $arrElements;
		}
		
		while ($objElements->next())
		{
			if ($objElements->type == 'folder')
			{
				$elements = $this->getElements($objElements, $objPage, $level+1);
				
				if ($this->recursiveDownloadFolderHideEmptyFolders && !empty($elements) || !$this->recursiveDownloadFolderHideEmptyFolders)
				{
					$strCssClass = 'folder';
					if ($this->recursiveDownloadFolderShowAllLevels)
					{
						$strCssClass .= ' folder-open';
					}
					if (empty($elements))
					{
						$strCssClass .= ' folder-empty';
					}
					
					$arrFolders[$objElements->name] = array
					(
						'type'     => $objElements->type,
						'data'     => $this->getFolderData($objElements, $objPage),
						'elements' => $elements,
						'elements_rendered' => $this->getElementsRendered($elements, $level+1),
						'is_empty' => empty($elements),
						'css_class' => $strCssClass
					);
				}
			}
			else
			{
				$objFile = new \File($objElements->path, true);

				if (in_array($objFile->extension, $allowedDownload) && !preg_match('/^meta(_[a-z]{2})?\.txt$/', $objFile->basename))
				{
					$arrFileData = $this->getFileData($objFile, $objElements, $objPage);
					
					$fileMatches = true;
					if ($this->recursiveDownloadFolderAllowFileSearch && !empty(trim(\Input::get('keyword'))))
					{
						$visibleFileName = $arrFileData['name'];
						if (!empty($arrFileData['link']))
						{
							$visibleFileName = $arrFileData['link'];
						}
						// use exact, case insensitive string search
            $fileMatches = (stripos($visibleFileName, trim(\Input::get('keyword'))) !== FALSE);
					}

					if ($fileMatches)
					{
						$strCssClass = 'file file-' . $arrFileData['extension'];
						
						$arrFiles[$objFile->basename] = array
						(
							'type'      => $objElements->type,
							'data'      => $arrFileData,
							'css_class' => $strCssClass
						);
					}
				}
			}
		}
		
		// sort the folders and files alphabetically by their name
		ksort($arrFolders);
		ksort($arrFiles);
		
		// merge folders and files into one array (foders at first, files afterwards)
		$arrElements = array_values($arrFolders);
		$arrElements = array_merge($arrElements, array_values($arrFiles));
		
		return $arrElements;
	}
	
	/**
	 * Get all data for a file
	 */
	private function getFileData ($objFile, $objElements, $objPage)
	{
		$arrMeta = $this->getMetaData($objElements->meta, $objPage->language);

		// Use the file name as title if none is given
		if ($arrMeta['title'] == '')
		{
			$arrMeta['title'] = specialchars($objFile->basename);
		}
		
		// Use the title as link if none is given
		if ($arrMeta['link'] == '')
		{
			$arrMeta['link'] = $arrMeta['title'];
		}
		
		$strHref = \Environment::get('request');

		// Remove an existing file parameter (see #5683)
		if (preg_match('/(&(amp;)?|\?)file=/', $strHref))
		{
			$strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
		}

		$strHref .= (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . \System::urlEncode($objElements->path);
		
		return array(
			'id'        => $objFile->id,
			'uuid'      => $objFile->uuid,
			'name'      => $objFile->basename,
			'title'     => $arrMeta['title'],
			'link'      => $arrMeta['link'],
			'caption'   => $arrMeta['caption'],
			'href'      => $strHref,
			'filesize'  => $this->getReadableSize($objFile->filesize, 1),
			'icon'      => TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon,
			'mime'      => $objFile->mime,
			'meta'      => $arrMeta,
			'extension' => $objFile->extension,
			'path'      => $objFile->dirname
		);
	}
	
	/**
	 * Get all data for a folder
	 */
	private function getFolderData ($objFolder, $objPage)
	{
		$arrMeta = $this->getMetaData($objFolder->meta, $objPage->language);
		
		// Use the folder name as title if none is given
		if ($arrMeta['title'] == '')
		{
			$arrMeta['title'] = $objFolder->name;
		}
		
		// Use the title as link if none is given
		if ($arrMeta['link'] == '')
		{
			$arrMeta['link'] = $arrMeta['title'];
		}
		
		return array(
			'id'        => $objFolder->id,
			'uuid'      => $objFolder->uuid,
			'name'      => $objFolder->name,
			'title'     => $arrMeta['title'],
			'link'      => $arrMeta['link'],
			'meta'      => $arrMeta,
			'path'      => $objFolder->path
		);
	}
	
	private function getElementsRendered ($elements, $level=1)
	{
		// Layout template fallback
		if ($this->recursiveDownloadFolderTpl == '')
		{
			$this->recursiveDownloadFolderTpl = 'recursive-download-folder_default';
		}
		
		$objTemplate = new \FrontendTemplate($this->recursiveDownloadFolderTpl);
		$objTemplate->level = 'level_' . $level;
		$objTemplate->elements = $elements;
		return !empty($elements) ? $objTemplate->parse() : '';
	}
}