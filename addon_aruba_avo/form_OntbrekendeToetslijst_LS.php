<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
  
  function print_head($year, $schoolyear, $subject, $teacher, $groupname, $time = NULL)
  {
    echo("<P class=tophead>Klas ". $year. ", ". $schoolyear. "</p>");
    echo("<p class=subhead>". $groupname. " Vak: ". $subject. " Docent: ". $teacher);
	if(isset($time))
	  echo(" (". $time. ")");
	echo(" Trimester ". $_SESSION['OTfperiod']);
    echo("<table class=studlist><TR><TH class=firstcol>nr.</TH><TH class=othercol>Naam:</TH><TH class=othercol>Aantal toetsresultaten</TH></TR>");
  }
  
  function print_foot($corr)
  {	
    echo("<div class=newpage>&nbsp;</div>");
  }
  
  
  $uid = $_SESSION['uid'];
  $uid = intval($uid);
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
if(isset($_POST['Print']))
{ 
  // First part of the page
  //echo("<html><head><title>Ontbrekende Resultaten lijst</title></head><body link=blue vlink=blue bgcolor=#FFFFE0 onload=\"window.print();setTimeout('window.close();',10000);\">");
  echo("<html><head><title>Ontbrekende Resultaten lijst</title></head><body link=blue vlink=blue bgcolor=#FFFFE0 >");
  echo '<LINK rel="stylesheet" type="text/css" href="style_ahp.css" title="style1">';
  foreach($_POST AS $pix => $pvl)
  {
    if(substr($pix,0,3) == "gcb")
	{ // Generate list for group with gid given
	  $gid=substr($pix,3);
	  // Get the subjects and teacher codes
	  $subjects = SA_loadquery("SELECT shortname, data, mid FROM class LEFT JOIN subject USING(mid) LEFT JOIN t_dcode USING(tid) WHERE gid=". $gid);
	  // Get the year from the groupname
	  $groupnameqr = SA_loadquery("SELECT groupname FROM sgroup WHERE active=1 AND gid=". $gid);
	  $gn = $groupnameqr['groupname'][1];
	  if(substr($gn,0,3) == "VWO")
	    $ly = "VWO";
	  else if(substr($gn,0,1) == "1" || substr($gn,1,1) == "1" || substr($gn,2,1) == "1")
        $ly = "1";	  
	  else if(substr($gn,0,1) == "2" || substr($gn,1,1) == "2" || substr($gn,2,1) == "2")
        $ly = "2";	  
	  else if(substr($gn,0,1) == "3" || substr($gn,1,1) == "3" || substr($gn,2,1) == "3")
        $ly = "3";	  
	  else if(substr($gn,0,1) == "4" || substr($gn,1,1) == "4" || substr($gn,2,1) == "4")
        $ly = "4";
      else $ly = "5";
	  // See if a day/time can be retrieved from the groupname
	  $ltime = NULL;
	  if(substr($gn,-1) == 1 || substr($gn,-1) == 2 || substr($gn,-1) == 3 || substr($gn,-1) == 4 || substr($gn,-1) == 5 || substr($gn,-1) == 6)
	  {
	    $ltime = substr($gn,-1);
		if(substr($gn,-3) == strtolower(substr($gn,-3)))
		  $ltime = substr($gn,-3);
	  }
      // Get the names of the students
      $student = SA_loadquery("SELECT lastname,firstname AS data,sid FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " GROUP BY sid ORDER BY lastname,firstname");
	  // Now create a list for each subject
	  foreach($subjects['shortname'] AS $suix => $sname)
	  {
		// Get the number of tests for this group/subject combination
		$testcountq = "SELECT COUNT(tdid) AS ttcnt FROM testdef LEFT JOIN class USING(cid) WHERE mid=". $subjects['mid'][$suix]. " AND gid=". $gid. " AND period=". $_SESSION['OTfperiod']. " AND year='". $schoolyear. "'";
		$testcountqr = SA_loadquery($testcountq);
		
	    // Get the count of test results for each student in the class
		$tcntq = "SELECT sgrouplink.sid, IF(tcnt1 IS NULL,0,tcnt1) AS tcnt FROM sgrouplink LEFT JOIN 
		           (SELECT sid,COUNT(tdid) AS tcnt1 FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid)
				     WHERE mid=". $subjects['mid'][$suix]. 
		            " AND period=". $_SESSION['OTfperiod']. " AND year='". $schoolyear. "' GROUP BY sid) AS t1 
					ON(sgrouplink.sid=t1.sid) WHERE sgrouplink.gid=". $gid. " GROUP BY sid";
		$tcntqr = SA_loadquery($tcntq);
		if(isset($tcntqr['sid']))
		{
		  // Now convert the test count query result to an array
		  foreach($tcntqr['sid'] AS $six => $sid)
		  {
		    $tcnt[$sid] = $tcntqr['tcnt'][$six];
		  }
		  // See in any students were missing tests
		  if(isset($testcountqr['ttcnt']))
		    $ttcnt = $testcountqr['ttcnt'][1];
		  else
			$ttcnt = 0;
		  $havemissed = false;
		  foreach($student['sid'] AS $six => $ssid)
		  {
			if(isset($tcnt[$ssid]) && $tcnt[$ssid] < $ttcnt)
		      $havemissed = true;
		  }
		  if($ttcnt > 0 && $havemissed)
		  {
	        print_head($ly, $schoolyear, $sname, $subjects['data'][$suix], $gn, $ltime);
		    $snr = 1;
		    foreach($student['lastname'] AS $six => $sln)
		    {
		      if(isset($tcnt[$student['sid'][$six]]) && $tcnt[$student['sid'][$six]] < $ttcnt)
		        echo("<TR class=studrow><TD>". ($snr < 10 ? "0". $snr++ : $snr++). "</TD>
			           <TD>". $sln. ", ". $student['data'][$six]. "</TD>
					   <TD>". $tcnt[$student['sid'][$six]]. " (". $ttcnt. ")</TD></TR>");
			  else
			    $snr++;
		    }
		    echo("</TABLE><P class=newpage>&nbsp</P>");
		  }
		}
	  }

	}
	  
  }
 
  SA_closeDB();


} // End if print posted
else
{ // No print posted, must show filter and sort fields
  // Get the list of groups that have at least one class assigned
  $groupq = "SELECT gid,groupname,COUNT(cid) AS scount FROM sgroup LEFT JOIN class USING(gid) WHERE active=1 AND gid > 0";
  if(isset($_POST['subject']) && $_POST['subject'] != "all")
    $groupq .= " AND mid=". $_POST['subject'];
  if(isset($_POST['yearlayer']) && $_POST['yearlayer'] != "all")
  {
    $x = $_POST['yearlayer'];
    $groupq .= " AND (groupname LIKE '". $x. "%' OR groupname LIKE '_". $x. "%' OR groupname LIKE '__". $x. "%')";
  }
  $groupq .=" GROUP BY gid HAVING scount>0 ORDER BY groupname";
  $groups = SA_loadquery($groupq);
  $subjects = SA_loadquery("SELECT mid,shortname FROM subject ORDER BY shortname");
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>OntbrekendeToetslijst Avond HAVO</title></head><body link=blue vlink=blue bgcolor=#FFFFE0>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_ahp.css" title="style1">';
  //foreach($_POST AS $pkey => $pval)
  //  echo($pkey. " = ". $pval. "<BR>");
  // Show the filters
  // Year layer
  echo("<FORM METHOD=POST ACTION='form_OntbrekendeToetslijst_LS.php' name=filter>");
  echo("Filter op jaarlaag: <SELECT name=yearlayer id=yearlayer onchange='document.filter.submit()'>");
  echo("<OPTION value='all'>*</OPTION><OPTION value=1". (isset($_POST['yearlayer']) && $_POST['yearlayer'] == 1 ? " selected" : ""). ">1</OPTION>");
  echo("<OPTION value=2". (isset($_POST['yearlayer']) && $_POST['yearlayer'] == 2 ? " selected" : ""). ">2</OPTION>");
  echo("<OPTION value=3". (isset($_POST['yearlayer']) && $_POST['yearlayer'] == 3 ? " selected" : ""). ">3</OPTION>");
  echo("<OPTION value=VWO". (isset($_POST['yearlayer']) && $_POST['yearlayer'] == "VWO" ? " selected" : ""). ">VWO</OPTION></SELECT>");
  // Subject
  echo("<BR>Filter op vak: <SELECT name=subject id=subject onchange='document.filter.submit()'><OPTION value='all'>*</OPTION>");
  foreach($subjects['mid'] AS $sbix => $mid)
    echo("<OPTION value=". $mid. (isset($_POST['subject']) && $_POST['subject'] == $mid ? " selected" : ""). ">". $subjects['shortname'][$sbix]. "</OPTION>");
  echo("</SELECT>");
  // Period
  if(isset($_POST['fperiod']))
  {
    $_SESSION['OTfperiod'] = $_POST['fperiod'];
  }
  if(isset($_SESSION['OTfperiod']))
    $cp = $_SESSION['OTfperiod'];
  else
  {
    if(date("n") > 7)
      $cp=1;
    else if(date("n") < 5)
      $cp=2;
    else $cp=3;
	$_SESSION['OTfperiod'] = $cp;
  }
  echo("<BR>Trimester: <SELECT name=fperiod id=fperiod onchange='document.filter.submit()'>");
  echo("<OPTION value=1". ($cp == 1 ? " selected " : ""). ">1</OPTION>
        <OPTION value=2". ($cp == 2 ? " selected " : ""). ">2</OPTION>
		<OPTION value=3". ($cp == 3 ? " selected " : ""). ">3</OPTION></SELECT>");
  echo("</FORM>");
  
  echo("<FORM METHOD=POST ACTION='form_OntbrekendeToetslijst_LS.php'>");
  // Put the group checkboxes
  echo("<BR>Groepen:<BR>");
  if(isset($groups['gid']))
  {
    foreach($groups['gid'] AS $gix => $gid)
	{
	  if(substr($groups['groupname'][$gix],0,2) == "O3" || substr($groups['groupname'][$gix],0,3) == "SN3" || substr($groups['groupname'][$gix],0,3) == "VWO")
	    $checked = "";
	  else
	    $checked = "CHECKED";
      echo("<SPAN class=groupcb><INPUT TYPE=checkbox ". $checked. " NAME=gcb". $gid. "> ". $groups['groupname'][$gix]. "</SPAN>");
	}
    echo("<BR><INPUT TYPE=SUBMIT NAME='Print' VALUE='Afdrukken'>");
  }
  else
    echo("<BR>Controleer de filters!");
  echo("</FORM>");
  echo("</html>");
  
}
?>
