<?php
// $Id: addpic.php,v 1.1 2004/11/23 22:15:06 praedator Exp $
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
require __DIR__ . '/include/picmgmt.inc.php';

//if (!GALLERY_ADMIN_MODE)
if (!is_object($xoopsUser) || !($xoopsUser->isAdmin($xoopsModule->mid()))) {
    redirect_header(XOOPS_URL . '/', 3, _NOPERM);
}

$aid = (int)$_GET['aid'];
$pic_file = base64_decode($_GET['pic_file'], true);
$dir_name = dirname($pic_file) . '/';
$file_name = basename($pic_file);

$sql = 'SELECT pid ' . 'FROM ' . $xoopsDB->prefix('xcgal_pictures') . ' ' . "WHERE filepath='" . addslashes($dir_name) . "' AND filename='" . addslashes($file_name) . "' " . 'LIMIT 1';
$result = $xoopsDB->query($sql);

if ($xoopsDB->getRowsNum($result)) {
    $file_name = 'images/up_dup.gif';
} elseif (add_picture($aid, $dir_name, $file_name)) {
    $file_name = 'images/up_ok.gif';
} else {
    $file_name = 'images/up_pb.gif';

    echo $ERROR;
}

if (ob_get_length()) {
    ob_end_flush();

    exit;
}

header('Content-type: image/gif');
echo fread(fopen($file_name, 'rb'), filesize($file_name));
ob_end_flush();
