<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
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
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  require_once("inputlib/inputclasses.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  
  // Remove any plan entry if id for deletion given
  if(isset($_GET['dpid']))
    mysql_query("DELETE FROM bo_weekplan_data WHERE planid=". $_GET['dpid'],$userlink);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  if(!isset($_SESSION['CurrentTeacher']))
    $_SESSION['CurrentTeacher'] = $_SESSION['uid'];
  if(isset($HTTP_POST_VARS['NewTeacher']) )
    $_SESSION['CurrentTeacher'] = $HTTP_POST_VARS['NewTeacher'];
  if(isset($_GET['print']) && $_GET['print'] == 1)
    $_GET['nweek'] = 1;
	
  // Get dates
  if(!isset($_SESSION['CurrentDate']))
    $_SESSION['CurrentDate'] = date('Y-m-d');
  // See if correction is needed for next or previous week
  if(isset($_GET['pweek']))
    $_SESSION['CurrentDate'] = date('Y-m-d', mktime(0,0,0,substr($_SESSION['CurrentDate'],5,2),substr($_SESSION['CurrentDate'],8,2) - 7, substr($_SESSION['CurrentDate'],0,4)));
  if(isset($_GET['nweek']))
    $_SESSION['CurrentDate'] = date('Y-m-d', mktime(0,0,0,substr($_SESSION['CurrentDate'],5,2),substr($_SESSION['CurrentDate'],8,2) + 7, substr($_SESSION['CurrentDate'],0,4)));
  // Set to (first) monday
  $daycor = date('N') - 1;
  $fmonday = date('Y-m-d', mktime(0,0,0,substr($_SESSION['CurrentDate'],5,2),substr($_SESSION['CurrentDate'],8,2) - $daycor, substr($_SESSION['CurrentDate'],0,4)));
  
  // Create an array of weekdays
  $weekdays = array(0 => "Ma","Di","Wo","Do","Vr");
  // Create an array of days that need to be processed
  for($i=0; $i<15; $i++)
    $chkday[$i] = date("Y-m-d", mktime(0,0,0, substr($fmonday,5,2), substr($fmonday,8,2) + $i, substr($fmonday,0,4)));
  
  $uid = intval($uid);
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `bo_weekplan_data` (
    `planid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tid` INTEGER(11) DEFAULT NULL,
	`datum` DATE DEFAULT NULL,
	`subject` INTEGER(11) DEFAULT NULL,
    `stof` TEXT DEFAULT NULL,
    `huiswerk` TEXT DEFAULT NULL,
    `proefwerk` TEXT DEFAULT NULL,
	`year` CHAR(20),
	PRIMARY KEY (`planid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  // We need to get the year for entry!
  $curyears = SA_loadquery("SELECT year,id FROM period ORDER BY id DESC");
  $curyear = $curyears['year'][1];
   
  // Get all exisiting records in an array
  $wpdata = SA_loadquery("SELECT * FROM bo_weekplan_data WHERE year='". $curyear. "' AND tid=". $_SESSION['CurrentTeacher']);
  // Convert this to a more convenient array type
  if(isset($wpdata))
    foreach($wpdata['planid'] AS $xix => $xid)
	  $pdata[$wpdata['datum'][$xix]][$wpdata['subject'][$xix]] = $xid;

  // Get the teacher data
  $tdata = SA_loadquery("SELECT tid, firstname, lastname FROM teacher ORDER BY firstname, lastname");
  
  // Get a list of subjects
  $slist = SA_loadquery("SELECT mid,shortname FROM subject");
  foreach($slist['mid'] AS $six => $smid)
    $subjectlist[$smid] = $slist['shortname'][$six];
  
  // First part of the page
  echo("<html><head><title>Weekplanning</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_weekplan.css" title="style1">';
  echo("<font size=+2><center>Weekplanning</font><p>");
  
  // Show the back and next arrows
  echo("<form method=post action=form_Weekplanning.php id=teacherselect name=teacherselect>");
  echo("<a href=form_Weekplanning.php?pweek=1>Voorgaande week <img border=0 src='PNG/arrow_back.png'></a>");
  echo(" <a href=form_Weekplanning.php?nweek=1><img border=0 src='PNG/arrow_next.png'> Volgende week</a> ");

  // Show for which teacher current editing and allow changing the teacher
  echo(" Leraar: <select name=NewTeacher onchange='teacherselect.submit()'>");
  foreach($tdata['tid'] AS $tix => $tid)
  { // Add an option for each teacher, select the one currently active
    if($_SESSION['CurrentTeacher'] == $tid)
      $IsSelected = " selected";
    else
      $IsSelected = "";
    echo("<option value=" . $tdata['tid'][$tix]."$IsSelected>" . $tdata['firstname'][$tix]. " ".  $tdata['lastname'][$tix]. "</option>");
  }
  echo("</select></form>");
  
  $negindex=0;
  $mayedit = $_SESSION['uid'] == $_SESSION['CurrentTeacher'];
  if($chkday[0] < date('Y-m-d'))
    $mayedit = false;
  if(isset($_GET['print']))
    $mayedit = false;
  // Create the heading row for the first table
  echo("<table border=1 cellpadding=0>");
  // Create the row below it with the days and realised
  echo("<tr>");
  for($i=0; $i<5; $i++)
    echo("<th>". $weekdays[$i]. " ". inputclassbase::mysqldate2nl($chkday[$i]). "</th>");
  if(!isset($_GET['print']))
	echo("<th>Verwerkt <a href=form_Weekplanning.php?print=0><img src='PNG/file.png' border=0></a></th>");
  echo("</tr><tr style='vertical-align:top'>");
  // Add the fields for each day
  for($i=0; $i<5; $i++)
  {
	echo("<td>");
	$firstentry = true;
	if($mayedit)
	{
      // Start with a new item field
	  echo("Vak: ");
	  $newfield = new inputclass_listfield("newsubject". $negindex,"SELECT 0 AS id, '' AS tekst UNION SELECT mid, shortname FROM subject ORDER BY tekst",$userlink,"subject","bo_weekplan_data",$negindex,"planid",NULL,"datahandler.php");
	  $newfield->set_extrafield("year", $curyear);
	  $newfield->set_extrafield("datum",$chkday[$i]);
	  $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
	  $newfield->echo_html();
	  // Show add icon (just links to same page!)
	  echo(" <a href=# onClick='delayrefresh();'><img border=0 src='PNG/action_add.png'></a>");
	  echo("<BR>Stof:<BR>");
	  $newfield = new inputclass_textarea("newstof". $negindex,"20,*",$userlink,"stof","bo_weekplan_data",$negindex,"planid",NULL,"datahandler.php");
	  $newfield->set_extrafield("year", $curyear);
	  $newfield->set_extrafield("datum",$chkday[$i]);
	  $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
	  $newfield->echo_html();
	  echo("<BR>Huiswerk:<BR>");
	  $newfield = new inputclass_textarea("newhw". $negindex,"20,*",$userlink,"huiswerk","bo_weekplan_data",$negindex,"planid",NULL,"datahandler.php");
	  $newfield->set_extrafield("year", $curyear);
	  $newfield->set_extrafield("datum",$chkday[$i]);
	  $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
	  $newfield->echo_html();
	  echo("<BR>Proefwerk:<BR>");
	  $newfield = new inputclass_textarea("newpw". $negindex,"20,*",$userlink,"proefwerk","bo_weekplan_data",$negindex--,"planid",NULL,"datahandler.php");
	  $newfield->set_extrafield("year", $curyear);
	  $newfield->set_extrafield("datum",$chkday[$i]);
	  $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
	  $newfield->echo_html();
	  $firstentry = false;	  
	} // End if mayedit for new field
	// Add the existing fields
	if(isset($pdata[$chkday[$i]]))
	  foreach($pdata[$chkday[$i]] AS $planid)
	  {
	    if(!$firstentry)
	      echo("<HR>");
		echo("Vak: ");
	  	$afield = new inputclass_listfield("subject". $planid,"SELECT 0 AS id, '' AS tekst UNION SELECT mid, shortname FROM subject ORDER BY tekst",$userlink,"subject","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		echo($afield->__toString());
		if($mayedit)
		  echo(" <a href=form_Weekplanning.php?dpid=". $planid. "><img border=0 src='PNG/action_delete.png'></a>");
	    $afield = new inputclass_textarea("stof". $planid,"20,*",$userlink,"stof","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		if($mayedit || $afield->__toString() != "")
		  echo("<BR>Stof:<BR>");
		if($mayedit)
		  $afield->echo_html();
		else
		  echo($afield->__toString());
	    $afield = new inputclass_textarea("HW". $planid,"20,*",$userlink,"huiswerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		if($mayedit || $afield->__toString() != "")
		  echo("<BR>Huiswerk:<BR>");
		if($mayedit)
		  $afield->echo_html();
		else
		  echo("<span class=hw>". $afield->__toString(). "</span>");
	    $afield = new inputclass_textarea("PW". $planid,"20,*",$userlink,"proefwerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		if($mayedit || $afield->__toString() != "")
		  echo("<BR>Proefwerk:<BR>");
		if($mayedit)
		  $afield->echo_html();
		else
		  echo("<span class=pw>". $afield->__toString(). "</span>");
		$firstentry = false;
	  }
	echo("</td>");
  } // End loop for the days
  // define editing enabled for realised part
  $mayedit = $_SESSION['uid'] == $_SESSION['CurrentTeacher'];
  if($chkday[7] < date('Y-m-d'))
    $mayedit = false;
  if(isset($_GET['print']))
    $mayedit = false;
  // Make a list of subjects that should appear in the realised section
  unset($rlist);
  for($i=0; $i<5; $i++)
  {
    if(isset($pdata[$chkday[$i]]))
      foreach($pdata[$chkday[$i]] AS $psub => $plid)
	  {
	    //get the stof field as a string
	    $afield = new inputclass_textarea("stofv". $planid,"20,*",$userlink,"stof","bo_weekplan_data",$plid,"planid",NULL,"datahandler.php");
        // Add it to our list
	    if(isset($rlist[$psub]))
		  $rlist[$psub] .= ($mayedit ? "\r\n" : "<BR>"). $afield->__toString();
		else
		  $rlist[$psub] = $afield->__toString();
	  }
  }
  // Show the realised section
  if(!isset($_GET['print']))
  {
    echo("<td>");
    $firstentry = true;
    if(isset($rlist))
    {
      foreach($rlist AS $psub => $defstof)
	  {
	    if(!$firstentry)
	      echo("<HR>");
        echo("Vak: ". (isset($subjectlist[$psub]) ? $subjectlist[$psub] : "-"). "<BR>");
	    if(isset($pdata[$chkday[5]][$psub]))
	    { // Realised data has been defined for this subject
	      $afield = new inputclass_textarea("stofr". $pdata[$chkday[5]][$psub],"20,*",$userlink,"stof","bo_weekplan_data",$pdata[$chkday[5]][$psub],"planid",NULL,"datahandler.php");
		  if($mayedit)
		    $afield->echo_html();
		  else
		    echo($afield->__toString());
	    }
	    else
	    { // No data set in database
	      if($mayedit)
		  { // Create a field with the default contents
	        $afield = new inputclass_textarea("stofr". $negindex,"20,*",$userlink,"stof","bo_weekplan_data",$negindex--,"planid",NULL,"datahandler.php");
		    $afield->set_extrafield("datum",$chkday[5]);
		    $afield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
		    $afield->set_extrafield("subject",$psub);
		    $afield->set_extrafield("year",$curyear);
		    $afield->set_initial_value($defstof);
		    $afield->echo_html();
		  }
		  else
		  { // Just show the default contents
		    echo($defstof);
		  }
	    }
	    $firstentry = false;
	  }
    }
    else
      echo("&nbsp;");
	echo("</td>");
  }

  echo("</tr></table>");
  if(isset($_GET['print']))
  {
    echo("</html>");
    exit();
  }
  // Create the heading row for the second table
  echo("<HR><table border=1 cellpadding=0>");
  // Create the row below it with the days and realised
  echo("<tr>");
  for($i=0; $i<5; $i++)
    echo("<th>". $weekdays[$i]. " ". inputclassbase::mysqldate2nl($chkday[$i+7]). "</th>");
  echo("<th>Verwerkt <a href=form_Weekplanning.php?print=1><img src='PNG/file.png' border=0></a></th></tr><tr style='vertical-align:top'>");
  // Add the fields for each day
  for($i=0; $i<5; $i++)
  {
	echo("<td>");
	$firstentry = true;
	if($mayedit)
	{
      // Start with a new item field
	  echo("Vak: ");
	  $newfield = new inputclass_listfield("newsubject". $negindex,"SELECT 0 AS id, '' AS tekst UNION SELECT mid, shortname FROM subject ORDER BY tekst",$userlink,"subject","bo_weekplan_data",$negindex,"planid",NULL,"datahandler.php");
	  $newfield->set_extrafield("year", $curyear);
	  $newfield->set_extrafield("datum",$chkday[$i+7]);
	  $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
	  $newfield->echo_html();
	  // Show add icon (just links to same page!)
	  echo(" <a href=form_Weekplanning.php><img border=0 src='PNG/action_add.png'></a>");
	  echo("<BR>Stof:<BR>");
	  $newfield = new inputclass_textarea("newstof". $negindex,"20,*",$userlink,"stof","bo_weekplan_data",$negindex,"planid",NULL,"datahandler.php");
	  $newfield->set_extrafield("year", $curyear);
	  $newfield->set_extrafield("datum",$chkday[$i+7]);
	  $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
	  $newfield->echo_html();
	  echo("<BR>Huiswerk:<BR>");
	  $newfield = new inputclass_textarea("newhw". $negindex,"20,*",$userlink,"huiswerk","bo_weekplan_data",$negindex,"planid",NULL,"datahandler.php");
	  $newfield->set_extrafield("year", $curyear);
	  $newfield->set_extrafield("datum",$chkday[$i+7]);
	  $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
	  $newfield->echo_html();
	  echo("<BR>Proefwerk:<BR>");
	  $newfield = new inputclass_textarea("newpw". $negindex,"20,*",$userlink,"proefwerk","bo_weekplan_data",$negindex--,"planid",NULL,"datahandler.php");
	  $newfield->set_extrafield("year", $curyear);
	  $newfield->set_extrafield("datum",$chkday[$i+7]);
	  $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
	  $newfield->echo_html();
	  $firstentry = false;	  
	} // End if mayedit for new field
	// Add the existing fields
	if(isset($pdata[$chkday[$i+7]]))
	  foreach($pdata[$chkday[$i+7]] AS $planid)
	  {
	    if(!$firstentry)
	      echo("<HR>");
		echo("Vak: ");
	  	$afield = new inputclass_listfield("subject". $planid,"SELECT 0 AS id, '' AS tekst UNION SELECT mid, shortname FROM subject ORDER BY tekst",$userlink,"subject","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		echo($afield->__toString());
		if($mayedit)
		  echo(" <a href=form_Weekplanning.php?dpid=". $planid. "><img border=0 src='PNG/action_delete.png'></a>");
	    $afield = new inputclass_textarea("stof". $planid,"20,*",$userlink,"stof","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		if($mayedit || $afield->__toString() != "")
		  echo("<BR>Stof:<BR>");
		if($mayedit)
		  $afield->echo_html();
		else
		  echo($afield->__toString());
	    $afield = new inputclass_textarea("HW". $planid,"20,*",$userlink,"huiswerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		if($mayedit || $afield->__toString() != "")
		  echo("<BR>Huiswerk:<BR>");
		if($mayedit)
		  $afield->echo_html();
		else
		  echo($afield->__toString());
	    $afield = new inputclass_textarea("PW". $planid,"20,*",$userlink,"proefwerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		if($mayedit || $afield->__toString() != "")
		  echo("<BR>Proefwerk:<BR>");
		if($mayedit)
		  $afield->echo_html();
		else
		  echo("<span class=pw>". $afield->__toString(). "</span>");
		$firstentry = false;
	  }
	echo("</td>");
  } // End loop for the days
  // define editing enabled for realised part
  $mayedit = $_SESSION['uid'] == $_SESSION['CurrentTeacher'];
  if($chkday[14] < date('Y-m-d'))
    $mayedit = false;
  // Make a list of subjects that should appear in the realised section
  unset($rlist);
  for($i=0; $i<5; $i++)
  {
    if(isset($pdata[$chkday[$i+7]]))
      foreach($pdata[$chkday[$i+7]] AS $psub => $plid)
	  {
	    //get the stof field as a string
	    $afield = new inputclass_textarea("stofv". $planid,"20,*",$userlink,"stof","bo_weekplan_data",$plid,"planid",NULL,"datahandler.php");
        // Add it to our list
	    if(isset($rlist[$psub]))
		  $rlist[$psub] .= ($mayedit ? "\r\n" : "<BR>"). $afield->__toString();
		else
		  $rlist[$psub] = $afield->__toString();
	  }
  }
  // Show the realised section
  echo("<td>");
  $firstentry = true;
  if(isset($rlist))
  {
    foreach($rlist AS $psub => $defstof)
	{
	  if(!$firstentry)
	    echo("<HR>");
      echo("Vak: ". (isset($subjectlist[$psub]) ? $subjectlist[$psub] : "-"). "<BR>");
	  if(isset($pdata[$chkday[12]][$psub]))
	  { // Realised data has been defined for this subject
	    $afield = new inputclass_textarea("stofr". $pdata[$chkday[12]][$psub],"20,*",$userlink,"stof","bo_weekplan_data",$pdata[$chkday[12]][$psub],"planid",NULL,"datahandler.php");
		if($mayedit)
		  $afield->echo_html();
		else
		  echo($afield->__toString());
	  }
	  else
	  { // No data set in database
	    if($mayedit)
		{ // Create a field with the default contents
	      $afield = new inputclass_textarea("stofr". $negindex,"20,*",$userlink,"stof","bo_weekplan_data",$negindex--,"planid",NULL,"datahandler.php");
		  $afield->set_extrafield("datum",$chkday[12]);
		  $afield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
		  $afield->set_extrafield("subject",$psub);
		  $afield->set_extrafield("year",$curyear);
		  $afield->set_initial_value($defstof);
		  $afield->echo_html();
		}
		else
		{ // Just show the default contents
		  echo($defstof);
		}
	  }
	  $firstentry = false;
	}
  }
  else
    echo("&nbsp;");

  echo("</td></tr></table>");

  echo '<a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  echo '</a>';
  
  // Dummy form for delayed refresh
  echo("<form name=delayedrefresh id=delayedrefresh method=post action=form_Weekplanning.php></form>");
  // And it's JavaScript
  echo("<SCRIPT> function delayrefresh() { setTimeout(\"document.getElementById('delayedrefresh').submit();\",500); } </SCRIPT>");

  // close the page
  echo("</html>");
?>
