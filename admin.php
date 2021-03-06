<?php
// $Id: admin.php,v 1.1 2004/11/23 22:15:07 praedator Exp $
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

if (!USER_CAN_CREATE_ALBUMS && !USER_IS_ADMIN) {
    redirect_header('index.php', 2, _MD_ACCESS_DENIED);
}

if (!isset($_GET['admin_mode']) || !isset($_GET['referer'])) {
    redirect_header('index.php', 2, _MD_PARAM_MISSING);
}

$admin_mode = (int)$_GET['admin_mode'] ? 1 : 0;
$referer = $_GET['referer'] ?: 'index.php';
$USER['am'] = $admin_mode;
user_save_profile();
if (!$admin_mode) {
    $referer = $_GET['referer'] ?: 'index.php';
}
if (1 == $admin_mode) {
    redirect_header($referer, 2, _MD_ADMIN_ENTER);
} else {
    redirect_header($referer, 2, _MD_ADMIN_LEAVE);
}
