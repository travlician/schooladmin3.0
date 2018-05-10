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
    
  $offsubjects = array(1 => "Ne","En","Sp","Wa","Wb","Na","Sk","Bi","Mo","Ec","Gs","Ak","CKV","Fa");
  $altsubjects = array("Ne"=>1,"En"=>2,"Sp"=>3,"Wi-A"=>4,"Wi-B"=>5,"Na"=>6,"Sk"=>7,"Bio"=>8,"Ec"=>10,"M&O"=>9,"Ak"=>12,"Gs"=>11,"Pfw"=>14,"CKV"=>13,
                       "ne"=>1,"en"=>2,"sp"=>3,"wiA"=>4,"wiB"=>5,"na"=>6,"sk"=>7,"bio"=>8,"ec"=>10,"m&o"=>9,"ak"=>12,"gs"=>11,"pws"=>14,"ckv"=>13,"Fa"=>14,
											 "skBB"=>7,"naBB"=>6);
	$sub2full = array("Ne"=>"Nederlandse taal en literatuur", "En"=>"Engelse taal en literatuur", "Wa"=>"Wiskunde A",
                    "Ak"=>"Aardrijkskunde", "Gs"=>"Geschiedenis en staatsinrichting", "Sp"=>"Spaanse taal en literatuur",
										"Ec"=>"Economie", "Mo"=>"Management en organisatie", "Sk"=>"Scheikunde", "Na"=>"Natuurkunde",
										"Wb"=>"Wiskunde B", "Bi"=>"Biologie","CKV"=>"Culturele en kunstzinnige vorming");
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
  
  function print_head()
  {
    global $schoolyear,$schoolname,$subjects,$subtotal;
		global $issum,$iscnt,$isfsum,$isfcnt,$pfwsum,$pfwcnt,$pfwfsum,$pfwfcnt;
    echo("<div class=do>DIRECTIE ONDERWIJS ARUBA</div>");
    echo("<div class=ur>I&S en Profielwerkstuk gegevens</div>");
    //echo("<p>Genummerde alfabetische naamlijst van de kandidaten (art. 23 Landsbesluit eindexamens dagscholen, AB 1991 No. GT 35).");
    //echo("<BR>Inzenden voor 1 oktober (1 exemplaar).</p>");
    echo("<p>EINDEXAMEN <b>HAVO</b>, in het schooljaar ". $schoolyear. "</p>");
    echo("<p>School: ". $schoolname. "</p>");
    echo("<table class=studlist><TR><TH ROWSPAN=2 class=exnrhead>Ex.<BR>nr.</TH><TH class=studhead ROWSPAN=2>Achternaam en voornamen van de kandidaat<BR>(in alfabetische volgorde)</TH>");
    echo("<th COLSPAN=2 class=IS>Individu &<BR>Samenleving (I&S)</TH><TH COLSPAN=5 class=PFW>Profielwerkstuk</TH></TR>");
		echo("<TR><TH class=IS>Cijfer</th><th class=IS>Eind<BR>cijfer</th><TH class=PFW>PWS<BR>cijfer</th><th class=PFW>PWS<BR>Eindcijfer</th><TH class=PFW>Docent</TH><TH class=PFW>Vak</TH><TH class=PFW>Titel van het profielwerkstuk</TH></TR>");
		$issum=0.0;
		$iscnt=0.0;
		$pfwsum=0.0;
		$pfwcnt=0.0;
		$isfsum=0.0;
		$isfcnt=0.0;
		$pfwfsum=0.0;
		$pfwfcnt=0.0;
  }

  function print_foot()
  {
    global $subjects,$subtotal;
		global $issum,$iscnt,$isfsum,$isfcnt,$pfwsum,$pfwcnt,$pfwfsum,$pfwfcnt;
    // Show the total of students per subject
    echo("<TR><TD class=nolines>&nbsp</TD><TD class=total>Gemiddelde</TD>");
		if($iscnt > 0)
			echo("<td class=subjind>". number_format(round($issum / $iscnt,2),2,",","."). "</td><td class=ISEc>". number_format(round($isfsum / $isfcnt,2),2,",","."). "</td>");
		else
			echo("<td class=subjind>&nbsp;</td><td class=ISEc>&nbsp;</td>");
		if($pfwcnt > 0)
			echo("<td class=subjind>". number_format(round($pfwsum / $pfwcnt,2),2,",","."). "</td><td class=PFWEc>". number_format(round($pfwfsum / $pfwfcnt,2),2,",","."). "</td>");
		else
			echo("<td class=subjind>&nbsp;</td><td class=PFWEc>&nbsp;</td>");
    echo("</TR>");
    // Show the subjects again
    echo("<TR><TD class=nolines>&nbsp</TD><TD class=nolines>&nbsp</TD>");
    echo("</TR></TABLE>");
    // Footing
    echo("<div class=sign>..........................., .......................");
    echo("<BR>Plaats&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Datum");
    echo("<BR><BR><BR><BR>. . . . . . . . . . . . . . . . . . . . .");
    echo("<BR>(Handtekening directeur)</div>");
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
	// Create a view with teacher names
	mysql_query("CREATE OR REPLACE VIEW teachersel AS SELECT 0 AS id, '' AS tekst UNION SELECT tid,CONCAT(firstname,' ',lastname) FROM teacher",$userlink);
  
  // Get a list of students with PFW/PWS subject and title
  $squery = "SELECT lastname,firstname,sid,s_exnr.data AS exnr,s_pfwvak.data AS pfwvak,s_pfwtitel.data AS pfwtitle, s_pfwteacher.data AS pfwteacher FROM student";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_pfwvak USING(sid) LEFT JOIN s_pfwtitel USING(sid) LEFT JOIN s_pfwteacher USING(sid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND s_exnr.data IS NOT NULL AND s_exnr.data > '0'";
  $squery .= " AND groupname='ExamHavo' ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
	
	// Get a list of teachers based on tid
	$teachersqr = SA_loadquery("SELECT tid,CONCAT(firstname,' ',lastname) AS tname FROM teacher");
	foreach($teachersqr['tid'] AS $tix => $tid)
		$teachername[$tid] = $teachersqr['tname'][$tix];
  
  // Get the "vrijstelling" info
  $freest = SA_loadquery("SELECT * FROM ex45data WHERE xstatus>=5 AND year='". $schoolyear. "'");
  if(isset($freest))
    foreach($freest['sid'] AS $fix => $sid)
			$frees[$sid][$freest['mid'][$fix]] = $freest['xstatus'][$fix];
  // Also added to "vrijstelling" info is aquired results for I&S and PFW
  $freest = SA_loadquery("SELECT * FROM ahxdata WHERE mid<>0 AND year='". $schoolyear. "'");
  if(isset($freest))
    foreach($freest['sid'] AS $fix => $sid)
			$frees[$sid][$freest['mid'][$fix]] = $freest['xstatus'][$fix] + 13.0;

  // Get the I&S info gotten this year
  $ismidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname ='I&S'");
  if(isset($ismidqr['mid']))
    $ismid = $ismidqr['mid'][1];
  else
    $ismid = 0;
  $isresqr = SA_loadquery("SELECT sid,result FROM gradestore WHERE mid=". $ismid. " AND period=0 AND result > 0 ORDER BY year");
  if(isset($isresqr['sid']))
    foreach($isresqr['sid'] AS $lix => $lsid)
      $isres[$lsid] = $isresqr['result'][$lix];

  // Get the PFW info gotten this year
  $pfwmidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname ='Pfw' OR shortname LIKE 'pws'");
  if(isset($pfwmidqr['mid']))
    $pfwmid = $pfwmidqr['mid'][1];
  else
    $pfwmid = 0;
  $pfwresqr = SA_loadquery("SELECT sid,result FROM gradestore WHERE mid=". $pfwmid. " AND year='". $schoolyear. "' AND period=0 AND result > 0");
  if(isset($pfwresqr['sid']))
    foreach($pfwresqr['sid'] AS $lix => $lsid)
      $pfwres[$lsid] = $pfwresqr['result'][$lix];

  // Convert I&S and PFW results and calculate result combivak
  $cres = SA_loadquery("SELECT sid,mid,xstatus FROM ahxdata WHERE xstatus>0 AND year='". $schoolyear. "'");
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
		{
			$ahxdata[$csid][$cres['mid'][$cix]] = $cres['xstatus'][$cix];
		}
  foreach($studs['sid'] AS $ipsid)
  {
    if(!isset($isres[$ipsid]) && isset($ahxdata[$ipsid][$ismid]) && $ahxdata[$ipsid][$ismid] > 0)
			$isres[$ipsid] = $ahxdata[$ipsid][$ismid];
    if(!isset($pfwres[$ipsid]) && isset($ahxdata[$ipsid][$pfwmid]) && $ahxdata[$ipsid][$pfwmid] > 0)
			$pfwres[$ipsid] = $ahxdata[$ipsid][$pfwmid];
  }
	
	// Create tanslation of subjects
  // Get a list of subjects applicable to the exam subjects
  $subjectsqr = SA_loadquery("SELECT shortname,subjectpackage.mid FROM subjectpackage LEFT JOIN subject USING(mid) GROUP BY mid ORDER BY mid");

  // Get subject based on defined standard using translation tables as defined at start of this file.
  foreach($offsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
		{
			if(isset($altsubjects[$subsn]) && $altsubjects[$subsn] == $osix)
			{
				$subjects['shortname'][$osix] = $sjname;
				$subjects['mid'][$osix] = $subjectsqr['mid'][$sbix];
				$mids[$sjname] = $subjectsqr['mid'][$sbix];
				$mid2sjname[$subjectsqr['mid'][$sbix]] = $sjname;
			}
		}
  }

  // First part of the page
  // echo("<html><head><title>Formulier IS en Profielwerkstuk gegevens</title></head><body link=blue vlink=blue bgcolor=#E0FFE0 onload=\"window.print();setTimeout('window.close();',10000);\">");
  echo("<html><head><title>Formulier IS en Profielwerkstuk gegevens</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX_IS_PWS.css" title="style1">';
  
  print_head();

  $linecount = 0;
  
  // Student listing
  if(isset($studs['sid']))
    foreach($studs['sid'] AS $six => $sid)
    {
			unset($pfwvak);
      if($linecount > 29)
			{
					print_foot();
					$linecount = 0;
					print_head();
			}
      echo("<TR><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
			echo("<TD class=studname><b>". $studs['lastname'][$six]. "</b> ". $studs['firstname'][$six]. "</TD>");
			if(isset($isres[$sid]))
			{
				echo("<TD class=subjind>". number_format($isres[$sid],1,",","."). "</td><TD class=ISEc>". round($isres[$sid]). "</td>");
				$issum += $isres[$sid];
				$iscnt++;
				$isfsum += round($isres[$sid]);
				$isfcnt++;
			}
			else
				echo("<TD class=subjind>-</TD><TD class=ISEc>-</td>");
			if(isset($pfwres[$sid]))
			{
				echo("<TD class=subjind>". number_format($pfwres[$sid],1,",","."). "</td><TD class=PFWEc>". round($pfwres[$sid]). "</td>");
				$pfwsum += $pfwres[$sid];
				$pfwcnt++;
				$pfwfsum += round($pfwres[$sid]);
				$pfwfcnt++;
			}
			else
				echo("<TD class=subjind>-</TD><TD class=PFWEc>-</td>");
			// Teacher name, derived from subject... So get subject first.
			$subject = $studs['pfwvak'][$six];
			foreach($sub2full AS $vks => $vkf)
				if($vkf == $studs['pfwvak'][$six])
					$pfwvak = $vks;

			// If we got a subject, it is the official naming, lets's see if there is a mid for that
			if(isset($pfwvak) && isset($mids[$pfwvak]))
			{ // Ok, we got the mid, now see if we can find a teacher belonging to the mid and the student groupname
				if(isset($studs['pfwteacher'][$six]) && $studs['pfwteacher'][$six] != "") // If defined as field for student use that
					$tname = $teachername[$studs['pfwteacher'][$six]];
				else // Try to get it based on the subject and student's group membership
				{
					$tnqr = SA_loadquery("SELECT firstname,lastname FROM teacher LEFT JOIN class USING(tid) LEFT JOIN sgrouplink USING(gid) WHERE mid=". $mids[$pfwvak]. " AND sid=". $studs['sid'][$six]);
					if(isset($tnqr['firstname']))
						$tname = $tnqr['firstname'][1]. " ". $tnqr['lastname'][1];
					else // No solucion, damn, just don't show teacher name
						$tname = "&nbsp;";
				}
				echo("<td class=studname>". $tname. "</td>");
			}
			else // No teacher found
				echo("<td class=studname>&nbsp;</td>");
			
			if(isset($pfwvak))
				echo("<TD class=subjind>". $pfwvak. "</td>");
			else
				echo("<TD class=subjind>-</TD>");
			if(isset($studs['pfwtitle'][$six]) && $studs['pfwtitle'][$six] != "")
				echo("<td class=studname>". $studs['pfwtitle'][$six]. "</td>");
			else
				echo("<td class=studname>&nbsp;</td>");
				
			echo("</TR>");
			$linecount++;	
	  }
 
  print_foot();
  // close the page
  echo("</html>");
?>
