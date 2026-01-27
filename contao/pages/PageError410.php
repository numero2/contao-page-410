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

use Contao\CoreBundle\Exception\ForwardPageNotFoundException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Environment;
use Contao\Frontend;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;


class PageError410 extends Frontend {


    /**
     * Generate an error 410 page
     */
    public function generate() {
        /** @var PageModel $objPage */
        global $objPage;

        $obj410 = $this->prepare();
        $objPage = $obj410->loadDetails();

        // Reset inherited cache timeouts (see #231)
        if( !$objPage->includeCache ) {
            $objPage->cache = 0;
            $objPage->clientCache = 0;
        }

        /** @var PageRegular $objHandler */
        $objHandler = new $GLOBALS['TL_PTY']['regular']();

        header('HTTP/1.1 410 Gone');
        $objHandler->generate($objPage);
    }


    /**
     * Return a response object
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function getResponse() {

        /** @var Contao\PageModel $objPage */
        global $objPage;

        $obj410 = $this->prepare();
        $objPage = $obj410->loadDetails();

        // Reset inherited cache timeouts (see #231)
        if( !$objPage->includeCache ) {
            $objPage->cache = 0;
            $objPage->clientCache = 0;
        }

        /** @var Contao\PageRegular $objHandler */
        $objHandler = new $GLOBALS['TL_PTY']['regular']();

        return $objHandler->getResponse($objPage)->setStatusCode(410);
    }


    /**
     * Prepare the output
     *
     * @return Contao\PageModel
     *
     * @throws Contao\CoreBundle\Exception\ForwardPageNotFoundException
     * @throws Contao\CoreBundle\Exception\PageNotFoundException
     */
    protected function prepare() {

        // Find the matching root page
        $objRootPage = $this->getRootPageFromUrl();

        // Look for a 410 page
        $obj410 = PageModel::find410ByPid($objRootPage->id);

        // Die if there is no page at all
        if( $obj410 === null ) {
            throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
        }

        // Forward to another page
        if( $obj410->autoforward && $obj410->jumpTo ) {
            $objNextPage = PageModel::findPublishedById($obj410->jumpTo);

            if( $objNextPage === null ) {

                System::getContainer()->get('monolog.logger.contao.error')->error('Forward page ID "' . $obj410->jumpTo . '" does not exist');
                throw new ForwardPageNotFoundException('Forward page not found');
            }

            $this->redirect($objNextPage->getFrontendUrl());
        }

        return $obj410;
    }
}
