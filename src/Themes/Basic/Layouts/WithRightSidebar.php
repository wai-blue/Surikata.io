<?php

namespace Surikata\Themes\Basic\Layouts;

class WithRightSidebar extends \Surikata\Core\Web\Layout {
  public function getPreviewHtml() {
    return "
      <div class='row'>
        <div class='col col-12' data-panel-name='header'>Header</div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='navigation'>Navigation</div>
      </div>
      <div class='d-flex flex-row'>
        <div class='col-9 p-0'>
          <div class=' d-flex flex-column'>
            <div class='col col-12' data-panel-name='section_1'><i>Section 1</i></div>
            <div class='col col-12' data-panel-name='section_2'><i>Section 2</i></div>
            <div class='col col-12' data-panel-name='section_3'><i>Section 3</i></div>
            <div class='col col-12' data-panel-name='section_4'><i>Section 4</i></div>
          </div>
        </div>
        <div class='col col-3' data-panel-name='sidebar'><i>Sidebar</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='footer'>Footer</div>
      </div>
    ";
  }
}
