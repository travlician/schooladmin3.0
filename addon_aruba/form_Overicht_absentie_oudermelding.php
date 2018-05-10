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

  $login_qualify = 'AC';
  include ("schooladminfunctions.php");
  require_once("student.php");
  require_once("absence.php");
  // Avoid sorting on students messed up
  //unset($_SESSION['ssortertable']);
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  // Create tables and content if required
  // First: List of fields to consider
  mysql_query("CREATE TABLE IF NOT EXISTS `absencelistfields`  (`afid` int(11) unsigned NOT NULL AUTO_INCREMENT, `table_name` varchar(32) COLLATE utf8_general_ci, PRIMARY KEY (`afid`),  UNIQUE KEY `afid` (`afid`) ) ENGINE=MyISAM", $userlink);
  echo(mysql_error());
  $absencefieldlist = inputclassbase::load_query("SELECT * FROM `absencelistfields`");
  if(!isset($absencefieldlist['afid']))
  { // No fields are entered in field list table, now we add firstname and lastname for starters
    mysql_query("INSERT INTO absencelistfields (table_name) VALUES('*student.firstname')", $userlink);
    mysql_query("INSERT INTO absencelistfields (table_name) VALUES('*student.lastname')", $userlink);
  } 
  // And then we start giving out content
  echo ('<LINK rel="stylesheet" type="text/css" href="style_parentabsence.css" title="style1">');
  if(isset($_GET['manfields']))
  { // Need to manage the fields in stead of showing the list.
    // remove the entry if marked for delete
	if(isset($_GET['afidel']))
	  mysql_query("DELETE FROM `absencelistfields` WHERE afid=". $_GET['afidel'], $userlink);
    echo("<html><head><title>Overzicht Absentie oudermeldingen</title></head><body link=blue vlink=blue>");
	echo("<H1>Instelling velden voor overzicht absentie oudermeldingen</H1>");
	echo("<a href='". $_SERVER['PHP_SELF']. "'>Terug naar het overzicht absentiemeldingen</a>");
	$myfields = inputclassbase::load_query("SELECT afid FROM `absencelistfields`");
	echo("<table><tr><th>Veldnaam</th><th>&nbsp;</th></tr>");
	$selqry = "SELECT '' AS id, '' as tekst UNION SELECT ' primegroup','Primaire groep' UNION SELECT ' absencedate','Absentiedatum' UNION SELECT table_name,label FROM student_details WHERE raccess <> 'N' AND raccess <> 'O' AND raccess <> 'P' ORDER BY tekst";
	foreach($myfields['afid'] AS $aafid)
	{
	  $selfld = new inputclass_listfield("selfld". $aafid,$selqry,NULL,"table_name", "absencelistfields", $aafid, "afid",NULL,"datahandler.php");
	  echo("<TR><TD>");
	  $selfld->echo_html();
	  echo("</td><TD><A href='". $_SERVER['PHP_SELF']. "?manfields=1&afidel=". $aafid. "'><IMG SRC='PNG/action_delete.png'></A></td></tr>");
	}
	$selfld = new inputclass_listfield("selfld",$selqry,NULL,"table_name", "absencelistfields", 0, "afid",NULL,"datahandler.php");
	echo("<TR><TD>");
	$selfld->echo_html();	
	echo("</td><TD><A href='". $_SERVER['PHP_SELF']. "?manfields=1'><IMG SRC='PNG/action_add.png'></A></td></tr>");
	echo("</table>");
    echo("</body></html>");    
    exit;
  } 
  // Get a list of the fields to use
  $fldqr = inputclassbase::load_query("SELECT table_name,label FROM `absencelistfields` LEFT JOIN student_details USING(table_name) ORDER BY afid");  
  // Get a list of groups
  if(isset($PrimaryGroupFilter))
    $groepfilter = $PrimaryGroupFilter;
  else
    $groepfilter = "__";
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND (groupname LIKE '". $groepfilter. "') ORDER BY groupname");
  
  if(isset($groups))
  {
    echo("<html><head><title>Overzicht absentie oudermeldingen</title></head><body link=blue vlink=blue>");
	echo("<H1>Overzicht door ouders gemelde absentie</h1>");
	/*if(1==1)
	{
	  
	  echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' ID=absencefilterform>");
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
	} */
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
	  if($fldqr['table_name'][$lix] == " absencedate")
	    $alabel="Absentie datum";
	  echo("<TH><A href='". $_SERVER['PHP_SELF']. "?sortseq=". $fldqr['table_name'][$lix]. "'>". $alabel. "</a></th>");
	}
	echo("<TH>". $dtext['Time']. "</th><TH>". $dtext['Reason']. "</th><TH>". $dtext['Subject']. "</th><TH>". $dtext['Remarks']. "</th>");
	echo("</tr>");
    foreach ($groups['gid'] AS $groupid)
	{
	  $group = new group($groupid);
	  if(isset($group))
	  {
	    $abslistqr = inputclassbase::load_query("SELECT asid,sid FROM absence LEFT JOIN sgrouplink USING(sid) WHERE authorization='Parent' AND gid=". $groupid);
	    //$LLlijst = student::student_list($group);
		if(isset($abslistqr['asid']))
		foreach ($abslistqr['asid'] AS $asid) 
		{
		 $absrec = new absence($asid);
		 $student = $absrec->get_student();
		 $stdata[$asid]['sid'] = $student->get_id();
		 $stdata[$asid]['asid'] = $absrec;
		 if(isset($student))
		  foreach($fldqr['table_name'] AS $fldref)
		  {
		    if($fldref == " primegroup")
		      $stdata[$asid][$fldref] = $group->get_groupname();
			else if($fldref == " lastreporttime")
			{
			  $lrtqr = inputclassbase::load_query("SELECT MAX(LastUpdate) AS rlu FROM reports WHERE sid=". $student->get_id());
			  if(!isset($lrtqr['rlu'][0]))
			    $stdata[$asid][$fldref] = "";
			  else
			  {
			    $stdata[$asid][$fldref] = $lrtqr['rlu'][0];
				$lrrqr = inputclassbase::load_query("SELECT lastaccess FROM lastreportaccess WHERE sid=". $student->get_id(). " AND tid=". $_SESSION['uid']);
				if(!isset($lrrqr['lastaccess'][0]) || $lrrqr['lastaccess'][0] < $lrtqr['rlu'][0])
				{ // Report has been modified or created after last read or edit by this teacher
				  $stdata[$asid][$fldref] .= "<img src='PNG/letter.png'>";
				}
			  }
			}
			else
		      $stdata[$asid][$fldref] = $student->get_student_detail($fldref);
		  }
		} // einde foreach student / leerling uit de klas
	  }	
	} // einde foreach group / klas
	// Now we filter, sort and display
	// Filtering first
	/*if(isset($_SESSION['carecodefilter']) && isset($stdata))
	{
	  foreach($stdata AS $sid => $stdat)
	  {
	    if(isset($filtertext) && $stdat[$carecodetable] != $filtertext)
		  unset($stdata[$sid]);
		else if($_SESSION['carecodefilter'] == '-' && $stdat[$carecodetable] != '')
		  unset($stdata[$sid]);
	  }
	}*/
	// Sorting second
	if(isset($stdata))
	{
	  foreach($stdata AS $asid => $stdat)
	  {
	    $sar[$asid] = "";
	    if(isset($sortseq))
	      $sar[$asid] = $stdat[$sortseq];
	    if(!isset($sortseq) || $sortseq != "*student.lastname")
	      $sar[$asid] .= " ". $stdat["*student.lastname"];
	    if(!isset($sortseq) || $sortseq != "*student.firstname")
	      $sar[$asid] .= " ". $stdat["*student.firstname"];
	  }
	  if(isset($sortdir) && $sortdir == "reverse")
	    arsort($sar);
	  else
	    asort($sar);
	  foreach($sar AS $asid => $dummy)
	  {
	    $stdat = $stdata[$asid];
	    $prefix = "";
	    if(isset($carecodetable))
	    {
		  $careprefix = inputclassbase::load_query("SELECT `". $carecodecolors. "`.tekst AS pref FROM `". $carecodetable. "` LEFT JOIN `". $carecodecolors. "` ON(data=id) WHERE sid=". $stdata[$asid]['sid']);
		  if(isset($careprefix['pref'][0]))
		  {
		    $prefix = " STYLE='". $careprefix['pref'][0]. "'";
		  }
	    }
	    echo("<tr". $prefix. ">");
	    foreach($fldqr['table_name'] AS $fldref)
	    {
		  if($fldref == " absencedate")
		  {
		    echo("<TD>". $stdat['asid']->get_date(). "</td>");
		  }
		  else
		    echo("<TD>". $stdat[$fldref]. "</td>");
	    }
	    // Add columns with absence data
		echo("<td>". $stdat['asid']->get_time(). "</td><td>". $stdat['asid']->get_reason(). "</td><td>". $stdat['asid']->get_subject(). "</td><td>". $stdat['asid']->get_explanation(). "</td>");
	    echo("</tr>") ;
	  }
	}
	echo("</table>");
  } // Endif we have groups
    
  // close the page
  echo("</html>");
?>
