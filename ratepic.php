<?php
// $Id: ratepic.php,v 1.1 2004/11/23 22:15:09 praedator Exp $
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

// Check if required parameters are present
if (!isset($_GET['pic']) || !isset($_GET['rate'])) {
    redirect_header('index.php', 2, _MD_PARAM_MISSING);
}

$pic = (int)$_GET['pic'];
$rate = (int)$_GET['rate'];

$rate = min($rate, 5);
$rate = max($rate, 0);

// If user does not accept script's cookies, we don't accept the vote
if (!isset($HTTP_COOKIE_VARS[$xoopsModuleConfig['cookie_name'] . '_data'])) {
    header('Location: displayimage.php?pid=' . $pic . '&amp;pos=' . (-$pic));

    exit;
}

$location = 'displayimage.php?pid=' . $pic . '&amp;pos=' . (-$pic);
// Retrieve picture/album information & check if user can rate picture
$sql = 'SELECT a.votes as votes_allowed, p.votes as votes, pic_rating ' . 'FROM ' . $xoopsDB->prefix('xcgal_pictures') . ' AS p, ' . $xoopsDB->prefix('xcgal_albums') . ' AS a ' . "WHERE p.aid = a.aid AND pid = '$pic' LIMIT 1";
$result = $xoopsDB->query($sql);
if (!$xoopsDB->getRowsNum($result)) {
    redirect_header('index.php', 2, _MD_NON_EXIST_AP);
}
$row = $xoopsDB->fetchArray($result);
$xoopsDB->freeRecordSet($result);
if (!USER_CAN_RATE_PICTURES || 'NO' == $row['votes_allowed']) {
    redirect_header($location, 2, _MD_PERM_DENIED);
}

// Clean votes older votes
$curr_time = time();
if ($xoopsModuleConfig['keep_votes_time'] > 0) {
    $clean_before = $curr_time - $xoopsModuleConfig['keep_votes_time'] * 86400;

    $sql = 'DELETE ' . 'FROM ' . $xoopsDB->prefix('xcgal_votes') . ' ' . "WHERE vote_time < $clean_before >";

    $result = $xoopsDB->queryF($sql);
}

// Check if user already rated this picture
if (is_object($xoopsUser)) {
    $vid = $xoopsUser->uid();

    $sql = 'SELECT * ' . 'FROM ' . $xoopsDB->prefix('xcgal_votes') . ' ' . "WHERE pic_id = '$pic' AND v_uid = '$vid'";
} else {
    $vid = 0;

    $sql = 'SELECT * ' . 'FROM ' . $xoopsDB->prefix('xcgal_votes') . ' ' . "WHERE pic_id = '$pic' AND vote_time > '" . (time() - 86400) . "' AND ip='" . $HTTP_SERVER_VARS['REMOTE_ADDR'] . "'";
}

$result = $xoopsDB->query($sql);
if ($xoopsDB->getRowsNum($result)) {
    redirect_header($location, 2, _MD_RATE_ALREADY);
}

// Update picture rating
$new_rating = round(($row['votes'] * $row['pic_rating'] + $rate * 2000) / ($row['votes'] + 1));
$sql = 'UPDATE ' . $xoopsDB->prefix('xcgal_pictures') . ' ' . "SET pic_rating = '$new_rating', votes = votes + 1 " . "WHERE pid = '$pic' LIMIT 1";
$result = $xoopsDB->queryF($sql);

// Update the votes table
$sql = 'INSERT INTO ' . $xoopsDB->prefix('xcgal_votes') . ' ' . "VALUES ('$pic', '" . $HTTP_SERVER_VARS['REMOTE_ADDR'] . "', '$curr_time', '$vid')";
$result = $xoopsDB->queryF($sql);
redirect_header($location, 2, _MD_RATE_OK);
