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
     * @return Response
     */
    public function getResponse() {

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

        return $objHandler->getResponse($objPage)->setStatusCode(410);
    }


    /**
     * Prepare the output
     *
     * @return PageModel
     *
     * @internal Do not call this method in your code. It will be made private in Contao 5.0.
     */
    protected function prepare() {

        // Find the matching root page
        $objRootPage = $this->getRootPageFromUrl();

        // Forward if the language should be but is not set (see #4028)
        if( $objRootPage->urlPrefix && System::getContainer()->getParameter('contao.legacy_routing') ) {
            // Get the request string without the script name
            $strRequest = Environment::get('relativeRequest');

            // Only redirect if there is no language fragment (see #4669)
            if( $strRequest && !preg_match('@^[a-z]{2}(-[A-Z]{2})?/@', $strRequest) ) {
                // Handle language fragments without trailing slash (see #7666)
                if( preg_match('@^[a-z]{2}(-[A-Z]{2})?$@', $strRequest) ) {
                    $this->redirect(Environment::get('request') . '/', 301);
                } else {
                    if( $strRequest == Environment::get('request') ) {
                        $strRequest = $objRootPage->language . '/' . $strRequest;
                    } else {
                        $strRequest = Environment::get('script') . '/' . $objRootPage->language . '/' . $strRequest;
                    }

                    $this->redirect($strRequest);
                }
            }
        }

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
