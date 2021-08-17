<?php

namespace Surikata\Plugins {

  use Surikata\Plugins\ContactForm\ContactFormMail;

  class ContactForm extends \Surikata\Core\Web\Plugin {
    public function renderJSON() {
      $returnArray = array();

      $languageIndex = (int) ($this->websiteRenderer->domain["languageIndex"] ?? 1);
      // action tag for later use
      $action = $this->websiteRenderer->urlVariables['action'] ?? "";
      $name = htmlspecialchars($this->websiteRenderer->urlVariables['name'] ?? "");
      $email = htmlspecialchars($this->websiteRenderer->urlVariables['email'] ?? "");
      $phone_number = htmlspecialchars($this->websiteRenderer->urlVariables['phone_number'] ?? "");
      $message = htmlspecialchars($this->websiteRenderer->urlVariables['contact_message'] ?? "");

      if (!$this->validateFields($name, "name") ||
            !$this->validateFields($email, "email") ||
              !$this->validateFields($phone_number, "phone_number") ||
                !$this->validateFields($message, "message")) {
        $returnArray['status'] = "error";
        $returnArray['message'] = "Wrong field";
        return $returnArray;
      }

      $contactFormModel = $this->adminPanel
        ->getModel("Widgets/CRM/Models/ContactForm");

      $status = $contactFormModel->insertRow([
        "email" => $email,
        "phone_number" => $phone_number,
        "message" => "Name: ". $name."<br>\n".$message,
        "received" => date('Y-m-d H:i:s'),
        "recipient" => "lukas.koska@wai.sk"]);

      $returnArray['status'] = ($status > 0 || !is_null($status)) ? "success" : "error";
      $returnArray['message'] = "Message is saved";

      $settings = $this->websiteRenderer->getCurrentPagePluginSettings("ContactForm") ?? [];
      if ($settings['sendEmail']) {

        $fields = [
          "name" => $name,
          "message" => $message,
          "email" => $email,
          "phone" => $phone_number,
        ];
        $content = ContactFormMail::getMailContent($fields);

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
        $receive_email = strlen($settings["emailAddress"]) > 0 ? $settings["emailAddress"] : "";
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

namespace ADIOS\Plugins {
  class ContactForm extends \Surikata\Core\AdminPanel\Plugin {

    public function getAvailableSettings() {
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
        ],
        "sendEmail" => [
          "title" => "Send email",
          "type" => "boolean",
        ],
        "emailAddress" => [
          "title" => "Email address to send form",
          "type" => "varchar",
        ],
      ];
    }
    
  }
}