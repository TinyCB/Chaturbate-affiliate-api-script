# TinyCB - Chaturbate affiliate website / script

TinyCB is a small sand simple script that fetches models from Chaturbate's API feed and create your own free cam site. 

02/03/2019 - Updated script to responsive design including IFRAME. Sadly, because of how Chaturbate has set the IFRAME, it's not possible to get it 100% right.

If you find something not working as intended, please send me a message.

## Setup

1. [Sign up at Chaturbate](https://chaturbate.com/in/?track=default&tour=9O7D&campaign=2DLMP)
2. Download and unzip the archive [here](https://github.com/Kudocams/TinyCB/archive/master.zip).
3. Go to functions/edit.php and change everything accordingly.
4. Upload all files to your server.
5. Make sure chaturbate.xml is writable.

NOTE: Tested script with PHP7 and works.

NOTE: It is recommended to set display_errors=Off in your php.ini file.
      Alternatively, if you don't have access to php.ini you can turn off error reporting by adding
      error_reporting(0); to index.php or php_flag display_errors off to your htaccess file.

## Cache and speed

This script makes use of a very simple xml caching method. It pré downloads the feed from Chaturbate and then saves it to a local xml file on your server.
There's no need to setup any cron jobs etc.

## License

TinyCB is released under the MIT license.

Copyright (c) [Searchaporn.com](https://www.searchaporn.com/)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
