# Elbmultiupload

Multiple files upload module, with revisioning and category functionality for files, for Dolibarr ERP

## Installation

Module installs as a standard Dolibarr module by downloading and extracting in htdocs folder.

Enable module via admin interface (Modules/Application setup -> Other) section of modules pages.

## Description

Elbmultiupload module adds a new tab "Additional files" for every module/object in Dolibarr ERP.

Drag & drop function is supported for uploading functionality.

Uploaded files can be renamed, descriptioned, versioned, categorized, and a new version for current version can be uploaded also.

Subversion of a file can be activated as a current version. 

User can group files by: default view, by revisions, or by file category (if categorization of files is enabled)

## Usage

Enabling module automatically creates new folders in DOL_DOCUMENT_ROOT folder which are needed for uploading files.

One is needed as a buffer for uploading files and a second one is needed for storing uploaded files.

Names of those two folders are stored in Dolibarr's global variable and can be adjusted via settings page of elbmultiupload module.

If you need a categorization of files then enable it via elbmultiupload's module settings page.

Module works from Dolibarr's 4 version until the last stable version (current version 9)

## Tip

Hence the module stores files by their ids on the file system there should be placed one small hack which should be added to the htdocs/document.php file.

In case you need to download original file name then add these lines

`if (!empty($conf->elbmultiupload->enabled)) {
    require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file.class.php';
    require_once DOL_DOCUMENT_ROOT.'/elbmultiupload/class/elb.file_mapping.class.php';
    if (isset($fullpath_original_file)) {
        $filename = ELbFile::getDocumentFilename($fullpath_original_file);
    } elseif (isset($original_file)) {
        $filename = ELbFile::getDocumentFilename($original_file);
    }
}` 

after the line

`$filename = basename($fullpath_original_file);`

in htdocs/document.php file.

In case you use dolibarr's version less than 6.0 than the same code should be placed in the same file after the line

`$filename = basename($original_file);`
 