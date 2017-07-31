# Atom Framework

## Installation

The simplest way to install and get started is using the skeleton project:
```bash
$ composer create-project atomwares/atom-project <project dir>
```
Or install Atom standalone using Composer:
```bash
$ composer require atomwares/atom
```

## Usage

Create an bootstrap file with the following contents:
```php
<?php

require 'vendor/autoload.php';

$app = (new Atom\App())->router
  ->get(['home' => '/'], function () {
        return 'Hello World!';
    });

$app->run();
```

You may run script using the built-in PHP server:
```bash
$ php -S localhost:8000
```

## License

The Atom Framework is licensed under the MIT license. See [License File](LICENSE.md) for more information.
