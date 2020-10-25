<?php
// $Id: db_input.php,v 1.1 2004/11/23 22:15:09 praedator Exp $
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

if (!isset($_GET['event']) && !isset($_POST['event'])) {
    redirect_header('index.php', 2, _MD_PARAM_MISSING);
}
$myts = MyTextSanitizer::getInstance(); // MyTextSanitizer object
$event = $_POST['event'] ?? $_GET['event'];
switch ($event) {
    //
    // Update album
    //
    case 'album_update':
        if (!(USER_ADMIN_MODE || GALLERY_ADMIN_MODE)) {
            redirect_header('index.php', 2, _MD_PERM_DENIED);
        }

        $aid = (int)$_POST['aid'];
        $title = $myts->addSlashes(trim($_POST['title']));
        $category = (int)$_POST['category'];
        $description = $myts->addSlashes(trim($_POST['description']), 0);
        $thumb = (int)$_POST['thumb'];
        $visibility = (int)$_POST['visibility'];
        $uploads = 'YES' == $_POST['uploads'] ? 'YES' : 'NO';
        $comments = 'YES' == $_POST['comments'] ? 'YES' : 'NO';
        $votes = 'YES' == $_POST['votes'] ? 'YES' : 'NO';

        if (!$title) {
            redirect_header('index.php', 2, _MD_DB_ALB_NEED_TITLE);
        }

        if (GALLERY_ADMIN_MODE) {
            $query = 'UPDATE ' . $xoopsDB->prefix('xcgal_albums') . " SET title='$title', description='$description', category='$category', thumb='$thumb', uploads='$uploads', comments='$comments', votes='$votes', visibility='$visibility' WHERE aid='$aid' LIMIT 1";
        } else {
            $category = FIRST_USER_CAT + USER_ID;

            if ($visibility != $category && (is_array($USER_DATA['group_id']) && in_array($visibility, $USER_DATA['group_id'], true))) {
                $visibility = 0;
            }

            $query = 'UPDATE ' . $xoopsDB->prefix('xcgal_albums') . " SET title='$title', description='$description', thumb='$thumb',  comments='$comments', votes='$votes', visibility='$visibility' WHERE aid='$aid' AND category='$category' LIMIT 1";
        }
        $update = $xoopsDB->query($query);
        if (!$xoopsDB->getAffectedRows()) {
            redirect_header('modifyalb.php?album=$aid', 2, _MD_DB_NO_NEED);
        }
        redirect_header("modifyalb.php?album=$aid", 2, _MD_DB_ALB_UPDATED);
        exit;
        break;
    //
    // Picture upload
    //
    case 'picture':
        if (!USER_CAN_UPLOAD_PICTURES) {
            redirect_header('index.php', 2, _MD_PERM_DENIED);
        }

        $album = (int)$_POST['album'];
        $title = $myts->addSlashes($_POST['title']);
        $caption = $myts->addSlashes($_POST['caption'], 0);
        $keywords = $myts->addSlashes($_POST['keywords']);
        $user1 = $myts->addSlashes($_POST['user1']);
        $user2 = $myts->addSlashes($_POST['user2']);
        $user3 = $myts->addSlashes($_POST['user3']);
        $user4 = $myts->addSlashes($_POST['user4']);

        // Check if the album id provided is valid
        if (!GALLERY_ADMIN_MODE) {
            $result = $xoopsDB->query('SELECT category FROM ' . $xoopsDB->prefix('xcgal_albums') . " WHERE aid='$album' and (uploads = 'YES' OR category = '" . (USER_ID + FIRST_USER_CAT) . "')");

            if (0 == $xoopsDB->getRowsNum($result)) {
                redirect_header('index.php', 2, _MD_DB_UNKOWN);
            }

            $row = $xoopsDB->fetchArray($result);

            $xoopsDB->freeRecordSet($result);

            $category = $row['category'];
        } else {
            $result = $xoopsDB->query('SELECT category FROM ' . $xoopsDB->prefix('xcgal_albums') . " WHERE aid='$album'");

            if (0 == $xoopsDB->getRowsNum($result)) {
                redirect_header('index.php', 2, _MD_DB_UNKOWN);
            }

            $row = $xoopsDB->fetchArray($result);

            $xoopsDB->freeRecordSet($result);

            $category = $row['category'];
        }

        // Test if the filename of the temporary uploaded picture is empty
        if ('' == $HTTP_POST_FILES['userpicture']['tmp_name']) {
            redirect_header('index.php', 2, _MD_DB_NO_PICUP);
        }

        // Pictures are moved in a directory named 10000 + USER_ID
        if (USER_ID && !defined('SILLY_SAFE_MODE')) {
            $filepath = $xoopsModuleConfig['userpics'] . (USER_ID + FIRST_USER_CAT);

            $dest_dir = $xoopsModuleConfig['fullpath'] . $filepath;

            if (!is_dir($dest_dir)) {
                mkdir($dest_dir, octdec($xoopsModuleConfig['default_dir_mode']));

                chmod($dest_dir, octdec($xoopsModuleConfig['default_dir_mode']));

                if (!is_dir($dest_dir)) {
                    redirect_header('index.php', 2, sprintf(_MD_DB_ERR_MKDIR, $dest_dir));
                }

                $fp = fopen($dest_dir . '/index.html', 'wb');

                fwrite($fp, ' ');

                fclose($fp);
            }

            $dest_dir .= '/';

            $filepath .= '/';
        } else {
            $filepath = $xoopsModuleConfig['userpics'];

            $dest_dir = $xoopsModuleConfig['fullpath'] . $filepath;
        }

        // Check that target dir is writable
        if (!is_writable($dest_dir)) {
            redirect_header('index.php', 2, sprintf(_MD_DB_DEST_DIR_RO, $dest_dir));
        }

        // Replace forbidden chars with underscores
        $matches = [];
        $forbidden_chars = strtr($xoopsModuleConfig['forbidden_fname_char'], ['&amp;' => '&', '&quot;' => '"', '&lt;' => '<', '&gt;' => '>']);

        // Check that the file uploaded has a valid extension
        $HTTP_POST_FILES['userpicture']['name'] = $myts->addSlashes($HTTP_POST_FILES['userpicture']['name']);
        $picture_name = strtr($HTTP_POST_FILES['userpicture']['name'], $forbidden_chars, str_repeat('_', mb_strlen($xoopsModuleConfig['forbidden_fname_char'])));
        if (!preg_match("/(.+)\.(.*?)\Z/", $picture_name, $matches)) {
            $matches[1] = 'invalid_fname';

            $matches[2] = 'xxx';
        }
        if ('' == $matches[2] || !mb_stristr($xoopsModuleConfig['allowed_file_extensions'], $matches[2])) {
            redirect_header('index.php', 2, sprintf(_MD_DB_ERR_FEXT, $xoopsModuleConfig['allowed_file_extensions']));
        }

        // Create a unique name for the uploaded file
        $nr = 0;
        $picture_name = $matches[1] . '.' . $matches[2];
        while (file_exists($dest_dir . $picture_name)) {
            $picture_name = $matches[1] . '~' . $nr++ . '.' . $matches[2];
        }
        $uploaded_pic = $dest_dir . $picture_name;

        // Move the picture into its final location
        if (!move_uploaded_file($HTTP_POST_FILES['userpicture']['tmp_name'], $uploaded_pic)) {
            redirect_header('index.php', 2, sprintf(_MD_DB_ERR_MOVE, $picture_name, $dest_dir));
        }

        // Change file permission
        chmod($uploaded_pic, octdec($xoopsModuleConfig['default_file_mode']));

        // Get picture information
        $imginfo = getimagesize($uploaded_pic);

        // Check that picture size (in pixels) is lower than the maximum allowed
        if (max($imginfo[0], $imginfo[1]) > $xoopsModuleConfig['max_upl_width_height']) {
            @unlink($uploaded_pic);

            redirect_header('index.php', 2, sprintf(_MD_DB_ERR_PIC_SIZE, $xoopsModuleConfig['max_upl_width_height'], $xoopsModuleConfig['max_upl_width_height']));

        // Check that picture file size is lower than the maximum allowed
        } elseif (filesize($uploaded_pic) > ($xoopsModuleConfig['max_upl_size'] << 10)) {
            @unlink($uploaded_pic);

            redirect_header('index.php', 2, sprintf(_MD_DB_ERR_FSIZE, $xoopsModuleConfig['max_upl_size']));

        // getimagesize does not recognize the file as a picture
        } elseif (null === $imginfo) {
            @unlink($uploaded_pic);

            redirect_header('index.php', 2, _MD_DB_ERR_IMG_INVALID);

        // JPEG and PNG only are allowed with GD
        } elseif (GIS_JPG != $imginfo[2] && GIS_PNG != $imginfo[2] && ('gd1' == $xoopsModuleConfig['thumb_method'] || 'gd2' == $xoopsModuleConfig['thumb_method'])) {
            @unlink($uploaded_pic);

            redirect_header('index.php', 2, _MD_GD_FILE_TYPE_ERR);

        // Check image type is among those allowed for ImageMagick
        } elseif (!mb_stristr($xoopsModuleConfig['allowed_img_types'], $IMG_TYPES[$imginfo[2]]) && 'im' == $xoopsModuleConfig['thumb_method']) {
            @unlink($uploaded_pic);

            redirect_header('index.php', 2, sprintf(_MD_DB_IMG_ALLOWED, $xoopsModuleConfig['allowed_img_types']));
        } else {
            // Create thumbnail and internediate image and add the image into the DB

            $result = add_picture($album, $filepath, $picture_name, $title, $caption, $keywords, $user1, $user2, $user3, $user4, $category);

            if (!$result) {
                @unlink($uploaded_pic);

                redirect_header('index.php', 2, sprintf(_MD_DB_ERR_INSERT, $uploaded_pic) . '<br><br>' . $ERROR);
            } elseif ($PIC_NEED_APPROVAL) {
                redirect_header('index.php', 2, _MD_DB_UPLOAD_SUCC);
            } else {
                $header_location = (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE'))) ? 'Refresh: 0; URL=' : 'Location: ';

                $redirect = 'displayimage.php?pid=' . $picinID . '&amp;pos=' . -$picinID;

                redirect_header($redirect, 2, _MD_DB_UPL_SUCC);

                exit;
            }
        }
        break;
    //
    // Unknow event
    //
    default:
        redirect_header('index.php', 2, _MD_PARAM_MISSING);
}
