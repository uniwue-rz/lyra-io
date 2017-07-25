# Lyra IO
The Lyra/IO is a wrapper to the Symfony file system which allows the user to have a log actions and also run the changes in dryrun mode. This is good
for working with sensitive data. Every action can be simulated before being actually acted on.

## Installation
To install lyra/io you only need to have composer installed. The composer can be downloaded and installed from the [composer website](https://getcomposer.org/download/). Afterwards you only need to add `rzuw/lyra-io` to your requirements. The `rzwu/lyra-io` will be downloaded from [packagist](https://packagist.org/).

```lang=bash
composer require rzuw/lyra-io
composer update
```

You can also clone this repository and add it as a filesystem to your composer system. This is a good choice if you want to test some changes in the original library:

```lang=json
{
    "repositories": [
        {
            "type": "path",
            "url": "/path-to-git-clone"
        }
    ],
    "require": {
        "rzuw/lyra-io": "*"
    }
}

```

Adding the repository directly to the composer.json is also possible.

```lang=json
{
    "require": {
        "rzuw/lyra-io": "*"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "ssh://git@github.com/uniwue-rz/lyra-io.git"
        }
    ]
}

```

## Configuration
The configuration is very simple. You need to only a logger object which delivers the method:

```lang=php
public function log($level, $text, $context = array()){
    // do logging
}
```

If you want to log the actions by this library. You can also just run it without logger.

## Usage
After adding the library in composer.json and installing it with:

```lang=bash
composer install # or Update
```
To use this library create a new instance:

```lang=php
$logger = new Logger();
$io = new IO($logger);
$io->copy("/etc/passwd","/etc/passwd.back");
```

if there is an error the application will through `IOExceptions`.

## Test and Development
The test can be done using `phpunit`. The needed configuration and sample tests are available in `tests` folder.

## License
See LICENSE file