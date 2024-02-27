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

use Contao\Date;
use Contao\PageModel as CorePageModel;


class PageModel extends CorePageModel {


    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_page';


    /**
     * Find an error 410 page by its parent ID
     *
     * @param integer $intPid
     * @param array $arrOptions
     *
     * @return Contao\PageModel|null The model or null if there is no 403 page
     */
    public static function find410ByPid( $intPid, array $arrOptions=[] ) {

        $t = static::$strTable;
        $arrColumns = ["$t.pid=? AND $t.type='error_410'"];

        if( !static::isPreviewMode($arrOptions) ) {
            $time = Date::floorToMinute();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')";
        }

        if( !isset($arrOptions['order']) ) {
            $arrOptions['order'] = "$t.sorting";
        }

        return static::findOneBy($arrColumns, $intPid, $arrOptions);
    }
}
