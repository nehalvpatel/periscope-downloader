# periscope-downloader
PHP script to download videos from Periscope

## Installation

Add the package to your composer file:
```bash
composer require nehalvpatel/periscope-downloader
composer update
```

## Usage
```php
<?php

require_once("vendor/autoload.php");

use nehalvpatel\PeriscopeDownloader;
use Guzzle\Http\Client;
use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;

$url = "https://watchonperiscope.com/broadcast/1kvJpPkkXRQJE";

$periscope_downloader = new PeriscopeDownloader();
echo $periscope_downloader->download($url); // returns location of combined .ts file
```
