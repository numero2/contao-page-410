<?php

/**
 * Page 410 bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   Commercial
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\Page410Bundle\EventListener\DataContainer;

use Contao\CoreBundle\Event\FilterPageTypeEvent;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;


#[AsEventListener(event: FilterPageTypeEvent::class, method: 'limitPageTypes')]
class PageListener {


    private Connection $connection;


    public function __construct( Connection $connection ) {

        $this->connection = $connection;
    }



    /**
     * Make sure error 410 type is always hidden from navigation, sitemap, etc.
     *
     * @param Contao\DataContainer $dc
     *
     * @Callback(table="tl_page", target="config.onload")
     */
    public function hide410FromNavigation( DataContainer $dc ) {

        if( $dc && $dc->id ) {

            $page = PageModel::findById($dc->id);

            if( $page && $page->type == 'error_410' ) {

                $page->hide = 1;
                $page->sitemap = 'map_never';
                $page->noSearch = 1;
                $page->robots = 'noindex,nofollow';

                if( $page->isModified() ) {
                    $page->save();
                }
            }
        }
    }


    /**
     * Return the status icon for error page 410
     *
	 * @param Contao\PageModel|Contao\Result|stdClass $page
     * @param string $image
     *
     * @return string
     *
     * @Hook("getPageStatusIcon")
     */
    public function getPageStatusIcon( $page, string $image ): string {

        if( $page->type !== 'error_410' ) {
            return $image;
        }

        $sub = 0;
        $type = 'error_410';

        // Page not published or not active
        if( !$page->published || ($page->start && $page->start > time()) || ($page->stop && $page->stop <= time()) ) {
            $sub++;
        }

        // Get the image name
        if( $sub > 0 ) {
            $image = $type . '_' . $sub . '.svg';
        } else {
            $image = $type . '.svg';
        }

        $image = '/bundles/page410/' . $image;


        return $image;
    }


    /**
     * Error 410 page type can only be inside root page and max one per root page
     *
     * @param \Contao\CoreBundle\Event\FilterPageTypeEvent $event
     */
    public function limitPageTypes( FilterPageTypeEvent $event ): void {

        $dc = $event->getDataContainer();

        if (!$dc->activeRecord) {
            return;
        }

        $t = PageModel::getTable();

        $parentType = $this->connection->fetchOne("SELECT type FROM $t WHERE id=?", [$dc->activeRecord->pid]);

        // 410 only be placed directly inside root pages
        if ('root' !== $parentType) {

            $event->removeOption('error_410');

            return;
        }

        // Only allow one 410 per root page
        $siblingTypes = $this->connection->fetchFirstColumn(
            "SELECT DISTINCT type FROM $t WHERE pid=? AND id!=?",
            [$dc->activeRecord->pid, $dc->activeRecord->id]
        );

        if( in_array('error_410', $siblingTypes) ) {
            $event->removeOption('error_410');
        }
    }
}