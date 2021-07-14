<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_content']['folderSRC']                               = [
    'Quellordner',
    'Bitte wählen Sie einen Ordner aus dem Dateibaum aus, dessen Inhalt rekursiv zum Download zur Verfügung stehen soll.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderHideEmptyFolders'] = [
    'Leere Ordner nicht anzeigen',
    'Bitte wählen Sie diese Option wenn leere Ordner nicht Frontend nicht angezeigt werden sollen.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderShowAllLevels']    = [
    'Alle Ebenen anzeigen',
    'Bitte wählen Sie diese Option wenn die Ordner-Struktur initial im Frontend expandiert angezeigt werden soll.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderAllowFileSearch']  = [
    'Dateisuche erlauben',
    'Bitte wählen Sie diese Option wenn die Suche nach Dateien im Frontend erlaubt sein soll.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderShowAllSearchResults']  = [
    'Ordner mit Suchergebnissen aufklappen',
    'Bitte wählen Sie diese Option wenn Ordner mit Suchergebnissen standardmäßig aufgeklappt sein sollen.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderTpl']              = [
    'Template für den rekursiven Download Ordner',
    'Bitte wählen Sie das Template für den rekursiven Download Ordner aus.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderMode']             = [
    'Darstellungsmodus',
    'Wählen Sie den Darstellungsmodus für die Ordnerstruktur.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderVisibleRoot']      = [
    'Zeige Quellordner',
    'Erzwinge die Darstellung des Quellordners (wird automatisch angezeigt, wenn mehrere Quellordner ausgewählt wurden).',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderAllowAll']         = [
    'Einstellung Erlaubte Downloads ignorieren',
    'Wenn aktiviert, werden alle Dateien aufgelistet, auch wenn Sie nicht in der Liste der erlaubten Downloads enthalten sind.',
];
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderZipDownload']         = [
    'Ordner herunterladen',
    'Wenn aktiviert, können Ordner als Zip-Archiv heruntergeladen werden.',
];

$GLOBALS['TL_LANG']['tl_content']['recursive-download-folder_legend'] = 'Rekursiver Download Ordner - Einstellungen';

$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderModes']['breadcrumb'][0] = 'Ordnerbasiert mit Pfadnavigation';
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderModes']['breadcrumb'][1] = '';
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderModes']['toggleable'][0] = 'Aufklappbarer Ordnerbaum';
$GLOBALS['TL_LANG']['tl_content']['recursiveDownloadFolderModes']['toggleable'][1] = '';
