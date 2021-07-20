<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

if (('float' != $adios->db->tables[$_REQUEST['table']][$_REQUEST['column']]['type'] && 'int' != $adios->db->tables[$_REQUEST['table']][$_REQUEST['column']]['type']) || 'id' == $_REQUEST['column']) {
    echo 'wrong column';
    die();
}

$ids = explode(',', $_REQUEST['order']);

if (_count($ids)) {
    foreach ($ids as $id) {
        if (is_numeric($id) && $id > 0) {
            $in[] = $id;
        }
    }
    $ret = $adios->db->get_all_rows(
      $_REQUEST['table'],
      [
        "where" => 'id in ('.implode(',', $in).')',
        "order" => "{$_REQUEST['column']} asc"
      ]
    );
    if (false == $ret && '' != $adios->db->db_rights_callback_return['error']) {
        $error = $adios->db->db_rights_callback_return['error'];
    }
    if (_count($ret)) {
        foreach ($ret as $val) {
            $new_order[] = $val[$_REQUEST['column']];
        }
    }

    try {
        $adios->db->start_transaction();
        foreach ($ids as $key => $id) {
            if (is_numeric($id) && $id > 0) {
                $adios->db->update_row_part("{$_REQUEST['table']}", ["{$_REQUEST['column']}" => $new_order[$key]], $id);
            }
        }
        $adios->db->commit();
    } catch (\ADIOS\Core\DBException $e) {
        $adios->db->rollback();
        $error = l('Nastala databázová chyba!');
    }
}

if ('' != $error) {
    echo $error;
} else {
    echo 1;
}
