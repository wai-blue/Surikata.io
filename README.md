# Surikata.io
Full-featured e-commerce platform with multi-domain and multi-language support for PHP 8

## Prerequisities

For a successfull installation, you'll need:

  * running web development environment with:
    * PHP7 or PHP8,
    * MariaDB or MySQL and
    * webserver
  * composer

## Step 1. Download and unzip Surikata's source code.

  * create your project folder
  * clone Surikata's source code

Now, you should have following folder structure in your project folder:

    ./
      admin/      // the index.php and .htaccess for the administration panel
      install/    // the installer; should be used only once
      log/        // log files
      src/        // source code including all "boxes" (core, plugins & theme)
      tmp/        // temporary files
      upload/     // files uploaded in the administration panel

## Step 2. Install required dependencies using composer.

  * go to your project folder and install required packages using composer:

        composer install

Following packages should be installed:

    {
        "require": {
            "twig/twig": "^3.0",
            "hoa/regex": "1.17.01.13",
            "illuminate/database": "^8.19",
            "twig/markdown-extra": "^3.2",
            "erusev/parsedown": "^1.7",
            "voku/html-compress-twig": "^4.0",
        }
    }

Note: If for some reason this installation fails, install the packages manually.

## Step 3. Configure the development environment.

  * copy ConfigEnv.php.tmp to ConfigEnv.php
  * in the ConfigEnv.php file (located in project's root folder) configure database connection ...

        define('DB_HOST', 'localhost');
        define('DB_PORT', 3306);
        define('DB_LOGIN', '');
        define('DB_PASSWORD', '');
        define('DB_NAME', ''); // database will be created automatically, if not exists

  * ... and configure the URL of your project, relative to $_SERVER['HTTP_HOST']. **In a similar way how the RewriteBase in .htaccess file is configure.**

        define('REWRITE_BASE', '/my_first_surikata/');

## Step 4. Run the installer.

  * in your browser navigate to the install/index.php script.
    The URL can be e.g. http://127.0.0.1/my_first_surikata/install
  * use the installer's UI to select the parts you want to install
  * delete the installer's folder

## Done

That's all, folks! Now you have your first online store installed. You can navigate to:

  * the presentation layer - the index.php script in the project's root folder, e.g. http://127.0.0.1/my_first_surikata
  * the administration panel - the admin/index.php script, e.g. http://127.0.0.1/my_first_surikata/admin

Default logins and passwords are:

  * administrator / administrator - full-featured user with all privileges
  * product.manager / product.manager - user with product management role
  * sales / sales - user with sales role
  * online.marketing / online.marketing - user with online marketing role

It is adviced to change these defaults.

## Go beyond

Now, when you are ready with your first & default Surikata online store, follow these guides to customize it:

  * <a href='https://www.surikata.io/documentation/basic-configuration-options' target=_blank>Basic configuration options</a>
  * <a href='https://www.surikata.io/documentation/create-your-first-plugin' target=_blank>Create your first plugin</a>
  * <a href='https://www.surikata.io/documentation/customize-the-theme' target=_blank>Customize the theme</a>
  
