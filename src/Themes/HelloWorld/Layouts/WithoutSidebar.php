<?php

namespace Surikata\Themes\HelloWorld\Layouts;

class WithoutSidebar extends \Surikata\Core\Web\Layout {

  public function getPreviewHtml() {
    return "
      <div class='row'>
        <div class='col col-12' data-panel-name='header'>Header</div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='navigation'>Navigation</div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='section_1'><i>Section 1</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='section_2'><i>Section 2</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='section_3'><i>Section 3</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='section_4'><i>Section 4</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='section_5'><i>Section 5</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='section_6'><i>Section 6</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='section_7'><i>Section 7</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='section_8'><i>Section 8</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='section_9'><i>Section 9</i></div>
      </div>
      <div class='row'>
        <div class='col col-12' data-panel-name='footer'>Footer</div>
      </div>
    ";
  }
}
