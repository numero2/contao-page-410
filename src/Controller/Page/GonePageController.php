<?php

/**
 * Page 410 bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2026, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\Page410Bundle\Controller\Page;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsPage;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\Page\ContentCompositionInterface;
use Contao\FrontendIndex;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Response;


#[AsPage('error_410', path: false)]
class GonePageController extends AbstractController implements ContentCompositionInterface {


    /**
     * @var Contao\CoreBundle\Framework\ContaoFramework
     */
    private ContaoFramework $framework;


    public function __construct( ContaoFramework $framework ) {

        $this->framework = $framework;
    }


    public function __invoke( PageModel $pageModel ): Response {

        $this->framework->initialize();

        return $this->framework
            ->createInstance(FrontendIndex::class)
            ->renderPage($pageModel)
        ;
    }


    public function supportsContentComposition( PageModel $pageModel ): bool {

        return !$pageModel->autoforward;
    }
}