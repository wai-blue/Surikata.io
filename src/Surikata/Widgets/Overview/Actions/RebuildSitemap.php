<?php

namespace ADIOS\Actions\Overview;

class RebuildSitemap extends \ADIOS\Core\Action {
  public function render() {
    $this->adios->widgets["Website"]->rebuildSitemapForAllDomains();
  }
}