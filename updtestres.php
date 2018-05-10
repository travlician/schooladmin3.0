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
  require_once("schooladmingradecalc.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  $tdid = trim($HTTP_POST_VARS['tdid']);

  // From the post vars snoxxx and sresxxx we setup a new array
  $sc=1;
  while(isset($HTTP_POST_VARS['sno'.$sc]))
  {
    $sid_array[$sc] = trim($HTTP_POST_VARS['sno'.$sc]);
    $res_array[$sc] = trim($HTTP_POST_VARS['sres'.$sc]);
    $sc++;
  }
  $res_n = $sc-1;

  if ($tdid == "")
  {
    echo($dtext['missing_tdid']);
    echo("<br><a href=mantests.php>" . $dtext['back_testdef'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if ($res_n < 1)
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mantests.php>" . $dtext['back_testdef'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  // Now for every entry in the created sid and res array, make a query and execute it
  $query_fault=0;
  for($sc=1;$sc<=$res_n;$sc++)
  {
    // Replace comma by dot (internationalisation required)
	$res_array[$sc] = str_replace(',','.',$res_array[$sc]);
    if($res_array[$sc] == "") 
      $sql_query = "DELETE FROM testresult WHERE sid='$sid_array[$sc]' AND tdid='$tdid'";
    else
      $sql_query = "REPLACE INTO testresult VALUES('$tdid', '$sid_array[$sc]', '$res_array[$sc]', NULL)";
    $mysql_query = $sql_query;
    //echo $sql_query;

    $sql_result = mysql_query($mysql_query,$userlink);
    if(!$sql_result)
    {
      $query_fault++;
      echo("<br>" . $dtext['op_fail'] . ": " . mysql_error($userlink));
    }
  }

  // recalculate grades on card
  $sql_query = "SELECT cid,period FROM testdef WHERE tdid='$tdid'";
  $sql_result = mysql_query($sql_query,$userlink);
  $cid = mysql_result($sql_result,0,'cid');
  $period = mysql_result($sql_result,0,'period');
  SA_calcGradeGroup($cid, $period);

  SA_closeDB();
  
  if($query_fault < 1)
  {	// operation succeeded, back to the manage testtypes page!
    header("Location: " . $livesite ."mantests.php");
    exit;
  }
  else
  {
    echo($query_faults . " " . $dtext['err_tres_summ']);
    echo("<br><a href=mantests.php>" . $dtext['back_testdef'] . "</a>");
  }   

?>