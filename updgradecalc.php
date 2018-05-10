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

  $login_qualify = 'A';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  $orgmid = trim($HTTP_POST_VARS['orgmid']);
  $orgtesttype = trim($HTTP_POST_VARS['orgtesttype']);
  $mid = trim($HTTP_POST_VARS['mid']);
  $testtype = trim($HTTP_POST_VARS['testtype']);
  $dropworst = trim($HTTP_POST_VARS['dropworst']);
  $validifatleast = trim($HTTP_POST_VARS['validifatleast']);
  $weight = trim($HTTP_POST_VARS['weight']);
  $digitsafterdot = trim($HTTP_POST_VARS['digitsafterdot']);
  $passthreshold = trim($HTTP_POST_VARS['passthreshold']);
  $on_average = trim($HTTP_POST_VARS['on_average']);


  if ($mid == "")
  {
    echo($dtext['missing_mid']);
    echo("<br><a href=mangradecalc.php>" . $dtext['back_gradecalcman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if ($testtype == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mangradecalc.php>" . $dtext['back_gradecalcman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if ($dropworst == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mangradecalc.php>" . $dtext['back_gradecalcman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if ($validifatleast == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mangradecalc.php>" . $dtext['back_gradecalcman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if ($weight == "")
  {
    echo($dtext['missing_weight']);
    echo("<br><a href=mangradecalc.php>" . $dtext['back_gradecalcman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if ($digitsafterdot == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mangradecalc.php>" . $dtext['back_gradecalcman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if ($passthreshold == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mangradecalc.php>" . $dtext['back_gradecalcman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if ($on_average == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mangradecalc.php>" . $dtext['back_gradecalcman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
 
  if($mid != "")
    if($testtype != "")
    {
      $del_query = "DELETE FROM reportcalc WHERE mid='$orgmid' AND testtype='$orgtesttype'";
      mysql_query($del_query,$userlink);
    }
  $sql_query = "INSERT INTO reportcalc VALUES('$mid','$testtype','$dropworst','$validifatleast','$weight','$digitsafterdot','$passthreshold','$on_average')";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the manage grade calculation page!
    header("Location: " . $livesite ."mangradecalc.php");
    exit;
  }
  else
  {
    echo($dtext['op_failed']);
    echo("<br><a href=mangradecalc.php>" . $dtext['back_gradecalcman'] . "</a>");
  }   

?>


