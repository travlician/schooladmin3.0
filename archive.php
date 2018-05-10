<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)       |
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
  include ("schooladminfunctions.php");
  require_once("inputlib/inputclasses.php");
	session_start();

	// Connect db
	inputclassbase::dbconnect($userlink);
	
	// Check if data posted
	if(isset($_POST['fieldid']))
	{
		echo("OK");
		exit;
	}
	
	// Check if action to be done
	if(isset($_POST['Archive']))
	{
		$datestart = inputclassbase::nldate2mysql($_POST['asd']);
		$dateend = inputclassbase::nldate2mysql($_POST['aed']);
		switch($_SESSION['ArchiveSelection'])
		{
			case 1:
				mysql_query("REPLACE INTO archived_reports SELECT * FROM reports WHERE (type='C' OR type='X') AND date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				if(!mysql_error($userlink))
					mysql_query("DELETE FROM reports WHERE (type='C' OR type='X') AND date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				break;
			case 2:
				mysql_query("REPLACE INTO archived_reports SELECT * FROM reports WHERE (type='F' OR type='T') AND date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				if(!mysql_error($userlink))
					mysql_query("DELETE FROM reports WHERE (type='T' OR type='F') AND date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				break;
			case 3:
				mysql_query("REPLACE INTO archived_absence SELECT * FROM absence WHERE date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				if(!mysql_error($userlink))
				  mysql_query("DELETE FROM absence WHERE date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				break;
			case 4:
				mysql_query("REPLACE INTO archived_eventlog SELECT * FROM eventlog WHERE LastUpdate >= '". $datestart. "' AND LastUpdate <= '". $dateend. "'", $userlink);
				echo(mysql_error($userlink));
				if(!mysql_error($userlink))
				  mysql_query("DELETE FROM eventlog WHERE LastUpdate >= '". $datestart. "' AND LastUpdate <= '". $dateend. "'", $userlink);
				break;
			default:
			  echo("No valid dataset selected");			
		}	
		// Reset selected dates
		unset($_SESSION['ArchiveArchiveStart']);
		unset($_SESSION['ArchiveArchiveEnd']);
		unset($_SESSION['ArchiveRecoverStart']);
		unset($_SESSION['ArchiveRecoverEnd']);
	}
	
	if(isset($_POST['Recover']))
	{
		$datestart = inputclassbase::nldate2mysql($_POST['rsd']);
		$dateend = inputclassbase::nldate2mysql($_POST['red']);
		switch($_SESSION['ArchiveSelection'])
		{
			case 1:
				mysql_query("REPLACE INTO reports SELECT * FROM archived_reports WHERE (type='C' OR type='X') AND date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				echo(mysql_error($userlink));
				mysql_query("UPDATE reports LEFT JOIN archived_reports USING(rid) SET reports.date = archived_reports.date WHERE (reports.type='C' OR reports.type='X') AND archived_reports.date >= '". $datestart. "' AND archived_reports.date <= '". $dateend. "'", $userlink);
				echo(mysql_error($userlink));
				if(!mysql_error($userlink))
					mysql_query("DELETE FROM archived_reports WHERE (type='C' OR type='X') AND date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				break;
			case 2:
				mysql_query("REPLACE INTO reports SELECT * FROM archived_reports WHERE (type='F' OR type='T') AND date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				echo(mysql_error($userlink));
				mysql_query("UPDATE reports LEFT JOIN archived_reports USING(rid) SET reports.date = archived_reports.date WHERE (reports.type='F' OR reports.type='T') AND archived_reports.date >= '". $datestart. "' AND archived_reports.date <= '". $dateend. "'", $userlink);
				echo(mysql_error($userlink));
				if(!mysql_error($userlink))
					mysql_query("DELETE FROM archived_reports WHERE (type='T' OR type='F') AND date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				break;
			case 3:
				mysql_query("REPLACE INTO absence SELECT * FROM archived_absence WHERE date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				if(!mysql_error($userlink))
				  mysql_query("DELETE FROM archived_absence WHERE date >= '". $datestart. "' AND date <= '". $dateend. "'", $userlink);
				break;
			case 4:
				mysql_query("REPLACE INTO eventlog SELECT * FROM archived_eventlog WHERE LastUpdate >= '". $datestart. "' AND LastUpdate <= '". $dateend. "'", $userlink);
				echo(mysql_error($userlink));
				if(!mysql_error($userlink))
				  mysql_query("DELETE FROM archived_eventlog WHERE LastUpdate >= '". $datestart. " 00:00:00' AND LastUpdate <= '". $dateend. " 23:59:59'", $userlink);
				echo(mysql_error($userlink));				
				break;
			default:
			  echo("No valid dataset selected");			
		}
		// Reset selected dates
		unset($_SESSION['ArchiveArchiveStart']);
		unset($_SESSION['ArchiveArchiveEnd']);
		unset($_SESSION['ArchiveRecoverStart']);
		unset($_SESSION['ArchiveRecoverEnd']);
	}

	
	// Create tables as needed
	mysql_query("CREATE TABLE IF NOT EXISTS archived_reports LIKE reports",$userlink);
	mysql_query("CREATE TABLE IF NOT EXISTS archived_absence LIKE absence",$userlink);
	mysql_query("CREATE TABLE IF NOT EXISTS archived_eventlog LIKE eventlog",$userlink);
	
  $login_qualify = 'ACT';

  $uid = $_SESSION['uid'];
	
	$archivesels = array(1 => "GroupReports","StudentReports","AbsenceRecords","LoginRecords");
	$adateqry = array(1 => "SELECT MIN(date) AS stadate, MAX(date) AS edate FROM reports WHERE type='C' OR type='X'",
												"SELECT MIN(date) AS stadate, MAX(date) AS edate FROM reports WHERE type='T' OR type='F'",
												"SELECT MIN(date) AS stadate, MAX(date) AS edate FROM absence",
												"SELECT DATE(MIN(LastUpdate)) AS stadate, DATE(MAX(LastUpdate)) AS edate FROM eventlog");
	$rdateqry = array(1 => "SELECT MIN(date) AS stadate, MAX(date) AS edate FROM archived_reports WHERE type='C' OR type='X'",
												"SELECT MIN(date) AS stadate, MAX(date) AS edate FROM archived_reports WHERE type='T' OR type='F'",
												"SELECT MIN(date) AS stadate, MAX(date) AS edate FROM archived_absence",
												"SELECT DATE(MIN(LastUpdate)) AS stadate, DATE(MAX(LastUpdate)) AS edate FROM archived_eventlog");
	
	if(!isset($_SESSION['ArchiveSelection']))
		$_SESSION['ArchiveSelection'] = 1;
	if(isset($_POST['ArchiveSelection']))
	{
		$_SESSION['ArchiveSelection'] = $_POST["ArchiveSelection"];
		unset($_SESSION['ArchiveArchiveStart']);
		unset($_SESSION['ArchiveArchiveEnd']);
		unset($_SESSION['ArchiveRecoverStart']);
		unset($_SESSION['ArchiveRecoverEnd']);
	}


  // First part of the page
  echo("<html><head><title>" . $dtext['Archive'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="layout.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['Archive'] . "</font><p>");
  echo("<a href=admin.php>" . $dtext['back_admin'] . "</a>");
	
	echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' ID=ArchiveSelForm><SELECT NAME='ArchiveSelection' onChange='document.getElementById(\"ArchiveSelForm\").submit();'>");
	foreach($archivesels AS $asix => $archsel)
	{
		echo("<OPTION VALUE='". $asix. "'". ($_SESSION['ArchiveSelection'] == $asix ? " selected" : ""). ">". $dtext[$archsel]. "</OPTION>");
	}
	echo("</SELECT>");
	
	// Now archive-able dates depend on which dataset is selected
	$adates = inputclassbase::load_query($adateqry[$_SESSION['ArchiveSelection']]);
	$rdates = inputclassbase::load_query($rdateqry[$_SESSION['ArchiveSelection']]);
  if(!isset($_SESSION['ArchiveArchiveStart']))
		$_SESSION['ArchiveArchiveStart'] = $adates['stadate'][0];
  if(!isset($_SESSION['ArchiveArchiveEnd']))
		$_SESSION['ArchiveArchiveEnd'] = $adates['edate'][0];
  if(!isset($_SESSION['ArchiveRecoverStart']))
		$_SESSION['ArchiveRecoverStart'] = $rdates['stadate'][0];
  if(!isset($_SESSION['ArchiveRecoverEnd']))
		$_SESSION['ArchiveRecoverEnd'] = $rdates['edate'][0];
	
	echo("<BR>");
	echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
	echo("<BR>". $dtext['Startdate']. " ");
	$asdfld = new inputclass_datefield("asd",inputclassbase::mysqldate2nl($_SESSION['ArchiveArchiveStart']));
	$asdfld->echo_html();
	echo(" ". $dtext['Enddate']. " ");
	$aedfld = new inputclass_datefield("aed",inputclassbase::mysqldate2nl($_SESSION['ArchiveArchiveEnd']));
	$aedfld->echo_html();
	echo(" <INPUT TYPE=SUBMIT NAME='Archive' VALUE='". $dtext['Archive']. "'></FORM>");
	// Recovery part is only shown if recovery is possible
	if($_SESSION['ArchiveRecoverEnd'] != "")
	{
		echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
		echo("<BR>". $dtext['Startdate']. " ");
		$rsdfld = new inputclass_datefield("rsd",inputclassbase::mysqldate2nl($_SESSION['ArchiveRecoverStart']));
		$rsdfld->echo_html();
		echo(" ". $dtext['Enddate']. " ");
		$redfld = new inputclass_datefield("red",inputclassbase::mysqldate2nl($_SESSION['ArchiveRecoverEnd']));
		$redfld->echo_html();
	  echo(" <INPUT TYPE=SUBMIT NAME='Recover' VALUE='". $dtext['Recover']. "'></FORM>");	
	}
	
	

  // close the page
  echo("</html>");

?>
