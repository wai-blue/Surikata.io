<?php

// http://127.0.0.1/github/Surikata.io/install/?do_install=1
// &product-catalog=yes
// &random_products_count=10
// &customers=yes
// &delivery-and-payment-services=yes
// &orders=yes
// &domain_1_slug=hello-world
// &domain_1_description=Developer%60s+Hello+World+example
// &domain_1_language_index=1
// &domain_1_theme_name=HelloWorld
// &domain_2_slug=en
// &domain_2_description=English+version
// &domain_2_language_index=1
// &domain_2_theme_name=Abelo
// &domain_3_slug=sk
// &domain_3_description=Slovenská+verzia
// &domain_3_language_index=2
// &domain_3_theme_name=Abelo
// &domain_4_slug=
// &domain_4_description=
// &domain_4_language_index=3
// &domain_4_theme_name=Abelo
// &slideshow_image_set=books

$_GET = [
  "do_install" => "1",
  "slideshow_image_set" => "books",
  "domain_1_slug" => "en",
  "domain_1_description" => "English",
  "domain_1_language_index" => "1",
  "domain_1_theme_name" => "AbeloTheme",
  "domain_2_slug" => "sk",
  "domain_2_description" => "Slovenská",
  "domain_2_language_index" => "2",
  "domain_2_theme_name" => "AbeloTheme",
  "random_products_count" => 50,
  "product-catalog" => "yes",
  "delivery-and-payment-services" => "yes",
  "customers" => "yes",
  "orders" => "yes",
  "rewrite_base_is_correct" => "1",
];

include(__DIR__."/index.php");
