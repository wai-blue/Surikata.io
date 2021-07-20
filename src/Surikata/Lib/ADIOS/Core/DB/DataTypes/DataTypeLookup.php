<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\DB\DataTypes;

if (
  (empty($wa_list_select_get_text) || $wa_list_select_get_text != 'yes')
  && (empty($onkeypress_request) || $onkeypress_request != 'yes')
) {

    class DataTypeLookup extends DataType {
        public function get_sql_create_string($table_name, $col_name, $params = [])
        {
            $col_def = $this->adios->db->tables[$table_name][$col_name];

            if (!$col_def['disable_foreign_key']) {
                $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' NULL ';
            } else {
                $params['sql_definitions'] = '' != trim($params['sql_definitions']) ? $params['sql_definitions'] : ' default 0 ';
            }

            return "`$col_name` ".('' == $params['sql_type'] ? 'int(8)' : $params['sql_type'])." {$params['sql_definitions']}";
        }

        public function get_sql_column_data_string($table_name, $col_name, $value, $params = []) {
            $col_def = $this->adios->db->tables[$table_name][$col_name];

            $params = _put_default_params_values($params, [
                'null_value' => false,
                'dumping_data' => false,
                'escape_string' => $this->adios->getConfig('m_datapub/escape_string', true),
            ]);
            if ($params['null_value']) {
                $retval = "$col_name=null";
            } else {
                if (!$col_def['disable_foreign_key']) {
                    if (0 == intval($value)) {
                        $retval = "$col_name=null";
                    } // uprava kvoli foreign key, nemoze byt hondota 0 , musi sa pouzit null
                    else {
                        $retval = "$col_name='".((int) ($params['escape_string'] ? $this->adios->db->escape($value) : $value))."'";
                    }
                } else {
                    if ($params['dumping_data'] && 0 == $value) {
                        $retval = "$col_name=0";
                    } // bugfix to solve this in returned sql: "..., owner=, ..."
                    else {
                        $retval = "$col_name='".((int) ($params['escape_string'] ? $this->adios->db->escape($value) : $value))."'";
                    }
                }

                return $retval;
            }
        }

        public function get_html_or_csv($value, $params = []) {
          $html = $params['row']["{$params['col_name']}_lookup_sql_value"] ?? "";
          return $params['export_csv'] ? $html : htmlspecialchars($html);
        }

        public function get_html($value, $params = []) {
          return $this->get_html_or_csv($value, $params);
        }

        public function get_csv($value, $params = []) {
          return $this->get_html_or_csv($value, $params);
        }
    }

} elseif ('xyes' == $onkeypress_request) {
    $col_definition = $this->adios->db->tables[$table_name][$col_name];

    $tmp = json_decode($extra_params, true);
    if (is_array($tmp)) {
        foreach ($tmp as $k => $v) {
            $col_definition[$k] = $v;
        }
    }

    $lookup_table = $col_definition['table'];
    $key_field = $col_definition['key'];
    $lookup_field = $col_definition['field'];

    // z '$meno $priezvisko' musim spravit nieco ako concat(meno, ' ', priezvisko)
    $concat = 'concat(';
    $tmp = '';
    $state = 'adding string';
    $tmp_left_joins = [];
    for ($i = 0; $i <= strlen($col_definition['field']); ++$i) {
        if ($i < strlen($col_definition['field'])) {
            $chr = $col_definition['field'][$i];
        } else {
            $chr = ' ';
        }

        if ('$' == $chr) {
            if ('adding string' == $state) {
                $concat .= "'$tmp', ";
                $tmp = '';
            }
            $state = 'adding column';
        }

        $tmp .= $chr;

        if ('adding column' == $state) {
            if ('$' != $chr && !preg_match('/[a-z_]/', $chr)) {
                $state = 'adding string';
                $tmp = substr($tmp, 1, -1); // vymazat $ a posledny pridany znak

                $tmp_lookup_table = $lookup_table;

                if (strpos($tmp, 'LOOKUP___')) {
                    list($tmp_dummy, $tmp_col_name, $tmp_lookup_col_name) = explode('___', $tmp);
                    $tmp_col_definition = $this->adios->db->tables[$lookup_table][$tmp_col_name];
                    $tmp_lookup_table = $tmp_col_definition['table'].'_'.$tmp_col_name;
                    $tmp = $tmp_lookup_col_name;
                }

                $concat .= "ifnull({$tmp_lookup_table}.{$tmp}, ''), ";

                $tmp = $chr;
            }
        }
    }
    $concat = substr($concat, 0, -2).')';

    if (isset($filter)) {
        // premenna $filter sa sem moze dostat cez js_filter_formatter (vid autocomplete)
        $where = $filter;
    } else {
        if ('' == $col_definition['filter']) {
            $where = 'TRUE';
        } else {
            $where = $col_definition['filter'];
        }
    }

    $tmp_value = str_replace(' ', '', str_replace('.', '', str_replace(',', '', str_replace('-', '', str_replace('_', '', $value)))));
    $where = "({$where}) and replace(replace(replace(replace(replace({$concat}, ' ', ''), '.', ''), ',', ''), '-', ''), '_', '') like '%".$this->adios->db->escape($tmp_value)."%'";

    _d();
    $lookup_rows = $this->adios->db->get_all_rows(
      $lookup_table,
      [
        "where" => $where,
        "order" => $col_definition['order']
      ]
    );

    $retval = [];
    foreach ($lookup_rows as $key => $value) {
        $retval[] = $value[$key_field].'|||||'.$this->adios->db->_parse($lookup_field, $value);
    }

    echo json_encode($retval);
}
