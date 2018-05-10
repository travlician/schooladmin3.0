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
  
  function print_head($subjecttext, $scount, $sgroup, $stid, $testname)
  {
    global $schoolyear,$schoolname,$subjects,$subjects,$subtotal,$groups,$teacher;
    echo("<div class=do>DIRECTIE ONDERWIJS ARUBA</div>");
    echo("<p>PROCES-VERBAAL VAN TOEZICHT (art. 26.2 van Landsbesluit eindexamens dagscholen VWO, HAVO, MAVO, AB 1991 No. GT 35).</p>");
    echo("<p>SCHOOLONDERZOEK MAVO, in het schooljaar <u>". $schoolyear. "</u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
	echo("Klas/Cluster: ");
	foreach($groups['gid'] AS $gix => $gid)
	  if($gid == $sgroup)
	    echo($groups['groupname'][$gix]. " ");
	echo(", ". $scount. " Leerlingen");
	echo("</p>");
    echo("<p>School: <u>". $schoolname. "</u> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
    echo(" Vak: <u>". $subjecttext. "</u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<B>". $testname. "</B>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Docent:");
	foreach($teacher['tid'] AS $tix => $tid)
	  if($tid == $stid)
	    echo($teacher['firstname'][$tix]. " ". $teacher['lastname'][$tix]);
	echo("<u>". $stid. "</u>");
    echo(($_POST['tdate'] != "" ? "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Datum: <u>". $_POST['tdate']. "</u>" : ""). "</p>");
	
    echo("<table class=studlist><TR><TH class=exnrhead>Ex. Nr.</TH><TH class=studhead2>Achternaam</TH><TH class=studhead2>Alle voorname voluit</TH><TH class=exnrhead>Cijfer</TH>");
	echo("<TH class=tabhead> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Handtekening &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </TH>");
    echo("</TR>");
  }
  
  function print_foot($corr)
  {	
	echo("</TABLE>");
	if($corr != "")
	  echo("<BR>Corrector: ". $corr. "<BR>");
    // Footing
	echo("<p>Ex nr. en naam dienen in overeenstemming te zijn met formulier EX. 1.</p>");
    echo("<div class=zoz>Z.O.Z.&nbsp;</div>");
  }
  
  
  $uid = $_SESSION['uid'];
  $uid = intval($uid);

  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get a list of subjects applicable to the exam subjects
  $subjects = SA_loadquery("SELECT shortname,subjectpackage.mid,fullname FROM subjectpackage LEFT JOIN subject USING(mid) GROUP BY mid ORDER BY mid");
  
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage");
  $groups = SA_loadquery("SELECT groupname,gid FROM sgroup WHERE active=1 ORDER BY groupname");
  $teacher = SA_loadquery("SELECT tid,firstname,lastname FROM teacher WHERE is_gone <> 'Y'");
if(isset($_POST['Print']))
{ 
  // Get a list of students with the subject package and extra subject
  $squery = "SELECT sid,lastname,firstname,gid,s_exnr.data AS exnr,packagename,extrasubject,extrasubject2,extrasubject3 FROM student LEFT JOIN sgrouplink USING(sid)";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_package USING(sid) LEFT JOIN (SELECT sid FROM sgrouplink WHERE ";
  // Add the filter for the groups
  $fstr = "";
  foreach($_POST AS $pix => $pvl)
  {
    if(substr($pix,0,3) == "gcb")
	  $fstr .= "gid=". substr($pix,3). " OR ";
  }
  $squery .= " (". substr($fstr,0,-4). ") GROUP BY sid) AS t1 USING(sid)";
  $squery .= " WHERE t1.sid IS NOT NULL AND s_exnr.data IS NOT NULL AND s_exnr.data > '0' ";
  // Add filter for the exam numbers
  $squery .= "AND s_exnr.data >= ". $_POST['exafrom']. " AND s_exnr.data <= ". $_POST['exato']. " ";
  $squery .= "ORDER BY lastname,firstname";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
  // Get the list of presence and exemptions 
  $pedata = SA_loadquery("SELECT sid,mid,xstatus FROM ex45data WHERE year='". $schoolyear. "'");
  if(isset($pedata['sid']))
    foreach($pedata['sid'] AS $peix => $sid)
	  $pexdata[$sid][$pedata['mid'][$peix]] = $pedata['xstatus'][$peix];
  // Get the corrector name
  if($_POST['corr'] != 0)
    $corname = SA_loadquery("SELECT firstname,lastname FROM teacher WHERE tid=". $_POST['corr']);
  if(isset($corname['firstname'][1]))
    $corr = $corname['firstname'][1]. " ". $corname['lastname'][1];
  else
    $corr = "";
  

  // First part of the page
  echo("<html><head><title>Formulier EX. 3-M</title></head><body link=blue vlink=blue bgcolor=#FFFFE0>");
  // onload=\"window.print();setTimeout('window.close();',10000);\">");
  echo '<LINK rel="stylesheet" type="text/css" href="style_sop.css" title="style1">';

  // Remove subjects that were not selected
  foreach($subjects['mid'] AS $sbix => $sbid)
    if(!isset($_POST["scb". $sbid]))
      unset($subjects['shortname'][$sbix]);

  foreach($subjects['shortname'] AS $subjix => $sn)
  {
    $mid = $subjects['mid'][$subjix];
	// Get a list of test definitions that qualify
	$tdq = "SELECT tdid,gid,short_desc,description,tid FROM testdef LEFT JOIN class USING(cid)";
	$tdq .= " WHERE year='". $schoolyear. "' AND type LIKE 'SE%' AND mid=". $mid. " AND (";
	$tcbfilt = "";
	foreach($_POST AS $pkey => $pval)
	{
	  if(substr($pkey,0,3) == "tcb")
	    $tcbfilt .= " short_desc = '". substr($pkey,3). "' OR";
	}
	if($tcbfilt == "")
	{
	  echo("<BR>Geen bestaand SE gekozen!<BR>");
	  $tcbfilt="1=1 OR";
	}
	$tcbfilt = substr($tcbfilt,0,-3);
	$tdq .= $tcbfilt. ")";
	$tdqr = SA_loadquery($tdq);
	if(isset($tdqr['tdid']))
	{
      foreach($tdqr['tdid'] AS $tdix => $tdid)
	  {
	    $testname = $tdqr['description'][$tdix];
        // Find out how many students we got here
        $studcount = 0;
        foreach($studs['gid'] AS $six => $gid)
        {
          $hassubject = 0;
	      // check for subjects here!
	      foreach($packages['packagename'] AS $subix => $pname)
	      {
	        if($pname == $studs['packagename'][$six] && $mid == $packages['mid'][$subix])
	        $hassubject = 1;
	      }
	      if($mid == $studs['extrasubject'][$six] || $mid == $studs['extrasubject2'][$six] || $mid == $studs['extrasubject3'][$six])
	        $hassubject = 1;
	      // Now filter out those that are not to be shown
	      if((isset($_POST['pcb1']) || isset($_POST['pcb2']) || isset($_POST['pcb3']) || isset($_POST['pcb4']) ) && 
             (!isset($pexdata[$studs['sid'][$six]][$mid]) || $pexdata[$studs['sid'][$six]][$mid] == 0))
	        $hassubject = 0;
	      if(isset($pexdata[$studs['sid'][$six]][$mid]) && $pexdata[$studs['sid'][$six]][$mid] != 0 && !isset($_POST["pcb". $pexdata[$studs['sid'][$six]][$mid]]))
	  	    $hassubject = 0;
		  // Student not belonging to group for test definition is ignored too
		  if($gid != $tdqr['gid'][$tdix])
		    $hassubject = 0;
          if($hassubject != 0)
	        $studcount++;
        } // End loop for each student for counting
		
		if($studcount > 0)
		{
	      // Get the results for this test
		  unset($tress);
		  $trqr = SA_loadquery("SELECT sid,result FROM testresult WHERE tdid=". $tdid);
		  if(isset($trqr))
		    foreach($trqr['sid'] AS $trix => $trsid)
			  $tress[$trsid] = $trqr['result'][$trix];
          print_head($subjects['fullname'][$subjix], $studcount, $tdqr['gid'][$tdix], $tdqr['tid'][$tdix], $testname);
          $linecount = 0;
  
          // Student listing
          foreach($studs['gid'] AS $six => $gid)
          {
            if($linecount > 25)
	        {
              print_foot($corr, $studcount);
	          $linecount = 0;
	          print_head($subjects['fullname'][$subjix], $studcount, $tdqr['gid'][$tdix], $tdqr['tid'][$tdix], $testname);
	        }
            $hassubject = 0;
	        // check for subjects here!
	        foreach($packages['packagename'] AS $subix => $pname)
	        {
	          if($pname == $studs['packagename'][$six] && $mid == $packages['mid'][$subix])
	          $hassubject = 1;
	        }
	        if($mid == $studs['extrasubject'][$six] || $mid == $studs['extrasubject2'][$six] || $mid == $studs['extrasubject3'][$six])
	          $hassubject = 1;
	        // Now filter out those that are not to be shown
	        if((isset($_POST['pcb1']) || isset($_POST['pcb2']) || isset($_POST['pcb3']) || isset($_POST['pcb4']) ) && 
               (!isset($pexdata[$studs['sid'][$six]][$mid]) || $pexdata[$studs['sid'][$six]][$mid] == 0))
	          $hassubject = 0;
	        if(isset($pexdata[$studs['sid'][$six]][$mid]) && $pexdata[$studs['sid'][$six]][$mid] != 0 && !isset($_POST["pcb". $pexdata[$studs['sid'][$six]][$mid]]))
	  	      $hassubject = 0;
		    if($gid != $tdqr['gid'][$tdix])
		      $hassubject = 0;
            if($hassubject != 0)
	        {
              echo("<TR><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
              echo("<TD class=studname>". $studs['lastname'][$six]. "</TD><TD class=studname>". $studs['firstname'][$six]. "</TD>");
			  if(isset($tress[$studs['sid'][$six]]))
			    echo("<TD class=result>". $tress[$studs['sid'][$six]]. "</TD>");
			  else
			    echo("<TD class=result>X</TD>");
	          // Empty column for signature
	          echo("<TD class=signcol>&nbsp;</TD>");
	  
              echo("</TR>");
	          $linecount++;	
	        } // End if student has package
          } // End loop for each student
  
          print_foot($corr);
		} // End if at least one student present
	  } // End loop for each testdef
	} // End if test def present
  } // End loop for each subject
  // close the page
  echo("</html>");
  SA_closeDB();
} // End if print posted
else
{ // No print posted, must show filter and sort fields
  $presenceoptions = array(1 => "Her","Afw. Mo","Afw. SE","Afw. Ex","Vrijst. 7","Vrijst. 8","Vrijst. 9","Vrijst. 10");
  $teacher = SA_loadquery("SELECT tid,firstname,lastname FROM teacher WHERE is_gone <> 'Y'");
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>Cijferlijst SE</title></head><body link=blue vlink=blue bgcolor=#FFFFE0>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_sop.css" title="style1">';
  
  // Testing posted values
  foreach($_POST AS $pix => $pvl)
  {
    echo("POST[". $pix. "]=". $pvl. "<BR>");
  }
  echo("<FORM METHOD=POST ACTION='form_Cijferlijst_SO.php'>");
  // Put the subject checkboxes
  echo("Vakken:<BR>");
  foreach($subjects['mid'] AS $sbix => $sbid)
    echo("<SPAN style='width: 200px; display: inline-block;'><INPUT TYPE=checkbox NAME=scb". $sbid. "> ". $subjects['shortname'][$sbix]. "</SPAN>");
  // Put the group checkboxes
  echo("<BR>Groepen:<BR>");
  foreach($groups['gid'] AS $gix => $gid)
  {
    echo("<SPAN style='width: 200px; display: inline-block;'><INPUT TYPE=checkbox NAME=gcb". $gid);
	//if(substr($groups['groupname'][$gix],0,1) == "4")
	//  echo(" CHECKED");
	echo("> ". $groups['groupname'][$gix]. "</SPAN>");
  }
  // Put the test checkboxes
  $tests = SA_loadquery("SELECT short_desc FROM testdef WHERE type LIKE 'SE%' AND year='". $schoolyear. "' GROUP BY short_desc ORDER BY short_desc");
  if(isset($tests['short_desc']))
  {
    echo("<BR>SE:<BR>");
    foreach($tests['short_desc'] AS $tsn)
	{
	  echo("<SPAN style='width: 200px; display: inline-block;'><INPUT type=checkbox NAME='tcb". $tsn. "'>". $tsn. "</SPAN>");
	}
  }
  else
    echo("<BR>Geen SE toetsen gedefinieerd!<BR>");
  echo("<BR>Afwezigheid en vrijstelling:<BR>");
  foreach($presenceoptions AS $pix => $ptxt)
  {
    echo("<SPAN style='width: 200px; display: inline-block;'><INPUT TYPE=checkbox NAME=pcb". $pix);
	echo("> ". $ptxt. "</SPAN>");
  }
  echo("<BR>");
  echo("Examennummers van <INPUT TYPE=TEXT SIZE=10 NAME=exafrom VALUE=1> t/m <INPUT TYPE=TEXT SIZE=10 NAME=exato VALUE=99999><BR>");
  echo("Corrector: <SELECT NAME=corr ID=corr><OPTION VALUE=0> </OPTION>");
  foreach($teacher['tid'] AS $tix => $tid)
   echo("<OPTION VALUE=". $tid. ">". $teacher['firstname'][$tix]. " ". $teacher['lastname'][$tix]. "</OPTION>");
  echo("</SELECT><BR>");
  echo("Datum: <INPUT TYPE=TEXT NAME=tdate ID=tdate SIZE=10><BR>");

  echo("<INPUT TYPE=SUBMIT NAME='Print' VALUE='Afdrukken'>");
  echo("</FORM>");
  echo("</html>");
  
}
?>
