<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\Desktop\Ajax;

/**
 * @package UI\Actions
 */
class SaveProfile extends \ADIOS\Core\Action {
  public function render() {
    $id = $this->adios->userProfile['id'];

    if (!is_numeric($id)) {
      exit();
    }

    // kontrola dlzky hesla
    if ('' != $this->params['new_password_1'] && (strlen($this->params['new_password_1']) < 4 || strlen($this->params['new_password_1']) > 35)) {
      echo "Password must be at least 8 characters long.";
      exit();
    }

    // heslo nebolo spravne potvrdene
    if ('' != $this->params['new_password_1'] && $this->params['new_password_1'] != $this->params['new_password_2']) {
      echo "Re-typed password does not match with the original.";
      exit();
    }

    if ('' != $this->params['new_password_1'] && password_verify($this->params['old_password'], $this->adios->userProfile['password'])) {
      echo "Old password is invalid.";
      exit();
    }

    if ('' == $this->params['new_password_1']) {
      $this->params['new_password_1'] = $this->adios->userProfile['password'];
    } else {
      $this->params['new_password_1'] = password_hash($this->params['new_password_1'], PASSWORD_DEFAULT);
    }

    if (false === filter_var($this->params['email'], FILTER_VALIDATE_EMAIL) && '' != $this->params['email']) {
      echo $this->translate("Please provide valid email address.");
      exit();
    }

    $this->adios->db->update_row_part(
      "{$this->gtp}_{$this->adios->config['system_table_prefix']}_users",
      [
        'name' => $this->params['name'],
        'surname' => $this->params['surname'],
        'nick' => $this->params['nick'],
        'email' => $this->params['email'],
        'photo' => $this->params['photo'],
        'password' => $this->params['new_password_1'],
      ],
      $id
    );

    $this->adios->authUser($this->adios->userProfile['login'], $this->params['new_password_1'], 0);

    echo $id;
  }
}