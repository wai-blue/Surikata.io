<?php

namespace ADIOS\Actions\HelpAndSupport;

class ContactForm extends \ADIOS\Core\Widget\Action {
  public function render() {
    $uid = $this->params['uid'];

    $html = "
      <div class='row'>
        <div class='col-12 col-md-6'>
          <div class='card shadow-sm mb-2'>
            <div class='card-header py-3'>
              <h6 class='m-0 font-weight-bold text-primary'>".$this->translate("Your message")."</h6>
            </div>
            <div class='card-body text-justify'>
              <textarea
                id='{$uid}_message'
                style='width:100%;height:200px;display:block;'
                placeholder='".$this->translate("Leave us a message.")."'
              ></textarea>
            </div>
          </div>
          
          <div class='card shadow-sm mb-2'>
            <div class='card-header py-3'>
              <h6 class='m-0 font-weight-bold text-primary'>".$this->translate("Your contact email address")."</h6>
            </div>
            <div class='card-body text-justify'>
              <input
                id='{$uid}_reply_to'
                style='width:100%;display:block;'
                placeholder='".$this->translate("Where can we reach you?")."'
                value='".ads($this->adios->config['smtp_from'])."'
              />
            </div>
          </div>
          
          <br/>
          <a
            href='javascript:void(0)'
            onclick=''
            class='btn btn-info btn-icon-split'
          >
            <span class='icon'><i class='fas fa-share'></i></span>
            <span class='text'>".$this->translate("Send the message")."</span>
          </a>
        </div>
        <div class='col-6 d-none d-md-block text-center'>
          <i class='fas fa-question-circle' style='color:#EFA550;font-size:20em'></i>
        </div>
      </div>
    ";

    return $this->adios->ui->Window([
      "uid" => "{$this->uid}_window",
      "title" => $this->translate("Contact the developer"),
      "subtitle" => $this->translate("Ask for help, report a bug or request new feature"),
      "content" => $html,
    ])->render();
  }
}