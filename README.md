Contao Page 410
======================

[![](https://img.shields.io/packagist/v/numero2/contao-page-410.svg?style=flat-square)](https://packagist.org/packages/numero2/contao-page-410) [![License: LGPL v3](https://img.shields.io/badge/License-LGPL%20v3-blue.svg?style=flat-square)](http://www.gnu.org/licenses/lgpl-3.0)

About
--

This extension adds a new page type for "410 Gone".


System requirements
--

* [Contao 4.13](https://github.com/contao/contao) or [Contao 5.3](https://github.com/contao/contao) (or newer)


Installation
--

* Install via Contao Manager or Composer (`composer require numero2/contao-page-410`)
* In `public/.htaccess` add the following line to route 410 errors to Contao:
```
ErrorDocument 410 /index.php
```
* To specify which URL's should return a 410 page, add some proper redirects as well
```
Redirect 410 /this-page/is-gone.html
```