<?php

// This is a OnlyAdminPanel example plugin for Surikata.
// This plugin demonstrates basic funcionality for the plugin in order
// to access the plugin in the administration panel. The plugin
// introduces his own global settings (the are also plugin settings
// specific for its usage on the website).
//
// This plugin does nothing on the website. Such plugin is usable
// e.g. for custom functionalities in the administration panel or
// for various automation/cron scripts.
//
// You should notice following:
//
// 1. There is a 'Main' action of the plugin. This action is called
// when a user clicks on "Manage" button in plugins overview in the
// administration panel.
//
// 2. There is a 'Settings' action of the plugin. This action is called
// when a user clicks on "Settings" button in plugins overview in the
// administration panel. This action uses ADIOS standard UI/SettingsPanel
// action.

namespace Surikata\Plugins\Examples {
  class OnlyAdminPanel extends \Surikata\Core\Web\Plugin {
  }
}

namespace ADIOS\Plugins\Examples {
  class OnlyAdminPanel extends \Surikata\Core\AdminPanel\Plugin {
    public function manifest() {
      return [
        "faIcon" => "fas fa-box-open",
        "title" => $this->translate("OnlyAdminPanel example"),
      ];
    }
  }
}