<?php

namespace ADIOS\Actions\HelpAndSupport\Ajax;

class SendMessage extends \ADIOS\Core\Widget\Action {
  public function render() {
    $message = $this->params['message'];
    $replyTo = $this->params['replyTo'];

    try {
      $this->adios->sendEmail([
        "to" => "info@surikata.io",
        "replyTo" => $replyTo,
        "subject" => "Message from Surikata.io eshop",
        "bodyText" => $message,
      ]);

      return TRUE;
    } catch (\Exception $e) {
      return $this->adios->renderFatal([
        "error" => $e->getMessage(),
      ]);
    }
  }
}