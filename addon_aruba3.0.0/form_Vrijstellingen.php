<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  //error_reporting(E_STRICT);
  require_once("inputlib/inputclasses.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
 
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","Sp","Wi-A","Wi-B","Sk","Bio","M&O","Ec","Ak","Gs","Inf","Pa");
  $xxsubjects = array(100=>"I&S","Pfw");


  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;
  
  $uid = intval($uid);
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `ex45data` (
    `ex45id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `mid` INTEGER(11) DEFAULT NULL,
    `xstatus` INTEGER(11) DEFAULT NULL,
	`year` varchar(20),
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`ex45id`),
    UNIQUE KEY `sidmid` (`sid`, `mid`, `year`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());

  $sqlquery = "CREATE TABLE IF NOT EXISTS `ahxdata` (
    `ahxid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `mid` INTEGER(11) DEFAULT NULL,
    `xstatus` TEXT DEFAULT NULL,
	`year` varchar(20),
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`ahxid`),
    UNIQUE KEY `sidmid` (`sid`, `mid`, `year`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());

  $sqlquery = "CREATE TABLE IF NOT EXISTS `examresult` (
    `xid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `xresult` TEXT DEFAULT NULL,
	`ckvres` INTEGER(1) DEFAULT 0,
	`year` TEXT,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`xid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
    
  // We need to get the year for entry!
  $curyearqr = SA_loadquery("SELECT year FROM period WHERE id=3");
  $curyear = $curyearqr['year'][1];
    
  // The states are predefined, here is the query to get that list (actually no DB access but the library works that way...)
  $statquery = "SELECT 0 AS id, '' AS tekst  
                UNION SELECT 5,'v7' UNION SELECT 6,'v8' UNION SELECT 7,'v9' UNION SELECT 8,'v10' ";
  
  // Get a list of all applicable students
  $students = SA_loadquery("SELECT CONCAT(lastname,', ',firstname) AS name,sid FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND groupname='". $CurrentGroup. "' GROUP BY sid ORDER BY name");

  // Get a list of applicable subjects
  $subjectsqr = SA_loadquery("SELECT shortname,mid FROM subject ORDER BY mid");
  foreach($offsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
	{
	  if($offsubjects[$osix] == $subsn)
	  {
	    $subjects['shortname'][$osix] = $sjname;
		$subjects['mid'][$osix] = $subjectsqr['mid'][$sbix];
	  }
	}
  }
  foreach($xxsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
	{
	  if($xxsubjects[$osix] == $subsn)
	  {
	    $xsubjects['shortname'][$osix] = $sjname;
		$xsubjects['mid'][$osix] = $subjectsqr['mid'][$sbix];
	  }
	}
  }
 
  // Get all exisiting records in an array
  $ex45data = SA_loadquery("SELECT * FROM ex45data WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($ex45data))
    foreach($ex45data['ex45id'] AS $xix => $xid)
	  $ex45s[$ex45data['sid'][$xix]][$ex45data['mid'][$xix]] = $xid;
  $exres = SA_loadquery("SELECT * FROM examresult WHERE year='". $curyear. "'");

  $ahxdata = SA_loadquery("SELECT * FROM ahxdata WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($ahxdata))
    foreach($ahxdata['ahxid'] AS $xix => $xid)
	  $ahxs[$ahxdata['sid'][$xix]][$ahxdata['mid'][$xix]] = $xid;

  $exres = SA_loadquery("SELECT * FROM examresult WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($exres))
    foreach($exres['xid'] AS $xix => $xid)
	  $exr[$exres['sid'][$xix]] = $xid; 

  // First part of the page
  echo("<html><head><title>Vrijstellingen invoer</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Vrijstellingen</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");

  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td>nr</td><td><center>Studentnaam</td>");
  foreach($subjects['shortname'] AS $sname)
  {
    echo("<td><center>". strtolower($sname). "</td>");
  }
  foreach($xsubjects['shortname'] AS $sname)
  {
    echo("<td><center>". strtolower($sname). "</td>");
  }
  echo("<td><center>Opmerkingen</center></td>");
  echo("<td><center>Jaarresultaat</center></td>");
  echo("</tr>");

  // Create a row in the table for each student
  $negix = 0;
  $stdnr = 1;
  foreach($students['sid'] AS $six => $sid)
  {
    echo("<tr><td>". $stdnr++. "</td><td>". $students['name'][$six]. "</td>");
	// Get the subjects the student is supposed to have
	foreach($subjects['mid'] AS $mid)
	{
	  echo("<TD>");
	  { // Create an entry field for the status and show it
	    if(isset($ex45s[$sid][$mid]))
		{
          $statfield = new inputclass_listfield("statfield". $ex45s[$sid][$mid],$statquery,$userlink,"xstatus","ex45data",$ex45s[$sid][$mid],"ex45id","","ex45procpage.php");
		}
		else
		{
          $statfield = new inputclass_listfield("statfield". (--$negix),$statquery,$userlink,"xstatus","ex45data",$negix,"ex45id","","ex45procpage.php");
		  $statfield->set_extrafield("mid", $mid);
		  $statfield->set_extrafield("sid", $sid);
		  $statfield->set_extrafield("year", $curyear);
		}
		$statfield->echo_html();	    
	  }
      echo("</TD>");
	}
	foreach($xsubjects['mid'] AS $mid)
	{
	  echo("<TD>");
	  if(isset($ahxs[$sid][$mid]))
	  {
        $statfield = new inputclass_textfield("xstatfield". $ahxs[$sid][$mid],3,$userlink,"xstatus","ahxdata",$ahxs[$sid][$mid],"ahxid","","ex45procpage.php");
	  }
	  else
	  {
        $statfield = new inputclass_textfield("xstatfield". (--$negix),3,$userlink,"xstatus","ahxdata",$negix,"ahxid","","ex45procpage.php");
		$statfield->set_extrafield("mid", $mid);
		$statfield->set_extrafield("sid", $sid);
		$statfield->set_extrafield("year", $curyear);
	  }
	  $statfield->echo_html();	    
      echo("</TD>");
	}
	echo("<td>");
	if(isset($ahxs[$sid][0]))
	  $resfield = new inputclass_textarea("opmfield". $sid,"15,*",$userlink,"xstatus","ahxdata",$ahxs[$sid][0],"ahxid","","ex45procpage.php");
	else
	{
	  $resfield = new inputclass_textarea("opmfield". ($negix),"15,*",$userlink,"xstatus","ahxdata",$negix,"ahxid","","ex45procpage.php");
	  $resfield->set_extrafield("mid", "0");
	  $resfield->set_extrafield("sid", $sid);
	  $resfield->set_extrafield("year", $curyear);
	}
	$resfield->echo_html();
	echo("</td>");
	echo("<td>");
	if(isset($exr[$sid]))
	  $resfield = new inputclass_textarea("resfield". $sid,"15,*",$userlink,"xresult","examresult",$exr[$sid],"xid","","ex45procpage.php");
	else
	{
	  $resfield = new inputclass_textarea("resfield". ($negix),"15,*",$userlink,"xresult","examresult",$negix,"xid","","ex45procpage.php");
	  $resfield->set_extrafield("sid", $sid);
	  $resfield->set_extrafield("year", $curyear);
	}
	$resfield->echo_html();
	echo("</td>");
	echo("</tr>");
  }
 echo("</table>");
  echo '<a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
 
  // close the page
  echo("</html>");
?>
