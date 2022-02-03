<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions;

/**
 * 'Desktop' action. Renders the ADIOS application's desktop.
 *
 * This is the default action rendered when the ADIOS application is open via a URL.
 * The desktop is divided into following visual parts:
 *   * Left sidebar
 *   * Notification and profile information area on the top of the screen
 *   * The main content area
 *
 * Action can be configured to render another action in the main content area.
 *
 * @package UI\Actions
 */
class Desktop extends \ADIOS\Core\Action {
  public function preRender() {
    $settingsMenuItems = [];

    $settingsMenuItems[] = [
      "fa_icon" => "fas fa-user",
      "text" => $this->translate("My profile"),
      "onclick" => "
        window_render(
          'MyProfile',
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
      "text" => $this->translate("Open new tab"),
      "onclick" => "window.open('{$this->adios->config['url']}');",
    ];

    $settingsMenuItems[] = [
      "fa_icon" => "fas fa-bolt",
      "text" => $this->translate("Restart"),
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

    $settingsLogoutItems = [
      "fa_icon" => "fas fa-sign-out-alt",
      "text" => $this->translate("Log out"),
      "consent" => $this->translate("Are you sure to log out?"),
      "not_logout" => $this->translate("Do not logout"),
    ];

    // if (
    //   is_array($this->adios->config['available_languages'])
    //     && count($this->adios->config['available_languages']) > 1
    // ) {
    //   foreach ($this->adios->config['available_languages'] as $val) {
    //     $settingsMenuItems[] = [
    //       "text" => strtoupper($val),
    //       "onclick" => "window.location.href=\"?language={$val}",
    //     ];
    //   }
    // }

    // $settingsMenuItems[] = [
    //   "fa_icon" => "fas fa-cogs",
    //   "text" => "Nastavenia",
    //   "onclick" => "desktop_show_settings();",
    // ];


    // develMenuItems
    $develMenuItems = [];

    if ($this->adios->config['devel_mode']) {
      // $develMenuItems[] = [
      //   "text" => $this->translate("Show console"),
      //   "fa_icon" => "fas fa-terminal",
      //   "onclick" => "desktop_show_console();",
      // ];
      $develMenuItems[] = [
        "text" => $this->translate("Examples of UI"),
        "fa_icon" => "fas fa-hammer",
        "onclick" => "desktop_render('SkinSamples');",
      ];
    }

    $params = [
      "console" => $this->adios->console->getLogs(),
      "settingsMenuItems" => $settingsMenuItems,
      "settingsLogoutItems" => $settingsLogoutItems,
      "searchQuery" => $_GET['search'],
      "develMenuItems" => $develMenuItems,
      "desktopContentAction" => $this->adios->desktopContentAction == "Desktop" ? "" : $this->adios->desktopContentAction,
      "desktopContentActionParams" => $this->adios->desktopContentActionParams,
    ];

    $desktopContentActionClassName = $this->adios->getActionClassName($this->adios->desktopContentAction);
    $desktopContentActionObject = new $desktopContentActionClassName($this->adios);
    $params = $desktopContentActionObject->onAfterDesktopPreRender($params);

    return $params;
  }
}
