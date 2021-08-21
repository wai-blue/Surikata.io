<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

define('DELETE_IMAGE', 'delete_image');

/**
 * @package DataTypes
 */
class DataTypeImage extends DataType
{
    public function get_sql_create_string($table_name, $col_name, $params = [])
    {
        $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : " default '' ";

        return "`$col_name` varchar(255) {$params['sql_definitions']}";
    }

    public function get_sql_column_data_string($table_name, $col_name, $value, $params = [])
    {
        $params = _put_default_params_values($params, [
            'null_value' => false,
            'dumping_data' => false,
            'supported_extensions' => $this->adios->getConfig('m_datapub/columns/image/supported_extensions', ['jpg', 'gif', 'png', 'jpeg']),
            'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
        ]);

        if ($params['dumping_data']) {
            $sql = "$col_name='$value'";
        } else {
            if (DELETE_IMAGE == $value) {
                $sql = "$col_name=''";
            } else {
                $sql = "$col_name='".($params['escape_string'] ? $this->adios->db->escape($value) : $value)."'";
            }
        }

        return $sql;
    }

    public function get_html($value, $params = [])
    {
        $html = '';

        $value = htmlspecialchars($value);

        if ('' != $value && file_exists($this->adios->config['files_dir']."/{$value}")) {
            $img_url = "{$this->adios->config['images_url']}/{$value}";
            $img_style = "style='height:30px;border:none'";

            $img_url = "{$this->adios->config['url']}/Image?f=".urlencode($value).'&cfg=wa_list&rand='.rand(1, 999999);
            $img_style = "style='border:none'";

            $pathinfo = pathinfo($value);
            $html = "<a href='{$this->adios->config['url']}/Image?f=".urlencode($value)."' target='_blank' onclick='event.cancelBubble=true;'><img src='{$img_url}' {$img_style} class='list_image'></a>";
            if ($params['display_basename']) {
                $html .= "<br/>{$pathinfo['basename']}";
            }
        }

        $html = "<div style='text-align:center'>{$html}</div>";

        return $html;
    }

    public function get_csv($value, $params = [])
    {
        return "{$this->adios->config['images_url']}/{$value}";
    }
}
