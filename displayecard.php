<?php
// $Id: displayecard.php,v 1.1 2004/11/23 22:15:09 praedator Exp $
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

if (!isset($_GET['data'])) {
    redirect_header('index.php', 2, _MD_PARAM_MISSING);
}
$data = $_GET['data'];
$delete_time = time() - ($xoopsModuleConfig['ecards_saved_db'] * 86400);
$xoopsDB->queryF('DELETE from ' . $xoopsDB->prefix('xcgal_ecard') . ' WHERE s_time < ' . $delete_time . '');

$result = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix('xcgal_ecard') . ' as e, ' . $xoopsDB->prefix('xcgal_pictures') . " as p WHERE e_id ='" . $data . "' AND e.pid=p.pid");

if (!$xoopsDB->getRowsNum($result)) {
    redirect_header('index.php', 2, "Sorry, can't find e-card!");
} else {
    $row = $xoopsDB->fetchArray($result);
}
if ($xoopsModuleConfig['make_intermediate'] && max($row['pwidth'], $row['pheight']) > $xoopsModuleConfig['picture_width']) {
    $n_picname = get_pic_url($row, 'normal');
} else {
    $n_picname = get_pic_url($row, 'fullsize');
}

$msg_content = $myts->displayTarea($row['message'], 0);
if (!mb_stristr($n_picname, 'http:')) {
    $n_picname = XOOPS_URL . '/modules/xcgal/' . $n_picname;
}
require_once XOOPS_ROOT_PATH . '/class/template.php';
$xoopsTpl = new XoopsTpl();
$xoopsTpl->assign('sitename', $xoopsConfig['sitename']);
$xoopsTpl->assign('ecard_title', sprintf(_MD_CARD_ECARD_TITLE, htmlspecialchars($row['sender_name'], ENT_QUOTES | ENT_HTML5)));
$xoopsTpl->assign('charset', _CHARSET);
$xoopsTpl->assign('view_ecard_tgt', 0);
$xoopsTpl->assign('view_ecard_lnk', _MD_CARD_VIEW_ECARD);
$xoopsTpl->assign('pic_url', $n_picname);
//$xoopsTpl->assign('url_prefix',$gallery_url_prefix);
$xoopsTpl->assign('greetings', $row['greetings']);
$xoopsTpl->assign('message', $msg_content);
$xoopsTpl->assign('sender_email', htmlspecialchars($row['sender_email'], ENT_QUOTES | ENT_HTML5));
$xoopsTpl->assign('sender_name', htmlspecialchars($row['sender_name'], ENT_QUOTES | ENT_HTML5));
$xoopsTpl->assign('view_more_tgt', $xoopsModuleConfig['ecards_more_pic_target']);
$xoopsTpl->assign('view_more_lnk', _MD_CARD_VIEW_MORE_PICS);
$xoopsTpl->display('db:xcgal_discard.html');
$xoopsDB->queryF('UPDATE ' . $xoopsDB->prefix('xcgal_ecard') . " SET picked=1 WHERE e_id='" . $data . "'");

exit();
