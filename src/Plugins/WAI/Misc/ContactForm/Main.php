<?php

namespace Surikata\Plugins\WAI\Misc {

  class ContactForm extends \Surikata\Core\Web\Plugin {

    public function getMailContent($fields) {
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
            <td style='width: 150px'>Name</td>
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

    public function renderJSON() {
      $returnArray = array();

      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);

      $name = htmlspecialchars($this->websiteRenderer->urlVariables['name'] ?? "");
      $email = htmlspecialchars($this->websiteRenderer->urlVariables['email'] ?? "");
      $phoneNumber = htmlspecialchars($this->websiteRenderer->urlVariables['phone_number'] ?? "");
      $message = htmlspecialchars($this->websiteRenderer->urlVariables['contact_message'] ?? "");

      if (
        !$this->validateFields($name, "name") ||
        !$this->validateFields($email, "email") ||
        !$this->validateFields($phoneNumber, "phone_number") ||
        !$this->validateFields($message, "message")
      ) {
        $returnArray['status'] = "error";
        $returnArray['message'] = "Wrong field";
        return $returnArray;
      }

      $contactFormModel = $this->adminPanel
        ->getModel("Widgets/CRM/Models/ContactForm")
      ;

      $settings = $this->adminPanel->getPluginSettings("WAI/Misc/ContactForm");

      $status = $contactFormModel->insertRow([
        "email" => $email,
        "name" => $name,
        "phone_number" => $phoneNumber,
        "message" => "Name: ". $name."<br>\n".$message,
        "received" => date('Y-m-d H:i:s'),
        "recipient" => $settings["sendMailIsEnabled"] ? $settings["recipientEmail"] : ""
      ]);

      $returnArray['status'] = ($status > 0 || !is_null($status)) ? "success" : "error";
      $returnArray['message'] = "Message is saved";

      if ($settings['sendMailIsEnabled']) {
        $fields = [
          "name" => $name,
          "message" => $message,
          "email" => $email,
          "phone" => $phoneNumber,
        ];
        $content = $this->getMailContent($fields);

        // sending mail
        $config = $this->websiteRenderer->adminPanel->config;
        $emailController = new \Surikata\Lib\Email(
          $config['smtp_host'],
          $config['smtp_port']
        );

        if ($config['smtp_protocol'] == 'ssl') {
          $emailController->setProtocol(\Surikata\Lib\Email::SSL);
        }

        if ($config['smtp_protocol'] == 'tls') {
          $emailController->setProtocol(\Surikata\Lib\Email::TLS);
        }

        $receive_email = strlen($settings["recipientEmail"]) > 0 ? $settings["recipientEmail"] : "";
        $emailController->setLogin($config['smtp_login'], $config['smtp_password']);
        $emailController->setSubject("Contact Form | Surikata Eshop");
        $emailController->setHtmlMessage($content);
        $emailController->addTo($email, $name);
        $emailController->addTo($receive_email, 'Surikata Eshop');
        $emailController->setFrom($config["smtp_from"]);
        $emailStatus = $emailController->send();
        $returnArray['status'] = ($emailStatus || !is_null($emailStatus)) ? "success" : "error";
        $returnArray['message'] = "Message is saved";
      }

      return $returnArray;

    }

    private function validateFields($field, $type) {
      switch ($type) {
        case "message":
        case "name":
          return strlen($field) > 2;
          break;
        case "email":
          return filter_var($field, FILTER_VALIDATE_EMAIL);
          break;
        case "phone_number":
          return strlen($field) > 9 && strlen($field) < 17;
          break;
      }
    }

    public function getTwigParams($pluginSettings) {
      $twigParams = $pluginSettings;

      $twigParams["random_form_id"] = rand(0, 1000000);
      return $twigParams;
    }
  }
}

namespace ADIOS\Plugins\WAI\Misc {
  class ContactForm extends \Surikata\Core\AdminPanel\Plugin {

    public function manifest() {
      return [
        "title" => "Contact form",
        "faIcon" => "fa fa-file-signature",
      ];
    }

    public function getSettingsForWebsite() {
      return [
        "heading" => [
          "title" => "Heading",
          "type" => "varchar",
        ],
        "headingLevel" => [
          "title" => "Heading Level",
          "type" => "int",
          "enum_values" => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6],
        ],
        "formClass" => [
          "title" => "Form Class",
          "type" => "varchar",
        ]
      ];
    }

    public function onADIOSBeforeActionRender($event) {
      $adios = $event["adios"];
      $pluginConfig = $adios->getPluginSettings("WAI/Misc/ContactForm");
  
      if (
        empty($adios->requestedURI)
        && empty($pluginConfig["recipientEmail"])
      ) {
        $adios->userNotifications->addHtml("
          ".$this->translate("Contact form: You do not have the recipient email configured.")."
          <a
            href='javascript:void(0)'
            onclick='window_render(\"Plugins/WAI/Misc/ContactForm/Settings\");'
          > ".$this->translate("Configure")."</a>
        ");
      }
  
      return $event;
    }

  }
}