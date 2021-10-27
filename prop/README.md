# Your proprietary folder

This folder should contain all proprietary files of your project.
Files in this folder are not part of Surikata.io and therfore are not licensed
under Surikata.io's license.

Your proprietary *Plugins* and *Themes* should be stored here.
(see registerPluginFolder() and registerThemeFolder() in MyEcommerceProject.php file)

A composer's vendor folder for 3rd pary libs that you will use in your proprietary plugins is recommended to be placed here. 

If this folder contains a vendor/autoload.php, this will be included by ROOT/Init.php.
