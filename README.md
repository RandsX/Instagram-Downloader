<h1 align="center">Instagram Downloader</h1>

> A package for download the image or video in Instagram.

## Installation

Install using composer
```bash
composer require randsx/instagram-downloader
```

## Usage

```php
use RandsX\InstagramDownloader\InstagramDownloader;

$instagram = new InstagramDownloader;
```

First set the url that you want to download the image / video as follows.

- Method 1

```php
$instagram = new InstagramDownloader("https://instagram.com/link");
```

- Method 2

```php
$instagram = new InstagramDownloader;
$instagram->setURL("https://instagram.com/link");
```

If you want to know the image / video type of the url you have defined, do it like this

```php
$instagram->getType();
// Returned "image" or "video"
```

Then if you want to get the download link, do it like this

```php
$instagram->getDownloadLink();
```