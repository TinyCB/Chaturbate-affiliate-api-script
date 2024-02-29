# Chaturbate adult affiliate API script

## 2024 Update

A revised version of the script, currently in development, is set to be released this year. A preview is available for viewing here: [cameono.com](https://cameono.com/)

## About TinyCB

TinyCB is an adult affiliate script dedicated to Chaturbate (written in PHP) that you can deploy on your server. What it does is, it downloads Chaturbate's XML API feed on your own server and uses that file to parse the models on your website. This script comes with a default template (mobile ready). To change the default template, it is recommended to have some basic knowledge about HTML / CSS / PHP.

Furthermore, TinyCB should be easy to setup (no programming knowledge required). All values are predefined in the edit.php file.

Tinycb is specifically created to work as the front end for a Chaturbate whitelabel however. It's not manditory, and also runs without.

## Setup

1. [Sign up at Chaturbate](https://chaturbate.com/in/?track=default&tour=9O7D&campaign=2DLMP)
2. Download and unzip the archive [here](https://github.com/Kudocams/TinyCB/archive/master.zip).
3. Change edit.php accordingly.
4. Upload all files to your server.
5. Make sure chaturbate.xml is writable.

NOTE: Tested script with PHP7 and works.

NOTE: It is recommended to set display_errors=Off in your php.ini file.
      Alternatively, if you don't have access to php.ini you can turn off error reporting by adding
      error_reporting(0); to index.php or php_flag display_errors off to your htaccess file.

## Cache and speed

This script makes use of a very simple xml caching method. It pré downloads the feed from Chaturbate and then saves it to a local xml file on your server. There's no need to setup any cron jobs etc. The script is very light weight and should run with minimal system specifications however. Because of Chaturbate's ever growing API feed I recommed using an SSD and fairly decent processor to keep pages loading fast.

## Features

The script is currently divided into 4 categories. Features cams, female cams, male cams, and transsexual cams. All rooms are provided with Chaturbate's theater mode. I've chosen for this mode because it had the best results for mobile devices. The script works most optimal by using Chaturbate's white label in the back end (this is optional but recommended).

## Vuukle

TinyCB make use of Vuukle. Vuukle is a free online commenting system similar to disqus. Using a comment system has multiple benifits in either community building and or SEO. Users that enter a model's room are able to either leave a comment or review.
Using Vuukle is not manditory but I do recommend it.

Signing up for [Vuukle](https://vuukle.com/dashboard.html) is easy and fast. All you need is an API key and paste it in the edit.php file.

## Udate: US date: 07/26/2019 - EU date: 26-07-2019

- Added some model info below the cam content.
- Added the option to add a logo.
- Tracking code now works properly.

## For the future

- Template options.
- CMS (possible rework needed for the script).
- More SEO options.
- Wordpress Plugin (Looking into this).
- More categories (Looking into this).
- Looking into XML -> MYSQL storage.
- Bongacams version of the script (or integrated).
- Better mobile template (might be difficult because the way Chaturbate has set IFRAME).
- Options to choose between stream canvas (Theather, default etc.).

More to come...

## Twitter

Follow me on [twitter.com/TinyCB_](https://twitter.com/TinyCB_) to stay updated for further updates of the script (or a complete rework).

## License

TinyCB is released under the MIT license.

Copyright (c) [TinyCB](https://github.com/TinyCB/Chaturbate-affiliate-api-script)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
