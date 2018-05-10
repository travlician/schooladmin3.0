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
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `bo_houding_data` (
    `houdingid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `aspect` CHAR(10) DEFAULT NULL,
    `xstatus` TEXT DEFAULT NULL,
	`year` CHAR(20),
	`period` INTEGER(11) UNSIGNED DEFAULT NULL,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`houdingid`),
    UNIQUE KEY `sidaspectperyear` (`sid`, `aspect`, `period`, `year`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  // We need to get the year for entry!
  $curyears = SA_loadquery("SELECT year,id FROM period ORDER BY id DESC");
  $curyear = $curyears['year'][1];
  $percount = $curyears['id'][1];
    
  // Get all exisiting records in an array
  $houdingdata = SA_loadquery("SELECT * FROM bo_houding_data WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($houdingdata))
    foreach($houdingdata['houdingid'] AS $xix => $xid)
	  $hdata[$houdingdata['sid'][$xix]][$houdingdata['aspect'][$xix]][$houdingdata['period'][$xix]] = $xid;
  
  // The states are predefined, here is the query to get that list (actually no DB access but the library works that way...)
  $statquery = "SELECT '' AS id, ' ' AS tekst UNION SELECT 'A' AS id,'A' AS tekst UNION SELECT 'B','B' UNION SELECT 'C','C' UNION SELECT 'D','D' UNION SELECT 'E','E'";
  
  // Get a list of all applicable students
  $students = SA_loadquery("SELECT CONCAT(lastname,', ',firstname) AS name,firstname, lastname, sid FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND sgroup.groupname = '$CurrentGroup' ORDER BY name");

  // Create a list of applicable aspects
  // SKOA standard: $aspects = array('Gedr' => 'Gedrag', 'Conc' => 'Concentratie', 'Wrkt' => 'Werktempo', 'Zelfs' => 'Zelfstandigheid', 'SocVrd' => 'Sociale Vaardigheden', 'Wrkvz' => 'Werkverzorging', 'MotIjvr' => 'Motivatie/IJver');
  $aspects = array('Gedr' => 'Gedrag', 'Conc' => 'Concentratie', 'Wrkvz' => 'Werkverzorging', 'Zelfs' => 'Zelfstandigheid', 'Motv' => 'Motivatie');
  
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
  echo("<html><head><title>Houding aspecten invoer</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Houdingsaspecten</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");

  // Show for which group current editing and allow changing the group
  echo("<form method=post action=form_Houding_invoer.php>" . $dtext['Group_Cap'] . " <select name=NewGroup>");
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
  echo("<tr><td><center>Aspect</td>");
  foreach($aspects AS $aspkey => $aspect)
  {
    //echo("<td colspan=". $percount. "><center>". $aspest. "</td>");
    echo("<td colspan=". $percount. "><center><SPAN title='". $aspect. "'>". $aspkey. "</span></td>");
  }
  echo("</tr>");
  // Create the row below it with the periods
  echo("<tr><td><center>Leerling</td>");
  foreach($aspects AS $aspect)
  {
    for($tper = 1; $tper <= $percount; $tper++)
      echo("<th class=per". $tper. "><center>". $tper. "</td>");
  }
  echo("</tr>");


  // Create a row in the table for each student
  $negix = 0;
  foreach($students['sid'] AS $six => $sid)
  {
    echo("<tr><td>". initials($students['firstname'][$six]). " ". $students['lastname'][$six]. "</td>");
	
	foreach($aspects AS $aspix => $aspect)
	{
	  for($tper=1; $tper <= $percount; $tper++)
	  {
	    echo("<TD class=per". $tper. ">");
	    { // Create an entry field for the aspect
	      if(isset($hdata[$sid][$aspix][$tper]))
		  {
            $statfield = new inputclass_listfield("statfield". $hdata[$sid][$aspix][$tper],$statquery,$userlink,"xstatus","bo_houding_data",$hdata[$sid][$aspix][$tper],"houdingid","","hdprocpage.php");
		  }
		  else
		  {
            $statfield = new inputclass_listfield("statfield". (--$negix),$statquery,$userlink,"xstatus","bo_houding_data",$negix,"houdingid","","hdprocpage.php");
		    $statfield->set_extrafield("aspect", $aspix);
		    $statfield->set_extrafield("sid", $sid);
		    $statfield->set_extrafield("year", $curyear);
			$statfield->set_extrafield("period",$tper);
		  }
		  $statfield->echo_html();	    
	    }
        echo("</TD>");
	  }
	}
	echo("</tr>");
  }
  // Create the row below it with the periods
  echo("<tr><td><center>Leerling</td>");
  foreach($aspects AS $aspect)
  {
    for($tper = 1; $tper <= $percount; $tper++)
      echo("<th class=per". $tper. "><center>". $tper. "</td>");
  }
  echo("</tr>");
  echo("<tr><td><center>Aspect</td>");
  foreach($aspects AS $aspkey => $aspect)
  {
    //echo("<td colspan=". $percount. "><center>". $aspest. "</td>");
    echo("<td colspan=". $percount. "><center>". $aspkey. "</td>");
  }
  echo("</tr>");
  echo("</table>");
  echo '<a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  // close the page
  echo("</html>");
  function initials($name)
  {
    $names = explode(" ",$name);
	$res = "";
	foreach($names AS $init)
	{
	  $res .= substr($init,0,1). ".";
	}
	return $res;
  }
?>
