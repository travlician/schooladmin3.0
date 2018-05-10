<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.  (http://www.aim4me.info)        |
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
// | Changenote: added support for lessonplan                   |
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
  $cid = trim($HTTP_POST_VARS['cid']);
  $type = trim($HTTP_POST_VARS['type']);
  $date = trim($HTTP_POST_VARS['date']);
  $short_desc = trim($HTTP_POST_VARS['short_desc']);
  $description = trim($HTTP_POST_VARS['description']);
  $realised = trim($HTTP_POST_VARS['realised']);
  $period = trim($HTTP_POST_VARS['period']);
  if(isset($lessonplan) && $lessonplan == 1)
  {
    $week = trim($HTTP_POST_VARS['week']);
    $domain = trim($HTTP_POST_VARS['domain']);
    $term = trim($HTTP_POST_VARS['term']);
    $duration = trim($HTTP_POST_VARS['duration']);
    $assignments = trim($HTTP_POST_VARS['assignments']);
    $tools = trim($HTTP_POST_VARS['tools']);
  }

  if ($cid == "" || $type=="" || $period=="")
  {
    echo($dtext['missing_params'] . " ($cid,$type,$period)");
    echo("<br><a href=mantests.php>" . $dtext['back_testdef'] . "</a>");
    SA_closeDB();
    exit;
  }
  if ($date=="")
  {
    if(isset($_POST['week']) && $_POST['week'] != "")
	{ // Convert week to a date
	  // First get current week
	  $today = date("Y-m-d");
	  $sixweeksago = date("Y-m-d",mktime(0,0,0,substr($today,5,2),substr($today,8,2) - 42,substr($today,0,4)));
	  $firstweekno = date("W",mktime(0,0,0,substr($sixweeksago,5,2),substr($sixweeksago,8,2),substr($sixweeksago,0,4)));
	  if($week >= $firstweekno)
	  { // date is in same year, so just add 7 * diff in weeks days
	    $date = date("Y-m-d",mktime(0,0,0,substr($sixweeksago,5,2),substr($sixweeksago,8,2) + (7 * ($week - $firstweekno)),substr($sixweeksago,0,4)));
	  }
	  else
	  { // Week is in next year, so use 4 jan as base
	    $date = date("Y-m-d",mktime(0,0,0,1,(7 * $week) - 3, substr($sixweeksago,0,4) + 1));
	  }
	}
	else
	{
      echo($dtext['missing_params']);
      echo("<br><a href=mantests.php>" . $dtext['back_testdef'] . "</a>");
      SA_closeDB();
      exit;
	}
  }
  if ($short_desc=="")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mantests.php>" . $dtext['back_testdef'] . "</a>");
    SA_closeDB();
    exit;
  }
  if ($description=="")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mantests.php>" . $dtext['back_testdef'] . "</a>");
    SA_closeDB();
    exit;
  }
  
  // get the year belonging to the period.
  $yearar = SA_loadquery("SELECT year FROM period WHERE id=". $period);
  $year = $yearar['year'][1];

  // See if yearsflag is set and get an array of cids if so...
  if(isset($_POST['yearsflag']) && $_POST['yearsflag'] == 1)
  {
    $cidinfo = SA_loadquery("SELECT class.*,groupname FROM class LEFT JOIN sgroup USING(gid) WHERE active=1 AND cid=". $cid);
	$cidlist = SA_loadquery("SELECT cid FROM class LEFT JOIN sgroup USING(gid) WHERE active=1 AND groupname LIKE '". substr($cidinfo['groupname'][1],0,1). "%' AND mid=". $cidinfo['mid'][1]);
  }
  else
    $cidlist = SA_loadquery("SELECT cid FROM class WHERE cid=". $cid);
	
	
  foreach($cidlist['cid'] AS $acid)
  {
    // Convert date to week
    $week = date("W",mktime(0,0,0,substr($date,5,2),substr($date,8,2),substr($date,0,4)));
    if($tdid == "")
	{
      if(isset($lessonplan) && $lessonplan == 1)
	  { 
	    $sql_query = "INSERT INTO testdef (short_desc,description,realised,date,type,period,cid,week,domain,term,duration,assignments,tools,year) VALUES(";
	    $sql_query .= "\"". $short_desc. "\",\"". $description. "\",\"". $realised. "\", '". $date. "', '". $type. "', ". $period. ", ". $acid. ", ". $week. ", '". $domain. "', \"". $term. "\", '". $duration. "', \"". $assignments. "\", \"". $tools. "\", '". $year. "')";
	  }
	  else
	  {
	    $sql_query = "INSERT INTO testdef (short_desc,description,date,type,period,cid,year) VALUES(";
	    $sql_query .= "\"". $short_desc. "\",\"". $description. "\", '". $date. "', '". $type. "', ". $period. ", ". $acid. ", '". $year. "')";
		//$sql_query = "";
	  }
	}	
    else
    { // Existing test definition change. 
	  $orgtestdef = SA_loadquery("SELECT * FROM testdef WHERE tdid=". $tdid);
      $sql_query = "UPDATE testdef SET short_desc=\"". $short_desc. "\",description=\"". $description. "\",date='". $date. "',type='". $type. "',period=". $period.",cid=". $acid. ",year='". $year. "'";
      if(isset($lessonplan) && $lessonplan == 1)
        $sql_query .= ",realised=\"". $realised. "\",week=". $week. ",domain='". $domain. "',term=\"". $term. "\",duration='". $duration. "',assignments=\"". $assignments. "\",tools=\"". $tools. "\"";
      $sql_query .= " WHERE tdid=$tdid";
    }
    $mysql_query = $sql_query;
    /*echo mysql_error($userlink). "{". $sql_query. " X: ". $assignments. "}";
    exit; */
    $sql_result = mysql_query($mysql_query,$userlink);
    if($tdid != "" && ($orgtestdef['cid'][1] != $acid || $orgtestdef['period'][1] != $period || $orgtestdef['type'][1] != $type))
	{ // Recalc results (with original sets and new sets) if a changed testdef has new type and/or cid and/or period
      SA_calcGradeGroup($orgtestdef['cid'][1], $orgtestdef['period'][1]);
      SA_calcGradeGroup($acid, $period);
	}
  }
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the mantests page!
    header("Location: " . $livesite ."mantests.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']. " => ". mysql_error($userlink). ". Query=". $sql_query);
    echo("<br><a href=mantests.php>" . $dtext['back_testdef'] . "</a>");
  }   

?>
