# LUYA Composer Plugin Changelog

All notable changes to this project will be documented in this file. This project make usage of the [Yii Versioning Strategy](https://github.com/yiisoft/yii2/blob/master/docs/internals/versions.md).

## 1.0.4.1 (18. December 2018)

+ [#8](https://github.com/luyadev/luya-composer/issues/8) Use PackageInterface instead of Package for ensureLuyaExtraSectionSymlinkIsDisabled type hinting.

## 1.0.4 (12. December 2018)

+ [#7](https://github.com/luyadev/luya-composer/issues/7) Add option `symlink` in luya extra section to disable the symlinking of the luya binary into the application folder.
+ [#2](https://github.com/luyadev/luya-composer/issues/2) Relative vendor directory and alias for block paths.

## 1.0.3 (16. January 2018)

+ [#4](https://github.com/luyadev/luya-composer/issues/4) Save all LUYA packages in installer.php whether they have extra data or not.

## 1.0.2 (December 2017)

+ [#2](https://github.com/luyadev/luya-composer/issues/2) Suppress symlink exception on windows system when creating symlink.

## 1.0.1 (September 2017)

+ [#1](https://github.com/luyadev/luya-composer/issues/1) Ability to bootstrap files and register blocks. Therefore a new extra section named *luya* can be part of the `composer.json` file.

## 1.0.0 (Mai 2017)

+ First release of Composer Plugin.
