<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.  (http://www.aim4me.info)        |
// +----------------------------------------------------------------------+
// | This program is free software.  You can redistribute in and/or       |
// | modify it under the terms of the GNU General Public License Version  |
// | 2 as published by the Free Software Foundation.                      |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY, without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program;  If not, write to the Free Software         |
// | Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.            |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlcian@bigfoot.com                   |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  $rid = trim($HTTP_POST_VARS['rid']);
  $sid = trim($HTTP_POST_VARS['sid']);
  $tid = trim($HTTP_POST_VARS['tid']);
  $date = trim($HTTP_POST_VARS['date']);
  $protect = trim($HTTP_POST_VARS['protect']);
  $type= trim($HTTP_POST_VARS['type']);
  $summary = $HTTP_POST_VARS['summary'];
  if(isset($HTTP_POST_VARS['content']))
    $content = $HTTP_POST_VARS['content'];
  else
    $content = "";
  $userfile = $HTTP_POST_FILES;
  if(isset($userfile['userfile']['tmp_name']))
    $file = $userfile['userfile']['tmp_name'];

  if ($sid == "" || $tid=="" || $date=="" || $protect=="" || $type=="" || $summary=="")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=reportsongroup.php>" . $dtext['back_repgrp'] . "</a>");
    SA_closeDB();
    exit;
  }
  /*if ($content == "" && ($type == "T" || $type=="C"))
  {
    echo($dtext['missing_content']);
    echo("<br><a href=reportsongroup.php>" . $dtext['back_repgrp'] . "</a>");
    SA_closeDB();
    exit;
  } */
  if($rid == "") 
    $sql_query = "INSERT INTO reports VALUES(NULL, '$sid', '$tid', '$protect', '$type', '$date', NULL, '$summary', '$content')";
  else
    $sql_query = "UPDATE reports SET sid='$sid',tid='$tid',protect='$protect',type='$type',date='$date',summary='$summary',content='$content' WHERE rid=$rid;";
  $mysql_query = $sql_query;
  // echo $sql_query; //~~~

  $sql_result = mysql_query($mysql_query,$userlink);
  
  // if it's a new record, we now can get the rid!
  if($rid == "")
    $rid = mysql_insert_id();

  // OK, but now we migth need to upload a file and change the content record!
  if($type == "F" || $type == "X")
  {
    $newfilename = "Report" .$rid;
    // Copy the extension from the tmp_file!
    $extension = (strstr($userfile['userfile']['name'],'.')) ? @strstr($userfile['userfile']['name'],'.') : '.file';
    $newfilename .= $extension;
    // Put the new filename in the database
    $sql_query = "UPDATE reports SET content='" . $newfilename . "' WHERE rid=" . $rid;
    mysql_query($sql_query,$userlink);
    // Prepend the directory name as specified in the configuration.
    $newfilename = $reportspath . $newfilename;
    // Open the temporary file & newfile, copy this way
    if (!($fp = fopen($file,"rb")))
    {
      echo($dtext['err_openfile'] . " \"". $file . "\" " . $dtext['4read']);
      echo("<br><a href=reportsongroup.php>" . $dtext['back_repgrp'] . "</a>");
      SA_closeDB();
      exit;
    }
    if (!($fpn = fopen($newfilename,"wb")))
    {
      echo($dtext['err_openwrite']);
      echo("<br><a href=reportsongroup.php>" . $dtext['back_repgrp'] . "</a>");
      SA_closeDB();
      exit;
    }
    // files are opened, now we start the copy
    while ($lineoftext=fgets($fp,65536))
    {   
      fputs($fpn,$lineoftext);
    }
    fclose($fp);
    fclose($fpn);
  }  

//~~~  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the manteacher page!
    header("Location: " . $livesite ."reportsongroup.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo(@mysql_error($userlink));
    echo("<br><a href=reportsongroup.php>" . $dtext['back_repgrp'] . "</a>");
  }   

?>


