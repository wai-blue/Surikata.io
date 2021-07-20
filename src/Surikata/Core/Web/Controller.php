<?php

namespace Surikata\Core\Web;

class Controller extends \Cascada\Controller {
  var $adminPanel;
  var $websiteRenderer;

  public function __construct($websiteRenderer, $params = []) {
    parent::__construct($websiteRenderer);

    $this->websiteRenderer = &$websiteRenderer;
    $this->adminPanel = $this->websiteRenderer->adminPanel;
  }

}