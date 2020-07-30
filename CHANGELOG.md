# LUYA Composer Plugin Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).
In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 1.1.2 (30. July 2020)

+ [#18](https://github.com/luyadev/luya-composer/pull/18) Fix issue with Composer 2.0 compatibility (uncaught promise)
+ Replaced Travis with GitHub Actions

## 1.1.1 (22. April 2020)

+ Ensure Composer 2.0 Plugin class compatibility.

## 1.1.0 (14. April 2020)

+ Ensure compatibility with Composer 2.0 API.
+ [#14](https://github.com/luyadev/luya-composer/issues/14) Do not add packages into installer.php when does are not part of the require or require-dev composer.json root file.

## 1.0.6 (13. September 2019)

+ [#11](https://github.com/luyadev/luya-composer/issues/11) Replace linux paths with back slashes for windows systems.  
+ [#12](https://github.com/luyadev/luya-composer/pull/12) Add new package type `luya-theme` and added to extras section.

## 1.0.5 (14. May 2019)

+ [#10](https://github.com/luyadev/luya-composer/issues/10) Add new `{{DS}}` variable to fix problem with windows systems paths.

## 1.0.4.2 (24. December 2018)

+ [#9](https://github.com/luyadev/luya-composer/issues/9) Fix issue with paths on windows systems.

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
