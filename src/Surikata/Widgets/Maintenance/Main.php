<?php

namespace ADIOS\Widgets;

class Maintenance extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_ADMINISTRATOR)) {
      $this->adios->config['desktop']['sidebarItems']['Maintenance'] = [
        "fa_icon" => "fas fa-wrench",
        "title" => $this->translate("Maintenance"),
        "sub" => [
          [
            "title" => $this->translate("Logs"),
            "onclick" => "desktop_update('Maintenance/LogViewer', {'severity': 'info'});",
            "sub" => [
              [
                "title" => $this->translate("Info"),
                "onclick" => "desktop_update('Maintenance/LogViewer', {'severity': 'info'});",
              ],
              [
                "title" => $this->translate("Warnings"),
                "onclick" => "desktop_update('Maintenance/LogViewer', {'severity': 'warning'});",
              ],
              [
                "title" => $this->translate("Errors"),
                "onclick" => "desktop_update('Maintenance/LogViewer', {'severity': 'error'});",
              ],
            ],
          ],
          [
            "title" => $this->translate("Maintenance mode"),
            "onclick" => "window_render('Maintenance/MaintenanceMode');",
          ],
        ],
      ];
    }
  }

}
