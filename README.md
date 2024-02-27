Contao Page 410
======================

[![](https://img.shields.io/packagist/v/numero2/contao-page-410.svg?style=flat-square)](https://packagist.org/packages/numero2/contao-page-410) [![License: LGPL v3](https://img.shields.io/badge/License-LGPL%20v3-blue.svg?style=flat-square)](http://www.gnu.org/licenses/lgpl-3.0)

About
--

This extension offers the possibility to add at most one error page 410 per root page.


System requirements
--

* [Contao 4.13](https://github.com/contao/contao), [Contao 5.3](https://github.com/contao/contao) or newer


Installation
--

* Install via Contao Manager or Composer (`composer require numero2/contao-page-410`)
* In `.htaccess` add the following line to route 410 errors to Contao: `ErrorDocument 410 /index.php`
* Specify which pages should return a 410 page, e.g. in `.htaccess` add following to route the page `/gone-page` to a 410 error: `Redirect 410 /gone-page`