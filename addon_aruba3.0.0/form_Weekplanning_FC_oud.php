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
  inputclassbase::DBconnect($userlink);

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
  
  // Create an array of weekdays and months
  $weekdays = array(0 => "Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag");
  $months = array(1 => "januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
  // Create an array of days that need to be processed
  for($i=0; $i<6; $i++)
    $chkday[$i] = date("Y-m-d", mktime(0,0,0, substr($fmonday,5,2), substr($fmonday,8,2) + $i, substr($fmonday,0,4)));
	
  $startdate = substr($chkday[0],8,2). " ". $months[0 + substr($chkday[0],5,2)]. " ". substr($chkday[0],0,4);
  $enddate = substr($chkday[4],8,2). " ". $months[0 + substr($chkday[4],5,2)]. " ". substr($chkday[4],0,4);
  
  $uid = intval($uid);
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `bo_weekplan_data` (
    `planid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tid` INTEGER(11) DEFAULT NULL,
	`datum` DATE DEFAULT NULL,
	`starttime` TIME DEFAULT NULL,
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
  $slist = SA_loadquery("SELECT mid,fullname FROM subject");
  foreach($slist['mid'] AS $six => $smid)
    $subjectlist[$smid] = $slist['fullname'][$six];

  // Get the group and groupname related to the current teacher
  $grpdata = inputclassbase::load_query("SELECT gid,groupname FROM sgroup WHERE tid_mentor=". $_SESSION['CurrentTeacher']);
  if(isset($grpdata['gid']))
  {
    $mygid = $grpdata['gid'][0];
	$mygn = $grpdata['groupname'][0];
  }
  else
  {
    $mygid = 0;
	$mygn = "";
  }
  
  // First part of the page
  echo("<html><head><title>Weekplanning</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_weekplan_FC.css" title="style1">';
  echo("<H1 class=heading>Weekplanning Klas ". $mygn. "</H1>");
  
  // Show the back and next arrows
  echo("<form method=post action=form_Weekplanning_FC.php id=teacherselect name=teacherselect><p class=selectors>");
  echo("<a href=form_Weekplanning_FC.php?pweek=1>Voorgaande week <img border=0 src='PNG/arrow_back.png'></a>");
  echo(" <a href=form_Weekplanning_FC.php?nweek=1><img border=0 src='PNG/arrow_next.png'> Volgende week</a> ");

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
  echo("</select> <a href=form_Weekplanning_FC.php?print=0><img src='PNG/file.png' alt=Afdrukken title=Afdrukken border=0></a></form></p>");
  
  $negindex=0;
  $mayedit = $_SESSION['uid'] == $_SESSION['CurrentTeacher'];
//  if($chkday[0] < date('Y-m-d'))
//    $mayedit = false;
  if(isset($_GET['print']))
    $mayedit = false;
  // Create the heading row for the first table
  echo("<table border=1 cellpadding=0>");
  echo("<TR><TH COLSPAN=3 class=tableheader>". $startdate. " t/m ". $enddate. "</TH></TR>");
  // Create the row below it with the days and realised
  echo("<tr>");
  for($i=0; $i<3; $i++)
    echo("<th>". $weekdays[$i]. "</th>");
  echo("</tr><tr style='vertical-align:top'>");
  // Add the fields for each day
  for($i=0; $i<3; $i++)
  {
	echo("<td>");
	$firstentry = true;
	$rdata = get_roosteritems($mygid,$i+1);
	if($rdata != NULL)
	{
	  foreach($rdata AS $ix => $rd)
	  {
	    if(!$firstentry)
		  echo("<HR>");
		echo("<SPAN class=subjecttime>");
		if($rd['mid'] != 0)
		  echo($subjectlist[$rd['mid']]. " ");
		echo($rd['text']. "</SPAN>");
		// Now depending on wether a corresponding entry can be found, add a new field or edit and exiting entry
		unset($planid);
		if($rd['mid'] != 0)
		{
		  //$planqr = inputclassbase::load_query("SELECT planid FROM bo_weekplan_data WHERE tid=". $_SESSION['CurrentTeacher']. " AND datum='". $chkday[$i]. "' AND subject=". $rd['mid']. " AND starttime = '". $rd['starttime']. "' ORDER BY planid");
		  $planqr = inputclassbase::load_query("SELECT planid FROM bo_weekplan_data WHERE tid=". $_SESSION['CurrentTeacher']. " AND datum='". $chkday[$i]. "' AND subject=". $rd['mid']. " ORDER BY planid");
		  if(isset($planqr['planid'][$rd['sq']]))
		    $planid=$planqr['planid'][$rd['sq']];
	    }
		if(!isset($planid))
		  $planid=$negindex--;
		if($planid <= 0 && $mayedit)
		{ // New entry for editing
	      echo("<HR>Stof:<BR>");
	      $newfield = new inputclass_textarea("newstof". $planid,"38,*",$userlink,"stof","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
	      $newfield->set_extrafield("year", $curyear);
	      $newfield->set_extrafield("datum",$chkday[$i]);
	      $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
		  $newfield->set_extrafield("subject",$rd['mid']);
		  $newfield->set_extrafield("starttime",$rd['starttime']);
	      $newfield->echo_html();
	      echo("<BR>Huiswerk:<BR>");
	      $newfield = new inputclass_textarea("newhw". $planid,"38,*",$userlink,"huiswerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
	      $newfield->set_extrafield("year", $curyear);
	      $newfield->set_extrafield("datum",$chkday[$i]);
	      $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
		  $newfield->set_extrafield("subject",$rd['mid']);
		  $newfield->set_extrafield("starttime",$rd['starttime']);
	      $newfield->echo_html();
	      echo("<BR>Proefwerk:<BR>");
	      $newfield = new inputclass_textarea("newpw". $planid,"38,*",$userlink,"proefwerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
	      $newfield->set_extrafield("year", $curyear);
	      $newfield->set_extrafield("datum",$chkday[$i]);
	      $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
		  $newfield->set_extrafield("subject",$rd['mid']);
		  $newfield->set_extrafield("starttime",$rd['starttime']);
	      $newfield->echo_html();
		}
		else
		{ // exiting entry for editing or display
	      $afield = new inputclass_textarea("stof". $planid,"38,*",$userlink,"stof","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		  if($mayedit || $afield->__toString() != "")
		    echo("<HR>Stof:<BR>");
		  if($mayedit)
		    $afield->echo_html();
		  else
		    echo($afield->__toString());
	      $afield = new inputclass_textarea("HW". $planid,"38,*",$userlink,"huiswerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		  if($mayedit || $afield->__toString() != "")
		    echo("<BR>Huiswerk:<BR>");
		  if($mayedit)
		    $afield->echo_html();
		  else
		    echo("<span class=hw>". $afield->__toString(). "</span>");
	      $afield = new inputclass_textarea("PW". $planid,"38,*",$userlink,"proefwerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		  if($mayedit || $afield->__toString() != "")
		    echo("<BR>Proefwerk:<BR>");
		  if($mayedit)
		    $afield->echo_html();
		  else
		    echo("<span class=pw>". $afield->__toString(). "</span>");
		}
		$firstentry = false;
	  }
	}
	echo("</td>");
  } // End loop for the days

  echo("</tr></table>");

  // Create the second table
  echo("<table border=1 cellpadding=0>");
  // Create the row below it with the days and realised
  echo("<tr>");
  for($i=3; $i<5; $i++)
    echo("<th>". $weekdays[$i]. "</th>");
  echo("<th>Verwerkt</th>");
  echo("</tr><tr style='vertical-align:top'>");
  // Add the fields for each day
  for($i=3; $i<5; $i++)
  {
	echo("<td>");
	$firstentry = true;
	$rdata = get_roosteritems($mygid,$i+1);
	if($rdata != NULL)
	{
	  foreach($rdata AS $ix => $rd)
	  {
	    if(!$firstentry)
		  echo("<HR>");
		echo("<SPAN class=subjecttime>");
		if($rd['mid'] != 0)
		  echo($subjectlist[$rd['mid']]. " ");
		echo($rd['text']. "</SPAN>");
		// Now depending on wether a corresponding entry can be found, add a new field or edit and exiting entry
		unset($planid);
		if($rd['mid'] != 0)
		{
		  //$planqr = inputclassbase::load_query("SELECT planid FROM bo_weekplan_data WHERE tid=". $_SESSION['CurrentTeacher']. " AND datum='". $chkday[$i]. "' AND subject=". $rd['mid']. " AND starttime = '". $rd['starttime']. "' ORDER BY planid");
		  $planqr = inputclassbase::load_query("SELECT planid FROM bo_weekplan_data WHERE tid=". $_SESSION['CurrentTeacher']. " AND datum='". $chkday[$i]. "' AND subject=". $rd['mid']. "  ORDER BY planid");
		  if(isset($planqr['planid'][$rd['sq']]))
		    $planid=$planqr['planid'][$rd['sq']];
	    }
		if(!isset($planid))
		  $planid=$negindex--;
		if($planid <= 0 && $mayedit)
		{ // New entry for editing
	      echo("<HR>Stof:<BR>");
	      $newfield = new inputclass_textarea("newstof". $planid,"38,*",$userlink,"stof","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
	      $newfield->set_extrafield("year", $curyear);
	      $newfield->set_extrafield("datum",$chkday[$i]);
	      $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
		  $newfield->set_extrafield("subject",$rd['mid']);
		  $newfield->set_extrafield("starttime",$rd['starttime']);
	      $newfield->echo_html();
	      echo("<BR>Huiswerk:<BR>");
	      $newfield = new inputclass_textarea("newhw". $planid,"38,*",$userlink,"huiswerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
	      $newfield->set_extrafield("year", $curyear);
	      $newfield->set_extrafield("datum",$chkday[$i]);
	      $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
		  $newfield->set_extrafield("subject",$rd['mid']);
		  $newfield->set_extrafield("starttime",$rd['starttime']);
	      $newfield->echo_html();
	      echo("<BR>Proefwerk:<BR>");
	      $newfield = new inputclass_textarea("newpw". $planid,"38,*",$userlink,"proefwerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
	      $newfield->set_extrafield("year", $curyear);
	      $newfield->set_extrafield("datum",$chkday[$i]);
	      $newfield->set_extrafield("tid",$_SESSION['CurrentTeacher']);
		  $newfield->set_extrafield("subject",$rd['mid']);
		  $newfield->set_extrafield("starttime",$rd['starttime']);
	      $newfield->echo_html();
		}
		else
		{ // exiting entry for editing or display
	      $afield = new inputclass_textarea("stof". $planid,"38,*",$userlink,"stof","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		  if($mayedit || $afield->__toString() != "")
		    echo("<HR>Stof:<BR>");
		  if($mayedit)
		    $afield->echo_html();
		  else
		    echo($afield->__toString());
	      $afield = new inputclass_textarea("HW". $planid,"38,*",$userlink,"huiswerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		  if($mayedit || $afield->__toString() != "")
		    echo("<BR>Huiswerk:<BR>");
		  if($mayedit)
		    $afield->echo_html();
		  else
		    echo("<span class=hw>". $afield->__toString(). "</span>");
	      $afield = new inputclass_textarea("PW". $planid,"38,*",$userlink,"proefwerk","bo_weekplan_data",$planid,"planid",NULL,"datahandler.php");
		  if($mayedit || $afield->__toString() != "")
		    echo("<BR>Proefwerk:<BR>");
		  if($mayedit)
		    $afield->echo_html();
		  else
		    echo("<span class=pw>". $afield->__toString(). "</span>");
		}
		$firstentry = false;
	  }
	}
  } // End loop for each day
  // define editing enabled for realised part
  $mayedit = $_SESSION['uid'] == $_SESSION['CurrentTeacher'];
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
  if(date('Y-m-d') > $chkday[5] || !isset($_GET['print']))
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

  echo '<a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  echo '</a>';
  
  // Dummy form for delayed refresh
  echo("<form name=delayedrefresh id=delayedrefresh method=post action=form_Weekplanning_FC.php></form>");
  // And it's JavaScript
  echo("<SCRIPT> function delayrefresh() { setTimeout(\"document.getElementById('delayedrefresh').submit();\",500); } </SCRIPT>");

  // close the page
  echo("</html>");
  
  function get_roosteritems($gid,$day)
  {
    $rdata = inputclassbase::load_query("SELECT * FROM bo_rooster WHERE gid=". $gid. " AND weekday=". $day. " ORDER BY starttime");
	if(isset($rdata['rid']))
	{
	  $prevend = "00:00:00";
	  $curslot = 1;
	  foreach($rdata['rid'] AS $rix => $rid)
	  {
	    if($prevend != "00:00:00" && $rdata['starttime'][$rix] != $prevend)
		{
		  $rslot[$curslot]['rid'] = 0;
		  $rslot[$curslot]['mid'] = 0;
		  $rslot[$curslot]['text'] = "PAUZE";
		  $rslot[$curslot]['sq'] = 0;
		  $rslot[$curslot]['starttime'] = $prevend;
		  $curslot++;
		}
		$rslot[$curslot]['rid'] = $rid;
		$rslot[$curslot]['starttime'] = $rdata['starttime'][$rix];
		$rslot[$curslot]['mid'] = $rdata['mid'][$rix];
		$rslot[$curslot]['text'] = " ". substr($rdata['starttime'][$rix],0,5). "-". substr($rdata['endtime'][$rix],0,5);
		if(isset($ssq[$rslot[$curslot]['mid']]))
		  $ssq[$rslot[$curslot]['mid']]++;
		else
		  $ssq[$rslot[$curslot]['mid']] = 0;
		$rslot[$curslot]['sq'] = $ssq[$rslot[$curslot]['mid']];
		$prevend = $rdata['endtime'][$rix];
        $curslot++;		
	  }
	  return($rslot);
	}
	else
	  return NULL;
  }
  
  
?>
