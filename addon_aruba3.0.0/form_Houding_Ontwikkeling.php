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
  require_once("teacher.php");
  
  // Link inputclasses with database
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `bo_houding_defs` (
    `aspectid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `aspect` CHAR(20) DEFAULT NULL,
	`categorie` CHAR(40) DEFAULT NULL,
    `omschrijving` TEXT DEFAULT NULL,
	PRIMARY KEY (`aspectid`),
    UNIQUE KEY `aspect` (`aspect`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  // If the defenitions table is empty, we prefill it with 2 values
  $defcntqr = SA_loadquery("SELECT DISTINCT categorie AS defcat FROM bo_houding_defs WHERE categorie <> 'range'");
  if(!isset($defcntqr['defcat'][2]))
  { // Now we enter defs, 1 for 2 different categories
    mysql_query("INSERT INTO bo_houding_defs (aspect,categorie,omschrijving) VALUES ('Houding','Werkhouding','')", $userlink);
    mysql_query("INSERT INTO bo_houding_defs (aspect,categorie,omschrijving) VALUES ('Ontwikkeling','Sociaal emotionele ontwikkeling','')", $userlink);
  }
	
	// If the record for the range is not present, insert it as 5 (default)
	$currangeqr = SA_loadquery("SELECT * FROM bo_houding_defs WHERE `categorie`='range'");
	if(!isset($currangeqr['aspectid']))
	{
		mysql_query("INSERT INTO bo_houding_defs (aspect,categorie,omschrijving) VALUES('range','range','5')", $userlink);
		$bolrange=5;
	}
	else
	{
		$bolrange=$currangeqr['omschrijving'][1];
	}
  
  // A category to work with needs to be set, either already in session, POSTED or the first one defined
  if(isset($_POST['Category']))
    $_SESSION['CurrentAspectCategory'] = $_POST['Category'];
  $defcatqr = SA_loadquery("SELECT DISTINCT categorie AS defcat FROM bo_houding_defs WHERE categorie <> 'range'");
  if(!isset($_SESSION['CurrentAspectCategory']))
  {
		$_SESSION['CurrentAspectCategory'] = $defcatqr['defcat'][1];   
  }
  
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
  $statquery = "SELECT '' AS id, ' ' AS tekst UNION SELECT '1' AS id,'1' AS tekst UNION SELECT '2','2' UNION SELECT '3','3' UNION SELECT '4','4' UNION SELECT '5','5'";
  
  // Get a list of all applicable students
  $students = SA_loadquery("SELECT CONCAT(lastname,', ',firstname) AS name,firstname, lastname, sid FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND sgroup.groupname = '$CurrentGroup' ORDER BY name");

  // Create a list of applicable aspects
  // SKOA standard: $aspects = array('Gedr' => 'Gedrag', 'Conc' => 'Concentratie', 'Wrkt' => 'Werktempo', 'Zelfs' => 'Zelfstandigheid', 'SocVrd' => 'Sociale Vaardigheden', 'Wrkvz' => 'Werkverzorging', 'MotIjvr' => 'Motivatie/IJver');
  //$aspects = array('Gedr' => 'Gedrag', 'Conc' => 'Concentratie', 'Wrkt' => 'Werktempo', 'Nwk' => 'Nauwkeurigheid', 'Zelfv' => 'Zelfvertrouwen', 'Dzv' => 'Doorzettingsvermogen', 'Zelfs' => 'Zelfstandigheid', 'Clkr' => 'Contact met leerkracht', 'Cklg' => 'Contact met klasgenoten', 'Wrkvz' => 'Werkverzorging', 'Motv' => 'Motivatie', 'Ijvr' => 'IJver');
  $aspectsqr = SA_loadquery("SELECT aspect,omschrijving FROM bo_houding_defs WHERE categorie='". $_SESSION['CurrentAspectCategory']. "'");
	if(isset($aspectsqr['aspect']))
  foreach($aspectsqr['aspect'] AS $asix => $anaspect)
    $aspects[$anaspect] = $aspectsqr['omschrijving'][$asix];
  
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
  echo("<html><head><title>Leerling aspecten invoer</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  if(isset($_GET['manfields']))
  { // Need to manage the fields in stead of showing the input list.
    // remove the entry if marked for delete
		if(isset($_GET['aspdel']))
			mysql_query("DELETE FROM `bo_houding_defs` WHERE aspectid=". $_GET['aspdel'], $userlink);
		echo("<H1>Instelling aspecten voor ". $_SESSION['CurrentAspectCategory']. "</H1>");
		echo("<a href='". $_SERVER['PHP_SELF']. "'>Terug naar het invoerscherm</a>");
		$myfields = SA_loadquery("SELECT aspectid FROM `bo_houding_defs` WHERE categorie='". $_SESSION['CurrentAspectCategory']. "'");
		echo("<table><tr><th>Aspect</th><th>Omschrijving</th><th>&nbsp;</th></tr>");
		if(isset($myfields['aspectid']))
		foreach($myfields['aspectid'] AS $aspectid)
		{
			$aspfld = new inputclass_textfield("aspfld". $aspectid,12,$userlink,"aspect", "bo_houding_defs", $aspectid, "aspectid",NULL,"datahandler.php");
			echo("<TR><TD>");
			$aspfld->echo_html();
			$desfld = new inputclass_textfield("desfld". $aspectid,80,NULL,"omschrijving", "bo_houding_defs", $aspectid, "aspectid",NULL,"datahandler.php");
			echo("</td><TD>");
			$desfld->echo_html();
			echo("</td><TD><A href='". $_SERVER['PHP_SELF']. "?manfields=1&aspdel=". $aspectid. "'><IMG SRC='PNG/action_delete.png'></A></td></tr>");
		}
		$aspfld = new inputclass_textfield("aspfld",12,NULL,"aspect", "bo_houding_defs", 0, "aspectid",NULL,"datahandler.php","categorie",$_SESSION['CurrentAspectCategory']);
		$desfld = new inputclass_textfield("desfld",80,NULL,"omschrijving", "bo_houding_defs", 0, "aspectid",NULL,"datahandler.php","categorie",$_SESSION['CurrentAspectCategory']);
		echo("<TR><TD>");
		$aspfld->echo_html();	
		echo("</td><TD>");
		$desfld->echo_html();
		echo("</TD><TD><A href='". $_SERVER['PHP_SELF']. "?manfields=1'><IMG SRC='PNG/action_add.png'></A></td></tr>");
		echo("</table>");
		echo("categorie toevoegen: ");
		$catfld = new inputclass_textfield("catfld",12,$userlink,"categorie", "bo_houding_defs", -1, "aspectid",NULL,"datahandler.php");
		$catfld->echo_html();
		echo("<A href='". $_SERVER['PHP_SELF']. "?manfields=1'><IMG SRC='PNG/action_add.png'></A><BR>");
		echo("Aantal bolletjes: ");
		$rangeidqr = SA_loadquery("SELECT aspectid FROM bo_houding_defs WHERE categorie='range'");
		$rangefld = new inputclass_textfield("rangefld",2,$userlink,"omschrijving", "bo_houding_defs", $rangeidqr['aspectid'][1], "aspectid",NULL,"datahandler.php");
		$rangefld->echo_html();
    echo("</body></html>");    
    exit;
  }
  // Not managing field so show data entry  
  // Show for which category current editing and allow changing the category
	//echo("<H3>Current category = ". $_SESSION['CurrentAspectCategory']. "</h3>");
  echo("<center><form method=post action='". $_SERVER['PHP_SELF']. "' NAME=catsel ID=catsel><font size=+2>Invoer </font><select name=Category onChange='document.getElementById(\"catsel\").submit()'>");
  foreach($defcatqr['defcat'] AS $adefcat)
  { // Add an option for each category, select the one currently active
    if($_SESSION['CurrentAspectCategory'] == $adefcat)
      $IsSelected = " selected";
    else
      $IsSelected = "";
    echo("<option value='" . $adefcat. "' $IsSelected>" . $adefcat."</option>");
  }
  echo("</select></form>");

  // Show for which group current editing and allow changing the group
  echo("<form method=post action='". $_SERVER['PHP_SELF']. "' NAME=grpsel ID=grpsel>" . $dtext['Group_Cap'] . " <select name=NewGroup onChange='document.getElementById(\"grpsel\").submit()'>");
  for($gc=1;$gc<=$group_n;$gc++)
  { // Add an option for each group, select the one currently active
    if($CurrentGroup == $group_array['groupname'][$gc])
      $IsSelected = " selected";
    else
      $IsSelected = "";
    echo("<option value=" . $group_array['groupname'][$gc]."$IsSelected>" . $group_array['groupname'][$gc]."</option>");
  }
  echo("</select></form>");
  $I=new teacher();
  $I->load_current();
  if($I->has_role("admin"))
    echo("<a href='". $_SERVER['PHP_SELF']. "?manfields=1'>Instellen mogelijke aspecten</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");
  echo("Het eerste bolletje is het slechtse resultaat, het laatste bolletje is het beste resultaat.");
  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>Aspect</td>");
	$itemcount = 0;
  foreach($aspects AS $aspkey => $aspect)
  {
    //echo("<td colspan=". $percount. "><center>". $aspest. "</td>");
    echo("<td colspan=". $percount. "><center><SPAN title='". $aspect. "'>". $aspkey. "</span></td>");
		if($itemcount++ == 2)
		{
			echo("<TD>&nbsp;</td>");
			$itemcount = 0;
		}
  }
  echo("</tr>");
  // Create the row below it with the periods
  echo("<tr><td><center>Leerling</td>");
	$itemcount = 0;
  foreach($aspects AS $aspect)
  {
    for($tper = 1; $tper <= $percount; $tper++)
      echo("<th class=per". $tper. "><center>". $tper. "</td>");
		if($itemcount++ == 2)
		{
			echo("<td><center>Leerling</td>");
			$itemcount = 0;
		}
  }
  echo("</tr>");


  // Create a row in the table for each student
  $negix = 0;
	$altrow=false;
  foreach($students['sid'] AS $six => $sid)
  {
		$itemcount = 0;
    echo("<tr><td class=per3". ($altrow ? "alt" : ""). ">". initials($students['firstname'][$six]). " ". $students['lastname'][$six]. "</td>");
	
		foreach($aspects AS $aspix => $aspect)
		{
			for($tper=1; $tper <= $percount; $tper++)
			{
				echo("<TD class=per". $tper. ($altrow ? "alt" : ""). " style='min-width: ". ($bolrange * 16). "px'>");
				{ // Create an entry field for the aspect
					if(isset($hdata[$sid][$aspix][$tper]))
					{
						$statfield = new inputclass_bolfield("statfield". $hdata[$sid][$aspix][$tper],$bolrange,$userlink,"xstatus","bo_houding_data",$hdata[$sid][$aspix][$tper],"houdingid","","hdprocpage.php");
					}
					else
					{
						$statfield = new inputclass_bolfield("statfield". (--$negix),$bolrange,$userlink,"xstatus","bo_houding_data",$negix,"houdingid","","hdprocpage.php");
						$statfield->set_extrafield("aspect", $aspix);
						$statfield->set_extrafield("sid", $sid);
						$statfield->set_extrafield("year", $curyear);
					$statfield->set_extrafield("period",$tper);
					}
					$statfield->echo_html();	    
				}
				echo("</TD>");
			}
			if($itemcount++ == 2)
			{
				echo("<td class=per3". ($altrow ? "alt" : ""). ">". initials($students['firstname'][$six]). " ". $students['lastname'][$six]. "</td>");
				$itemcount = 0;
			}
		}
		echo("</tr>");
		$altrow = !$altrow;
  }
  // Create the row below it with the periods
  echo("<tr><td><center>Leerling</td>");
	$itemcount=0;
  foreach($aspects AS $aspect)
  {
    for($tper = 1; $tper <= $percount; $tper++)
      echo("<th class=per". $tper. "><center>". $tper. "</td>");
		if($itemcount++ == 2)
		{
			echo("<TD>&nbsp;</td>");
			$itemcount = 0;
		}
  }
  echo("</tr>");
  echo("<tr><td><center>Aspect</td>");
	$itemcount = 0;
  foreach($aspects AS $aspkey => $aspect)
  {
    //echo("<td colspan=". $percount. "><center>". $aspest. "</td>");
    echo("<td colspan=". $percount. "><center>". $aspkey. "</td>");
		if($itemcount++ == 2)
		{
			echo("<td><center>Leerling</td>");
			$itemcount = 0;
		}
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
