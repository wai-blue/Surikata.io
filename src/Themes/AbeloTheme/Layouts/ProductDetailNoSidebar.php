<?php

namespace Surikata\Themes\AbeloTheme\Layouts;

class ProductDetailNoSidebar extends \Surikata\Core\Web\Layout {
  public function getPreviewHtml() {
    return "
      <div class='row'>
        <div class='col col-12' data-panel-name='header'>Header</div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='navigation'>Navigation</div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='product_title'>Product title</div>
      </div>
      <div class='row'>
        <div class='col col-6' data-panel-name='product_gallery'>Product gallery</div>
        <div class='col col-6' data-panel-name='product_details'>Product details</div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='product_tabs'>Product additional tabs</div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='product_related'>Product related</div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='footer'>Footer</div>
      </div>
    ";
  }
}
