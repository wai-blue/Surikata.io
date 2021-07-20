<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\Desktop;

class SimpleProfile extends \ADIOS\Core\Action {
  function render() {
    return $this->adios->renderAction("UI/Form", [
      'uid' => $this->uid,
      'title' => l('Môj profil'),
      'save_action' => 'Desktop/Ajax/SaveProfile',
      'show_save_button' => true,
      'width' => 830,
      "template" => [
        "columns" => [
          [
            "tabs" => [
              "O mne" => [
                "name",
                "surname",
                "email",
                // "photo",
              ],
              "Password" => [
                "old",
                "new",
                "confirm_new",
              ]
            ],
          ],
        ],
      ],
      'columns' => [
        'name' => ['type' => 'varchar', 'value' => "{$this->adios->userProfile['name']}", 'title' => l('Meno'), 'uid' => $this->uid.'_name'],
        'surname' => ['type' => 'varchar', 'value' => "{$this->adios->userProfile['surname']}", 'title' => l('Priezvisko'), 'uid' => $this->uid.'_surname'],
        'nick' => ['type' => 'varchar', 'value' => "{$this->adios->userProfile['nick']}", 'title' => l('Prezývka'), 'uid' => $this->uid.'_nick'],
        'email' => ['type' => 'varchar', 'value' => "{$this->adios->userProfile['email']}", 'title' => l('Email'), 'uid' => $this->uid.'_email'],
        'photo' => ['type' => 'image', 'value' => "{$this->adios->userProfile['photo']}", 'title' => l('Fotografia'), 'uid' => $this->uid.'_photo'],
        'old' => ['type' => 'password', 'value' => '', 'title' => "Old password", 'uid' => $this->uid.'_old_password'],
        'new' => ['type' => 'password', 'value' => '', 'title' => "New password", 'uid' => $this->uid.'_new_password_2'],
        'confirm_new' => ['type' => 'password', 'value' => '', 'title' => "Confirm new password", 'uid' => $this->uid.'_new_password_2'],
      ],
    ]);
  }
}