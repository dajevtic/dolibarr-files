<?php
/* Copyright (C) 2019-2019 Elb Solutions - Milos Petkovic <milos.petkovic@elb-solutions.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class ElbForm extends Form
{
    /**
     * @var DoliDB Database handler.
     */
    public $db;

    /**
     * Constructor
     *
     * @param		DoliDB		$db      Database handler
     */
    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     *	Show a multiselect form from an array.
     *
     *	@param	string	$htmlname		Name of select
     *	@param	array	$array			Array with key+value
     *	@param	array	$selected		Array with key+value preselected
     *	@param	int		$key_in_label   1 pour afficher la key dans la valeur "[key] value"
     *	@param	int		$value_as_key   1 to use value as key
     *	@param  string	$morecss        Add more css style
     *	@param  int		$translate		Translate and encode value
     *  @param	int		$width			Force width of select box. May be used only when using jquery couch. Example: 250, 95%
     *  @param	string	$moreattrib		Add more options on select component. Example: 'disabled'
     *  @param	string	$elemtype		Type of element we show ('category', ...)
     *  @param	string	$placeholder	String to use as placeholder
     *  @param	int		$addjscombo		Add js combo
     *	@return	string					HTML multiselect string
     *  @see selectarray, selectArrayAjax, selectArrayFilter
     */
    static function multiselectarray($htmlname, $array, $selected=array(), $key_in_label=0, $value_as_key=0, $morecss='', $translate=0, $width=0, $moreattrib='', $elemtype='', $placeholder='', $addjscombo=-1)
    {
        global $conf, $langs;

        $out = '';

        if ($addjscombo < 0) {
            if (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) $addjscombo = 1;
            else $addjscombo = 0;
        }

        // Add code for jquery to use multiselect
        if (! empty($conf->global->MAIN_USE_JQUERY_MULTISELECT) || defined('REQUIRE_JQUERY_MULTISELECT'))
        {
            $out.="\n".'<!-- JS CODE TO ENABLE  for id '.$htmlname.' -->
						<script type="text/javascript">'."\n";
            if ($addjscombo == 1)
            {
                $tmpplugin=empty($conf->global->MAIN_USE_JQUERY_MULTISELECT)?constant('REQUIRE_JQUERY_MULTISELECT'):$conf->global->MAIN_USE_JQUERY_MULTISELECT;
                $out.=	'function formatResult(record) {'."\n";
                if ($elemtype == 'category')
                {
                    $out.='	//return \'<span><img alt="" src="'.DOL_URL_ROOT.'/theme/eldy/img/object_category.png'.'"> <a href="'.DOL_URL_ROOT.'/categories/viewcat.php?type=0&id=\'+record.id+\'">\'+record.text+\'</a></span>\';
									  	return \'<span><img alt="" src="'.DOL_URL_ROOT.'/theme/eldy/img/object_category.png'.'"> \'+record.text+\'</span>\';';
                }
                else
                {
                    $out.='return record.text;';
                }
                $out.=	'};'."\n";
                $out.=	'function formatSelection(record) {'."\n";
                if ($elemtype == 'category')
                {
                    $out.='	//return \'<span><img alt="" src="'.DOL_URL_ROOT.'/theme/eldy/img/object_category.png'.'"> <a href="'.DOL_URL_ROOT.'/categories/viewcat.php?type=0&id=\'+record.id+\'">\'+record.text+\'</a></span>\';
									  	return \'<span><img alt="" src="'.DOL_URL_ROOT.'/theme/eldy/img/object_category.png'.'"> \'+record.text+\'</span>\';';
                }
                else
                {
                    $out.='return record.text;';
                }
                $out.=	'};'."\n";
                $out.=	'$(document).ready(function () {
							$(\'#'.$htmlname.'\').'.$tmpplugin.'({
								dir: \'ltr\',
								// Specify format function for dropdown item
								formatResult: formatResult,
							 	templateResult: formatResult,		/* For 4.0 */
								// Specify format function for selected item
								formatSelection: formatSelection,
							 	templateResult: formatSelection		/* For 4.0 */
							 	,tags:true
							});
						});'."\n";
            }
            elseif ($addjscombo == 2)
            {
                // Add other js lib
                // ...
                $out.= '$(document).ready(function () {
							$(\'#'.$htmlname.'\').multiSelect({
								containerHTML: \'<div class="multi-select-container">\',
								menuHTML: \'<div class="multi-select-menu">\',
								buttonHTML: \'<span class="multi-select-button '.$morecss.'">\',
								menuItemHTML: \'<label class="multi-select-menuitem">\',
								activeClass: \'multi-select-container--open\',
								noneText: \''.$placeholder.'\'
							});
						})';
            }
            $out.=	'</script>';
        }

        // Try also magic suggest

        $out .= '<select id="'.$htmlname.'" class="multiselect'.($morecss?' '.$morecss:'').'" multiple name="'.$htmlname.'[]"'.($moreattrib?' '.$moreattrib:'').($width?' style="width: '.(preg_match('/%/',$width)?$width:$width.'px').'"':'').'>'."\n";
        if (is_array($array) && ! empty($array))
        {
            if ($value_as_key) $array=array_combine($array, $array);

            if (! empty($array))
            {
                foreach ($array as $key => $value)
                {
                    $out.= '<option value="'.$key.'"';
                    if (is_array($selected) && ! empty($selected) && in_array($key, $selected) && ((string) $key != ''))
                    {
                        $out.= ' selected';
                    }
                    $out.= '>';

                    $newval = ($translate ? $langs->trans($value) : $value);
                    $newval = ($key_in_label ? $key.' - '.$newval : $newval);
                    $out.= dol_htmlentitiesbr($newval);
                    $out.= '</option>'."\n";
                }
            }
        }
        $out.= '</select>'."\n";

        return $out;
    }

}

