# Periscope Downloader
PHP script to download videos from Periscope

## Installation

Add the package to your composer file:
```bash
composer require nehalvpatel/periscope-downloader "1.*"
composer update
```

## Usage
```php
<?php

require_once("vendor/autoload.php");

use nehalvpatel\PeriscopeDownloader;

$url = "https://www.periscope.tv/w/aLNeFzcxNTU4OTB8MU93eFdqV2JMWG5KUYZyGwCPkFvgC3JUE0AN9NhEjC-hHOCPBg1HCrUoiMxg";

$periscope_downloader = new PeriscopeDownloader();
echo $periscope_downloader->download($url); // returns location of combined .ts file
```

## Arguments
```php
$url = "https://www.periscope.tv/w/aLNeFzcxNTU4OTB8MU93eFdqV2JMWG5KUYZyGwCPkFvgC3JUE0AN9NhEjC-hHOCPBg1HCrUoiMxg";
$directory = "/path/to/save/folder"; // defaults to __DIR__
$filename = "my_periscope.ts"; // defaults to $username_$date.ts

$periscope_downloader->download($url, $directory, $filename);
```

## Error Handling

The class will throw an Exception when it encounters an error. Use these error codes to identify what went wrong:
```php
try {
    $periscope_downloader->download($url);
}
catch (\Exception $e) {
    switch ($e->getCode() {
        case 1:
            echo "Unsupported URL";
            break;
        case 2:
            echo "Invalid watchonperiscope.com URL";
            break;
        case 3:
            echo "Invalid Periscope token";
            break;
        default:
}
```
