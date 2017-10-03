# LUYA Composer Plugin

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-composer/downloads)](https://packagist.org/packages/luyadev/luya-composer)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-composer/v/stable)](https://packagist.org/packages/luyadev/luya-composer)
[![Join the chat at https://gitter.im/luyadev/luya](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/luyadev/luya)

Following Tasks are done by the luya composer task:

+ Provides the symlink to the bin file inside the root directory of the application.
+ Bind blocks into the system without a module
+ Add files to the LUYA Bootstrapping process.

> In order to enable luya extra section in your package, the package type must be either `luya-extension` or `luya-module`.

An example of define a blocks folder inside your composer json file.

```json
"type" : "luya-extension",
"extra" : {
    "luya" : {
        "blocks": [
            "path/to/blocks",
            "path/to/one/Block.php"
        ],
        "bootstrap": [
            "namespace\\to\\my\\BootstrapFile"
        ]
    }
}
```

LUYA will now import those blocks when running the `import` command.

## Local Testing for Composer Plugin Development

In order to test the luya composer plugins you have to create a new folder **outside of the current luya-composer folder** and include the the composer package with a composer.json as following:

```json
{
    "minimum-stability" : "dev",
    "repositories": [
        {
            "type": "path",
            "url": "../luya-composer"
        }
    ],
    "require": {
        "luyadev/luya-composer": "*"
    }
}
```

Then you can create a test.sh file to test the plugin process like

```sh
#!/bin/bash

rm -rf vendor
rm -r composer.lock
composer update -v
```

Give the script `test.sh` the permissions with `chmod +x test.sh` and now run

```sh
./test
```

In order to test the installer events you have to create a sub package like

```json
{
    "name" : "my/test",
    "extra" : {
        "luya" : {
            "blocks": [
                "path/to/blocks/*"
            ]
        }
    }
}
```

The sub package must be linked in your local testing composer.json 

```json
{
    "minimum-stability": "dev",
    "repositories": [
        {
            "type": "path",
            "url": "../luya-composer"
        }
        {
            "type": "path",
            "url": "../path/to/my/test/package"
        }
    ],
    "require": {
        "luyadev/luya-composer": "*",
        "my/test" : "*"
    },
    "extra" : {
        "luya" : {
            "blocks": [
                "path/to/blocks/*"
            ]
        }
    }
}
```
