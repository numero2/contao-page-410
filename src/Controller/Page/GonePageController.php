<?php

/**
 * Page 410 bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   Commercial
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\Page410Bundle\Controller\Page;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\Page\ContentCompositionInterface;
use Contao\CoreBundle\ServiceAnnotation\Page;
use Contao\FrontendIndex;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Page("error_410", path=false)
 */
class GonePageController extends AbstractController implements ContentCompositionInterface {


    private ContaoFramework $framework;


    public function __construct( ContaoFramework $framework ) {

        $this->framework = $framework;
    }


    public function __invoke( PageModel $pageModel): Response {

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