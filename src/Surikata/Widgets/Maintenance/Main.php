<?php

namespace ADIOS\Widgets;

class Maintenance extends \ADIOS\Core\Widget {
  public function init() {
    if ($this->adios->hasUserRole(\Surikata\Core\AdminPanel\Loader::USER_ROLE_ADMINISTRATOR)) {
      $this->adios->config['desktop']['sidebarItems']['Maintenance'] = [
        "fa_icon" => "fas fa-cog",
        "title" => "Maintenance",
        "sub" => [
          [
            "title" => "Logs",
            "onclick" => "desktop_update('Maintenance/LogViewer', {'severity': 'info'});",
            "sub" => [
              [
                "title" => "Info",
                "onclick" => "desktop_update('Maintenance/LogViewer', {'severity': 'info'});",
              ],
              [
                "title" => "Warnings",
                "onclick" => "desktop_update('Maintenance/LogViewer', {'severity': 'warning'});",
              ],
              [
                "title" => "Errors",
                "onclick" => "desktop_update('Maintenance/LogViewer', {'severity': 'error'});",
              ],
            ],
          ],
          [
            "title" => "Maintenance mode",
            "onclick" => "window_render('Maintenance/MaintenanceMode');",
          ],
        ],
      ];
    }
  }

}
