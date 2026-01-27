<?php

/**
 * Page 410 bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2026, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\Page410Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;


class Page410Bundle extends Bundle {


    /**
     * {@inheritdoc}
     */
    public function getPath(): string {

        return \dirname(__DIR__);
    }
}
