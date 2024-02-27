<?php

/**
 * Page 410 bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   Commercial
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\Page410Bundle\Routing;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Exception\NoRootPageFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\AbstractPageRouteProvider;
use Contao\CoreBundle\Routing\Page\PageRegistry;
use Contao\CoreBundle\Routing\Page\PageRoute;
use Contao\CoreBundle\Routing\Route404Provider;
use Contao\CoreBundle\Util\LocaleUtil;
use Contao\PageModel;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Cmf\Component\Routing\Candidates\CandidatesInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


if( version_compare(ContaoCoreBundle::getVersion(), '5.0', '>=') ) {

    class Route410Provider extends AbstractPageRouteProvider {

        use Route410ProviderBase;


        public function getRoutesByNames( ?array $names=null ): array {

            $this->getRoutesByNamesForContao5($names);
        }
    }

} else {

    class Route410Provider extends AbstractPageRouteProvider {

        use Route410ProviderBase;


        public function getRoutesByNames( $names ): array {

            if( !is_array($names) ) {
                return [];
            }

            $this->getRoutesByNamesForContao5($names);
        }
    }
}

trait Route410ProviderBase {


    private Route404Provider $routeProvider;


    /**
     * @internal Do not inherit from this class; decorate the "contao.routing.route_410_provider" service instead
     */
    public function __construct( Route404Provider $routeProvider, ContaoFramework $framework, CandidatesInterface $candidates, PageRegistry $pageRegistry ) {

        parent::__construct($framework, $candidates, $pageRegistry);

        $this->routeProvider = $routeProvider;
    }


    public function getRouteCollectionForRequest( Request $request ): RouteCollection {

        if( ($_SERVER['REDIRECT_STATUS']??null) !== '410' ) {
            return $this->routeProvider->getRouteCollectionForRequest($request);
        }

        $this->framework->initialize(true);

        $collection = new RouteCollection();

        $routes = array_merge(
            $this->getGoneRoutes(),
            $this->getLocaleFallbackRoutes($request)
        );

        $this->sortRoutes($routes, $request->getLanguages());

        foreach( $routes as $name => $route ) {
            $collection->add($name, $route);
        }

        return $collection;
    }


    public function getRouteByName( $name ): Route {

        if( ($_SERVER['REDIRECT_STATUS']??null) !== '410' ) {
            return $this->routeProvider->getRouteByName($name);
        }

        $this->framework->initialize(true);

        $ids = $this->getPageIdsFromNames([$name]);

        if( empty($ids) ) {
            throw new RouteNotFoundException('Route name does not match a page ID');
        }

        /** @var PageModel $pageModel */
        $pageModel = $this->framework->getAdapter(PageModel::class);
        $page = $pageModel->findByPk($ids[0]);

        if( $page === null ) {
            throw new RouteNotFoundException(sprintf('Page ID "%s" not found', $ids[0]));
        }

        $routes = [];

        $this->addNotFoundRoutesForPage($page, $routes);
        $this->addLocaleRedirectRoute($this->pageRegistry->getRoute($page), null, $routes);

        if( !array_key_exists($name, $routes) ) {
            throw new RouteNotFoundException('Route "'.$name.'" not found');
        }

        return $routes[$name];
    }


    public function getRoutesByNamesForContao5( ?array $names=null ): array {

        if( ($_SERVER['REDIRECT_STATUS']??null) === '410' ) {
            return $this->routeProvider->getRoutesByNames($name);
        }

        $this->framework->initialize(true);

        /** @var PageModel $pageAdapter */
        $pageAdapter = $this->framework->getAdapter(PageModel::class);

        if( $names === null ) {
            $pages = $pageAdapter->findAll();
        } else {
            $ids = $this->getPageIdsFromNames($names);

            if( empty($ids) ) {
                return [];
            }

            $pages = $pageAdapter->findBy('tl_page.id IN ('.implode(',', $ids).')', []);
        }

        $routes = [];

        foreach ($pages as $page) {
            $this->addNotFoundRoutesForPage($page, $routes);
            $this->addLocaleRedirectRoute($this->pageRegistry->getRoute($page), null, $routes);
        }

        $this->sortRoutes($routes);

        return $routes;
    }


    private function getGoneRoutes(): array {

        if( ($_SERVER['REDIRECT_STATUS']??null) !== '410' ) {
            return $this->routeProvider->getGoneRoutes();
        }

        $this->framework->initialize(true);

        /** @var PageModel $pageModel */
        $pageModel = $this->framework->getAdapter(PageModel::class);
        $pages = $pageModel->findByType('error_410');

        if(  $pages === null ) {
            return [];
        }

        $routes = [];

        foreach( $pages as $page ) {
            $this->addNotFoundRoutesForPage($page, $routes);
        }

        return $routes;
    }


    private function addNotFoundRoutesForPage( PageModel $page, array &$routes ): void {

        if( $page->type !== 'error_410' ) {
            return;
        }

        try {
            $page->loadDetails();

            if( !$page->rootId ) {
                return;
            }
        } catch( NoRootPageFoundException $e ) {
            return;
        }

        $defaults = [
            '_token_check' => true,
            '_controller' => 'Contao\FrontendIndex::renderPage',
            '_scope' => ContaoCoreBundle::SCOPE_FRONTEND,
            '_locale' => LocaleUtil::formatAsLocale($page->rootLanguage ?? ''),
            '_format' => 'html',
            '_canonical_route' => 'tl_page.'.$page->id,
            'pageModel' => $page,
        ];

        $requirements = ['_url_fragment' => '.*'];
        $path = '/{_url_fragment}';

        $routes['tl_page.'.$page->id.'.error_410'] = new Route(
            $path,
            $defaults,
            $requirements,
            ['utf8' => true],
            $page->domain,
            $page->rootUseSSL ? 'https' : 'http'
        );

        if( !$page->urlPrefix ) {
            return;
        }

        $path = '/'.$page->urlPrefix.$path;

        $routes['tl_page.'.$page->id.'.error_410.locale'] = new Route(
            $path,
            $defaults,
            $requirements,
            ['utf8' => true],
            $page->domain,
            $page->rootUseSSL ? 'https' : 'http'
        );
    }


    private function getLocaleFallbackRoutes( Request $request ): array {

        if( $request->getPathInfo() === '/' ) {
            return [];
        }

        $routes = [];

        foreach( $this->findCandidatePages($request) as $page ) {
            $this->addLocaleRedirectRoute($this->pageRegistry->getRoute($page), $request, $routes);
        }

        return $routes;
    }


    private function addLocaleRedirectRoute( PageRoute $route, ?Request $request, array &$routes ): void {

        $length = strlen($route->getUrlPrefix());

        if( $length === 0) {
            return;
        }

        $redirect = new Route(
            substr($route->getPath(), $length + 1),
            $route->getDefaults(),
            $route->getRequirements(),
            $route->getOptions(),
            $route->getHost(),
            $route->getSchemes(),
            $route->getMethods()
        );

        $path = $route->getPath();

        if( null !== $request ) {
            $path = '/'.$route->getUrlPrefix().$request->getPathInfo();
        }

        $redirect->addDefaults([
            '_controller' => RedirectController::class,
            'path' => $path,
            'permanent' => false,
        ]);

        $routes['tl_page.'.$route->getPageModel()->id.'.locale'] = $redirect;
    }


    /**
     * Sorts routes so that the FinalMatcher will correctly resolve them.
     *
     * 1. Sort locale-aware routes first, so e.g. /de/not-found.html renders the german error page
     * 2. Then sort by hostname, so the ones with empty host are only taken if no hostname matches
     * 3. Lastly pages must be sorted by accept language and fallback, so the best language matches first
     */
    private function sortRoutes( array &$routes, array $languages = null ): void {

        uasort(
            $routes,
            function( Route $a, Route $b ) use ( $languages, $routes ) {
                $errorA = strpos('.error_410', array_search($a, $routes, true)) !== false;
                $errorB = strpos('.error_410', array_search($a, $routes, true), -7) !== false;
                $localeA = substr(array_search($a, $routes, true), -7) === '.locale';
                $localeB = substr(array_search($b, $routes, true), -7) === '.locale';

                if( $errorA && !$errorB ) {
                    return 1;
                }

                if( $errorB && !$errorA ) {
                    return -1;
                }

                if( $localeA && !$localeB ) {
                    return -1;
                }

                if( $localeB && !$localeA ) {
                    return 1;
                }

                // Convert languages array so key is language and value is priority
                if( $languages !== null ) {
                    $languages = $this->convertLanguagesForSorting($languages);
                }

                return $this->compareRoutes($a, $b, $languages);
            }
        );
    }
}