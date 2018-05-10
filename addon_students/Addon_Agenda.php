<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 3.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
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

  $login_qualify = 'S';
  include ("schooladminfunctions.php");

  // Find out what tid is applicable for this student
  $tiddata = SA_loadquery("SELECT tid_mentor FROM sgroup LEFT JOIN sgrouplink USING(gid) WHERE active=1 AND sid=". $_SESSION['uid']);
  $tid = $tiddata['tid_mentor'][1];

  // Get the list of applicable subjects with their details
  $subjdata = SA_loadquery("SELECT mid, fullname FROM subject");
  if(isset($subjdata))
  {
    foreach($subjdata['mid'] AS $midx => $mid)
	  $subject[$mid] = $subjdata['fullname'][$midx];
  }

  // We need to get the year for selection!
  $curyears = SA_loadquery("SELECT year,id FROM period ORDER BY id DESC");
  $curyear = $curyears['year'][1];

  // Get the agenda data
  $firstdate = date('Y-m-d');
  $lastdate = date('Y-m-d',mktime(0,0,0,date('m'),date('d') + 14,date('Y')));
  $agddata = SA_loadquery("SELECT subject,datum,huiswerk,proefwerk FROM bo_weekplan_data WHERE tid=". $tid. " AND year='". $curyear. "' AND datum >='". $firstdate. "' AND datum < '". $lastdate. "' ORDER BY datum");
  if(isset($agddata['datum']))
  {
    foreach($agddata['datum'] AS $agix => $add)
	{
	  if($agddata['huiswerk'][$agix] != "")
	  {
	    $addstr = "<U>". $subject[$agddata['subject'][$agix]]. "</U>: ". $agddata['huiswerk'][$agix];
		if(isset($huiswerk[$agddata['datum'][$agix]]))
		  $huiswerk[$agddata['datum'][$agix]] .= "<BR>". $addstr;
		else
		  $huiswerk[$agddata['datum'][$agix]] = $addstr;
	  }
	}
    foreach($agddata['datum'] AS $agix => $add)
	{
	  if($agddata['proefwerk'][$agix] != "")
	  {
	    $addstr = "<U>". $subject[$agddata['subject'][$agix]]. "</U>: ". $agddata['proefwerk'][$agix];
		if(isset($proefwerk[$agddata['datum'][$agix]]))
		  $proefwerk[$agddata['datum'][$agix]] .= "<BR>". $addstr;
		else
		  $proefwerk[$agddata['datum'][$agix]] = $addstr;
	  }
	}
  }
  
  $weekdays = array(1 => "Maandag", "Dinsdag", "Woensdag", "Donderdag", "Vrijdag");
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>Agenda</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Agenda</font><p>");
  include("studentmenu.php");

  echo("<br>");

  // Now create a table with all agenda dates during the week
  // Create the first heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><th>Datum</th><th>Huiswerk</th><th>Proefwerk</th></tr>");

  // Create a row in the table for date
  $curdate = $firstdate;
  while($curdate < $lastdate)
  {
  	$wkd = date("N",mktime(0,0,0,substr($curdate,5,2),substr($curdate,8,2),substr($curdate,0,4)));
    if($wkd < 6) 
	{ // It's a weekday (not weekend!)
	  echo("<tr><th class=rightaligned>". $weekdays[$wkd]. " ". date("d-m-Y",mktime(0,0,0,substr($curdate,5,2),substr($curdate,8,2),substr($curdate,0,4))). "</th><td class=padded>");
	  if(isset($huiswerk[$curdate]))
	    echo($huiswerk[$curdate]);
	  else
	    echo("&nbsp;");
	  echo("</td><td class=padded>");
	  if(isset($proefwerk[$curdate]))
	    echo($proefwerk[$curdate]);
	  else
	    echo("&nbsp;");
	  echo("</td></tr>");
	}
    $curdate = date("Y-m-d",mktime(0,0,0,substr($curdate,5,2),substr($curdate,8,2)+1,substr($curdate,0,4)));
  }
  echo("</table>");
  // close the page
  echo("</html>");

?>
