<?php
namespace Surikata\Plugins\ContactForm;

class ContactFormMail {

  public static function getMailContent($fields) {

    $content = "
      <style>
        table td {
          padding: 5px;
        }
      </style>
      <h1 style='font-size=16px'>
        Contact Form message
      </h1>
      <table>
        <tr>
          <td style='width: 150px'>Name:</td>
          <td>
            {$fields["name"]}
          </td>
        </tr>
        <tr>
          <td style='width: 150px'>E-Mail</td>
          <td>{$fields["email"]}</td>
        </tr>
        <tr>
          <td style='width: 150px'>Phone</td>
          <td>{$fields["phone"]}</td>
        </tr>
        <tr>
          <td style='width: 150px'>Message</td>
          <td>{$fields["message"]}</td>
        </tr>
      </table>
    ";
    return $content;
  }
}
