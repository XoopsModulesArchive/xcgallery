<?php
// $Id: comment_post.php,v 1.1 2004/11/23 22:15:08 praedator Exp $
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

require dirname(__DIR__, 2) . '/mainfile.php';
define('IN_XCGALLERY', true);
require __DIR__ . '/include/init.inc.php';
$com_itemid = isset($_GET['com_itemid']) ? (int)$_GET['com_itemid'] : (isset($_POST['com_itemid']) ? (int)$_POST['com_itemid'] : 0);

if ($com_itemid > 0) {
    $sql = 'SELECT a.comments FROM ' . $xoopsDB->prefix('xcgal_albums') . ' as a, ' . $xoopsDB->prefix('xcgal_pictures') . ' as p WHERE a.aid=p.aid AND p.pid=' . $com_itemid . '';

    $result = $xoopsDB->query($sql);

    $CURRENT_ALBUM_DATA = $xoopsDB->fetchArray($result);
}
if (USER_CAN_POST_COMMENTS && 'YES' == $CURRENT_ALBUM_DATA['comments']) {
    require XOOPS_ROOT_PATH . '/include/comment_post.php';
} else {
    redirect_header('index.php', 2, "You aren't allowed to post comments for this pic.");
}
