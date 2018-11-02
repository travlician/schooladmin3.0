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
  // WATCH OUT!!!! THIS LIST IS NOT THE SAME AS FOR OTHER EX FORMS!
  $offsubjects = array(1 => "Ne","En","Sp","Wi-A","Wi-B","Na","Sk","Bio","Ec","M&O","Ak","Gs","CKV","Fa","Pa","Inf","Re","Pfw","I&S");
   $altsubjects = array("Ne"=>1,"En"=>2,"Sp"=>3,"Wi-A"=>4,"Wi-B"=>5,"Na"=>6,"Sk"=>7,"Bio"=>8,"Ec"=>9,"M&O"=>10,"Ak"=>11,"Gs"=>12,"CKV"=>13,"Fa"=>14,"Pa"=>15,"Inf"=>16,
                       "ne"=>1,"en"=>2,"sp"=>3,"wiA"=>4,"wiB"=>5,"na"=>6,"sk"=>7,"bio"=>8,"ec"=>9,"m&o"=>10,"ak"=>11,"gs"=>12,"ckv"=>13,"Re"=>17,"Pfw"=>18,"I&S"=>19,"pap"=>15,"inf"=>16);


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
	
	// Cleanup ahx entries that are empty
	mysql_query("DELETE FROM ahxdata WHERE xstatus IS NULL",$userlink);
  echo(mysql_error());
  
  // We need to get the year for entry!
  $curyearqr = SA_loadquery("SELECT year FROM period WHERE id=3");
  $curyear = $curyearqr['year'][1];
    
  // The states are predefined, here is the query to get that list (actually no DB access but the library works that way...)
  $statquery = "SELECT 0 AS id, '' AS tekst UNION SELECT 1 AS id,'Her' AS tekst UNION SELECT 2,'Af.M' UNION SELECT 3,'Af.S' UNION SELECT 4,'Af.E' 
                UNION SELECT 5,'V 7' UNION SELECT 6,'V 8' UNION SELECT 7,'V 9' UNION SELECT 8,'V 10'
                UNION SELECT 9,'C 6' UNION SELECT 10,'C 7' UNION SELECT 11,'C 8' UNION SELECT 12,'C 9' UNION SELECT 13,'C 10'";
  
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
  
  // Process results from previous year
  // Find out which mid belongs to I&S and Pfw
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

	// Get all certdata records for which there is no ex45data record and result >= 6
	$tosetxdqr = SA_loadquery("SELECT sid,mid,endresult FROM excertdata LEFT JOIN (SELECT sid,mid,xstatus FROM ex45data WHERE year='". $curyear. "') AS t1 USING(sid,mid) WHERE endresult >= 6 AND xstatus IS NULL AND sid IS NOT NULL AND mid IS NOT NULL");
	if(isset($tosetxdqr['sid']))
		foreach($tosetxdqr['sid'] AS $tsix => $asid)
		{
		  $insq = "INSERT INTO ex45data (sid,mid,xstatus,year) VALUES(". $asid. ",". $tosetxdqr['mid'][$tsix]. ",". ($tosetxdqr['endresult'][$tsix] + 3). ",'". $curyear. "')";
			mysql_query($insq,$userlink);
			echo(mysql_error($userlink));
			//echo($insq. "<BR>");
		}
	else
	{
		//echo("No need to set ex45data from excertdata!<BR>");
	}
	// Get certdata records for I&S and PWS so we can set these in ahxdata (as done in ex1-5 input)
	$tosetxdqr = SA_loadquery("SELECT sid,mid,endresult FROM excertdata LEFT JOIN (SELECT sid,mid,xstatus FROM ahxdata WHERE year='". $curyear. "') AS t1 USING(sid,mid) WHERE endresult >= 6 AND xstatus IS NULL AND sid IS NOT NULL AND (mid=". $ismid. " OR mid=". $pfwmid. ")");
	if(isset($tosetxdqr['sid']))
		foreach($tosetxdqr['sid'] AS $tsix => $asid)
		{
		  $insq = "INSERT INTO ahxdata (sid,mid,xstatus,year) VALUES(". $asid. ",". $tosetxdqr['mid'][$tsix]. ",". ($tosetxdqr['endresult'][$tsix]). ",'". $curyear. "')";
			mysql_query($insq,$userlink);
			echo(mysql_error($userlink));
		  $insq = "INSERT INTO ex45data (sid,mid,xstatus,year) VALUES(". $asid. ",". $tosetxdqr['mid'][$tsix]. ",". ($tosetxdqr['endresult'][$tsix] + 3). ",'". $curyear. "')";
			mysql_query($insq,$userlink);
			echo(mysql_error($userlink));
			//echo($insq. "<BR>");
		}
	else
	{
		//echo("No need to set ex45data from excertdata!<BR>");
	}
	
  // Get all exisiting records in an array
  $ex45data = SA_loadquery("SELECT * FROM ex45data WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($ex45data))
    foreach($ex45data['ex45id'] AS $xix => $xid)
	  $ex45s[$ex45data['sid'][$xix]][$ex45data['mid'][$xix]] = $xid;

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
  echo("<section><div class=fixheader><table border=1 cellpadding=0><thead>");
  echo("<tr><th>Exnr.<div>Exnr.</div></th><th><center>Studentnaam<div><center>Studentnaam</div></th>");
  foreach($subjects['shortname'] AS $sname)
  {
    echo("<th><center>". $sname. "<div><center>". $sname. "</div></th>");
  }
  echo("<th><center>I&S</center><div><center>I&S</center></div></th><th><center>PFW</center><div><center>PFW</center></div></th><th><center>Uitslag</center><div><center>Uitslag</center></div></th></tr></thead><tbody>");

  // Create a row in the table for each student
  $negix = 0;
  foreach($students['sid'] AS $six => $sid)
  {
    if($curgname != $students['groupname'][$six])
	{
	  $curgname = $students['groupname'][$six];
	  echo("</tbody></table></div></section><H1>VWO</H1>");
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
	// add a column for the I&S and Pfw result entry
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
  echo $dtext['back_teach_page'];
  //echo "</a><BR><BR><a href='" .$_SERVER['PHP_SELF']. "?extractfromprevyear=1'>Haal vrijstellingen en CKV resultaat uit resultaten vorig jaar</a>";
 
  // close the page
  echo("</html>");
?>
