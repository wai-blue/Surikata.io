<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

define('DELETE_FILE', 'delete_file');

class DataTypeFile extends DataType
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
            'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
        ]);

        $col_definition = $this->adios->db->tables[$table_name][$col_name];

        if ($params['dumping_data']) {
            $sql = "$col_name='$value'";
        } else {
            if (DELETE_FILE == $value) {
                $sql = "$col_name=''";
            } else {
                if (is_string($value)) {
                    $sql = "$col_name='".($params['escape_string'] ? $this->adios->db->escape($value) : $value)."'";
                }
                if (is_array($value)) { // ak to nie je uploadovane ajaxom, tak vo $value je hodnota z $_FILES
                    if (UPLOAD_ERR_OK == $error) {
                        $tmp_name = $value['tmp_name'];
                        $name = $value['name'].'_'.date('YmdHis');
                        if (@move_uploaded_file($tmp_name, "{$this->adios->config['files_dir']}/".('' == $col_definition['subdir'] ? '' : "{$col_definition['subdir']}/")."{$name}")) {
                            $sql = "$col_name='".($params['escape_string'] ? $this->adios->db->escape($name) : $name)."'";
                        }
                    }
                }
            }
        }

        return $sql;
    }

    public function get_html($value, $params = [])
    {
        $html = '';

        $value = htmlspecialchars($value);

        if ('' != $value && file_exists($this->adios->config['files_dir']."/{$value}")) {
            $value = str_replace('\\', '/', $value);
            $value = explode('/', $value);
            $value[count($value) - 1] = rawurlencode($value[count($value) - 1]);
            $value = implode('/', $value);

            $html = "<a href='{$this->adios->config['url']}/File?f={$value}' onclick='event.cancelBubble = true;' target='_blank'>".basename($value).'</a>';
        }

        return $html;
    }

    public function get_csv($value, $params = [])
    {
        return "{$this->adios->config['url']}/File?f=/{$value}";
    }
}
