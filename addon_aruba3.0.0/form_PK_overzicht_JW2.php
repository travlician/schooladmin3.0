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
  require_once("teacher.php");
  require_once("student.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  
  // Link the database connection with the input library
  inputclassbase::dbconnect($userlink);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;
  
  if(!isset($_SESSION['CurrentMid']))
    $_SESSION['CurrentMid'] = 0;
  $CurrentMid = $_SESSION['CurrentMid'];
  if(isset($_POST['NewMid']))
  {
    $CurrentMid = $_POST['NewMid'];
	$_SESSION['CurrentMid'] = $CurrentMid;
  }
  
  if(!isset($_SESSION['CurrentPer']))
    $_SESSION['CurrentPer'] = 1;
  $CurrentPer = $_SESSION['CurrentPer'];
  if(isset($_POST['NewPer']))
  {
    $CurrentPer = $_POST['NewPer'];
	$_SESSION['CurrentPer'] = $CurrentPer;
  }
  
  $uid = intval($uid);
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `avo_pk_data` (
    `houdingid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `aspect` CHAR(10) DEFAULT NULL,
	`mid` INT(11) DEFAULT NULL,
    `xstatus` TEXT DEFAULT NULL,
	`year` CHAR(20),
	`period` INTEGER(11) UNSIGNED DEFAULT NULL,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`houdingid`),
    UNIQUE KEY `sidaspectsubjectperyear` (`sid`, `aspect`, `mid`, `period`, `year`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  // We need to get the year for entry!
  $curyears = SA_loadquery("SELECT year,id FROM period ORDER BY id DESC");
  $curyear = $curyears['year'][1];
  $percount = $curyears['id'][1];
  

    
  // The states are predefined, here is the query to get that list (actually no DB access but the library works that way...)
  $statquery = "SELECT '' AS id, ' ' AS tekst UNION SELECT 'V','Voldoende' UNION SELECT 'M','Matig' UNION SELECT 'O','Onvoldoende'";
  //$statquery = "SELECT '' AS id, ' ' AS tekst UNION SELECT 'G' AS id,'Goed' AS tekst UNION SELECT 'V','Voldoende' UNION SELECT 'M','Matig' UNION SELECT 'S','Slecht'";
  
  // Get a list of all applicable students
  //$students = SA_loadquery("SELECT CONCAT(lastname,', ',firstname) AS name,firstname, lastname, sid FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND sgroup.groupname = '$CurrentGroup' ORDER BY name");
  $students = student::student_list();

  // Create a list of applicable aspects
  //$aspects = array('Inzet' => 'Inzet','Gedr' => 'Gedrag', 'Regels' => 'Regels', 'HWerk' => 'Huiswerk', 
  //                 'Conc' => 'Concentratie', 'Cap' => 'Capaciteit', 'Wrkvz' => 'Werkverzorging', 'Tempo' => 'Tempo');
  $aspects = array('Inz/Mot' => 'Inzet/motivatie','Conc' => 'Concentratie', 'Werkverz' => 'Werkverzorging', 'HWerk' => 'Huiswerkattitude','Omgaan ARP' => 'Omgaan ARP', 'Tempo' => 'Tempo', 'SocGedr' => 'Sociaal gedrag', 'Vlijt' => 'Vlijt');
   
  // Create a separate array with the groups
  $I = new teacher();
  $I->load_current();
  if($I->has_role("admin"))
    $sql_query = "SELECT gid,groupname FROM sgroup WHERE active=1 ORDER BY groupname";
  else
    $sql_query = "SELECT gid,groupname FROM class LEFT JOIN sgroup USING(gid) WHERE active=1 AND tid=". $_SESSION['uid']. " GROUP BY gid ORDER BY groupname";
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
  
  // Load the subjects that can be selected with the current group
  if($I->has_role("admin"))
    $subjqry = "SELECT mid,shortname FROM class LEFT JOIN sgroup USING(gid) LEFT JOIN subject USING(mid) WHERE active=1 AND groupname='". $CurrentGroup. "'";
  else
    $subjqry = "SELECT mid,shortname FROM class LEFT JOIN sgroup USING(gid) LEFT JOIN subject USING(mid) WHERE active=1 AND groupname='". $CurrentGroup. "' AND tid=". $_SESSION['uid'];
  $subjsels = inputclassbase::load_query($subjqry);
  if(isset($subjsels['mid']))
  { // Check if current selected subject is in the list, if not, we set the first one as such
    $curvalid=false;
	foreach($subjsels['mid'] AS $smid)
	{
	  if($smid == $CurrentMid)
	    $curvalid=true;
	}
	if(!$curvalid)
	{ // Current mid is not valid! we change it now to a valid one
	  $CurrentMid = $subjsels['mid'][0];
	  $_SESSION['CurrentMid'] = $CurrentMid;
	}
  }

  // Get all exisiting records in an array
  $houdingdata = SA_loadquery("SELECT aspect,sid,xstatus,GROUP_CONCAT(shortname) AS score FROM avo_pk_data LEFT JOIN subject USING(mid) WHERE year='". $curyear. "' AND period=". $CurrentPer. " GROUP BY sid,aspect,xstatus");
  // Convert this to a more convenient array type
  if(isset($houdingdata))
    foreach($houdingdata['score'] AS $xix => $score)
	  $hdata[$houdingdata['sid'][$xix]][$houdingdata['aspect'][$xix]][$houdingdata['xstatus'][$xix]] = $score;
  

  // First part of the page
  echo("<html><head><title>PK Overzicht</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Persoonlijke Kwaliteiten Overzicht</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");

  // Show for which group current editing and allow changing the group
  echo("<form method=post action=form_PK_overzicht_JW2.php ID=groupsel NAME=groupsel>" . $dtext['Group_Cap'] . 
        " <select name=NewGroup onChange='document.groupsel.submit()'>");
  for($gc=1;$gc<=$group_n;$gc++)
  { // Add an option for each group, select the one currently active
    if($CurrentGroup == $group_array['groupname'][$gc])
      $IsSelected = " selected";
    else
      $IsSelected = "";
    echo("<option value=" . $group_array['groupname'][$gc]."$IsSelected>" . $group_array['groupname'][$gc]."</option>");
  }
  //echo("</select><input type=submit value=" . $dtext['Change'] . "></form>");
  echo("</select></form>");

  // Show for which subject current editing and allow changing the subject
/*  echo("<form method=post action=form_PK_overzicht_v2.php ID=subjsel NAME=subjsel>" . $dtext['Subject'] . 
        " <select name=NewMid onChange='document.subjsel.submit()'>");
  foreach($subjsels['mid'] AS $mix => $smid)
  { // Add an option for each subject, select the one currently active
    if($CurrentMid == $smid)
      $IsSelected = " selected";
    else
      $IsSelected = "";
    echo("<option value=" . $smid."$IsSelected>" . $subjsels['shortname'][$mix]."</option>");
  }
  //echo("</select><input type=submit value=" . $dtext['Change'] . "></form>");
  echo("</select></form>"); */

  // Show for which period current editing and allow changing the period
  echo("<form method=post action=form_PK_overzicht_JW2.php ID=persel NAME=persel>" . $dtext['Period'] . 
        " <select name=NewPer onChange='document.persel.submit()'>");
  for($selper=1; $selper <= $percount; $selper++)
  { // Add an option for each period, select the one currently active
    if($CurrentPer == $selper)
      $IsSelected = " selected";
    else
      $IsSelected = "";
    echo("<option value=" . $selper."$IsSelected>" . $selper."</option>");
  }
  //echo("</select><input type=submit value=" . $dtext['Change'] . "></form>");
  echo("</select></form>");

  // Create the heading row for the table
  $fields = student::get_list_headers();
  $fldcnt = count($fields);
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td colspan=". $fldcnt. "><center>Aspect</td>");
  foreach($aspects AS $aspkey => $aspect)
  {
    //echo("<td colspan=". $percount. "><center>". $aspest. "</td>");
    echo("<td colspan=3><center><SPAN title='". $aspect. "'>". $aspkey. "</span></td>");
  }
  echo("</tr>");
  // Create the row below it with the periods
/*  echo("<tr><td><center>Leerling</td>");
  foreach($aspects AS $aspect)
  {
    for($tper = 1; $tper <= $percount; $tper++)
      echo("<th class=per". $tper. "><center>". $tper. "</td>");
  }
  echo("</tr>"); */
  // Create the row below it with the values
  echo("<tr><td colspan=". $fldcnt. "><center>Leerling</td>");
  foreach($aspects AS $aspect)
  {
    echo("<th class=per". $CurrentPer. "><center>V</td>");
    echo("<th class=per". $CurrentPer. "><center>M</td>");
    echo("<th class=per". $CurrentPer. "><center>O</td>");
  }
  echo("</tr>");


  // Create a row in the table for each student
  $negix = 0;
  foreach($students AS $stobj)
  {
   if(isset($stobj))
   {
    $sid = $stobj->get_id();
    //echo("<tr><td>". $stobj->get_firstname(). " ". $stobj->get_lastname(). "</td><td>". $stobj->get_student_detail('s_foto'). "</td>");
	echo("<TR>");
    $sdata = $stobj->get_list_data();
    foreach($sdata AS $stdata)
      echo("<TD>". $stdata. "</TD>");
	
	foreach($aspects AS $aspix => $aspect)
	{
	  echo("<TD class=per". $CurrentPer. ">". (isset($hdata[$sid][$aspix]["V"]) ? $hdata[$sid][$aspix]["V"] : " "). "</td>");
	  echo("<TD class=per". $CurrentPer. ">". (isset($hdata[$sid][$aspix]["M"]) ? $hdata[$sid][$aspix]["M"] : " "). "</td>");
	  echo("<TD class=per". $CurrentPer. ">". (isset($hdata[$sid][$aspix]["O"]) ? $hdata[$sid][$aspix]["O"] : " "). "</td>");
	}
	echo("</tr>");
   }
  }
  // Create the row below it with the periods
/*  echo("<tr><td><center>Leerling</td>");
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
  echo("</tr>"); */
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
