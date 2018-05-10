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
  require_once("inputlib/inputclasses.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

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
  
  // This function is based on tables that is created as needed. So now we create them if they do not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `bo_opmrap_data` (
    `opmerkingid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `opmtext` TEXT DEFAULT NULL,
	`year` CHAR(20),
	`period` INTEGER(11) UNSIGNED DEFAULT NULL,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`opmerkingid`),
    UNIQUE KEY `sidopmperyear` (`sid`, `period`, `year`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `bo_jaarresult_data` (
    `resultid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
	`year` CHAR(20),
	`result` TEXT DEFAULT NULL,
	`advice` TEXT DEFAULT NULL,
	`klas` TEXT DEFAULT NULL,
	`mentor` INTEGER(11) UNSIGNED DEFAULT NULL,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`resultid`),
    UNIQUE KEY `sidyear` (`sid`, `year`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  // We need to get the year for entry!
  $curyears = SA_loadquery("SELECT year,id FROM period ORDER BY id DESC");
  $curyear = $curyears['year'][1];
  $percount = $curyears['id'][1];
  
  // We need to get the class and mentor data for automatic entry
  $curxdata = SA_loadquery("SELECT tid_mentor FROM sgroup WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "'");
  $curmtid = $curxdata['tid_mentor'][1];
    
  // Get all exisiting records in an array
  $rapdata = SA_loadquery("SELECT * FROM bo_opmrap_data WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($rapdata))
    foreach($rapdata['opmerkingid'] AS $xix => $xid)
	  $rdata[$rapdata['sid'][$xix]][$rapdata['period'][$xix]] = $xid;
  $resultdata = SA_loadquery("SELECT * FROM bo_jaarresult_data WHERE year='". $curyear. "'");
  if(isset($resultdata))
    foreach($resultdata['resultid'] AS $xix => $xid)
	  $yrdata[$resultdata['sid'][$xix]] = $xid;
  
  // Create and array for year results
  $yrsel = "SELECT '' AS id, '' AS tekst UNION SELECT 'OVER','OVER' UNION SELECT 'NIET OVER','NIET OVER' UNION SELECT 'O.W.L.','O.W.L.' UNION SELECT 'S.V.','S.V.'";
  // Get a list of all applicable students
  $students = SA_loadquery("SELECT CONCAT(lastname,', ',firstname) AS name,sid FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND sgroup.groupname = '$CurrentGroup' ORDER BY name");

  // Create a separate array with the groups
  $sql_query = "SELECT gid,groupname FROM sgroup WHERE active=1 ORDER BY groupname";
  $sql_result = mysql_query($sql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $group_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $group_n = $nrows;

  // First part of the page
  echo("<html><head><title>Opmerkingen rapport</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Opmerkingen rapport</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");

  // Show for which group current editing and allow changing the group
  echo("<form method=post action=form_Opmerkingen_rapport.php>" . $dtext['Group_Cap'] . " <select name=NewGroup>");
  for($gc=1;$gc<=$group_n;$gc++)
  { // Add an option for each group, select the one currently active
    if($CurrentGroup == $group_array['groupname'][$gc])
      $IsSelected = " selected";
    else
      $IsSelected = "";
    echo("<option value=" . $group_array['groupname'][$gc]."$IsSelected>" . $group_array['groupname'][$gc]."</option>");
  }
  echo("</select><input type=submit value=" . $dtext['Change'] . "></form>");

  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  // Create the row below it with the periods
  echo("<tr><td><center>Leerling</td>");
  for($tper = 1; $tper <= $percount; $tper++)
    echo("<th class=per". $tper. "><center>Rapport ". $tper. "</td>");
  echo("<TD>Resultaat</TD><TD>Advies</TD></tr>");


  // Create a row in the table for each student
  $negix = 0;
  foreach($students['sid'] AS $six => $sid)
  {
    echo("<tr><td>". $students['name'][$six]. "</td>");
	
    for($tper=1; $tper <= $percount; $tper++)
    {
	  echo("<TD class=per". $tper. ">");
	  { // Create an entry field for the comment on this period
	    if(isset($rdata[$sid][$tper]))
		{
          $opmfield = new inputclass_textarea("opmfield". $rdata[$sid][$tper],"40,*",$userlink,"opmtext","bo_opmrap_data",$rdata[$sid][$tper],"opmerkingid","","hdprocpage.php");
		}
		else
		{
          $opmfield = new inputclass_textarea("opmfield". (--$negix),"40,*",$userlink,"opmtext","bo_opmrap_data",$negix,"opmerkingid","","hdprocpage.php");
		  $opmfield->set_extrafield("sid", $sid);
		  $opmfield->set_extrafield("year", $curyear);
		  $opmfield->set_extrafield("period",$tper);
		}
		$opmfield->echo_html();	    
	  }
      echo("</TD>");
	}
	// Add the field for the year result
	echo("<TD>");
	if(isset($yrdata[$sid]))
	{
	  $yrfield = new inputclass_listfield("yrfield". $yrdata[$sid],$yrsel,$userlink,"result","bo_jaarresult_data",$yrdata[$sid],"resultid","","hdprogpage.php");
	  $adfield = new inputclass_textfield("adfield". $yrdata[$sid],"20",$userlink,"advice","bo_jaarresult_data",$yrdata[$sid],"resultid","","hdprogpage.php");
	}
	else
	{
	  $yrfield = new inputclass_listfield("yrfield". (--$negix),$yrsel,$userlink,"result","bo_jaarresult_data",$negix,"resultid","","hdprogpage.php");
	  $yrfield->set_extrafield("sid",$sid);
	  $yrfield->set_extrafield("year",$curyear);
	  $yrfield->set_extrafield("klas",$_SESSION['CurrentGroup']);
	  $yrfield->set_extrafield("mentor",$curmtid);
	  $adfield = new inputclass_textfield("yrfield". (--$negix),"20",$userlink,"advice","bo_jaarresult_data",$negix,"resultid","","hdprogpage.php");
	  $adfield->set_extrafield("sid",$sid);
	  $adfield->set_extrafield("year",$curyear);
	  $adfield->set_extrafield("klas",$_SESSION['CurrentGroup']);
	  $adfield->set_extrafield("mentor",$curmtid);
	}
	$yrfield->echo_html();
	echo("</TD><TD>");
	$adfield->echo_html();
	echo("</TD></tr>");
  }
  echo("</table>");
  echo '<a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  // close the page
  echo("</html>");
?>
