<?php
// $Id: displayimage.php,v 1.1 2004/11/23 22:15:09 praedator Exp $
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

define('IN_XCGALLERY', true);

include '../../mainfile.php';
require __DIR__ . '/include/init.inc.php';

if ((1 != ($xoopsModuleConfig['anosee'])) && !is_object($xoopsUser)) {
    redirect_header(XOOPS_URL . '/user.php', 2, _NOPERM);

    exit();
}
if ($xoopsModuleConfig['read_exif_data'] && function_exists('exif_read_data')) {
    include 'include/exif_php.inc.php';
} elseif ($xoopsModuleConfig['read_exif_data']) {
    redirect_header('index.php', 2, _MD_DIS_EXIF_ERR);

    exit();
}
$myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object
/**************************************************************************
 * Local functions definition
 **************************************************************************/

// Prints the image-navigation menu
function html_img_nav_menu()
{
    global $xoopsModuleConfig, $HTTP_SERVER_VARS, $_GET, $CURRENT_PIC_DATA, $PHP_SELF;

    global $album, $cat, $pos, $pic_count;

    global $xoopsTpl, $album_name;

    $cat_link = is_numeric($album) ? '' : '&amp;cat=' . $cat;

    $human_pos = $pos + 1;

    $page = ceil(($pos + 1) / ($xoopsModuleConfig['thumbrows'] * $xoopsModuleConfig['thumbcols']));

    $pid = $CURRENT_PIC_DATA['pid'];

    if ($pos > 0) {
        $prev = $pos - 1;

        $prev_data = get_pic_data($album, $pic_count, $album_name, $prev, 1, false);

        $prev_tgt = "$PHP_SELF?album=$album$cat_link&amp;pos=$prev&amp;pid=" . $prev_data[0]['pid'];

        $prev_title = _MD_DIS_PREV;
    } else {
        $prev_tgt = 'javascript:;';

        $prev_title = '';
    }

    if ($pos < ($pic_count - 1)) {
        $next = $pos + 1;

        $next_data = get_pic_data($album, $pic_count, $album_name, $next, 1, false);

        $next_tgt = "$PHP_SELF?album=$album$cat_link&amp;pos=$next&amp;pid=" . $next_data[0]['pid'];

        $next_title = _MD_DIS_NEXT;
    } else {
        $next_tgt = 'javascript:;';

        $next_title = '';
    }

    if (USER_CAN_SEND_ECARDS) {
        $ecard_tgt = "ecard.php?album=$album$cat_link&amp;pid=$pid&amp;pos=$pos";

        $ecard_title = _MD_DIS_SEND_CARD;
    } else {
        $ecard_tgt = "javascript:alert('" . addslashes(_MD_DIS_CARD_DISABLEMSG) . "');";

        $ecard_title = _MD_DIS_CARD_DISABLE;
    }

    $thumb_tgt = "thumbnails.php?album=$album$cat_link&amp;page=$page";

    $slideshow_tgt = "$PHP_SELF?pid=$pid&amp;album=$album$cat_link&amp;pid=$pid&amp;slideshow=5000";

    $pic_pos = sprintf(_MD_DIS_PICPOS, $human_pos, $pic_count);

    $xoopsTpl->assign('thumb_tgt', $thumb_tgt);

    $xoopsTpl->assign('thumb_title', _MD_DIS_BACK_TNPAGE);

    $xoopsTpl->assign('pic_info_title', _MD_DIS_SHOW_PIC_INFO);

    $xoopsTpl->assign('slideshow_tgt', $slideshow_tgt);

    $xoopsTpl->assign('slideshow_title', _MD_DIS_SLIDE);

    $xoopsTpl->assign('pic_pos', $pic_pos);

    $xoopsTpl->assign('ecard_tgt', $ecard_tgt);

    $xoopsTpl->assign('ecard_title', $ecard_title);

    $xoopsTpl->assign('prev_tgt', $prev_tgt);

    $xoopsTpl->assign('prev_title', $prev_title);

    $xoopsTpl->assign('next_tgt', $next_tgt);

    $xoopsTpl->assign('next_title', $next_title);
}

// Displays a picture
function html_picture()
{
    global $xoopsModuleConfig, $CURRENT_PIC_DATA, $CURRENT_ALBUM_DATA, $USER, $HTTP_COOKIE_VARS;

    global $album, $comment_date_fmt;

    global $xoopsTpl, $myts;

    $pid = $CURRENT_PIC_DATA['pid'];

    if (!isset($USER['liv']) || !is_array($USER['liv'])) {
        $USER['liv'] = [];
    }

    // Add 1 to hit counter

    if ('lasthits' != $album && !in_array($pid, $USER['liv'], true) && isset($HTTP_COOKIE_VARS[$xoopsModuleConfig['cookie_name'] . '_data'])) {
        add_hit($pid);

        if (count($USER['liv']) > 4) {
            array_shift($USER['liv']);
        }

        $USER['liv'][] = $pid;
    }

    if ($xoopsModuleConfig['make_intermediate'] && max($CURRENT_PIC_DATA['pwidth'], $CURRENT_PIC_DATA['pheight']) > $xoopsModuleConfig['picture_width']) {
        $picture_url = get_pic_url($CURRENT_PIC_DATA, 'normal');
    } else {
        $picture_url = get_pic_url($CURRENT_PIC_DATA, 'fullsize');
    }

    $image_size = compute_img_size($CURRENT_PIC_DATA['pwidth'], $CURRENT_PIC_DATA['pheight'], $xoopsModuleConfig['picture_width']);

    $xoopsTpl->assign('pid', $pid);

    $xoopsTpl->assign('picture_url', $picture_url);

    $xoopsTpl->assign('image_size', $image_size['geom']);

    //if (is_image($CURRENT_PIC_DATA['filename'])){

    $xoopsTpl->assign('file_type', 'image');

    if (isset($image_size['reduced'])) {
        $winsizeX = $CURRENT_PIC_DATA['pwidth'] + 16;

        $winsizeY = $CURRENT_PIC_DATA['pheight'] + 16;

        $xoopsTpl->assign('reduced', 1);

        $xoopsTpl->assign('winsizeX', $winsizeX);

        $xoopsTpl->assign('winsizeY', $winsizeY);

        $xoopsTpl->assign('uniqid_rand', uniqid(mt_rand()));

        $xoopsTpl->assign('lang_view_fs', _MD_DIS_FULL);
    } else {
        $xoopsTpl->assign('reduced', 0);
    }

    //}else if (is_movie($CURRENT_PIC_DATA['filename']))

    {
        $xoopsTpl->assign('file_type', 'movie');
    }

    if ($CURRENT_PIC_DATA['title']) {
        $xoopsTpl->assign('pic_title', htmlspecialchars($CURRENT_PIC_DATA['title'], ENT_QUOTES | ENT_HTML5));
    } else {
        $xoopsTpl->assign('pic_title', '');
    }

    if ($CURRENT_PIC_DATA['caption']) {
        $xoopsTpl->assign('pic_caption', $myts->displayTarea($CURRENT_PIC_DATA['caption'], 0));
    } else {
        $xoopsTpl->assign('pic_caption', '');
    }

    if ((USER_ADMIN_MODE && $CURRENT_ALBUM_DATA['category'] == FIRST_USER_CAT + USER_ID) || GALLERY_ADMIN_MODE) {
        $xoopsTpl->assign('lang_confirm_del', _MD_DIS_CONF_DEL);

        $xoopsTpl->assign('lang_del_pic', _MD_DIS_DEL_PIC);
    } else {
        $xoopsTpl->assign('lang_del_pic', '');
    }
}

function html_rating_box()
{
    global $CURRENT_PIC_DATA, $CURRENT_ALBUM_DATA;

    global $xoopsTpl;

    if (!(USER_CAN_RATE_PICTURES && 'YES' == $CURRENT_ALBUM_DATA['votes'])) {
        return '';
    }

    $votes = $CURRENT_PIC_DATA['votes'] ? sprintf(_MD_DIS_RATINGCUR, round($CURRENT_PIC_DATA['pic_rating'] / 2000, 1), $CURRENT_PIC_DATA['votes']) : _MD_DIS_NO_VOTE;

    $pid = $CURRENT_PIC_DATA['pid'];

    $xoopsTpl->assign('lang_rate_this_pic', _MD_DIS_RATE_THIS);

    $xoopsTpl->assign('votes', $votes);

    $xoopsTpl->assign('lang_rubbish', _MD_DIS_RUBBISH);

    $xoopsTpl->assign('lang_poor', _MD_DIS_POOR);

    $xoopsTpl->assign('lang_fair', _MD_DIS_FAIR);

    $xoopsTpl->assign('lang_good', _MD_DIS_GOOD);

    $xoopsTpl->assign('lang_excellent', _MD_DIS_EXCELLENT);

    $xoopsTpl->assign('lang_great', _MD_DIS_GREAT);
}

// Display picture information
function html_picinfo()
{
    global $CURRENT_PIC_DATA, $CURRENT_ALBUM_DATA;

    global $album, $xoopsModuleConfig, $myts;

    global $xoopsTpl;

    $info[_MD_DIS_FNAME] = htmlspecialchars($CURRENT_PIC_DATA['filename'], ENT_QUOTES | ENT_HTML5);

    $info[_MD_DIS_ANAME] = '<span class="alblink"><a href="thumbnails.php?album=' . $CURRENT_PIC_DATA['aid'] . '">' . htmlspecialchars($CURRENT_ALBUM_DATA['title'], ENT_QUOTES | ENT_HTML5) . '</a></span>';

    $userHandler = xoops_getHandler('member');

    $submitter = $userHandler->getUser($CURRENT_PIC_DATA['owner_id']);

    if (is_object($submitter)) {
        $info[_MD_DIS_UPLOADER] = '<span class="alblink"><a href="'
                                  . XOOPS_URL
                                  . '/userinfo.php?uid='
                                  . $submitter->uid()
                                  . '">'
                                  . $submitter->uname()
                                  . '</a>&nbsp;&nbsp;<a href="thumbnails.php?album=usearch&amp;suid='
                                  . $submitter->uid()
                                  . '" title="'
                                  . _MD_DIS_VIEW_MORE_BY
                                  . ' '
                                  . $submitter->uname()
                                  . '"><img src="images/more.gif" align="middle" alt=""></a></span>';
    }

    if ($CURRENT_PIC_DATA['votes'] > 0) {
        $info[sprintf(_MD_DIS_RATING, $CURRENT_PIC_DATA['votes'])] = '<img src="images/rating' . round($CURRENT_PIC_DATA['pic_rating'] / 2000) . '.gif" align="middle" alt="">';
    }

    $keys = explode(' ', htmlspecialchars($CURRENT_PIC_DATA['keywords'], ENT_QUOTES | ENT_HTML5));

    $info[_MD_KEYS] = '<span class="alblink">';

    foreach ($keys as $k) {
        $info[_MD_KEYS] .= '<a href="thumbnails.php?album=search&amp;search=' . rawurlencode($k) . "\">{$k}</a> ";
    }

    $info[_MD_KEYS] .= '</span>';

    //$info[_MD_KEYS]   = '<span class="alblink">'.preg_replace("/(\S+)/","<a href=\"thumbnails.php?album=search&amp;search=\\1\">\\1</a>" , htmlspecialchars($CURRENT_PIC_DATA['keywords'])).'</span>';

    for ($i = 1; $i <= 4; $i++) {
        if ($xoopsModuleConfig['user_field' . $i . '_name']) {
            $info[$xoopsModuleConfig['user_field' . $i . '_name']] = $CURRENT_PIC_DATA['user' . $i];
        }
    }

    $info[_MD_DIS_FSIZE] = ($CURRENT_PIC_DATA['filesize'] > 10240 ? ($CURRENT_PIC_DATA['filesize'] >> 10) . ' ' . _MD_KB : $CURRENT_PIC_DATA['filesize'] . ' ' . _MD_BYTES);

    $info[_MD_DIS_DIMEMS] = sprintf(_MD_DIS_SIZE, $CURRENT_PIC_DATA['pwidth'], $CURRENT_PIC_DATA['pheight']);

    $info[_MD_DIS_DISPLAYED] = sprintf(_MD_DIS_VIEWS, $CURRENT_PIC_DATA['hits']);

    $info[_MD_DIS_SENT] = sprintf(_MD_DIS_VIEWS, $CURRENT_PIC_DATA['sent_card']);

    $path_to_pic = $xoopsModuleConfig['fullpath'] . $CURRENT_PIC_DATA['filepath'] . $CURRENT_PIC_DATA['filename'];

    if ($xoopsModuleConfig['read_exif_data']) {
        $exif = exif_parse_file($path_to_pic);
    }

    if (isset($exif) && is_array($exif)) {
        if (isset($exif['Camera'])) {
            $info[_MD_DIS_CAMERA] = $exif['Camera'];
        }

        if (isset($exif['DateTaken'])) {
            $info[_MD_DIS_DATA_TAKEN] = $exif['DateTaken'];
        }

        if (isset($exif['Aperture'])) {
            $info[_MD_DIS_APERTURE] = $exif['Aperture'];
        }

        if (isset($exif['ExposureTime'])) {
            $info[_MD_DIS_EXPTIME] = $exif['ExposureTime'];
        }

        if (isset($exif['FocalLength'])) {
            $info[_MD_DIS_FLENGTH] = $exif['FocalLength'];
        }

        if (isset($exif['Comment'])) {
            $info[_MD_DIS_COMMENT] = $exif['Comment'];
        }
    }

    if (USER_IS_ADMIN) {
        $info[_MD_DIS_SUBIP] = $CURRENT_PIC_DATA['ip'];
    }

    $xoopsTpl->assign('lang_picinfo_title', _MD_DIS_TITLE);

    $xoopsTpl->assign('picinfo', $HTTP_COOKIE_VARS['picinfo'] ?? ($xoopsModuleConfig['display_pic_info'] ? 'block' : 'none'));

    foreach ($info as $key => $value) {
        $xoopsTpl->append('infos', ['key' => $key, 'value' => $value]);
    }
}

// Display the full size image
function display_fullsize_pic()
{
    global $xoopsModuleConfig, $_GET, $ALBUM_SET;

    global $xoopsDB, $pic_out;

    if (isset($_GET['picfile'])) {
        if (!GALLERY_ADMIN_MODE) {
            redirect_header('index.php', 2, _MD_ACCESS_DENIED);
        }

        $picfile = $_GET['picfile'];

        $picname = $xoopsModuleConfig['fullpath'] . $picfile;

        $imagesize = @getimagesize($picname);

        $pic_out = '<img src="' . path2url($picname) . "\" $imagesize[3] class=\"image\" border=\"0\" alt=\"$picfile\"><br>\n";
    } elseif (isset($_GET['pid'])) {
        $pid = (int)$_GET['pid'];

        $sql = 'SELECT * ' . 'FROM ' . $xoopsDB->prefix('xcgal_pictures') . ' ' . "WHERE pid='$pid' $ALBUM_SET";

        $result = $xoopsDB->query($sql);

        if (!$xoopsDB->getRowsNum($result)) {
            redirect_header('index.php', 2, _MD_NON_EXIST_AP);
        }

        $row = $xoopsDB->fetchArray($result);

        $pic_url = get_pic_url($row, 'fullsize');

        $geom = 'width="' . $row['pwidth'] . '" height="' . $row['pheight'] . '"';

        $pic_out = '<img src="' . $pic_url . "\" $geom class=\"image\" border=\"0\" alt=\"" . htmlspecialchars($row['filename'], ENT_QUOTES | ENT_HTML5) . "\"><br>\n";
    }
}

function get_subcat_data($parent, $level)
{
    global $ALBUM_SET_ARRAY, $xoopsDB;

    $result = $xoopsDB->query('SELECT cid, name, description FROM ' . $xoopsDB->prefix('xcgal_categories') . " WHERE parent = '$parent'");

    if ($xoopsDB->getRowsNum($result) > 0) {
        $rowset = db_fetch_rowset($result);

        foreach ($rowset as $subcat) {
            $result = $xoopsDB->query('SELECT aid FROM ' . $xoopsDB->prefix('xcgal_albums') . " WHERE category = {$subcat['cid']}");

            $album_count = $xoopsDB->getRowsNum($result);

            while (false !== ($row = $xoopsDB->fetchArray($result))) {
                $ALBUM_SET_ARRAY[] = $row['aid'];
            } // while
        }

        if ($level > 1) {
            get_subcat_data($subcat['cid'], $level - 1);
        }
    }
}

/**************************************************************************
 * Main code
 **************************************************************************/

$pos = isset($_GET['pos']) ? (int)$_GET['pos'] : (isset($_GET['pid']) ? -(int)$_GET['pid'] : 0);

$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$album = $_GET['album'] ?? '';

// Build the private album set
if (!GALLERY_ADMIN_MODE && $xoopsModuleConfig['allow_private_albums']) {
    get_private_album_set();
}

// Build the album set if required
if (!is_numeric($album) && $cat) { // Meta albums, we need to restrict the albums to the current category
    if ($cat < 0) {
        $ALBUM_SET .= 'AND aid IN (' . (-$cat) . ') ';
    } else {
        $ALBUM_SET_ARRAY = [];

        if (USER_GAL_CAT == $cat) {
            $where = 'category > ' . FIRST_USER_CAT;
        } else {
            $where = "category = '$cat'";
        }

        $result = $xoopsDB->query('SELECT aid FROM ' . $xoopsDB->prefix('xcgal_albums') . " WHERE $where");

        while (false !== ($row = $xoopsDB->fetchArray($result))) {
            $ALBUM_SET_ARRAY[] = $row['aid'];
        } // while

        get_subcat_data($cat, $xoopsModuleConfig['subcat_level']);

        // Treat the album set

        if (count($ALBUM_SET_ARRAY)) {
            $set = '';

            foreach ($ALBUM_SET_ARRAY as $album_id) {
                $set .= ('' == $set) ? $album_id : ',' . $album_id;
            }

            $ALBUM_SET .= "AND aid IN ($set) ";
        }
    }
}

// Retrieve data for the current picture

if ($pos < 0) {
    $pid = -$pos;

    $result = $xoopsDB->query('SELECT aid from ' . $xoopsDB->prefix('xcgal_pictures') . " WHERE pid='$pid' $ALBUM_SET LIMIT 1");

    if (0 == $xoopsDB->getRowsNum($result)) {
        redirect_header('index.php', 2, _MD_NON_EXIST_AP);
    }

    $row = $xoopsDB->fetchArray($result);

    $album = $row['aid'];

    $pic_data = get_pic_data($album, $pic_count, $album_name, -1, -1, false);

    for ($pos = 0; $pic_data[$pos]['pid'] != $pid && $pos < $pic_count; $pos++) {
    }

    $pic_data = get_pic_data($album, $pic_count, $album_name, $pos, 1, false);

    $CURRENT_PIC_DATA = $pic_data[0];
} elseif (isset($pos)) {
    $pic_data = get_pic_data($album, $pic_count, $album_name, $pos, 1, false);

    if (0 == $pic_count) {
        redirect_header('index.php', 2, _MD_NO_IMG_TO_DISPLAY);
    } elseif (0 == count($pic_data) && $pos >= $pic_count) {
        $pos = $pic_count - 1;

        $human_pos = $pos + 1;

        $pic_data = get_pic_data($album, $pic_count, $album_name, $pos, 1, false);
    }

    $CURRENT_PIC_DATA = $pic_data[0];
}

// Retrieve data for the current album
if (isset($CURRENT_PIC_DATA)) {
    $result = $xoopsDB->query('SELECT title, comments, votes, category FROM ' . $xoopsDB->prefix('xcgal_albums') . " WHERE aid='{$CURRENT_PIC_DATA['aid']}' LIMIT 1");

    if (!$xoopsDB->getRowsNum($result)) {
        redirect_header('index.php', 2, sprintf(_MD_PIC_IN_INVALID_ALBUM, $CURRENT_PIC_DATA['aid']));
    }

    $CURRENT_ALBUM_DATA = $xoopsDB->fetchArray($result);

    if (is_numeric($album)) {
        $cat = -$album;

        $actual_cat = $CURRENT_ALBUM_DATA['category'];
    } else {
        $actual_cat = $CURRENT_ALBUM_DATA['category'];
    }
}

if (isset($_GET['fullsize'])) {
    display_fullsize_pic();

    require_once XOOPS_ROOT_PATH . '/class/template.php';

    $xoopsTpl = new XoopsTpl();

    $xoopsTpl->assign('sitename', $xoopsConfig['sitename']);

    $xoopsTpl->assign('gallery', $xoopsModule->getVar('name'));

    $xoopsTpl->assign('pic_out', $pic_out);

    $xoopsTpl->display('db:xcgal_fullsize.html');

    exit();
} elseif (isset($_GET['slideshow'])) {
    $GLOBALS['xoopsOption']['template_main'] = 'xcgal_slideshow.html';

    require XOOPS_ROOT_PATH . '/header.php';

    $xoopsTpl->assign('speed', (int)$_GET['slideshow']);

    $i = 0;

    $j = 0;

    $pid = $_GET['pid'];

    $start_img = '';

    $pic_data = get_pic_data($_GET['album'], $pic_count, $album_name, -1, -1, false);

    foreach ($pic_data as $picture) {
        if ($xoopsModuleConfig['make_intermediate'] && max($picture['pwidth'], $picture['pheight']) > $xoopsModuleConfig['picture_width']) {
            $picture_url = get_pic_url($picture, 'normal');
        } else {
            $picture_url = get_pic_url($picture, 'fullsize');
        }

        $xoopsTpl->append('pics', ['pic_url' => $picture_url, 'i' => $i]);

        if ($picture['pid'] == $pid) {
            $j = $i;

            $start_img = $picture_url;
        }

        $i++;
    }

    $xoopsTpl->assign('j', $j);

    $xoopsTpl->assign('album', $_GET['album'] ?? '');

    $xoopsTpl->assign('cat', $_GET['cat'] ?? '');

    $xoopsTpl->assign('tab_width', $xoopsModuleConfig['picture_table_width']);

    $xoopsTpl->assign('cell_height', $xoopsModuleConfig['picture_width'] + 100);

    $xoopsTpl->assign('start_img', $start_img);

    $xoopsTpl->assign('lang_stop_slideshow', _MD_DIS_STOP_SLIDE);

    user_save_profile();

    $xoopsTpl->assign('gallery', $xoopsModule->getVar('name'));

    require_once 'include/theme_func.php';

    main_menu();

    do_footer();

    require_once '../../footer.php';
} else {
    if (!isset($pos)) {
        redirect_header('index.php', 2, _MD_NON_EXIST_AP);
    }

    $picture_title = $CURRENT_PIC_DATA['title'] ?: strtr(preg_replace("/(.+)\..*?\Z/", '\\1', htmlspecialchars($CURRENT_PIC_DATA['filename'], ENT_QUOTES | ENT_HTML5)), '_', ' ');

    $GLOBALS['xoopsOption']['template_main'] = 'xcgal_display.html';

    require XOOPS_ROOT_PATH . '/header.php';

    html_img_nav_menu();

    html_picture();

    html_rating_box();

    html_picinfo();

    user_save_profile();

    $xoopsTpl->assign('gallery', $myts->displayTarea($xoopsModule->getVar('name')));

    require_once 'include/theme_func.php';

    main_menu();

    do_footer();

    require XOOPS_ROOT_PATH . '/include/comment_view.php';

    require_once '../../footer.php';
}
