# Elbmultiupload

Multiple files upload module, with revisioning and category functionality for files, for Dolibarr ERP.

## Description

Elbmultiupload module adds a new tab "Additional files" for every module/object in Dolibarr ERP.

Drag & drop function is supported for uploading functionality.

Uploaded files can be renamed, descriptioned, versioned, categorized, and a new version for current version can be uploaded also.

Subversion of a file can be activated as a current version. 

User can group files by: default view, by revisions, or by file category (if categorization of files is enabled)

## Installation

Module installs as a standard Dolibarr module by downloading and extracting in htdocs folder.

Name of the module folder must be **elbmultiupload**.

Enable module via admin interface (Modules/Application setup -> Other) section of modules pages.

## Usage

Enabling module automatically creates new folders in DOL_DOCUMENT_ROOT folder which are needed for uploading files.

One is needed as a buffer for uploading files and a second one is needed for storing uploaded files.

Names of those two folders are stored in Dolibarr's global variable and can be adjusted via settings page of elbmultiupload module.

If you need a categorization of files then enable it via elbmultiupload's module settings page.

Module works from Dolibarr's 4 version until the last stable version (current version 9)
 