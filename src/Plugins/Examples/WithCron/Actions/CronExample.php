<?php

namespace ADIOS\Actions\Plugins\Examples\WithCron;

class CronExample extends \ADIOS\Core\Plugin\Action {
  public static $requiresUserAuthentication = FALSE;
  public static $webSAPIEnabled = FALSE;
  public static $cliSAPIEnabled = TRUE;
  public static $hideDefaultDesktop = TRUE;

  public function render() {
    return "Cron is running.";
  }
}
