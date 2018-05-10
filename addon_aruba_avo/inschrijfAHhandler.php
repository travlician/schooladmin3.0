<?
/* vim: set expandtab tabstop=2 shiftwidth=2: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
//
// MUST load the classes before session_start()!
require_once("inputlib/inputclasses.php");
require_once("schooladminconstants.php");
require_once("InschrijvingAHAfuncs.php");
session_start();
// echo("ERR"); // Force debug info'

// Reconnect with the database as we don't use persistent connections
inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);
$userlink = inputclassbase::$dbconnection;

// Catch "email", if exists as another record or in user database, need to inform user and let main form send mail about it.
if($_POST['fieldid'] == "semail" && $_POST['semail'] != "")
{ // First see if another record FOR THIS YEAR is already present
  $schoolyear = date("Y"). "-". (date("Y") + 1);
  $ridsearch = inputclassbase::load_query("SELECT rid FROM inschrijvingAHA WHERE email=\"". $_POST['semail']. "\" ORDER BY rid");
  if(isset($ridsearch['rid'][0]) && $ridsearch['rid'][0] != $_SESSION['inputobjects']['semail']->get_key())
  {
    echo("OK<REREGISTER>");
		// Remove this record!
		mysql_query("DELETE FROM inschrijvingAHA WHERE rid=". $_SESSION['inputobjects']['semail']->get_key(), $userlink);
		sendmaillink($_POST['semail'],$ridsearch['rid'][0]);
		exit;
	}
  // Now see if the student is already registered as student and did not enter through the logon screen, if so, copy values and let parent send mail
  $sidsearch = inputclassbase::load_query("SELECT sid FROM s_ASEmailStudent WHERE data=\"". $_POST['semail']. "\"");
  if(isset($sidsearch['sid'][0]))
  { // Student found! Copy data to this record and send mail
		$bd = "NULL"; $bm="NULL"; $by="NULL"; // Defaults in case conversion fails
		$bdqr = inputclassbase::load_query("SELECT data FROM s_ASBirthDate WHERE sid=". $sidsearch['sid'][0]);
		if(isset($bdqr['data']))
		{
			$orgbd = $bdqr['data'][0];
			$splitbd = explode("-",$orgbd);
			if(count($splitbd) < 3)
				$splitbd = explode(" ",$orgbd);
			if(count($splitbd >= 3))
			{ // Result is only valid if 3 items found.
				$splitbd[0] = trim($splitbd[0]);
				if(strlen($splitbd[0]) < 2)
					$splitbd[0] = '0'. $splitbd[0];
				$splitbd[1] = trim($splitbd[1]);
				if(strlen($splitbd[1]) < 3)
				{ // Month given as number, convert to string
					$mno = 0 + $splitbd[1]; // Force correct numeric format first
					$splitbd[1] = $montxt[$mno];
				}
				$splitbd[2] = trim($splitbd[2]);
				// Now the values to put in the inschrijvingAHA records
				$bd = "'". $splitbd[0]. "'";
				$bm = "'". $splitbd[1]. "'";
				$by = "'". $splitbd[2]. "'";
			}	
			$rid=$_SESSION['inputobjects']['semail']->get_key();
			$insrecq = "REPLACE INTO inschrijvingAHA (rid,year,sid,firstname,lastname,roepnaam,geslacht,gebdag,gebmaand,gebjaar,
									gebland,adres,email,telthuis,telmobile,laatsteschool,idnummer,wachtwoord,aantalkinderen,voertaal,werkzaambij)";
			$insrecq .= " SELECT ". $rid. ",'". $schoolyear. "',student.sid,firstname,lastname,s_roepnaam.data,s_ASGender.data,". $bd. ",". $bm. ",". $by. ",";
			$insrecq .= "s_ASBirthCountry.data,s_ASAddress.data,s_ASEmailStudent.data,s_ASPhoneHomeStudent.data,";
			$insrecq .= "s_ASMobilePhoneStudent.data,s_ASLastSchool.data,altsid,password,s_aantalkinderen.data,s_ASHomeLanguage.data,s_werkzaambij.data FROM student";
			$insrecq .= " LEFT JOIN s_roepnaam USING(sid) LEFT JOIN s_ASGender USING(sid)";
			$insrecq .= " LEFT JOIN s_ASBirthCountry USING(sid) LEFT JOIN s_ASAddress USING(sid) LEFT JOIN s_ASEmailStudent USING(sid)";
			$insrecq .= " LEFT JOIN s_ASPhoneHomeStudent USING(sid) LEFT JOIN s_ASMobilePhoneStudent USING(sid)";
			$insrecq .= " LEFT JOIN s_ASLastSchool USING(sid) LEFT JOIN s_aantalkinderen USING(sid) LEFT JOIN s_ASHomeLanguage USING(sid) LEFT JOIN s_werkzaambij USING(sid)";
			$insrecq .= " WHERE sid=". $sidsearch['sid'][0];
			mysql_query($insrecq,$userlink);
			echo(mysql_error($userlink));
			// Now see if a valid package is active for the student and if so, set it in the record
			/*
			$pkqr = inputclassbase::load_query("SELECT packagename FROM s_package 
																						WHERE (packagename LIKE 'MM%' OR packagename LIKE 'HU%' OR packagename LIKE 'NW%') 
													AND sid=". $sidsearch['sid'][0]);
			if(isset($pkqr['packagename']))
			{
				mysql_query("UPDATE inschrijvingAHA SET pakket='". substr($pkqr['packagename'][0],3,2). "' WHERE rid=". $rid, $userlink);
			// Also must put the subjects in the table with the package
			$s2storeqr = inputclassbase::load_query("SELECT mid FROM subjectpackage WHERE packagename='". $pkqr['packagename'][0]. "'");
			if(isset($s2storeqr['mid']))
				foreach($s2storeqr['mid'] AS $imid)
					mysql_query("INSERT INTO inschrijvingPakket (rid,mid) VALUES(". $rid. ",". $imid. ")", $userlink);
			} */
    }
    echo("OK<REREGISTER>");
		sendmaillink($_POST['semail'],$rid);
		exit;
  }
}
// Catch names and birthdate, if exists as another record or in user database, need to inform user and let main form send mail about it.
if(($_POST['fieldid'] == "slname" && $_POST['slname'] != "") ||
   ($_POST['fieldid'] == "sfname" && $_POST['sfname'] != "") ||
   ($_POST['fieldid'] == "sbday" && $_POST['sbday'] != "") ||
   ($_POST['fieldid'] == "sbmon" && $_POST['sbmon'] != "") ||
   ($_POST['fieldid'] == "sbyr" && $_POST['sbyr'] != "") )
{ // First see if another record FOR THIS YEAR is already present
  $schoolyear = date("Y"). "-". (date("Y") + 1);
	// Get the values of the above items
	$sdata['slname'] = $_SESSION['inputobjects']['slname']->__toString();
	$sdata['sfname'] = $_SESSION['inputobjects']['sfname']->__toString();
	$sdata['sbday'] = $_SESSION['inputobjects']['sbday']->__toString();
	$sdata['sbmon'] = $_SESSION['inputobjects']['sbmon']->__toString();
	$sdata['sbyr'] = $_SESSION['inputobjects']['sbyr']->__toString();
	// Overwrite the current value
	$sdata[$_POST['fieldid']] = $_POST[$_POST['fieldid']];
  $ridsearch = inputclassbase::load_query("SELECT rid FROM inschrijvingAHA WHERE firstname LIKE \"". $sdata['sfname']. "\" AND lastname LIKE \"". $sdata['slname']. "\" AND gebdag=\"". $sdata['sbday']. "\" AND gebmaand=\"". $sdata['sbmon']. "\" AND gebjaar=\"". $sdata['sbyr']. "\" ORDER BY rid");
  if(isset($ridsearch['rid'][0]) && $ridsearch['rid'][0] != $_SESSION['inputobjects'][$_POST['fieldid']]->get_key())
  {
    echo("OK<REREGISTER>");
		// Remove this record!
		mysql_query("DELETE FROM inschrijvingAHA WHERE rid=". $_SESSION['inputobjects']['semail']->get_key(), $userlink);
		sendmaillink($_POST['semail'],$ridsearch['rid'][0]);
		exit;
	}
  // Now see if the student is already registered as student and did not enter through the logon screen, if so, copy values and let parent send mail
  $sidsearch = inputclassbase::load_query("SELECT sid FROM student LEFT JOIN s_ASBirthDate USING(sid) WHERE firstname LIKE \"". $sdata['sfname']. "\" AND lastname LIKE \"". $sdata['slname']. "\" AND data=\"". ($sdata['sbday']. " ". $sdata['sbmon']. " ". $sdata['sbyr']). "\"");
  if(isset($sidsearch['sid'][0]))
  { // Student found! Copy data to this record and send mail
		$bd = "NULL"; $bm="NULL"; $by="NULL"; // Defaults in case conversion fails
		$bdqr = inputclassbase::load_query("SELECT data FROM s_ASBirthDate WHERE sid=". $sidsearch['sid'][0]);
		if(isset($bdqr['data']))
		{
			$orgbd = $bdqr['data'][0];
			$splitbd = explode("-",$orgbd);
			if(count($splitbd) < 3)
				$splitbd = explode(" ",$orgbd);
			if(count($splitbd >= 3))
			{ // Result is only valid if 3 items found.
				$splitbd[0] = trim($splitbd[0]);
				if(strlen($splitbd[0]) < 2)
					$splitbd[0] = '0'. $splitbd[0];
				$splitbd[1] = trim($splitbd[1]);
				if(strlen($splitbd[1]) < 3)
				{ // Month given as number, convert to string
					$mno = 0 + $splitbd[1]; // Force correct numeric format first
					$splitbd[1] = $montxt[$mno];
				}
				$splitbd[2] = trim($splitbd[2]);
				// Now the values to put in the inschrijvingAHA records
				$bd = "'". $splitbd[0]. "'";
				$bm = "'". $splitbd[1]. "'";
				$by = "'". $splitbd[2]. "'";
			}	
			$rid=$_SESSION['inputobjects']['semail']->get_key();
			$insrecq = "REPLACE INTO inschrijvingAHA (rid,year,sid,firstname,lastname,roepnaam,geslacht,gebdag,gebmaand,gebjaar,
									gebland,adres,email,telthuis,telmobile,laatsteschool,idnummer,wachtwoord,aantalkinderen,voertaal,werkzaambij)";
			$insrecq .= " SELECT ". $rid. ",'". $schoolyear. "',student.sid,firstname,lastname,s_roepnaam.data,s_ASGender.data,". $bd. ",". $bm. ",". $by. ",";
			$insrecq .= "s_ASBirthCountry.data,s_ASAddress.data,s_ASEmailStudent.data,s_ASPhoneHomeStudent.data,";
			$insrecq .= "s_ASMobilePhoneStudent.data,s_ASLastSchool.data,altsid,password,s_aantalkinderen.data,s_ASHomeLanguage.data,s_werkzaambij.data FROM student";
			$insrecq .= " LEFT JOIN s_roepnaam USING(sid) LEFT JOIN s_ASGender USING(sid)";
			$insrecq .= " LEFT JOIN s_ASBirthCountry USING(sid) LEFT JOIN s_ASAddress USING(sid) LEFT JOIN s_ASEmailStudent USING(sid)";
			$insrecq .= " LEFT JOIN s_ASPhoneHomeStudent USING(sid) LEFT JOIN s_ASMobilePhoneStudent USING(sid)";
			$insrecq .= " LEFT JOIN s_ASLastSchool USING(sid) LEFT JOIN s_aantalkinderen USING(sid) LEFT JOIN s_ASHomeLanguage USING(sid) LEFT JOIN s_werkzaambij USING(sid)";
			$insrecq .= " WHERE sid=". $sidsearch['sid'][0];
			mysql_query($insrecq,$userlink);
			echo(mysql_error($userlink));
    }
    echo("OK<REREGISTER2>");
		sendmaillink($_POST['semail'],$rid);
		exit;
  }
}
// Catch "siljaar" as it means putting the student with all data in the database and indicated group
if($_POST['fieldid'] == "siljaar")
{
  // Process the record as usual
	//echo("ERR");
  include("inputlib/procinput.php");
  echo("<PLACEDGROUP>{");
  // Get the sid
  $rid = $_SESSION['inputobjects']['siljaar']->get_key();
  $sidfld = new inputclass_checkbox("sidrq",0,NULL,"sid","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  if($sidfld->__toString() == '')
    $sid=0;
  else
    $sid=$sidfld->__toString();

  if($_POST['siljaar'] == "")
  { // Student no longer is in a group
    if($sid > 0)
		{ // Delete student from applicable groups
			$grplistqr = inputclassbase::load_query("SELECT gid FROM sgrouplink LEFT JOIN sgroup USING(gid) WHERE active=1 AND sid=". $sid. " AND groupname LIKE 'NW". date('Y'). "%'");
			if(isset($grplistqr['gid']))
				foreach($grplistqr['gid'] AS $rmgid)
				{
					mysql_query("DELETE FROM sgrouplink WHERE sid=". $sid. " AND gid=". $rmgid, $userlink);
					echo(mysql_error($userlink));
				}		  
		}
    echo("Deze student is <b>niet</b> geplaatst!");
  }
  else
  {
  // First see if we need to create a student record
	if($sid == 0)
	{
	  // Need to get userid and password first
	  $stbd = inputclassbase::load_query("SELECT idnummer,wachtwoord,gebdag,gebmaand,gebjaar FROM inschrijvingAHA WHERE rid=". $rid);
	  if(!isset($stbd['idnummer'][0]) || $stbd['idnummer'][0] == "")
	  { // no userid given, we contruct it
	    $idnr = substr(date("Y"),2). str_pad($rid,4,"0",STR_PAD_LEFT);
	  }
	  else
	    $idnr = $stbd['idnummer'][0];
	  if(!isset($stbd['wachtwoord'][0]) || $stbd['wachtwoord'][0] == "")
	  { // no password given, we contruct is from the record number
			$pw = str_pad(base_convert(($rid * 1313) % 32000,10,16),4,"0",STR_PAD_LEFT);
	  }
	  else
	    $pw = $stbd['wachtwoord'][0];
      // Create the student record
	  mysql_query("INSERT INTO student (altsid,password) VALUES('". $idnr. "','". $pw. "')", $userlink);
	  $sid = mysql_insert_id($userlink);
	  // Set this student id also in the rid record
	  mysql_query("UPDATE inschrijvingAHA SET sid=". $sid. " WHERE rid=". $rid, $userlink);
	}
	// Update the student data from the registration
	$idata = inputclassbase::load_query("SELECT * FROM inschrijvingAHA WHERE rid=". $rid);
	if($idata['firstname'][0] != '')
	{
	  mysql_query("UPDATE student SET firstname='". $idata['firstname'][0]. "' WHERE sid=". $sid);
	  $ffname = explode(" ",$idata['firstname'][0]);
	  mysql_query("REPLACE INTO s_roepnaam (sid,data) VALUES(". $sid. ",'". $ffname[0]. "')");
	}
	if($idata['lastname'][0] != '')
	  mysql_query("UPDATE student SET lastname='". $idata['lastname'][0]. "' WHERE sid=". $sid);
	if($idata['roepnaam'][0] != '')
	  mysql_query("REPLACE INTO s_roepnaam (sid,data) VALUES(". $sid. ",'". $idata['roepnaam'][0]. "')", $userlink);
	if($idata['geslacht'][0] != '')
	  mysql_query("REPLACE INTO s_ASGender (sid,data) VALUES(". $sid. ",'". $idata['geslacht'][0]. "')", $userlink);
	if($idata['gebdag'][0] != '' && $idata['gebmaand'][0] != '' && $idata['gebjaar'][0] != '')
	  mysql_query("REPLACE INTO s_ASBirthDate (sid,data) VALUES(". $sid. ",'". $idata['gebdag'][0]. " ".$idata['gebmaand'][0]. " ".$idata['gebjaar'][0]. "')", $userlink);
	if($idata['gebland'][0] != '')
	  mysql_query("REPLACE INTO s_ASBirthCountry (sid,data) VALUES(". $sid. ",'". $idata['gebland'][0]. "')", $userlink);
	if($idata['adres'][0] != '')
	  mysql_query("REPLACE INTO s_ASAddress (sid,data) VALUES(". $sid. ",'". $idata['adres'][0]. "')", $userlink);
	if($idata['email'][0] != '')
	  mysql_query("REPLACE INTO s_ASEmailStudent (sid,data) VALUES(". $sid. ",'". $idata['email'][0]. "')", $userlink);
	if($idata['telthuis'][0] != '')
	  mysql_query("REPLACE INTO s_ASPhoneHomeStudent (sid,data) VALUES(". $sid. ",'". $idata['telthuis'][0]. "')", $userlink);
	if($idata['telmobile'][0] != '')
	  mysql_query("REPLACE INTO s_ASMobilePhoneStudent (sid,data) VALUES(". $sid. ",'". $idata['telmobile'][0]. "')", $userlink);
	if($idata['bankrekening'][0] != '')
	  mysql_query("REPLACE INTO s_banknummer (sid,data) VALUES(". $sid. ",'". $idata['bankrekening'][0]. "')", $userlink);
	if($idata['laatsteschool'][0] != '')
	  mysql_query("REPLACE INTO s_ASLastSchool (sid,data) VALUES(". $sid. ",'". $idata['laatsteschool'][0]. "')", $userlink);

	if($idata['voertaal'][0] != '')
	  mysql_query("REPLACE INTO s_ASHomeLanguage (sid,data) VALUES(". $sid. ",'". $idata['voertaal'][0]. "')", $userlink);
	if($idata['aantalkinderen'][0] != '')
	  mysql_query("REPLACE INTO s_aantalkinderen (sid,data) VALUES(". $sid. ",'". $idata['aantalkinderen'][0]. "')", $userlink);
	if($idata['werkzaambij'][0] != '')
	  mysql_query("REPLACE INTO s_werkzaambij (sid,data) VALUES(". $sid. ",'". $idata['werkzaambij'][0]. "')", $userlink);

	// Entry year
	mysql_query("REPLACE INTO s_ASEntryYear SELECT sid,MIN(year) FROM testresult LEFT JOIN testdef USING(tdid) WHERE sid=". $sid);
	
	// Add other data like inschrijfgeld, certificaten, profiel, vakkenpakket, extra vak
	mysql_query("REPLACE INTO s_inschrijfgeld (sid,data) VALUES(". $sid. ",'". calcfee($rid). "')", $userlink);
	// Certficates
	$cl = inputclassbase::load_query("SELECT shortname AS `cl` FROM inschrijvingCerts LEFT JOIN subject USING(mid) WHERE rid=". $rid);
	mysql_query("DELETE FROM s_cert WHERE sid=". $sid, $userlink);
	if(isset($cl['cl'][0]) && $cl['cl'][0] != "")
  {
	  foreach($cl['cl'] AS $cln)
	    mysql_query("INSERT INTO s_cert (sid,data) VALUES(". $sid. ",'". $cln. "')", $userlink);
	}
	// Profile
	$pmq = "SELECT packagename, COUNT(isubpack.mid) AS pc, COUNT(t2.mid) AS sc FROM isubpack LEFT JOIN ";
	$pmq .= "(SELECT mid FROM inschrijvingPakket WHERE rid=". $rid. ") AS t2 USING(mid) ";
	$pmq .= "LEFT JOIN subject USING(mid) ";
	$pmq .= "WHERE (packagename LIKE '_MM%' OR packagename LIKE '_HU%' OR packagename LIKE '_NW%') ";
	$pmq .= "AND shortname <> 'I&S' AND shortname <> 'Pfw' AND shortname <> 'Re' ";
	$pmq .= "GROUP BY packagename HAVING pc=sc";
	$pmqr = inputclassbase::load_query($pmq);
	if(isset($pmqr['packagename'][0]))
	{
		mysql_query("DELETE FROM s_profiel WHERE sid=". $sid, $userlink);
		echo(mysql_error($userlink));
		mysql_query("INSERT INTO s_profiel (sid,data) VALUES(". $sid. ",'". substr($pmqr['packagename'][0],1,2). "')", $userlink);
		echo(mysql_error($userlink));
		mysql_query("DELETE FROM s_s_shokje_profiel_nr WHERE sid=". $sid, $userlink);
		echo(mysql_error($userlink));
		mysql_query("INSERT INTO s_s_shokje_profiel_nr VALUES(". $sid. ",'". $pmqr['packagename'][0]. "')", $userlink);
		echo(mysql_error($userlink));
	}
	// Amount of subjects
	$subjcnt = inputclassbase::load_query("SELECT COUNT(mid) AS scnt FROM inschrijvingPakket WHERE rid=". $rid);
	if(isset($subjcnt['scnt']))
	{
		mysql_query("REPLACE INTO s_vakken (sid,data) VALUES(". $sid. ",". $subjcnt['scnt'][0]. ")", $userlink);
	}
	// Vakkenpakket and extravak only if in third year or VWO
	if(substr($idata['plaatsjaar'][0],0,1) == "3" || substr($idata['plaatsjaar'][0],0,3) == "VWO")
	{
	  // Remove old entry
	  mysql_query("DELETE FROM s_package WHERE sid=". $sid, $userlink);
	  // First see if we got a perfect match.
		$pmq = "SELECT packagename, COUNT(subjectpackage.mid) AS pc, COUNT(t2.mid) AS sc, GROUP_CONCAT(subjectpackage.mid) AS slist FROM subjectpackage LEFT JOIN ";
		$pmq .= "(SELECT mid FROM inschrijvingPakket WHERE rid=". $rid. ") AS t2 USING(mid) ";
		$pmq .= "LEFT JOIN subject USING(mid) ";
		$pmq .= "GROUP BY packagename HAVING pc=sc";
		$pmqr = inputclassbase::load_query($pmq);
	  $scnt = inputclassbase::load_query("SELECT GROUP_CONCAT(mid) AS slist, COUNT(mid) AS sc FROM inschrijvingPakket WHERE rid=". $rid. " ORDER BY mid");
	  if(isset($pmqr['packagename'][0]))
    {  // One or more packages were found, see if there is a perfect match
	    foreach($pmqr['packagename'] AS $pmix => $pkname)
			{ // See for each matching pakket if it's a perfect match
				if($pmqr['pc'][$pmix] == $scnt['sc'][0])
					$pmatch = $pkname;
			}

			if(isset($pmatch))
			{ // There is a perfect match, set it and do no more on this
				mysql_query("INSERT INTO s_package (sid,packagename,extrasubject,extrasubject2,extrasubject3) VALUES(". $sid. ",'". $pmatch. "',0,0,0)", $userlink);
			}
			else
			{ // There is no perfect match, so see it there is a match with one subject less
				foreach($pmqr['packagename'] AS $pmix => $pkname)
				{ // See for each matching pakket if it's a match ommiting one subject
					if($pmqr['pc'][$pmix] == ($scnt['sc'][0] - 1))
					{
							$pmatch = $pkname;
						$pmatchix = $pmix;
					}
				}
				if(isset($pmatch))
				{ // We got a match with one subject missing, set that as extra subject
					$pkmids = explode(",",$pmqr['slist'][$pmatchix]);
					$selmids = explode(",",$scnt['slist'][0]);
					foreach($selmids AS $selmid)
					{ 
						$midpres = false;
						foreach($pkmids AS $pkmid)
							if($pkmid == $selmid)
							$midpres = true;
						if(!$midpres)
						{  // we found the extra subject, put it in the database
							mysql_query("INSERT INTO s_package (sid,packagename,extrasubject,extrasubject2,extrasubject3) VALUES(". $sid. ",'". $pmatch. "',". $selmid. ",0,0)", $userlink);
						}
					}		    
				}
				else
				{ // Non existing package qualifies, create a new one
					// First subject to omit is I&S.
					$ismidqr = inputclassbase::load_query("SELECT mid FROM subject WHERE shortname='I&S'");
					if(isset($ismidqr['mid'][0]))
						$ismid = 0; // Appear I&S is always part of the package
						//$ismid = $ismidqr['mid'][0];
					// Make an array of subjects to put in the package
					$selmids = explode(",",$scnt['slist'][0]);
					if(count($selmids) < 2)
					{  // Simple case of 0 or 1 subject, 0 subjects leads to no subjectpackage being set!
						if(isset($selmids[0]) && $selmids[0] > 0)
						{ // Create a single subject package and assign it to the student
							// Get the name of the subject
							$ssjnm = inputclassbase::load_query("SELECT shortname FROM subject WHERE mid=". $selmids[0]);
							if(isset($ssjnm['shortname'][0]))
							{
								mysql_query("INSERT INTO subjectpackage mid,packagename) VALUES(". $selmids[0]. ",'1V ". $ssjnm['shortname'][0]. "')", $userlink);
								mysql_query("INSERT INTO s_package (sid,packagename,extrasubject,extrasubject2,extrasubject3) VALUES(". $sid. ",'1V ". $ssjnm['shortname'][0]. "',0,0,0)", $userlink);
							}
						}	
					}
					else
					{ // Calculte the extra subject and remove that from the list, create the subjectpackage and set the ES as such
						foreach($selmids AS $smid)
							if($smid == $ismid)
							$ev = $smid; // Now ev is set if I&S is the subject to remove
						if(!isset($ev))
							foreach($selmids AS $smix => $smid)
								$ev = $smid; // After last iteration $ev is either I&S or if not, the subject with the highest mid
						// Remove the entry containing $ev from the list
						foreach($selmids AS $smix => $smid)
							if($smid == $ev)
							unset($selmids[$smix]);
						// To make the string for the subjectpackage we need a tranlastion from mid to subjectname
						$transqr = inputclassbase::load_query("SELECT mid,shortname FROM subject");
						if(isset($transqr['mid']))
							foreach($transqr['mid'] AS $tmix => $tmid)
							$m2sn[$tmid] = $transqr['shortname'][$tmix];
						// Now make the subjectname
						$nwpknm = count($selmids). "V ";
						foreach($selmids AS $atsm)
							$nwpknm .= $m2sn[$atsm];
						// Insert the package in the system
						foreach($selmids AS $smid)
							mysql_query("INSERT INTO subjectpackage (mid,packagename) VALUES(". $smid. ",'". $nwpknm. "')", $userlink);
						// And link it to the student with the extra subject
							mysql_query("INSERT INTO s_package (sid,packagename,extrasubject,extrasubject2,extrasubject3) VALUES(". $sid. ",'". $nwpknm. "',". $ev. ",0,0)", $userlink);			  
					}
				}	      
			}
	  }
	  else
	  {  // No package exists of which the all subjects are selected by the student. Need to create a new one
		    // First subject to omit is I&S.
			$ismidqr = inputclassbase::load_query("SELECT mid FROM subject WHERE shortname='I&S'");
			if(isset($ismidqr['mid'][0]))
			  $ismid = 0;
			  //$ismid = $ismidqr['mid'][0];
			// Make an array of subjects to put in the package
			$selmids = explode(",",$scnt['slist'][0]);
			if(isset($selmids[0]) && $selmids[0] > 0)
			{  // Simple case of 0 or 1 subject, 0 subjects leads to no subjectpackage being set!
			  if(count($selmids) == 1)
			  { // Create a single subject package and assign it to the student
			    // Get the name of the subject
					$ssjnm = inputclassbase::load_query("SELECT shortname FROM subject WHERE mid=". $selmids[0]);
					if(isset($ssjnm['shortname'][0]))
					{
						mysql_query("INSERT INTO subjectpackage mid,packagename) VALUES(". $selmids[0]. ",'1V ". $ssjnm['shortname'][0]. "')", $userlink);
						mysql_query("INSERT INTO s_package (sid,packagename,extrasubject,extrasubject2,extrasubject3) VALUES(". $sid. ",'1V ". $ssjnm['shortname'][0]. "',0,0,0)", $userlink);
					}
			  }
			}
			else
			{ // Calculte the extra subject and remove that from the list, create the subjectpackage and set the ES as such
			  foreach($selmids AS $smid)
			    if($smid == $ismid)
				  $ev = $smid; // Now ev is set if I&S is the subject to remove
			  if(!isset($ev))
			    foreach($selmids AS $smix => $smid)
			      $ev = $smid; // After last iteration $ev is either I&S or if not, the subject with the highest mid
			  // Remove the entry containing $ev from the list
			  foreach($selmids AS $smix => $smid)
			    if($smid == $ev)
				  unset($selmids[$smix]);
			  // To make the string for the subjectpackage we need a tranlastion from mid to subjectname
			  $transqr = inputclassbase::load_query("SELECT mid,shortname FROM subject");
			  if(isset($transqr['mid']))
			    foreach($transqr['mid'] AS $tmix => $tmid)
				  $m2sn[$tmid] = $transqr['shortname'][$tmix];
			  // Now make the subjectname
			  $nwpknm = count($selmids). "V ";
			  foreach($selmids AS $atsm)
			    $nwpknm .= $m2sn[$atsm];
			  // Insert the package in the system
			  foreach($selmids AS $smid)
			    mysql_query("INSERT INTO subjectpackage (mid,packagename) VALUES(". $smid. ",'". $nwpknm. "')", $userlink);
			  // And link it to the student with the extra subject
		      mysql_query("INSERT INTO s_package (sid,packagename,extrasubject,extrasubject2,extrasubject3) VALUES(". $sid. ",'". $nwpknm. "',". $ev. ",0,0)", $userlink);			  
			}
	  }
	}
	// Need to place student in group
	// First create group if doesn't exist already.
	$gname = "NW". date('Y'). $_POST['siljaar'];
	$gidqr = inputclassbase::load_query("SELECT gid FROM sgroup WHERE groupname='". $gname. "'");
	if(isset($gidqr['gid'][0]))
	  $gid = $gidqr['gid'][0];
	else
	{
	  mysql_query("INSERT INTO sgroup(groupname,tid_mentor) VALUES('". $gname. "',1)", $userlink);
	  $gid = mysql_insert_id($userlink);
	}
	// Now place the student in the group.
	mysql_query("INSERT INTO sgrouplink (sid,gid) VALUES(". $sid. ",". $gid. ")", $userlink);
    echo("Deze student is plaatst in groep NW". date("Y"). $_POST['siljaar']);
  }
  echo("}");
  exit;
}

// Let the library page handle the data
include("inputlib/procinput.php");

// Just for demo purposes: show the fields posted(note that the library only shows an alert with this data if something went wrong)
foreach($_POST AS $parm => $val)
{
  //echo("\r\nPassed parameter: ". $parm. " = ". $val); 
}
// Catch change of year, if VWO need to limit the choices in subjects
if($_POST['fieldid'] == "sljaar" && $_POST['sljaar'] == "VWO")
{ // VWO selected, inform with subject applicable
  $vslqr = inputclassbase::load_query("SELECT GROUP_CONCAT(mid) AS slist FROM subject 
                                       WHERE shortname = 'Ne' OR shortname = 'En' OR shortname = 'Sp' OR shortname = 'Gs'");
  if(isset($vslqr['slist']))
    echo("<VWOSWITCH>{". $vslqr['slist'][0]. "}");
  // Set the package to manual
  if($_SESSION['inputobjects']['selpack']->get_key() > 0)
    mysql_query("UPDATE inschrijvingAHA SET pakket='XX' WHERE rid=". $_SESSION['inputobjects']['selpack']->get_key(), $userlink);
}
// Catch change of year, if HAVO need to enable the choices in subjects (depending on year)
if($_POST['fieldid'] == "sljaar" && $_POST['sljaar'] != "VWO" && $_POST['sljaar'] != "")
{
  echo("<HAVOSEL". substr($_POST['sljaar'],0,1). ">");
}
// Catch change of subjectpackage
if($_POST['fieldid'] == "selpack")
{
  $rid = $_SESSION['inputobjects']['selpack']->get_key();
  // Clear all existing subject selections
  mysql_query("DELETE FROM inschrijvingPakket WHERE rid=". $rid, $userlink);
  mysql_query("DELETE FROM inschrijvingCerts WHERE rid=". $rid, $userlink);
  mysql_query("DELETE FROM inschrijvingVrijst WHERE rid=". $rid, $userlink);
  if($_POST['selpack'] == '' || $_POST['selpack'] == 'XX')
  { // None selected or request manual entry
		echo("<CLEARSUBJECTS>");
		if($_POST['selpack'] == 'XX')
			echo("<ENABLEMANUAL>");
		else
			echo("<DISABLEMANUAL><NOEXCEMPTIONS>");
  }
  else
  { // A subjectpackage is selected, get which ones and  set them and report back
    $slstq = "SELECT GROUP_CONCAT(mid) AS slst FROM isubpack LEFT JOIN subject USING(mid)";
		$slstq .= " WHERE CONCAT(SUBSTR(packagename,1,1),SUBSTR(packagename,4,2)) = '". $_POST['selpack']. "'";
		// Filter I&S and RE, only for 2 and 3
		$selyr = $_SESSION['inputobjects']['sljaar']->__toString();
		if(substr($selyr,0,1) != '2' && substr($selyr,0,1) != '3')
			$slstq .= " AND shortname <> 'I&S' AND shortname <> 'Re'";
		// Filter Pwf, only for year 3
		if(substr($selyr,0,1) != '3')
			$slstq .= " AND shortname <> 'Pfw'";
			$slstq .= " GROUP BY packagename";
			$slst = inputclassbase::load_query($slstq);
		if(isset($slst['slst'][0]))
		{
			$mids = explode(",",$slst['slst'][0]);
			foreach($mids AS $smid)
			{
				mysql_query("INSERT INTO inschrijvingPakket (rid,mid) VALUES(". $rid. ",". $smid. ")", $userlink);
			}
			echo("<DISABLEMANUAL>");
			echo("<SELECTEDSUBS>{". $slst['slst'][0]. "}");
		}
		// Set I&S with vrijstelling if in third year
		if(substr($selyr,0,1) == '3')
		{ // Get the mid applicable
			$ismidqr = inputclassbase::load_query("SELECT mid FROM subject WHERE shortname='I&S'");
			if(isset($ismidqr['mid']))
			{
				$ismid = $ismidqr['mid'][0];
				mysql_query("INSERT INTO inschrijvingVrijst (rid,mid) VALUES(". $rid. ",". $ismid. ")", $userlink);
				echo("<CHECKEXCEMPT>{". $ismid. "}");
			}
	}
  }
}

// Check and report matching of subjects with subjectpackages
// Calculate the adminsitration fee based on chosen subjects and exemptions
if($_POST['fieldid'] == "ssubs" || $_POST['fieldid'] == "sscerts" || $_POST['fieldid'] == "ssvrijst" || 
   $_POST['fieldid'] == "sljaar" || $_POST['fieldid'] == "selpack" ||
   $_POST['fieldid'] == "betaald" || $_POST['fieldid'] == "studiegids" || $_POST['fieldid'] == "boekenlijst")
{ // Something changed in the selected subjects or a checkbox was clicked, 
  // so see if a valid package results and report the resulting fee
  // First get the record id
  $recid = $_SESSION['inputobjects']['ssubs']->get_key();
	$seljr = SUBSTR($_SESSION['inputobjects']['sljaar']->__toString(),0,1);
  $pmq = "SELECT packagename, COUNT(isubpack.mid) AS pc, COUNT(t2.mid) AS sc FROM isubpack LEFT JOIN ";
  $pmq .= "(SELECT mid FROM inschrijvingPakket WHERE rid=". $recid. ") AS t2 USING(mid) ";
  $pmq .= "LEFT JOIN subject USING(mid) ";
  $pmq .= "WHERE (packagename LIKE '". $seljr. "MM%' OR packagename LIKE '". $seljr. "HU%' OR packagename LIKE '". $seljr. "NW%') ";
  //$pmq .= "AND shortname <> 'I&S' AND shortname <> 'Pfw' AND shortname <> 'Re' ";
  $pmq .= "GROUP BY packagename HAVING pc=sc";
  $pmqr = inputclassbase::load_query($pmq);
  if(isset($pmqr['packagename'][0]))
  {
		$protrans = array("MM"=>"Mens en Maatschappij","HU"=>"Humaniore","NW"=>"Natuur en Wetenschap");
    echo("<PACKAGERESULT>{<FONT color=blue><B>Het gekozen vakkenpakket is ". $protrans[substr($pmqr['packagename'][0],0,2)].
	     " pakket ". substr($pmqr['packagename'][0],3). ".</b></font>}");
  }
  else
    echo("<PACKAGERESULT>{<FONT color=RED><B>De gekozen vakken komen NIET overeen met een geldig vakkenpakket voor een HAVO diploma.</b></FONT>}");
  showfee($recid);
}
// Check if e-mail address is given or changed, if so send a message with the encoded record id as link so it can be retrieved afterward.
if($_POST['fieldid'] == 'semail')
{
  $emailadr = $_SESSION['inputobjects']['semail']->__toString();
  $recid = $_SESSION['inputobjects']['semail']->get_key();
  sendmaillink($emailadr,$recid);
}
function sendmaillink($emailadr,$recid)
{
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=utf8' . "\r\n";
  $msgtxt = "<html>
             <head>
			   <title>Aanmelding Avond Havo</title>
			 </head>
			 <body>
               Je aanmeldingformulier is opgenomen in het registratiesysteem van de Avond Havo.<BR>
               Je kunt de aanmelding wijzigen zolang niet betaald is.<BR>
			   <a href='https://myschoolresults.com/AH/RegistratieformulierAHA.php?regcode=". urlencode(base64_encode($recid)). "'>Klik hier om te wijzigen</a>.<BR>
			   (of navigeer naar https://myschoolresults.com/AH/RegistratieformulierAHA.php?regcode=". 	urlencode(base64_encode($recid)). ")
			 </body>
			 </html>";
  if($emailadr != "")
    mail($emailadr,"Aanmelding Avond Havo",$msgtxt,$headers,"-fnoreply@myschoolresults.com");
}
?>
