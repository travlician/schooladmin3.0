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
  $noexam = array("Ak","ak","Inf");
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
  
  function print_head($subjectindex)
  {
			global $schoolyear,$schoolname,$subjects,$subtotal,$studs, $packages, $pexdata;
		$subjecttext = $subjects['fullname'][$subjectindex];
			echo("<div class=do>DIRECTIE ONDERWIJS ARUBA</div>");
			echo("<div class=ur>EX. 2a</div>");
			// Tijdvak 1 or 2, depends on "herexamen" or "Afw, Examen" being selected.
			if(isset($_POST['pcb1']) || isset($_POST['pcb4']))
				echo(" <div class=ur>TV2</div>");
			else
				echo(" <div class=ur>TV1</div>");
		 echo("<p style='margin-top:-15px;'>LIJST VAN CIJFERS, bedoeld in de artikelen 21, lid 1, 29, lid 3 en 67, lid 4 van het Landsbesluit eindexamens dagscholen VWO, HAVO, MAVO,");
			echo("<BR> AB 1991 No. GT 35 voor het vak <u><b>". $subjecttext. "</b></u>");
		// Now if this list is for a single group, show teacher name, groupname and students without excemption
		$gcount = 0;
		foreach($_POST AS $pkey => $dummy)
		{
			if(substr($pkey,0,3) == "gcb")
			{
				$gcount++;
			$gid = substr($pkey,3);
			}
		}
		if($gcount == 1)
		{ // We do need to show teacher, group and #students!
			// First get the teacher name
			$mid = $subjects['mid'][$subjectindex];
			$tnqr = SA_loadquery("SELECT firstname,lastname FROM class LEFT JOIN teacher USING(tid) WHERE mid=". $mid. " AND gid=". $gid);
			echo(" Docent: <u><b>". $tnqr['firstname'][1]. " ". $tnqr['lastname'][1]. "</b></u>");
			// Get the group name
			$gnqr = SA_loadquery("SELECT groupname FROM sgroup WHERE active=1 AND groupname NOT LIKE '%VWO%' AND gid=". $gid);
			echo(" Groep: <u><b>". $gnqr['groupname'][1]. "</b></u>");
			// Get the student count
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
				if((isset($_POST['pcb1']) || isset($_POST['pcb2']) || isset($_POST['pcb3']) || isset($_POST['pcb4']) )&& 
					 (!isset($pexdata[$studs['sid'][$six]][$mid]) || $pexdata[$studs['sid'][$six]][$mid] == 0))
					$hassubject = 0;
				if(isset($pexdata[$studs['sid'][$six]][$mid]) && $pexdata[$studs['sid'][$six]][$mid] > 4)
					$hassubject = 0;
			if($hassubject != 0)
				$studcount++;
			}
			echo(" Aantal leerlingen: ". $studcount. "</p>");
		}
		echo("</p>");
			echo("<p>EINDEXAMEN HAVO/VWO, in het schooljaar <u>". $schoolyear. "</u></p>");
			echo("<p>Naam van de school: <u>". $schoolname. "</u></p>");
			echo("<table class=studlist><TR><TH class=exnrhead ROWSPAN=2>Ex.<BR>nr.</TH><TH class=studhead2 COLSPAN=2>Naam van de kandidaat (in alfabetische volgorde)</TH>");
		echo("<TH class=tabhead ROWSPAN=2>Cijfer schoolonderzoek<BR>(mond. ex.)</TH><TH class=tabhead ROWSPAN=2>Punten<BR>Schriftelijk<BR>examen</TH><TH class=tabhead ROWSPAN=2>Cijfers<BR>Schriftelijk<BR>examen</TH><TH class=tabhead ROWSPAN=2>Eindcijfer</TH>");
			echo("</TR><TR><TH class=studheadln>Achternaam</TH><TH class=studheadfn>Alle voornamen voluit</TH></TR>");
  }
  
  function print_foot()
  {
    // add the rows in the table for signatures
	echo("<TR><TD class=signrow colspan=3>A. Handtekening examinator: ");
	echo("</TD><TD class=arrows>&larr;&uarr;</TD><TD class=nobott colspan=2>&nbsp;</TD><TD class=nobott>&nbsp;</TD></TR>");
	echo("<TR><TD class=signrow colspan=4>B. Handtekening examinator: &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	echo(" gecommitteerde: &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	echo(" directeur:</TD><TD class=arrows colspan=2>&larr;&uarr;</TD><TD class=nobott>&nbsp;</TD></TR>");
	echo("<TR><TD class=signrow colspan=6>C. Handtekening examinator: &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	echo(" directeur:</TD><TD class=arrows>&larr;&uarr;</TD></TR>");
	
	echo("</TABLE>");
    // Footing
    echo("<div class=zoz>VOOR TOELICHTING Z.O.Z.&nbsp;</div>");
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
	  $subjfilt .=  "subjectpackage.mid=". substr($pkey,3). " OR ";
  if($subjfilt == "")
    $subjfilt = "1=1 OR ";
  $subjects = SA_loadquery("SELECT shortname,subjectpackage.mid,fullname FROM subjectpackage LEFT JOIN subject USING(mid) WHERE (". substr($subjfilt,0,-4). ") GROUP BY mid ORDER BY mid");
  
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage");
	// CKVEX issue: convert CKV mid to ckvex mid
	$ckvmidsqr = SA_loadquery("SELECT shortname,mid FROM subject WHERE shortname='ckv' OR shortname='ckvex'");
	if(isset($ckvmidsqr['mid']))
		foreach($ckvmidsqr['shortname'] AS $six => $ssn)
		{
			if($ssn == "ckv")
				$ockvmid = $ckvmidsqr['mid'][$six];
			if($ssn == "ckvex")
				$ockvexmid = $ckvmidsqr['mid'][$six];
		}
	// End of fix on ckv issue

  
  // Get a list of students with the subject package and extra subject
  $squery = "SELECT sid,lastname,firstname,gid,s_exnr.data AS exnr,packagename,extrasubject,extrasubject2,extrasubject3 FROM student LEFT JOIN sgrouplink USING(sid)";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_package USING(sid) WHERE s_exnr.data IS NOT NULL AND s_exnr.data > '0' ";
  // Add the filter for the groups
  $fstr = "";
  foreach($_POST AS $pix => $pvl)
  {
    if(substr($pix,0,3) == "gcb")
	  $fstr .= "gid=". substr($pix,3). " OR ";
  }
  if($fstr == "")
    $fstr = "1=1 OR ";
  $squery .= "AND (". substr($fstr,0,-4). ") ";
  // Add filter for the exam numbers
  if(isset($_POST['exafrom']) && $_POST['exafrom'] != '')
    $squery .= "AND s_exnr.data >= ". $_POST['exafrom']. " AND s_exnr.data <= ". $_POST['exato']. " ";
  $squery .= " GROUP BY sid ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
  // Get SO results
  $cquery = "SELECT sid,mid,result FROM gradestore WHERE period=2 AND year=\"". $schoolyear. "\"";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
  {
    foreach($cres['sid'] AS $cix => $csid)
	{
	  $soarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
	}
  }
  // Get Exam points results
  if(!isset($_POST['noexprint']))
  {
    // First get a list of test definitions for this subject with weight factor 0
		$pttdids = SA_loadquery("SELECT tdid FROM testdef LEFT JOIN reportcalc ON(type=testtype) WHERE period=3 AND year=\"". $schoolyear. "\" AND weight=0 ORDER BY date");
		if(isset($pttdids['tdid']))
			foreach($pttdids['tdid'] AS $atdid)
			{
				$cquery = "SELECT sid,mid,result FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) WHERE tdid=". $atdid;
				$cres = SA_loadquery($cquery);
				echo(mysql_error($userlink));
				if(isset($cres))
				{
					foreach($cres['sid'] AS $cix => $csid)
					{
						// Another part of fixing the ckvex issue
						if(isset($ockvmid) && isset($ockvexmid))
							if($cres['mid'][$cix] == $ockvexmid)
								$cres['mid'][$cix] = $ockvmid;
						$exptarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
					}
				}
			}
  }
  
  // Get Exam results
  if(!isset($_POST['noexprint']))
  {
	  if(isset($_POST['pcb1']) || isset($_POST['pcb4']))
		{ // TV2, so we get the latest results
			// First get a list of test definitions for this subject with weight factor is NOT 0
			$pttdids = SA_loadquery("SELECT tdid FROM testdef LEFT JOIN reportcalc ON(type=testtype) WHERE period=3 AND year=\"". $schoolyear. "\" AND weight<>0 ORDER BY date");
			if(isset($pttdids['tdid']))
				foreach($pttdids['tdid'] AS $atdid)
				{
					$cquery = "SELECT sid,mid,result FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) WHERE tdid=". $atdid;
					$cres = SA_loadquery($cquery);
					echo(mysql_error($userlink));
					if(isset($cres))
					{
						foreach($cres['sid'] AS $cix => $csid)
						{
							$exarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
						}
					}
				}
			
		}
		else
		{ // Not TV2 so get the counting results
			$cquery = "SELECT sid,mid,result FROM gradestore WHERE period=3 AND year=\"". $schoolyear. "\"";
			$cres = SA_loadquery($cquery);
			echo(mysql_error($userlink));
			if(isset($cres))
			{
				foreach($cres['sid'] AS $cix => $csid)
				{
				 $exarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
				}
			}
		}
  }
  // Get End results
  $cquery = "SELECT sid,mid,AVG(result) AS aresult FROM gradestore WHERE period>1 AND year=\"". $schoolyear. "\" GROUP BY sid,mid";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
  {
    foreach($cres['sid'] AS $cix => $csid)
	{
	  $endarray[$csid][$cres['mid'][$cix]] = $cres['aresult'][$cix];
	}
  }
  // Get "Vrijstellingen"
  $vrqr = SA_loadquery("SELECT sid,mid,xstatus FROM ex45data WHERE year='". $schoolyear. "' AND xstatus > 4 AND xstatus < 9");
  if(isset($vrqr['sid']))
    foreach($vrqr['sid'] AS $vix => $vsid)
	  $vr[$vsid][$vrqr['mid'][$vix]] = $vrqr['xstatus'][$vix] + 2;
  // Get "Certificaten"
  $crqr = SA_loadquery("SELECT sid,mid,xstatus FROM ex45data WHERE year='". $schoolyear. "' AND xstatus > 8 AND xstatus < 14");
  if(isset($crqr['sid']))
    foreach($crqr['sid'] AS $vix => $vsid)
	  $cr[$vsid][$crqr['mid'][$vix]] = $crqr['xstatus'][$vix] - 3;
  // Get "behaald"
  $beqr = SA_loadquery("SELECT sid,mid,xstatus FROM ex45data WHERE year='". $schoolyear. "' AND xstatus > 13");
  if(isset($beqr['sid']))
    foreach($beqr['sid'] AS $vix => $vsid)
	  $beh[$vsid][$beqr['mid'][$vix]] = $beqr['xstatus'][$vix] - 13;
  $groups = SA_loadquery("SELECT groupname,gid FROM sgroup WHERE active=1 AND groupname NOT LIKE '%vwo%' ORDER BY groupname");
  // Get the list of presence and exemptions 
  $pedata = SA_loadquery("SELECT sid,mid,xstatus FROM ex45data WHERE year='". $schoolyear. "'");
  if(isset($pedata['sid']))
    foreach($pedata['sid'] AS $peix => $sid)
	  $pexdata[$sid][$pedata['mid'][$peix]] = $pedata['xstatus'][$peix]; 


  // First part of the page
if(isset($_POST['Print']))
{ 
  echo("<html><head><title>Formulier EX. 2a-HV</title></head><body link=blue vlink=blue bgcolor=#E0E0FF>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX1-M.css" title="style1">';
  if(isset($subjects['fullname']))
  foreach($subjects['shortname'] AS $subjix => $sn)
  {
    print_head($subjix);

    $linecount = 0;
  
    // Student listing
    foreach($studs['gid'] AS $six => $gid)
    {
      if($linecount > 30)
	  {
        print_foot();
	    $linecount = 0;
	    print_head($subjix);
	  }
	  $mid = $subjects['mid'][$subjix];
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
	  if((isset($_POST['pcb1']) || isset($_POST['pcb2']) || isset($_POST['pcb3']) || isset($_POST['pcb4']) )&& 
	     (!isset($pexdata[$studs['sid'][$six]][$mid]) || $pexdata[$studs['sid'][$six]][$mid] == 0))
	    $hassubject = 0;
	  if(isset($pexdata[$studs['sid'][$six]][$mid]) && $pexdata[$studs['sid'][$six]][$mid] != 0 && !isset($_POST["pcb". $pexdata[$studs['sid'][$six]][$mid]]))
	    $hassubject = 0;
      if($hassubject != 0)
	  {
        echo("<TR class=manualfill><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
        echo("<TD class=studname>". $studs['lastname'][$six]. "</TD><TD class=studname>". $studs['firstname'][$six]. "</TD>");
	    // Show SO result
	    echo("<TD class=result>");
        if(isset($soarray[$studs['sid'][$six]][$mid]) && $soarray[$studs['sid'][$six]][$mid] > 0.9)
	      echo(number_format($soarray[$studs['sid'][$six]][$mid],1,",","."));
	    else if(isset($vr[$studs['sid'][$six]][$mid]) || isset($cr[$studs['sid'][$six]][$mid]) || isset($beh[$studs['sid'][$six]][$mid]))
	      echo("&nbsp");
	    else
  	      echo("X");
        echo("</TD>");
	    // Now follow two blank columns
	    //echo("<TD class=result>&nbsp</TD><TD class=result>&nbsp</TD>");
	    // Show exam result
	    echo("<TD class=result>");
        if(isset($exptarray[$studs['sid'][$six]][$mid]))
	      echo($exptarray[$studs['sid'][$six]][$mid]);
	    else
	      echo("&nbsp");
        echo("</TD>");
	    echo("<TD class=result>");
        if(isset($exarray[$studs['sid'][$six]][$mid]))
	      echo(number_format($exarray[$studs['sid'][$six]][$mid],1,",","."));
	    else if(isset($vr[$studs['sid'][$six]][$mid]) || isset($cr[$studs['sid'][$six]][$mid]) || isset($beh[$studs['sid'][$six]][$mid]))
	      echo("&nbsp");
	    else if(isset($_POST['noexprint']))
  	      echo("&nbsp;");
		else if(in_array($subjects['shortname'][$subjix],$noexam))
		{
		  echo("nvt");
		  // Pretend to have an exam result for no exam subjects
		  $exarray[$studs['sid'][$six]][$mid] = $soarray[$studs['sid'][$six]][$mid];
		}
	    else
  	      echo("X");
        echo("</TD>");
	    // Show End result
	    echo("<TD class=result>");
        if(isset($endarray[$studs['sid'][$six]][$mid]) && $endarray[$studs['sid'][$six]][$mid] > 0.9 && isset($exarray[$studs['sid'][$six]][$mid]))
	      echo(number_format($endarray[$studs['sid'][$six]][$mid],0,",","."));
	    else if(isset($vr[$studs['sid'][$six]][$mid]))
	      echo("V". $vr[$studs['sid'][$six]][$mid]);
	    else if(isset($cr[$studs['sid'][$six]][$mid]))
	      echo("C". $cr[$studs['sid'][$six]][$mid]);
	    else if(isset($beh[$studs['sid'][$six]][$mid]))
	      echo("B". $beh[$studs['sid'][$six]][$mid]);
	    else if(isset($_POST['noexprint']))
  	      echo("&nbsp;");
	    else
  	      echo("X");
        echo("</TD>");
	  
        echo("</TR>");
	    $linecount++;	
	  } // End if student has package
    } // End loop for each student
  
    print_foot();
  } // End loop for each subject
}
else
{ // Show selection options
  echo("<html><head><title>Formulier EX. 2a-HV</title></head><body link=blue vlink=blue bgcolor=#E0E0FF>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_sop.css" title="style1">';

  $presenceoptions = array(1 => "Herexamen","Afw. Mo","Afw. SO","Afw. Ex","Vrijst. 7","Vrijst. 8","Vrijst. 9","Vrijst. 10","Cert. 6","Cert. 7","Cert. 8","Cert. 9","Cert. 10");
  $teacher = SA_loadquery("SELECT tid,firstname,lastname FROM teacher WHERE is_gone <> 'Y'");
  SA_closeDB();
  
  // Testing posted values
  foreach($_POST AS $pix => $pvl)
  {
    echo("POST[". $pix. "]=". $pvl. "<BR>");
  }
  echo("<FORM METHOD=POST ACTION='form_EX2a-HV.php'>");
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
  echo("<BR>Afwezigheid en vrijstelling:<BR>");
  foreach($presenceoptions AS $pix => $ptxt)
  {
    echo("<SPAN style='width: 200px; display: inline-block'><INPUT TYPE=checkbox NAME=pcb". $pix);
	echo("> ". $ptxt. "</SPAN>");
  }
  echo("<BR>");
  echo("Examennummers van <INPUT TYPE=TEXT SIZE=10 NAME=exafrom VALUE=''> t/m <INPUT TYPE=TEXT SIZE=10 NAME=exato VALUE=99999><BR>");
  echo("Geen examencijfers afdrukken <INPUT TYPE=CHECKBOX NAME=noexprint><BR>");
  echo("<INPUT TYPE=SUBMIT NAME='Print' VALUE='Afdrukken'>");
  echo("</FORM>");
}
  // close the page
  echo("</html>");
?>
