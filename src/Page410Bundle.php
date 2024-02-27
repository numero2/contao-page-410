<?php

/**
 * Page 410 bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   Commercial
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\Page410Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;


class Page410Bundle extends Bundle {


    public function getPath(): string {

        return \dirname(__DIR__);
    }
}
