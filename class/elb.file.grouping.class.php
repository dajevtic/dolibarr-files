<?php

class ElbFileGrouping
{
    const GROUP_FILES_PARAM = 'file-list-display';
    const GROUP_FILES_DEFAULT = '';
    const GROUP_FILES_BY_REV = 'by_rev';
    const GROUP_FILES_BY_TAG = 'by_tag';

    /**
     * Get available files grouping methods
     *
     * @return array
     */
    static function returnAvailableGroupingMethods()
    {
        global $langs;
        return  array( self::GROUP_FILES_DEFAULT => '',
                       self::GROUP_FILES_BY_REV  => $langs->trans('Revision'),
                       self::GROUP_FILES_BY_TAG  => $langs->trans('Tag'));
    }
}