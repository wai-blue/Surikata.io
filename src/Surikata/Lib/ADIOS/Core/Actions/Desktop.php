<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions;

class Desktop extends \ADIOS\Core\Action {
  public function preRender() {
    $settingsMenuItems = [];

    $settingsMenuItems[] = [
      "fa_icon" => "fas fa-exclamation-triangle",
      "text" => "My profile",
      "onclick" => "
        window_render(
          'MojProfil',
          '',
          function() {
            setTimeout(function() {
              window.location.reload();
            }, 10);
          }
        );
      ",
    ];

    $settingsMenuItems[] = [
      "fa_icon" => "fas fa-window-restore",
      "text" => "Open new tab",
      "onclick" => "window.open('{$this->adios->config['url']}');",
    ];

    $settingsMenuItems[] = [
      "fa_icon" => "fas fa-bolt",
      "text" => "Restart",
      "onclick" => "
        if (window.location.href.indexOf('restart=1') == '-1') {
          if (window.location.href.indexOf('?') == -1) {
            window.location.href = window.location.href + '?restart=1';
          } else {
            window.location.href = window.location.href + '&restart=1';
          }
        } else {
          window.location.reload();
        }
      ",
    ];

    if (
      is_array($this->adios->config['available_languages'])
        && count($this->adios->config['available_languages']) > 1
    ) {
      foreach ($this->adios->config['available_languages'] as $val) {
        $settingsMenuItems[] = [
          "text" => strtoupper($val),
          "onclick" => "window.location.href=\"?language={$val}",
        ];
      }
    }

    // $settingsMenuItems[] = [
    //   "fa_icon" => "fas fa-cogs",
    //   "text" => "Nastavenia",
    //   "onclick" => "desktop_show_settings();",
    // ];


    // develMenuItems
    $develMenuItems = [];

    if ($this->adios->config['devel_mode']) {
      $develMenuItems[] = [
        "text" => "Show console",
        "fa_icon" => "fas fa-terminal",
        "onclick" => "desktop_show_console();",
      ];
      $develMenuItems[] = [
        "text" => "Examples of UI",
        "fa_icon" => "fas fa-hammer",
        "onclick" => "desktop_render('SkinSamples');",
      ];
    }

    return [
      "console" => $this->adios->console->getLogs(),
      "settingsMenuItems" => $settingsMenuItems,
      "develMenuItems" => $develMenuItems,
      "desktopContentAction" => $this->adios->desktopContentAction,
      "desktopContentActionParams" => $this->adios->desktopContentActionParams,
    ];
  }
}
