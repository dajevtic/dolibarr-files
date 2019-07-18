<?php

class ElbFileView
{
    /**
     * Render html select box
     *
     * @param   string    $selected     Selected option
     * @param   string    $htmlname     Name of select element
     * @param   array     $mapArr       Array of key and values for select options
     * @return  string
     */
    static function renderSelect($selected='', $htmlname='select_element', $mapArr=array())
    {
        $out = '';

        $out.= '<select class="flat" name="'.$htmlname.'">';
        foreach($mapArr as $key => $value) {
            ($key == $selected) ? $optionSelected=' selected="selected" ': $optionSelected='';
            $out.= '<option value="'.$key.'" '.$optionSelected.'>'.$value.'</option>';
        }
        $out.= '</select>';

        return $out;
    }
}