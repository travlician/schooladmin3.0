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
  
  function print_head($year, $place, $schoolyear, $subject, $teacher, $groupname, $time = NULL)
  {
    echo("<P class=tophead>Klas ". $year. ", ". $place. " ". $schoolyear. "</p>");
    echo("<p class=subhead><SPAN class=gname>". $groupname. "</SPAN> Vak: ". $subject. " Docent: ". $teacher);
	if(isset($time))
	  echo(" (". $time. ")");
	echo("<SPAN class=subheaddate>DATUM:</SPAN>");
    echo("<table class=studlist><TR><TH class=firstcol>nr.</TH><TH class=othercol>Naam:</TH><TH class=othercol>E-mail</TH><TH class=othercol>Handtekening:</TH></TR>");
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
  //echo("<html><head><title>Presentielijst Avond HAVO</title></head><body link=blue vlink=blue bgcolor=#FFFFE0 onload=\"window.print();setTimeout('window.close();',10000);\">");
  echo("<html><head><title>Presentielijst Avond HAVO</title></head><body link=blue vlink=blue bgcolor=#FFFFE0 >");
  echo '<LINK rel="stylesheet" type="text/css" href="style_ahp.css" title="style1">';
  $regions = array(4 => "O'stad", 142 => "San Nicolas");
  foreach($_POST AS $pix => $pvl)
  {
    if(substr($pix,0,3) == "gcb")
	{ // Generate list for group with gid given
	  $gid=substr($pix,3);
	  // Get the subjects and teacher codes
	  $subjects = SA_loadquery("SELECT shortname, data FROM class LEFT JOIN subject USING(mid) LEFT JOIN t_dcode USING(tid) WHERE gid=". $gid);
	  // Find out which region
	  $rq = "SELECT ";
	  foreach($regions AS $rgid => $rname)
	    $rq .= "SUM(`". $rname. "`) AS `c". $rname. "`,";
	  $rq .= "gid FROM sgrouplink LEFT JOIN (SELECT ";
  	  foreach($regions AS $rgid => $rname)
	    $rq .= "SUM(IF(gid=". $rgid. ",1,0)) AS `". $rname. "`,";
	  $rq .= "sid FROM sgrouplink GROUP BY sid) AS t1 USING(sid) WHERE gid=". $gid;
	  $regionqr = SA_loadquery($rq);
	  $myregion = "";
	  $maxregioncount = 0;
	  foreach($regions AS $rgid => $rname)
	  { // Check for each region result if it has more students than current max
	    if($regionqr["c". $rname][1] > $maxregioncount)
		{
		  $myregion = $rname;
		  $maxregioncount = $regionqr["c". $rname][1];
		}
	  }
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
      $student = SA_loadquery("SELECT lastname,s_roepnaam.data AS roepnaam,s_ASEmailStudent.data AS email 
	                           FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN s_roepnaam USING(sid)  
							   LEFT JOIN s_ASEmailStudent USING(sid) WHERE gid=". $gid. " GROUP BY sid ORDER BY lastname,s_roepnaam.data");
	  // Now create a list for each subject
	  foreach($subjects['shortname'] AS $suix => $sname)
	  {
	    print_head($ly, $myregion, $schoolyear, $sname, $subjects['data'][$suix], $gn, $ltime);
		$snr = 1;
		foreach($student['lastname'] AS $six => $sln)
		{
		  echo("<TR class=studrow><TD>". ($snr < 10 ? "0". $snr++ : $snr++). "</TD><TD>". $sln. ", ". $student['roepnaam'][$six]. "</TD><TD>". $student['email'][$six]. "</TD><TD>&nbsp</TD></TR>");
		}
		echo("</TABLE><P class=newpage>&nbsp</P>");
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
  echo("<html><head><title>Presentielijst Avond HAVO</title></head><body link=blue vlink=blue bgcolor=#FFFFE0>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_ahp.css" title="style1">';
  //foreach($_POST AS $pkey => $pval)
  //  echo($pkey. " = ". $pval. "<BR>");
  // Show the filters
  // Year layer
  echo("<FORM METHOD=POST ACTION='form_Presentielijst_email_AH.php' name=filter>");
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
  echo("</FORM>");
  
  echo("<FORM METHOD=POST ACTION='form_Presentielijst_email_AH.php'>");
  // Put the group checkboxes
  echo("<BR>Groepen:<BR>");
  if(isset($groups['gid']))
  {
    foreach($groups['gid'] AS $gix => $gid)
      echo("<SPAN class=groupcb><INPUT TYPE=checkbox NAME=gcb". $gid. "> ". $groups['groupname'][$gix]. "</SPAN>");

    echo("<BR><INPUT TYPE=SUBMIT NAME='Print' VALUE='Afdrukken'>");
  }
  else
    echo("<BR>Controleer de filters!");
  echo("</FORM>");
  echo("</html>");
  
}
?>
