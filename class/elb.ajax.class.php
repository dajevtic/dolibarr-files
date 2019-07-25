<?php
/* Copyright (C) 2019-2019 Elb Solutions - Milos Petkovic <milos.petkovic@elb-solutions.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class ElbAjax
{
    public $code = '';

    public function getParam($name)
    {
        return $_POST['params'][$name];
    }

    public function getFormData()
    {
        parse_str($_POST['formData'], $formData);
        return $formData;
    }

    public function start()
    {
        $this->code.='$(".jnotify-container").html("");';
    }

    public function escape($content)
    {
        $content = str_replace("\t", "", $content);
        $content = str_replace("\r", "\\r", $content);
        $content = str_replace("\n", "\\n", $content);
        $content= str_replace("'","\\'",$content);
        return $content;
    }

    public function addCode($code)
    {
        $this->code.=$code;
    }

    public function getResponse()
    {
        return array(
            'type'=>'js',
            'code'=> $this->code
        );
    }

    public function showMessages()
    {
        foreach($_SESSION['dol_events'] as $type => $msg_list) {
            $style = "ok";
            if($type=="errors") $style="error";
            if($type=="warnings") $style="warning";
            foreach($msg_list as $msg) {
                $this->code.= '$.jnotify("'.dol_escape_js($msg).'",
								"'.($style=="ok" ? 3000 : $style).'",
								'.($style=="ok" ? "false" : "true").',
								{ remove: function (){} } );';
            }
        }
        unset($_SESSION['dol_events']);
    }

    public function outputJson($object)
    {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($object);
        exit;
    }

}