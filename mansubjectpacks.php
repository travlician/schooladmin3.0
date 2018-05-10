<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
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

  $login_qualify = 'A';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  // First we get all the data from existing subjects in an array.
  $subjects = SA_loadquery("SELECT subject.*,AVG(show_sequence) AS avg FROM `class` LEFT JOIN subject USING(mid) LEFT JOIN subjectfiltergroups ON (`group`=`class`.gid) WHERE `group` IS NOT NULL GROUP BY subject.mid ORDER BY avg");
  if(!isset($subjects['shortname'])) // If none of the groups is selected for filtering, the above query yields no results, so give all subjects
    $subjects = SA_loadquery("SELECT subject.*,AVG(show_sequence) AS avg FROM subject LEFT JOIN `class` USING(mid) GROUP BY subject.mid ORDER BY avg");
  
  // Get all defined packages
  $packages = SA_loadquery("SELECT * FROM subjectpackage ORDER BY packagename");
  // Translate the packages to a double index array
  if(isset($packages))
    foreach($packages['packagename'] AS $r => $pname)
      $pack[$pname][$packages['mid'][$r]] = 1;

  $subfilters = SA_loadquery("SELECT * FROM subjectfiltergroups");
  $groups = SA_loadquery("SELECT * FROM sgroup WHERE active=1 ORDER BY groupname");
  $subselects = SA_loadquery("SELECT * FROM subjectselectgroups");

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['subpack_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['subpack_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("</center><table border=1 cellpadding=0>");

  // Create the heading row for the table
  echo("<tr><th><center>" . $dtext['Subpack'] . "</th>");
  foreach($subjects['shortname'] AS $sname)
    echo("<th>". $sname. "</th>");
  echo("<th>&nbsp</th><th>&nbsp</th></tr>");

  // Create a row for new entry
  echo("<tr><form name=newpack method=post action=updsubpack.php><td><input type=text name=pname size=20></td>");
  // Add the possible subjects
  foreach($subjects['mid'] AS $sname)
  {
    echo("<td><center><input type=checkbox name='". $sname. "'></td>");
  }
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newpack.submit();'></td></form></tr>");
  // Create a row in the table for every existing reportcalc record
  $ppname="";
  if(isset($pack))
  foreach($pack AS $pname => $pdets)
  {
    $r = md5($pname);
    echo("<tr><form method=post action=updsubpack.php name=up". $r. ">");
    // Insert the original package name as hidden to be able to delete b4 reinsert.
    echo("<td><input type=hidden name=opname value='" . $pname."'>");
	echo("<input type=text size=20 name=pname value='". $pname. "'></td>");
    // Insert the options for each subject
	foreach($subjects['mid'] AS $sname)
	{
	  echo("<td><center><input type=checkbox name='". $sname. "' ". (isset($pdets[$sname]) ? "checked " : ""). "></td>");
	}
    // Add the change button
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.up". $r. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=delsubpack.php name=dp". $r. "><input type=hidden name=pname value='");
    echo($pname);
    echo("'><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.dp". $r. ".submit(); }'></td></form></tr>");
  }
  echo("</table>");
  
  // Show a selectable for groups that can choose the subject package
  if(isset($groups))
  {
    // First we see how wide the table should be
	$fchar = "^";
	$maxwidth = 0;
	$curwidth = 0;
	foreach($groups['groupname'] AS $gname)
	{
	  if(substr($gname,0,1) != $fchar)
	  {
	    $fchar = substr($gname,0,1);
		$curwidth = 0;
	  }
	  $curwidth++;
	  if($curwidth > $maxwidth)
	    $maxwidth = $curwidth;
	}
	echo("<FORM method=POST action=updselgroups.php>");
	echo("<BR>". $dtext['subselgrp']. "<table border=1>");
	$fchar = "^";
	$curpos = 0;
	foreach($groups['groupname'] AS $gix => $gname)
	{
	  if(substr($gname,0,1) != $fchar)
	  { // New row
	    if($fchar != "^")
		{
		  echo("</TR>");
		}
		$fchar = substr($gname,0,1);
		echo("<TR>");
		$curpos = 0;
	  }
	  $curpos++;
	  echo("<TD><INPUT TYPE=checkbox NAME=subsel". $groups['gid'][$gix]);
	  if(isset($subselects))
	  {
	    foreach($subselects['group'] AS $fgrp)
		  if($fgrp == $groups['gid'][$gix])
		    echo(" checked");
	  } 
	  echo(">". $gname. "</TD>");
	}
	echo("</TR></TABLE><INPUT TYPE=SUBMIT VALUE=\"". $dtext['Change']. "\"></FORM>");	
  }
   
  // Show a selectable for groups that filter result entry based on subject package
  if(isset($groups))
  {
    // First we see how wide the table should be
	$fchar = "^";
	$maxwidth = 0;
	$curwidth = 0;
	foreach($groups['groupname'] AS $gname)
	{
	  if(substr($gname,0,1) != $fchar)
	  {
	    $fchar = substr($gname,0,1);
		$curwidth = 0;
	  }
	  $curwidth++;
	  if($curwidth > $maxwidth)
	    $maxwidth = $curwidth;
	}
	echo("<FORM method=POST action=updfiltgroups.php>");
	echo("<BR>". $dtext['subfiltgrp']. "<table border=1>");
	$fchar = "^";
	$curpos = 0;
	foreach($groups['groupname'] AS $gix => $gname)
	{
	  if(substr($gname,0,1) != $fchar)
	  { // New row
	    if($fchar != "^")
		{
		  echo("</TR>");
		}
		$fchar = substr($gname,0,1);
		echo("<TR>");
		$curpos = 0;
	  }
	  $curpos++;
	  echo("<TD><INPUT TYPE=checkbox NAME=filtsel". $groups['gid'][$gix]);
	  if(isset($subfilters))
	  {
	    foreach($subfilters['group'] AS $fgrp)
		  if($fgrp == $groups['gid'][$gix])
		    echo(" checked");
	  } 
	  echo(">". $gname. "</TD>");
	}
	echo("</TR></TABLE><INPUT TYPE=SUBMIT VALUE=\"". $dtext['Change']. "\"></FORM>");	
  }
   
  echo("</html>");
?>
