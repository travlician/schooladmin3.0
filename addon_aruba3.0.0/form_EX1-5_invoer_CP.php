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
  //error_reporting(E_STRICT);
  require_once("inputlib/inputclasses.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
 
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","I&S","Sp","Pa","Wi-A","Wi-B","Na","Sk","Bio","Ec","M&O","Ak","Gs","CKV","Fa");
  $altsubjects = array("Ne"=>1,"En"=>2,"I&S"=>3,"Sp"=>4,"Wi-A"=>6,"Wi-B"=>7,"Na"=>8,"Sk"=>9,"Bio"=>10,"Ec"=>11,"M&O"=>12,"Ak"=>13,"Gs"=>14,"Pfw"=>16,"CKV"=>15,
                       "ne"=>1,"en"=>2,"i&s"=>3,"sp"=>4,"pap"=>5,"wiA"=>6,"wiB"=>7,"na"=>8,"naBB"=>8,"sk"=>9,"skBB"=>9,"bio"=>10,"ec"=>11,"m&o"=>12,"ak"=>13,"gs"=>14,"ckv"=>15);

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
  $sqlquery = "CREATE TABLE IF NOT EXISTS `ex45data` (
    `ex45id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `mid` INTEGER(11) DEFAULT NULL,
    `xstatus` INTEGER(11) DEFAULT NULL,
		`year` TEXT,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`ex45id`),
    UNIQUE KEY `sidmid` (`sid`, `mid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `examresult` (
    `xid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `xresult` TEXT DEFAULT NULL,
	`ckvres` INTEGER(1) DEFAULT 0,
	`year` TEXT,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`xid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `ahxdata` (
    `ahxid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `mid` INTEGER(11) DEFAULT NULL,
    `xstatus` TEXT DEFAULT NULL,
		`year` varchar(20),
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`ahxid`),
    UNIQUE KEY `sidmid` (`sid`, `mid`, `year`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  // We need to get the year for entry!
  $curyearqr = SA_loadquery("SELECT year FROM period WHERE id=3");
  $curyear = $curyearqr['year'][1];
  
  // The states are predefined, here is the query to get that list (actually no DB access but the library works that way...)
  $statquery = "SELECT 0 AS id, '' AS tekst UNION SELECT 1 AS id,'Her' AS tekst UNION SELECT 2,'Afw. Mo' UNION SELECT 3,'Afw. SO' UNION SELECT 4,'Afw. Ex' 
                UNION SELECT 5,'Vrijst. 7' UNION SELECT 6,'Vrijst. 8' UNION SELECT 7,'Vrijst. 9' UNION SELECT 8,'Vrijst. 10'
                UNION SELECT 9,'Cert. 6' UNION SELECT 10,'Cert. 7' UNION SELECT 11,'Cert. 8' UNION SELECT 12,'Cert. 9' UNION SELECT 13,'Cert. 10'";
  
  // Get a list of all applicable students
  $students = SA_loadquery("SELECT CONCAT(lastname,', ',firstname) AS name,sid,packagename,extrasubject,extrasubject2,extrasubject3,s_exnr.data AS exnr, groupname FROM student LEFT JOIN s_package USING(sid) LEFT JOIN s_exnr USING(sid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND s_exnr.data IS NOT NULL AND groupname LIKE 'Exam%' GROUP BY sid ORDER BY groupname,s_exnr.data");

  // Get a list of applicable subjects
  $subjectsqr = SA_loadquery("SELECT shortname,subjectpackage.mid FROM subjectpackage LEFT JOIN subject USING(mid) GROUP BY mid ORDER BY mid");
  foreach($offsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
		{
			if(isset($altsubjects[$subsn]) && $altsubjects[$subsn] == $osix)
			{
				$subjects['shortname'][$osix] = $sjname;
				$subjects['mid'][$osix] = $subjectsqr['mid'][$sbix];
			}
		}
  }
 
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage");

  // See if we need to extract results from previous year
  if(isset($_GET['extractfromprevyear']))
  { // Need to extract results from previous year!
    $years = SA_loadquery("SELECT DISTINCT year FROM gradestore ORDER BY year DESC");
		if(isset($years['year'][2]))
		{ // The previous year exists (very important...)
			$prevyear = $years['year'][2];
			$pyrq = "SELECT sid,mid,result FROM gradestore LEFT JOIN sgrouplink USING (sid) LEFT JOIN sgroup USING(gid) ";
			$pyrq .= "WHERE active=1 AND year='". $prevyear. "' AND period=0 AND groupname LIKE '4%' GROUP BY sid,mid";
			$pygrades = SA_loadquery($pyrq);
			foreach($pygrades['sid'] AS $pyrix => $pysid)
			{
				$pyres[$pysid][$pygrades['mid'][$pyrix]] = $pygrades['result'][$pyrix];
			if(isset($pyrescount[$pysid]))
				$pyrescount[$pysid]++;
			else
				$pyrescount[$pysid] = 1;
			}
		}
  }
  
  // Find out which mid belongs to I&S and Pfw and CKV and LO
  $ismidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'IS%' OR shortname LIKE 'Is%' OR shortname LIKE 'I&S%'");
  if(isset($ismidqr['mid'][1]))
    $ismid = $ismidqr['mid'][1];
  else
    $ismid = 0;
  $pfwmidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'Pfw%' OR shortname LIKE 'PFW%' OR shortname LIKE 'pfw%' OR shortname LIKE 'pws'");
  if(isset($pfwmidqr['mid'][1]))
    $pfwmid = $pfwmidqr['mid'][1];
  else
    $pfwmid = 0;
  $lomidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'lo'");
  if(isset($lomidqr['mid'][1]))
    $lomid = $lomidqr['mid'][1];
  else
    $lomid = 0;
  $ckvmidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'ckv'");
  if(isset($ckvmidqr['mid'][1]))
    $ckvmid = $ckvmidqr['mid'][1];
  else
    $ckvmid = 0;
	
  foreach($students['sid'] AS $six => $sid)
  {
		foreach($subjects['mid'] AS $mid)
		{
			$hassubject = 0;
			// check for subjects here!
			foreach($packages['packagename'] AS $subix => $pname)
			{
				if($pname == $students['packagename'][$six] && $mid == $packages['mid'][$subix])
				$hassubject = 1;
			}
			if($mid == $students['extrasubject'][$six] || $mid == $students['extrasubject2'][$six] || $mid == $students['extrasubject3'][$six])
				$hassubject = 1;
			if($hassubject == 1)
			{ // First, see if we need to put a result here from the previous year as vrijstelling
				if(isset($_GET['extractfromprevyear']) && isset($pyres[$sid][$mid]))
				{
					if($pyres[$sid][$mid] > 6.5 && $pyrescount[$sid] < 10)
					{
						$xstat = ceil(round($pyres[$sid][$mid]) - 2);
						mysql_query("REPLACE INTO ex45data (sid,mid,xstatus,year) VALUES(". $sid. ",". $mid. ",". $xstat. ",'". $curyear. "')", $userlink);
					}
				}
			}
		}
  }  
	
  // See if we got posted that the i&s values from previous years need to be processed
  if(isset($_POST['getisresults']))
  {
    //echo("Processing I&S results<BR>");
	inputclassbase::dbconnect($userlink);
	if(isset($students))
	{
	  require_once("student.php");
	  foreach($students['sid'] AS $sid)
	  {
	    $stobj = new student($sid);
			//echo($stobj->get_student_detail("*student.firstname"));
	    // Get I&S results from previous years
			if($ismid > 0) // Only if we found and mid for this subject
			{
				$isqr = SA_loadquery("SELECT MAX(result) AS isres FROM gradestore WHERE sid=". $sid. " AND mid=". $ismid. " AND period=0 GROUP BY year ORDER BY year DESC LIMIT 2");		  
			} 
			if(isset($isqr['isres']))
			{
				if(strpos($stobj->get_student_detail("*grouphistory.*"),"HL2-") !== false && isset($isqr['isres'][2]))
				{
					//echo(" I&S results=". $isqr['isres'][1]. ", ". $isqr['isres'][2]);
					$isres = round((2 * $isqr['isres'][2] + $isqr['isres'][1])/3.0,1);
				}
				else
				{
					//echo(" I&S result=". $isqr['isres'][1]);
				$isres = $isqr['isres'][1];
				}
				//echo(" Using I&S result: ". $isres);
				mysql_query("REPLACE INTO ahxdata (sid,mid,xstatus,year) VALUES(". $sid. ",". $ismid. ",'". $isres. "','". $curyear. "')", $userlink);
				echo(mysql_error($userlink));
			}
			if($lomid > 0) // Only if we found a mid for this subject
			{
				$loqr = SA_loadquery("SELECT AVG(result) AS lores FROM gradestore LEFT JOIN s_exnr USING(sid) WHERE sid=". $sid. " AND mid=". $lomid. " AND period<>0 AND data LIKE 'HB%' GROUP BY year ORDER BY year DESC LIMIT 2");		  
			} 
			if(isset($loqr['lores']))
			{
				$lores = round($loqr['lores'][1]);
				//echo(" Using LO result: ". $ilores);
				mysql_query("REPLACE INTO ahxdata (sid,mid,xstatus,year) VALUES(". $sid. ",". $lomid. ",'". $lores. "','". $curyear. "')", $userlink);
				echo(mysql_error($userlink));
			}
			//else
			//  echo(" No I&S result!");
			//echo("<BR>");
			}
		}
    //exit;
  }
    
  // Get all exisiting records in an array
  $ex45data = SA_loadquery("SELECT * FROM ex45data WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($ex45data))
    foreach($ex45data['ex45id'] AS $xix => $xid)
		{
			$ex45s[$ex45data['sid'][$xix]][$ex45data['mid'][$xix]] = $xid;
			// Added 18-05-2017 : EX 5 needs to show previous year results for "vrijstellingen". These come from excertdata but are not filled automatically. 
			// We do that now but only if no results are already filled in and a vrijstelling or certificate is defined.
			if($ex45data['xstatus'][$xix] > 4)
			{ // It's a vrijstelling or certificate
				$sid=$ex45data['sid'][$xix];
				$mid=$ex45data['mid'][$xix];
				$checkcertexistqr = SA_loadquery("SELECT endresult FROM excertdata WHERE sid=". $sid. " AND mid=". $mid);
				if(!isset($checkcertexistqr['endresult']))
				{ // Vrijstelling or certificate present without data in excertdata, retrieve the last years result from gradestore for the subject / student
					$certdataqr = SA_loadquery("SELECT SUM(IF(period=0,result,0)) AS endres, SUM(IF(period=2,result,0)) AS seres, SUM(IF(period=3,result,0)) AS cseres, year FROM gradestore WHERE mid=". $mid. " AND sid=". $sid. " GROUP BY year ORDER BY year DESC");
					if(isset($certdataqr['endres'][1]))
					{ // So there is a result, let's put that into the certificate table
						$certyear = substr($certdataqr['year'][1],-4);
						mysql_query("INSERT INTO excertdata (sid,mid,year,seresult,exresult,endresult) VALUES (". $sid. ",". $mid. ",'". $certyear. "',". $certdataqr['seres'][1]. ",". ($certdataqr['cseres'][1] > 0 ? $certdataqr['cseres'][1] : 'NULL'). ",". $certdataqr['endres'][1]. ")");		
						echo(mysql_error());
					}					
				}					
			}
		}

  $exres = SA_loadquery("SELECT * FROM examresult WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($exres))
    foreach($exres['xid'] AS $xix => $xid)
			$exr[$exres['sid'][$xix]] = $xid; 
  
  $ahxdata = SA_loadquery("SELECT * FROM ahxdata WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($ahxdata))
    foreach($ahxdata['ahxid'] AS $xix => $xid)
			$ahxs[$ahxdata['sid'][$xix]][$ahxdata['mid'][$xix]] = $xid;
		
  // First part of the page
  echo("<html><head><title>Examen status invoer</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Status examens</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");

  $curgname = "ExamHavo";
  echo("<H1>HAVO</H1>");
  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td>Exnr.</td><td><center>Studentnaam</td>");
  foreach($subjects['shortname'] AS $sname)
  {
    echo("<td><center>". $sname. "</td>");
  }
  echo("<td><center>I&S</center></td><td><center>PWS</center></td><td><center>CKV</center><td><center>LO</center></td>");
	echo("<td><center>Uitslag</center></td></tr>");

  // Create a row in the table for each student
  $negix = 0;
  foreach($students['sid'] AS $six => $sid)
  {
    if($curgname != $students['groupname'][$six])
	{
	  $curgname = $students['groupname'][$six];
	  echo("</table><H1>VWO</H1>");
      // Create the heading row for the table
      echo("<table border=1 cellpadding=0>");
      echo("<tr><td>Exnr.</td><td><center>Studentnaam</td>");
      foreach($subjects['shortname'] AS $sname)
      {
        echo("<td><center>". $sname. "</td>");
      }
      echo("<td><center>I&S</center><td><center>Pfw</center></td><td><center>Uitslag</center></td></tr>");
	}
    echo("<tr><td>". $students['exnr'][$six]. "</td><td>". $students['name'][$six]. "</td>");
	
	foreach($subjects['mid'] AS $sbix => $mid)
	{
	  echo("<TD>");
	  $hassubject = 0;
	  // check for subjects here!
	  foreach($packages['packagename'] AS $subix => $pname)
	  {
	    if($pname == $students['packagename'][$six] && $mid == $packages['mid'][$subix])
		  $hassubject = 1;
	  }
	  if($mid == $students['extrasubject'][$six] || $mid == $students['extrasubject2'][$six] || $mid == $students['extrasubject3'][$six])
	    $hassubject = 1;
	  if($hassubject == 0)
	    echo("&nbsp");
	  else
	  { // Create an entry field for the status and show it
	    if(isset($ex45s[$sid][$mid]))
			{
						$statfield = new inputclass_listfield("statfield". $ex45s[$sid][$mid],$statquery,$userlink,"xstatus","ex45data",$ex45s[$sid][$mid],"ex45id","","ex45procpage.php");
			}
			else
			{
				$statfield = new inputclass_listfield("statfield". (--$negix),$statquery,$userlink,"xstatus","ex45data",$negix,"ex45id","","ex45procpage.php");
				$statfield->set_extrafield("mid", $mid);
				$statfield->set_extrafield("sid", $sid);
				$statfield->set_extrafield("year", $curyear);
			}
			$statfield->echo_html();	    
	  }
      echo("</TD>");
	}
	// add columns for the I&S ,Pfw, CKV and LO result entry
	echo("<TD>");
	$mid=$ismid;
	if(isset($ahxs[$sid][$mid]))
	{
		$statfield = new inputclass_textfield("xstatfield". $ahxs[$sid][$mid],3,$userlink,"xstatus","ahxdata",$ahxs[$sid][$mid],"ahxid","","ex45procpage.php");
	}
	else
	{
		$statfield = new inputclass_textfield("xstatfield". (--$negix),3,$userlink,"xstatus","ahxdata",$negix,"ahxid","","ex45procpage.php");
		$statfield->set_extrafield("mid", $mid);
		$statfield->set_extrafield("sid", $sid);
		$statfield->set_extrafield("year", $curyear);
	}
	$statfield->echo_html();	    
		echo("</TD>");
	echo("<TD>");
	$mid=$pfwmid;
	if(isset($ahxs[$sid][$mid]))
	{
		$statfield = new inputclass_textfield("xstatfield". $ahxs[$sid][$mid],3,$userlink,"xstatus","ahxdata",$ahxs[$sid][$mid],"ahxid","","ex45procpage.php");
	}
	else
	{
		$statfield = new inputclass_textfield("xstatfield". (--$negix),3,$userlink,"xstatus","ahxdata",$negix,"ahxid","","ex45procpage.php");
		$statfield->set_extrafield("mid", $mid);
		$statfield->set_extrafield("sid", $sid);
		$statfield->set_extrafield("year", $curyear);
	}
	$statfield->echo_html();	    
	echo("</TD>");
	
	
	echo("<TD>");
	$mid=$ckvmid;
	if(isset($ahxs[$sid][$mid]))
	{
		$statfield = new inputclass_textfield("xstatfield". $ahxs[$sid][$mid],3,$userlink,"xstatus","ahxdata",$ahxs[$sid][$mid],"ahxid","","ex45procpage.php");
	}
	else
	{
		$statfield = new inputclass_textfield("xstatfield". (--$negix),3,$userlink,"xstatus","ahxdata",$negix,"ahxid","","ex45procpage.php");
		$statfield->set_extrafield("mid", $mid);
		$statfield->set_extrafield("sid", $sid);
		$statfield->set_extrafield("year", $curyear);
	}
	$statfield->echo_html();	    
	echo("</TD>");
	echo("<TD>");
	$mid=$lomid;
	if(isset($ahxs[$sid][$mid]))
	{
		$statfield = new inputclass_textfield("xstatfield". $ahxs[$sid][$mid],3,$userlink,"xstatus","ahxdata",$ahxs[$sid][$mid],"ahxid","","ex45procpage.php");
	}
	else
	{
		$statfield = new inputclass_textfield("xstatfield". (--$negix),3,$userlink,"xstatus","ahxdata",$negix,"ahxid","","ex45procpage.php");
		$statfield->set_extrafield("mid", $mid);
		$statfield->set_extrafield("sid", $sid);
		$statfield->set_extrafield("year", $curyear);
	}
	$statfield->echo_html();	    
	echo("</TD>");

	// add a column for the manual exam result entry
	echo("<td>");
	if(isset($exr[$sid]))
	  $resfield = new inputclass_textarea("resfield". $sid,"15,*",$userlink,"xresult","examresult",$exr[$sid],"xid","","ex45procpage.php");
	else
	{
	  $resfield = new inputclass_textarea("resfield". ($negix),"15,*",$userlink,"xresult","examresult",$negix,"xid","","ex45procpage.php");
	  $resfield->set_extrafield("sid", $sid);
	  $resfield->set_extrafield("year", $curyear);
	}
	$resfield->echo_html();
	echo("</td></tr>");
  }
 echo("</table>");
  echo '<a href=# onClick="window.close();">';
  echo ($dtext['back_teach_page']. "</a>");
  //echo "</a><BR><BR><a href='" .$_SERVER['PHP_SELF']. "?extractfromprevyear=1'>Haal vrijstellingen en CKV resultaat uit resultaten vorig jaar</a>";
  // Create a button to get I&S results from previous years
  echo("<FORM METHOD=POST ACTION='". $_SERVER['PHP_SELF']. "'><INPUT type=hidden name=getisresults value=1><input type=SUBMIT value='Ophalen I&S en LO resultaten vorige jaren'></FORM>");
 
  // close the page
  echo("</html>");
?>
