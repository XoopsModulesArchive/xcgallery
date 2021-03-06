<?php
// $Id: thumbnails.php,v 1.1 2004/11/23 22:20:43 praedator Exp $
//  ------------------------------------------------------------------------ //
//                    xcGallery - XOOPS Gallery Modul                        //
//            Copyright (c) 2003 First Port 1RC1 Derya Kiran                 //
//                           meeresstille@gmx.de                             //
//	     Copyright (c) 2004 Further Versions Marko "Predator" Schmuck        //
//                          http://www.xoops2.org                            //
//  ------------------------------------------------------------------------ //
//  Based on Coppermine Photo Gallery 1.10 ( xcGal 1RC1 )					 //
//  Based on Coppermine Photo Gallery 1.32 ( xcGal 1 Final )                 //
//  (http://coppermine.sourceforge.net/)                                     //
//  developed by Grégory DEMAR                                               //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

include '../../mainfile.php';
define('IN_XCGALLERY', true);

require __DIR__ . '/include/init.inc.php';
$myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object
function get_subcat_data($parent, &$album_set_array, $level)
{
    global $xoopsDB;

    $result = $xoopsDB->query('SELECT cid, name, description FROM ' . $xoopsDB->prefix('xcgal_categories') . " WHERE parent = '$parent'");

    if ($xoopsDB->getRowsNum($result) > 0) {
        $rowset = db_fetch_rowset($result);

        foreach ($rowset as $subcat) {
            $result = $xoopsDB->query('SELECT aid FROM ' . $xoopsDB->prefix('xcgal_albums') . " WHERE category = {$subcat['cid']}");

            $album_count = $xoopsDB->getRowsNum($result);

            while (false !== ($row = $xoopsDB->fetchArray($result))) {
                $album_set_array[] = $row['aid'];
            } // while
        }

        if ($level > 1) {
            get_subcat_data($subcat['cid'], $album_set_array, $level - 1);
        }
    }
}

/**************************************************************************
 * Main code
 **************************************************************************/

if (isset($_GET['sort'])) {
    $USER['sort'] = $_GET['sort'];
}
if (isset($_GET['cat'])) {
    $cat = $_GET['cat'];
}
if (isset($_GET['search'])) {
    $USER['search'] = $_GET['search'];

    if (isset($_GET['type']) && 'full' == $_GET['type']) {
        $USER['search'] = '###' . $USER['search'];
    }
}
if (isset($_GET['suid'])) {
    $USER['suid'] = $_GET['suid'];
}
$album = $_GET['album'];

if (isset($_GET['page'])) {
    $page = max((int)$_GET['page'], 1);
} else {
    $page = 1;
}

$breadcrumb = '';
$breadcrumb_text = '';
$cat_data = [];

// Build the private album set
if (!GALLERY_ADMIN_MODE && $xoopsModuleConfig['allow_private_albums']) {
    get_private_album_set();
}

if (is_numeric($album)) {
    $result = $xoopsDB->query('SELECT category, title FROM ' . $xoopsDB->prefix('xcgal_albums') . " WHERE aid='$album' $ALBUM_SET");

    if ($xoopsDB->getRowsNum($result) > 0) {
        $CURRENT_ALBUM_DATA = $xoopsDB->fetchArray($result);

        $actual_cat = $CURRENT_ALBUM_DATA['category'];

        breadcrumb($actual_cat, $breadcrumb, $breadcrumb_text);

        $cat = -$album;
    } else {
        redirect_header('index.php', 3, _NOPERM);
    }
} elseif (isset($cat) && $cat) { // Meta albums, we need to restrict the albums to the current category
    if ($cat < 0) {
        $result = $xoopsDB->query('SELECT category, title FROM ' . $xoopsDB->prefix('xcgal_albums') . " WHERE aid='" . (-$cat) . "'");

        if ($xoopsDB->getRowsNum($result) > 0) {
            $CURRENT_ALBUM_DATA = $xoopsDB->fetchArray($result);

            $actual_cat = $CURRENT_ALBUM_DATA['category'];

            $CURRENT_CAT_NAME = htmlspecialchars($CURRENT_ALBUM_DATA['title'], ENT_QUOTES | ENT_HTML5);
        }

        $ALBUM_SET .= 'AND aid IN (' . (-$cat) . ') ';

        breadcrumb($actual_cat, $breadcrumb, $breadcrumb_text);
    } else {
        $album_set_array = [];

        if (USER_GAL_CAT == $cat) {
            $where = 'category > ' . FIRST_USER_CAT;
        } else {
            $where = "category = '$cat'";
        }

        $result = $xoopsDB->query('SELECT aid FROM ' . $xoopsDB->prefix('xcgal_albums') . " WHERE $where");

        while (false !== ($row = $xoopsDB->fetchArray($result))) {
            $album_set_array[] = $row['aid'];
        } // while

        if ($cat >= FIRST_USER_CAT) {
            $userHandler = xoops_getHandler('member');

            $alb_owner = $userHandler->getUser($cat - FIRST_USER_CAT);

            if (is_object($alb_owner)) {
                $CURRENT_CAT_NAME = sprintf(_MD_INDEX_USERS_GAL, $alb_owner->uname());
            } else {
                redirect_header('index.php', 2, _MD_NO_EXIST_CAT);
            }
        } else {
            $result = $xoopsDB->query('SELECT name FROM ' . $xoopsDB->prefix('xcgal_categories') . " WHERE cid = '" . $cat . "'");

            if (0 == $xoopsDB->getRowsNum($result)) {
                redirect_header('index.php', 2, _MD_NO_EXIST_CAT);
            }

            $row = $xoopsDB->fetchArray($result);

            $CURRENT_CAT_NAME = htmlspecialchars($row['name'], ENT_QUOTES | ENT_HTML5);
        }

        get_subcat_data($cat, $album_set_array, $xoopsModuleConfig['subcat_level']);

        // Treat the album set

        if (count($album_set_array)) {
            $set = '';

            foreach ($album_set_array as $album_id) {
                $set .= ('' == $set) ? $album_id : ',' . $album_id;
            }

            $ALBUM_SET .= "AND aid IN ($set) ";
        }

        breadcrumb($cat, $breadcrumb, $breadcrumb_text);
    }
}

$GLOBALS['xoopsOption']['template_main'] = 'xcgal_index.html';
require XOOPS_ROOT_PATH . '/header.php';
$xoopsTpl->assign('display_alb_list', '');
user_save_profile();
require_once 'include/theme_func.php';
if ($breadcrumb) {
    theme_display_cat_list($breadcrumb, $cat_data, '');
} else {
    $xoopsTpl->assign('breadcrumb', '<a href="index.php">' . $myts->displayTarea($xoopsModule->getVar('name')) . '</a>');

    $xoopsTpl->assign('lang_category', 0);

    $xoopsTpl->assign('set_stat', 0);
}
display_thumbnails($album, ($cat ?? 0), $page, $xoopsModuleConfig['thumbcols'], $xoopsModuleConfig['thumbrows'], true);

$xoopsTpl->assign('gallery', $myts->displayTarea($xoopsModule->getVar('name')));

main_menu();
do_footer();
require_once '../../footer.php';
