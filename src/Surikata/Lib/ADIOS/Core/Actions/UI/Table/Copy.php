<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Table;

/**
 * @package UI\Actions\Table
 */
class Copy extends \ADIOS\Core\Action {
  public function render($params = []) {

    $this->adios->db->startTransaction();

    $ids = explode(',', $_REQUEST['ids']);
    if (is_numeric($_REQUEST['id'])) {
      $ids = [$_REQUEST['id']];
    }
    if (_count($ids)) {
      foreach ($ids as $id) {
        if (is_numeric($id) && $id > 0) {
          if (array_key_exists($_REQUEST['table'], $this->adios->db->tables)) {
            $ret = $this->adios->db->copy($_REQUEST['table'], $id);
          }
        }
      }
    }

    if (1 == count($ids)) {
      $result = $ret;
    } else {
      $result = 1;
    }

    $this->adios->db->commit();

    return $result;
  }
}
