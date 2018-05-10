<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'C';
  include ("schooladminfunctions.php");
  require_once("student.php");
  // Avoid sorting on students messed up
  //unset($_SESSION['ssortertable']);
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  // Create tables and content if required
  // First: List of fields to consider
  mysql_query("CREATE TABLE IF NOT EXISTS `carelistfields`  (`cfid` int(11) unsigned NOT NULL AUTO_INCREMENT, `table_name` varchar(32) COLLATE utf8_general_ci, PRIMARY KEY (`cfid`),  UNIQUE KEY `cfid` (`cfid`) ) ENGINE=MyISAM", $userlink);
  echo(mysql_error());
  $carefieldlist = inputclassbase::load_query("SELECT * FROM `carelistfields`");
  if(!isset($carefieldlist['cfid']))
  { // No fields are entered in field list table, now we add firstname and lastname for starters
    mysql_query("INSERT INTO carelistfields (table_name) VALUES('*student.firstname')", $userlink);
    mysql_query("INSERT INTO carelistfields (table_name) VALUES('*student.lastname')", $userlink);
  } 
  // Second: database table for remarks from care service
  mysql_query("CREATE TABLE IF NOT EXISTS `careremarks`  (`sid` int(11) unsigned NOT NULL, `remarks` TEXT, PRIMARY KEY (`sid`),  UNIQUE KEY `sid` (`sid`) ) ENGINE=MyISAM", $userlink);
  // See if a new care code filter was selected
  if(isset($_POST['carecodefilter']))
  {
    if($_POST['carecodefilter'] == '')
	  unset($_SESSION['carecodefilter']);
	else
      $_SESSION['carecodefilter'] = $_POST['carecodefilter'];
  }
  // And then we start giving out content
  echo ('<LINK rel="stylesheet" type="text/css" href="style_zorglln.css" title="style1">');
  if(isset($_GET['manfields']))
  { // Need to manage the fields in stead of showing the list.
    // remove the entry if marked for delete
	if(isset($_GET['cfidel']))
	  mysql_query("DELETE FROM `carelistfields` WHERE cfid=". $_GET['cfidel'], $userlink);
    echo("<html><head><title>Overzicht zorgleerlingen</title></head><body link=blue vlink=blue>");
	echo("<H1>Instelling velden voor overzicht zorgleerlingen</H1>");
	echo("<a href='". $_SERVER['PHP_SELF']. "'>Terug naar het overzicht zorgleerlingen</a>");
	$myfields = inputclassbase::load_query("SELECT cfid FROM `carelistfields`");
	echo("<table><tr><th>Veldnaam</th><th>&nbsp;</th></tr>");
	$selqry = "SELECT '' AS id, '' as tekst UNION SELECT ' primegroup','Primaire groep' UNION SELECT ' lastreporttime','Laatste rapportage' UNION SELECT table_name,label FROM student_details WHERE raccess <> 'N' AND raccess <> 'O' AND raccess <> 'P' ORDER BY tekst";
	foreach($myfields['cfid'] AS $acfid)
	{
	  $selfld = new inputclass_listfield("selfld". $acfid,$selqry,NULL,"table_name", "carelistfields", $acfid, "cfid",NULL,"datahandler.php");
	  echo("<TR><TD>");
	  $selfld->echo_html();
	  echo("</td><TD><A href='". $_SERVER['PHP_SELF']. "?manfields=1&cfidel=". $acfid. "'><IMG SRC='PNG/action_delete.png'></A></td></tr>");
	}
	$selfld = new inputclass_listfield("selfld",$selqry,NULL,"table_name", "carelistfields", 0, "cfid",NULL,"datahandler.php");
	echo("<TR><TD>");
	$selfld->echo_html();	
	echo("</td><TD><A href='". $_SERVER['PHP_SELF']. "?manfields=1'><IMG SRC='PNG/action_add.png'></A></td></tr>");
	echo("</table>");
    echo("</body></html>");    
    exit;
  } 
  // Get a list of the fields to use
  $fldqr = inputclassbase::load_query("SELECT table_name,label FROM `carelistfields` LEFT JOIN student_details USING(table_name) ORDER BY cfid");  
  // Get a list of groups
  if(isset($PrimaryGroupFilter))
    $groepfilter = $PrimaryGroupFilter;
  else
    $groepfilter = "__";
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND (groupname LIKE '". $groepfilter. "') ORDER BY groupname");
  
  if(isset($groups))
  {
    echo("<html><head><title>Overzicht zorgleerlingen</title></head><body link=blue vlink=blue>");
	echo("<H1>Overzicht Leerlingen voor zorg</h1>");
	if(isset($carecodetable))
	{
	  echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' ID=carefilterform>");
	  echo("Zorgcode filter: ");
	  echo("<SELECT NAME=carecodefilter ID=carecodefilter onChange='getElementById(\"carefilterform\").submit();'>");
	  echo("<OPTION VALUE=''". (!isset($_SESSION['carecodefilter']) ? " SELECTED" : ""). ">*</OPTION>");
	  $carestructqr = inputclassbase::load_query("SELECT * FROM student_details WHERE table_name='". $carecodetable. "' AND type='choice' AND params LIKE '*%'");
	  if(isset($carestructqr['type']))
	  {
	    $choicetable = substr($carestructqr['params'][0],1);
	    $carecodesqr = inputclassbase::load_query("SELECT id AS ccode, tekst AS ctext FROM `". $choicetable. "` ORDER BY tekst");
	  }
	  else
	    $carecodesqr = inputclassbase::load_query("SELECT data AS ccode, data AS ctext FROM `". $carecodetable. "` GROUP BY data ORDER BY data");
	  if(isset($carecodesqr['ccode']))
	  {
	    foreach($carecodesqr['ccode'] AS $ccix => $ccdata)
		{
		  if(isset($ccdata) && $ccdata != '')
		  {
		    echo("<OPTION VALUE=". $ccdata. ((isset($_SESSION['carecodefilter']) && $_SESSION['carecodefilter'] == $ccdata) ? " SELECTED" : ""). ">". $carecodesqr['ctext'][$ccix]. "</OPTION>");
		    if(isset($_SESSION['carecodefilter']) && $_SESSION['carecodefilter'] == $ccdata)
			  $filtertext = $carecodesqr['ctext'][$ccix];
		  }
		}
	  }
	  echo("<OPTION VALUE='-'". ((isset($_SESSION['carecodefilter']) && $_SESSION['carecodefilter'] == '-') ? " SELECTED" : ""). ">-</OPTION>");
	  echo("</SELECT></FORM><BR>");
	}
	/* // ~~~ DEBUG: show care code filter status
	if(isset($_SESSION['carecodefilter']))
	{
	  if(isset($filtertext))
	    echo("Filtering on care code ". $filtertext. "<BR>");
	  else if($_SESSION['carecodefilter'] == '-')
	    echo("Filtering on non filled care code <BR>");
	  else
	    echo("Filtering on carecode sesvar ". $_SESSION['carecodefilter']. "<BR>");
	}
	else
	  echo("Not filtering on carecode<BR>"); */
    $remfld = new inputclass_textfield("dummy","1",NULL,NULL,NULL,NULL,NULL,"display: none","datahandler.php");
	$remfld->echo_html();
	echo("<a href='". $_SERVER['PHP_SELF']. "?manfields=1'>Wijzig velden</a>");
	echo("<table class = TblPropKaart><tr>");
	// Prep sort items
	if(isset($_GET['sortseq']))
	{
	  $sortdir = "forward";
	  if(isset($_SESSION['ccsortseq']) && isset($_SESSION['ccsortdir']))
	  {
	    if($_GET['sortseq'] == $_SESSION['ccsortseq'] && $_SESSION['ccsortdir'] != "reverse")
		  $sortdir = "reverse";
	  }
	  $_SESSION['ccsortseq'] = $_GET['sortseq'];
	  $_SESSION['ccsortdir'] = $sortdir;
	}
	if(isset($_SESSION['ccsortseq']))
	  $sortseq = $_SESSION['ccsortseq'];
	foreach($fldqr['label'] AS $lix => $alabel)
	{
	  if($fldqr['table_name'][$lix] == " primegroup")
	    $alabel="Klas";
	  if($fldqr['table_name'][$lix] == " lastreporttime")
	    $alabel="Laatste rapportage";
	  echo("<TH><A href='". $_SERVER['PHP_SELF']. "?sortseq=". $fldqr['table_name'][$lix]. "'>". $alabel. "</a></th>");
	}
	echo("<TH>Opmerkingen</th></tr>");
    foreach ($groups['gid'] AS $groupid)
	{
	  $group = new group($groupid);
	  if(isset($group))
	  {
	    $LLlijst = student::student_list($group);
			if(isset($LLlijst))
			foreach ($LLlijst AS $student) 
			{
				if(isset($student))
				{
					foreach($fldqr['table_name'] AS $fldref)
					{
						if($fldref == " primegroup")
							$stdata[$student->get_id()][$fldref] = $group->get_groupname();
						else if($fldref == " lastreporttime")
						{
							$lrtqr = inputclassbase::load_query("SELECT MAX(LastUpdate) AS rlu FROM reports WHERE sid=". $student->get_id());
							if(!isset($lrtqr['rlu'][0]))
								$stdata[$student->get_id()][$fldref] = "";
							else
							{
								$stdata[$student->get_id()][$fldref] = $lrtqr['rlu'][0];
								$lrrqr = inputclassbase::load_query("SELECT lastaccess FROM lastreportaccess WHERE sid=". $student->get_id(). " AND tid=". $_SESSION['uid']);
								if(!isset($lrrqr['lastaccess'][0]) || $lrrqr['lastaccess'][0] < $lrtqr['rlu'][0])
								{ // Report has been modified or created after last read or edit by this teacher
									$stdata[$student->get_id()][$fldref] .= "<img src='PNG/letter.png'>";
								}
							}
						}
						else
							$stdata[$student->get_id()][$fldref] = $student->get_student_detail($fldref);
					}
					if(!isset($stdata[$student->get_id()][$carecodetable])) // Apparently carecode does not appear in fieldlist...
						$stdata[$student->get_id()][$carecodetable] = $student->get_student_detail($carecodetable);
				}
			} // einde foreach student / leerling uit de klas
	  }	
	} // einde foreach group / klas
	// Now we filter, sort and display
	// Filtering first
	if(isset($_SESSION['carecodefilter']) && isset($stdata))
	{
	  foreach($stdata AS $sid => $stdat)
	  {
	    if(isset($filtertext) && (!isset($stdat[$carecodetable]) || $stdat[$carecodetable] != $filtertext))
			{
				unset($stdata[$sid]);
				//echo("Unset ". $sid. " (stdat carecodetable =". $stdat[$carecodetable]. ")<BR>");
			}
			else if($_SESSION['carecodefilter'] == '-' && $stdat[$carecodetable] != '')
				unset($stdata[$sid]);
	  }
	}
	// Sorting second
	if(isset($stdata))
	{
	  foreach($stdata AS $sid => $stdat)
	  {
			//echo($sid. "<BR>");
	    $sar[$sid] = "";
	    if(isset($sortseq))
	      $sar[$sid] = $stdat[$sortseq];
	    if(!isset($sortseq) || $sortseq != "*student.lastname")
	      $sar[$sid] .= " ". $stdat["*student.lastname"];
	    if(!isset($sortseq) || $sortseq != "*student.firstname")
	      $sar[$sid] .= " ". $stdat["*student.firstname"];
	  }
	  if(isset($sortdir) && $sortdir == "reverse")
	    arsort($sar);
	  else
	    asort($sar);
	  foreach($sar AS $sid => $dummy)
	  {
	    $stdat = $stdata[$sid];
	    $prefix = "";
	    if(isset($carecodetable))
	    {
		  $careprefix = inputclassbase::load_query("SELECT `". $carecodecolors. "`.tekst AS pref FROM `". $carecodetable. "` LEFT JOIN `". $carecodecolors. "` ON(data=id) WHERE sid=". $sid);
		  if(isset($careprefix['pref'][0]))
		  {
		    $prefix = " STYLE='". $careprefix['pref'][0]. "'";
		  }
	    }
	    echo("<tr". $prefix. ">");
	    foreach($fldqr['table_name'] AS $fldref)
	    {
	      if(isset($carecodetable) && $fldref == $carecodetable)
		  {
		    echo("<TD>");
		    $stobj = new student($sid);
		    $stobj->edit_student_detail($fldref);
		    echo("</td>");
		  }
		  else if($fldref == " lastreporttime")
		  {
		    echo("<TD>". (inputclassbase::mysqldate2nl(substr($stdat[$fldref],0,10))). substr($stdat[$fldref],10). "</td>");
		  }
		  else
		    echo("<TD>". $stdat[$fldref]. "</td>");
	    }
	    // Add a column with remarks
	    $remfld = new inputclass_textarea("remfld". $sid,"50,*",NULL,"remarks", "careremarks", $sid, "sid",NULL,"datahandler.php");
	    echo("<TD>");
	    $remfld->echo_html();
	    echo("</td></tr>") ;
	  }
	}
	echo("</table>");
  } // Endif we have groups
    
  // close the page
  echo("</html>");
?>
