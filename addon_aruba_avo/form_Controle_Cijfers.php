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
  
  function print_head($subjectindex,$gid)
  {
    global $schoolyear,$schoolname,$subjects,$subtotal,$studs, $packages, $pexdata;
		$subjecttext = $subjects['fullname'][$subjectindex];
    echo("<div class=do>Cijferlijst voor controle</div>");
 	  // Period depends on period being selected.
	  if(isset($_POST['pcb1']))
	    echo(" <div class=ur>Trimester 1</div>");
	  else if(isset($_POST['pcb2']))
	    echo(" <div class=ur>Trimester 2</div>");
	  else if(isset($_POST['pcb3']))
	    echo(" <div class=ur>Trimester 3</div>");
	  else
	    echo(" <div class=ur>Eindcijfer</div>");
    echo("<BR>Voor het vak <u><b>". $subjecttext. "</b></u>");
		// We do need to show tacher, group
	  // First get the teacher name
	  $mid = $subjects['mid'][$subjectindex];
	  $tnqr = SA_loadquery("SELECT firstname,lastname FROM class LEFT JOIN teacher USING(tid) WHERE mid=". $mid. " AND gid=". $gid);
	  echo(" Docent: <u><b>". $tnqr['firstname'][1]. " ". $tnqr['lastname'][1]. "</b></u>");
	  // Get the group name
	  $gnqr = SA_loadquery("SELECT groupname FROM sgroup WHERE active=1 AND gid=". $gid);
	  echo(" Groep: <u><b>". $gnqr['groupname'][1]. "</b></u>");
	
		echo("</p>");
    echo("<p>Schooljaar <u>". $schoolyear. "</u></p>");
    echo("<p>Naam van de school: <u>". $schoolname. "</u></p>");
    echo("<table class=studlist><TR><TH class=studhead2 COLSPAN=2>Naam (in alfabetische volgorde)</TH>");
		echo("<TH class=tabhead ROWSPAN=2>Cijfer</TH>");
    echo("</TR><TR><TH class=studheadln>Achternaam</TH><TH class=studheadfn>Voornamen</TH></TR>");
  }
  
  function print_foot()
  {
    // add the rows in the table for signatures
	echo("<TR><TD class=signrow colspan=3>Handtekening : ");
	echo("</TD></TR>");
	
	echo("</TABLE>");
    // Footing
    echo("<div class=zoz>&nbsp;</div>");
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
  $subjfilt = "";
  foreach($_POST AS $pkey => $pval)
    if(substr($pkey,0,3) == "scb")
			$subjfilt .=  "subject.mid=". substr($pkey,3). " OR ";
  if($subjfilt == "")
    $subjfilt = "1=1 OR ";
  $subjects = SA_loadquery("SELECT shortname,mid,fullname FROM subject WHERE (". substr($subjfilt,0,-4). ") ORDER BY mid");
      
  // Get results
	if(isset($_POST['pcb1']))
		$period=1;
	else if(isset($_POST['pcb2']))
		$period=2;
	else if(isset($_POST['pcb3']))
		$period=3;
	else
		$period=0;
  $cquery = "SELECT sid,mid,result FROM gradestore WHERE period=". $period. " AND year=\"". $schoolyear. "\"";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
  {
    foreach($cres['sid'] AS $cix => $csid)
		{
			$resarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
		}
  }
  $groups = SA_loadquery("SELECT groupname,gid FROM sgroup WHERE active=1 ORDER BY groupname");
	
  // First part of the page
if(isset($_POST['Print']))
{ 
  echo("<html><head><title>Controle cijfers</title></head><body link=blue vlink=blue bgcolor=#E0E0FF>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX1-M.css" title="style1">';
  if(isset($subjects['fullname']))
  foreach($subjects['shortname'] AS $subjix => $sn)
  {
		foreach($groups['gid'] AS $gid)
		{ // Only produce output if groups and subject are selected and have a class
			$classqr = SA_loadquery("SELECT cid FROM `class` WHERE gid=". $gid. " AND mid=". $subjects['mid'][$subjix]);
			if(isset($classqr['cid']) && isset($_POST["gcb". $gid]))
			{
				print_head($subjix,$gid);
				
				// Get our list of students
				$studs = SA_loadquery("SELECT sid,lastname,firstname FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid);

				$linecount = 0;
			
				// Student listing
				foreach($studs['sid'] AS $six => $sid)
				{
					if($linecount > 30)
					{
						print_foot();
						$linecount = 0;
						print_head($subjix,$gid);
					}
					$mid = $subjects['mid'][$subjix];
					if(1==1)
					{
						echo("<TR class=manualfill>");
						echo("<TD class=studname>". $studs['lastname'][$six]. "</TD><TD class=studname>". $studs['firstname'][$six]. "</TD>");
						// Show result
						echo("<TD class=result>");
							if(isset($resarray[$studs['sid'][$six]][$mid]) && $resarray[$studs['sid'][$six]][$mid] > 0.9)
							echo(number_format($resarray[$studs['sid'][$six]][$mid],1,",","."));
						else
								echo("X");
						echo("</TD>");
						// Show End result
					
							echo("</TR>");
						$linecount++;	
					} // End if student has package
				} // End loop for each student
			
				print_foot();
			}
		}
  } // End loop for each subject
}
else
{ // Show selection options
  echo("<html><head><title>Controle cijfers</title></head><body link=blue vlink=blue bgcolor=#E0E0E0>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_sop.css" title="style1">';

  $teacher = SA_loadquery("SELECT tid,firstname,lastname FROM teacher WHERE is_gone <> 'Y'");
  SA_closeDB();
  
  // Testing posted values
  foreach($_POST AS $pix => $pvl)
  {
    echo("POST[". $pix. "]=". $pvl. "<BR>");
  }
  echo("<FORM METHOD=POST>");
  // Put the subject checkboxes
  echo("Vakken:<BR>");
  foreach($subjects['mid'] AS $sbix => $sbid)
    echo("<SPAN style='width: 200px; display: inline-block'><INPUT TYPE=checkbox NAME=scb". $sbid. "> ". $subjects['shortname'][$sbix]. "</SPAN>");
  // Put the group checkboxes
  echo("<BR>Groepen:<BR>");
  foreach($groups['gid'] AS $gix => $gid)
  {
    echo("<SPAN style='width: 200px; display: inline-block'><INPUT TYPE=checkbox NAME=gcb". $gid);
	//if(substr($groups['groupname'][$gix],0,1) == "4")
	//  echo(" CHECKED");
	echo("> ". $groups['groupname'][$gix]. "</SPAN>");
  }
  echo("<BR>Trimester:<BR>");
  for($period=1; $period < 4; $period++)
  {
    echo("<SPAN style='width: 200px; display: inline-block'><INPUT TYPE=checkbox NAME=pcb". $period);
	echo("> Trimester ". $period. "</SPAN>");
  }
  echo("<BR>");
   echo("<INPUT TYPE=SUBMIT NAME='Print' VALUE='Afdrukken'>");
  echo("</FORM>");
}
  // close the page
  echo("</body></html>");
?>
