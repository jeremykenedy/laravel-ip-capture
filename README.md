# Laravel IP Capture

A Laravel package to automatically capture and track IP addresses on Eloquent model actions such as signup, login, update, and deletion.

[![Total Downloads](https://poser.pugx.org/jeremykenedy/laravel-ip-capture/d/total.svg)](https://packagist.org/packages/jeremykenedy/laravel-ip-capture)
[![Latest Stable Version](https://poser.pugx.org/jeremykenedy/laravel-ip-capture/v/stable.svg)](https://packagist.org/packages/jeremykenedy/laravel-ip-capture)
[![StyleCI](https://github.styleci.io/repos/1194807588/shield?branch=main)](https://github.styleci.io/repos/1194807588?branch=main)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

#### Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Trait](#trait)
  - [Available Methods](#available-methods)
- [License](#license)

## Features
| Feature |
| :--- |
| Automatic IP capture on model events |
| Tracks signup, login, admin, update, and delete IPs |
| Simple trait-based integration |
| Works with any Eloquent model |
| Proxy and load balancer aware |

## Installation

```bash
composer require jeremykenedy/laravel-ip-capture
```

## Configuration

```bash
php artisan vendor:publish --tag=ip-capture-config
```

## Usage

### Trait

Add the `CapturesIp` trait to your User model:

```php
use Jeremykenedy\LaravelIpCapture\Traits\CapturesIp;

class User extends Authenticatable
{
    use CapturesIp;
}
```

### Available Methods

```php
$user->setSignupIp();
$user->setAdminIp();
$user->setUpdatedIp();
$user->setDeletedIp();
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
