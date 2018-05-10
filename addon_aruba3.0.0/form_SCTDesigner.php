<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+

 // $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("schooladminconstants.php");
  require_once("inputlib/inputclasses.php");
  require_once("SCTclasses.php");
  session_start();
  // Connect to the database
  inputclassbase::dbconnect($userlink);
  // If a global SCTprefix is defined, we use that to prefix tables SCTtestand SCTtestitem, else global is emulated as empty
  if(!isset($SCTprefix))
    $SCTprefix="";
  
  if(isset($_POST['fieldid']))
  {
    // DEBUG: show field change
		//echo("RESULT: ". $_POST['fieldid']. " = ". $_POST[$_POST['fieldid']]);
		if($_POST['fieldid'] == "sctcname")
		{ // Conversion table name change, this is handled outside the library
			$fld = $_SESSION['inputobjects'][$_POST['fieldid']];
			$oldname = $fld->initial_value;
			mysql_query("UPDATE ". $SCTprefix. "SCTconversion SET conversiontype='". $_POST['sctcname']. "' WHERE conversiontype='". $oldname. "'");
			$_SESSION['SCTcnamerefresh'] = $_POST['sctcname'];
			echo("OK REFRESH");
			exit;
		}
    // Let the library page handle the data
    include("inputlib/procinput.php");  
    if(substr($_POST['fieldid'],0,8) == "sctcount")
		{
			// Remove any test configs with a seqence number greater than the number of tests.
			$myfld = $_SESSION['inputobjects'][$_POST['fieldid']];
			mysql_query("DELETE FROM ". $SCTprefix. "SCTtestitem WHERE sctid=". $myfld->get_key(). " AND seqno > ". $_POST[$_POST['fieldid']]);
			echo(" REFRESH");
			$_SESSION['SCTrefreshid']= $myfld->get_key();
		}
    if(substr($_POST['fieldid'],0,6) == "sctsub")
		{
			// Remove any sub-subject defined.
			$myfld = $_SESSION['inputobjects'][$_POST['fieldid']];
			mysql_query("UPDATE ". $SCTprefix. "SCTtestitem SET mid=NULL WHERE sctid=". $myfld->get_key(),$userlink);
			echo(" REFRESH");
			$_SESSION['SCTrefreshid']= $myfld->get_key();
		}
		if($_POST['fieldid'] == "SCTtpr-9")
		{
			//$_SESSION['SCTcnamerefresh'] = $_POST['sctcname'];
			echo("REFRESH");
		}
    exit;
  }
	
	// Handle deletion of convertion tables
	if(isset($_GET['dctab']))
	{
		mysql_query("DELETE FROM ". $SCTprefix. "SCTconversion WHERE conversiontype='". $_GET['dctab']. "'", $userlink);
		mysql_query("UPDATE ". $SCTprefix. "SCTtest SET conversiontype=NULL WHERE converisontype='". $_GET['dctab']. "'", $userlink);
		mysql_query("UPDATE ". $SCTprefix. "SCTtestsub SET conversiontype=NULL WHERE converisontype='". $_GET['dctab']. "'", $userlink);
	}
	
	// Handle deletion of tests
	if(isset($_GET['dtest']))
	{
		mysql_query("UPDATE ". $SCTprefix. "SCTtest SET description=NULL WHERE sctid='". $_GET['dtest']. "'", $userlink);
	}
	
  // Create a dummy field (invisible) to force a handler (myself)
  $trfld = new inputclass_listfield("dummy","SELECT '' AS id,'' AS tekst",NULL,"tdid","SCTtestref",0,"sctid","display:none",$_SERVER['PHP_SELF']);
  $trfld->echo_html();
  
  // Create tables if do not exist
  $sqlquery = "CREATE TABLE IF NOT EXISTS ". $SCTprefix. "SCTtest (
    `sctid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`description` TEXT,
		`mid` INTEGER(11),
		`signalborder` FLOAT,
		`signalweight` FLOAT,
		`signalmin` FLOAT,
		`controlborder` FLOAT,
		`controlweight` FLOAT,
		`controlmin` FLOAT,
		`terminalborder` FLOAT,
		`terminalweight` FLOAT,
		`terminalmin` FLOAT,
		`testcount` INTEGER(4),
		`pointsorerrors` ENUM ('p','e'),
		`ignorecatweights` BOOLEAN DEFAULT 0,
		PRIMARY KEY (`sctid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS ". $SCTprefix. "SCTtestitem (
    `sctid` INTEGER(11) UNSIGNED,
		`seqno` INTEGER(4),
		`mid` INTEGER(11),
		`category` TEXT,
		`description` TEXT,
		`maxpoints` INTEGER(4),
		`treshold` INTEGER(4),
		PRIMARY KEY (`sctid`,`seqno`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `SCTresult` (
    `sctresid` INTEGER(11) NOT NULL AUTO_INCREMENT,
		`sid` INTEGER(11),
    `sctid` INTEGER(11) UNSIGNED,
		`seqno` INTEGER(4),
		`year`  VARCHAR(20),
		`phase` ENUM ('s','c','t'),
		`result` INTEGER(4),
		`remediate` TEXT,
		PRIMARY KEY (`sctresid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `SCTtestref` (
    `sctid` INTEGER(11) UNSIGNED,
		`cid` INTEGER(11),
		`tdid` INTEGER(11),
		PRIMARY KEY (`sctid`,`cid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
    
  $sqlquery = "CREATE TABLE IF NOT EXISTS `SCTtestdates` (
    `sctid` INTEGER(11) UNSIGNED,
		`gid` INTEGER(11),
		`sdate` DATE,
		`cdate` DATE,
		`tdate` DATE,
		PRIMARY KEY (`sctid`,`gid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());

  $sqlquery = "CREATE TABLE IF NOT EXISTS ". $SCTprefix. "`SCTconversion` (
		`convid`  INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversiontype` VARCHAR(40),
		`trippoint` FLOAT,
		`result` FLOAT,
		PRIMARY KEY (`convid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
	
		$sqlquery = "CREATE TABLE IF NOT EXISTS ". $SCTprefix. "SCTtestsub (
			`sctid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`mid` INTEGER(11),
			`signalborder` FLOAT,
			`signalweight` FLOAT,
			`signalmin` FLOAT,
			`controlborder` FLOAT,
			`controlweight` FLOAT,
			`controlmin` FLOAT,
			`terminalborder` FLOAT,
			`terminalweight` FLOAT,
			`terminalmin` FLOAT,
			`pointsorerrors` ENUM ('p','e'),
			`conversiontype` VARCHAR(40) DEFAULT NULL,
			`ignorecatweights` BOOLEAN DEFAULT NULL,
			PRIMARY KEY (`sctid`,`mid`)
			) ENGINE=InnoDB;";
		mysql_query($sqlquery,$userlink);
		echo(mysql_error());

		// See if the intermediate treshold exists, if not, we need to do some conversions on the database
	$ithcheck = inputclassbase::load_query("SHOW COLUMNS FROM SCTtestitem LIKE 'itreshold'");
	if(!isset($ithcheck))
	{ 
		// Set the default conversiontypes
		mysql_query("ALTER TABLE ". $SCTprefix. "SCTtest ADD COLUMN conversiontype VARCHAR(40) AFTER pointsorerrors", $userlink);
		echo(mysql_error());
		mysql_query("UPDATE ". $SCTprefix. "SCTtest SET conversiontype='*K5.5' WHERE pointsorerrors='e'", $userlink);
		echo(mysql_error());
		mysql_query("UPDATE ". $SCTprefix. "SCTtest SET conversiontype='*K6.0' WHERE pointsorerrors='p'", $userlink);
		echo(mysql_error());
		mysql_query("ALTER TABLE ". $SCTprefix. "SCTtestitem ADD COLUMN abbreviation VARCHAR(40) AFTER description", $userlink);
		echo(mysql_error());
		mysql_query("ALTER TABLE ". $SCTprefix. "SCTtestitem ADD COLUMN itreshold INT(4) AFTER treshold", $userlink);
		echo(mysql_error());		
	}
    
  // Link with stylesheet
  echo ('<HTML><BODY><LINK rel="stylesheet" type="text/css" href="style_SCT.css" title="style1">');
  
  // Get the year
  $schoolyear = date("Y"). "-" .(date("Y")+1);
  echo("<H1>SCT (Signaal, Controle, Toepassing) Toets ontwerper</H1>");
  if(!isset($_GET['edit']) && !isset($_GET['ctab']))
  { // Select an SCT test or create a new one
    $sctlist = SCTtest::listObjects();
		if(isset($sctlist))
		{
			echo("<table><tr><th>Omschrijving</th><th>&nbsp</th><th>&nbsp</th></tr>");
			foreach($sctlist AS $scttestobj)
			{
				echo("<TR><TD>". $scttestobj->get_description(). "</td><td><a href=". $_SERVER['PHP_SELF']. "?edit=". $scttestobj->get_id(). "><img src='PNG/reply.png'></a></td><td><a href=". $_SERVER['PHP_SELF']. "?dtest=". $scttestobj->get_id(). "><img src='PNG/action_delete.png'></a></td>");
			}
			echo("</table>");
		}
		echo("<a href=". $_SERVER['PHP_SELF']. "?edit=0>Nieuwe SCT toets aanmaken</a><BR><BR>");
		
		// Now show the conversiontables
		$ctabs = SCTconversion::list_conversions();
		if(isset($ctabs))
		{
			echo("<table><tr><th>Conversie</th><th>&nbsp</th><th>&nbsp</th></tr>");
			foreach($ctabs AS $ctabname)
			{
				echo("<TR><TD>". $ctabname. "</td><td><a href=". $_SERVER['PHP_SELF']. "?ctab=". urlencode($ctabname). "><img src='PNG/reply.png'></a></td><td><a href=". $_SERVER['PHP_SELF']. "?dctab=". urlencode($ctabname). "><img src='PNG/action_delete.png'></a></td>");
			}
			echo("</table>");		
		}
		echo("<a href=". $_SERVER['PHP_SELF']. "?ctab=new>Nieuwe conversietabel aanmaken</a>");
		// Forget old refreshid
		unset($_SESSION['SCTrefreshid']);
		unset($_SESSION['SCTcnamerefresh']);
  }
  else if(isset($_GET['edit']))
  { // Editing a selected SCT test
    // 0 normally indicates a new one but if an id is set for refresh we need to revise that
		if($_GET['edit'] == 0 && isset($_SESSION['SCTrefreshid']))
			$_GET['edit'] = $_SESSION['SCTrefreshid'];
		echo("<BR><a href='". $_SERVER['PHP_SELF']. "'>Toetslijst</a><BR>");
			$scttestobj = new SCTtest($_GET['edit']);
		echo("<BR><LABEL>Omschrijving:</LABEL>");
		$scttestobj->edit_description();
		echo("<BR><LABEL>Vak:</LABEL>");
		$scttestobj->edit_subject();
		echo("<BR><LABEL>Score basis:</LABEL>");
		$scttestobj->edit_type();
		echo("<BR><LABEL>Aantal vraagcategorieën:</LABEL>");
		$scttestobj->edit_testcount();
		echo("<BR><LABEL>Alle categorieën tellen gelijk:</LABEL>");
		$scttestobj->edit_catweight();
		echo("<BR><LABEL>Cijfermethode:</LABEL>");
		$scttestobj->edit_conversiontype();
		echo("<table><TR><TH>&nbsp;</th><TH>Signaaltoets</th><TH>Controletoets</th><TH>Toepassingtoets</th></tr>");
		echo("<TR><th>Minimum cijfer</th><td>");
		$scttestobj->edit_item("signalmin");
		echo("</td><td>");
		$scttestobj->edit_item("controlmin");
		echo("</td><td>");
		$scttestobj->edit_item("terminalmin");
		echo("</td></tr>");
		echo("<TR><th>Foutgrens (%)</th><td>");
		$scttestobj->edit_item("signalborder");
		echo("</td><td>");
		$scttestobj->edit_item("controlborder");
		echo("</td><td>");
		$scttestobj->edit_item("terminalborder");
		echo("</td></tr>");
		echo("<TR><th>Gewicht</th><td>");
		$scttestobj->edit_item("signalweight");
		echo("</td><td>");
		$scttestobj->edit_item("controlweight");
		echo("</td><td>");
		$scttestobj->edit_item("terminalweight");
		echo("</td></tr>");
		echo("</table>");
		//Now if the subject is a meta-subject, deviating setting can be done per sub-subject
		$subsubjects = $scttestobj->get_subjectoptions();
		if(count($subsubjects) > 1)
		{
			foreach($subsubjects AS $smid => $subsub)
			{
				echo("<FIELDSET style='background-color: #FFC;'><LEGEND>". $subsub. "</LEGEND>");
				echo("<BR><LABEL>Alle categorieën tellen gelijk:</LABEL>");
				$scttestobj->edit_catweight($smid);
				echo("<BR><LABEL>Cijfermethode:</LABEL>");
				$scttestobj->edit_conversiontype($smid);
				echo("<table><TR><TH>&nbsp;</th><TH>Signaaltoets</th><TH>Controletoets</th><TH>Toepassingtoets</th></tr>");
				echo("<TR><th>Minimum cijfer</th><td>");
				$scttestobj->edit_item("signalmin",$smid);
				echo("</td><td>");
				$scttestobj->edit_item("controlmin",$smid);
				echo("</td><td>");
				$scttestobj->edit_item("terminalmin",$smid);
				echo("</td></tr>");
				echo("<TR><th>Foutgrens (%)</th><td>");
				$scttestobj->edit_item("signalborder",$smid);
				echo("</td><td>");
				$scttestobj->edit_item("controlborder",$smid);
				echo("</td><td>");
				$scttestobj->edit_item("terminalborder",$smid);
				echo("</td></tr>");
				echo("<TR><th>Gewicht</th><td>");
				$scttestobj->edit_item("signalweight",$smid);
				echo("</td><td>");
				$scttestobj->edit_item("controlweight",$smid);
				echo("</td><td>");
				$scttestobj->edit_item("terminalweight",$smid);
				echo("</td></tr>");
				echo("</table>");
				
				echo("</fieldset>");
			}
		}

		// And now come the questions:
		$testitemobjs = $scttestobj->get_testitems();
		if(isset($testitemobjs))
		{
			echo("<BR>Vraagcategorieën:<BR><table><TR><TH>Omschrijving</th><th>Afkorting</th><th>Remediering</th><TH>Vak</th><TH>");
			if($scttestobj->get_type() == "Punten")
				echo("Max punten");
			else
				echo("Max fouten");
			echo("</th><th>Signaleringsgrens</th><th>Tussengrens</th></tr>");
			foreach($testitemobjs AS $testitemobj)
			{
				echo("<TR><TD>");
				$testitemobj->edit_description();
				echo("</td><TD>");
				$testitemobj->edit_abreviation();
				echo("</td><TD>");
				$testitemobj->edit_category();
				echo("</td><TD>");
				$testitemobj->edit_subject();
				echo("</td><TD>");
				$testitemobj->edit_maxpoints();
				echo("</td><TD>");
				$testitemobj->edit_treshold();
				echo("</td><TD>");
				$testitemobj->edit_itreshold();
				echo("</td></tr>");
			}
			echo("</table>");
		}
  }
  else if(isset($_GET['ctab']))
	{
		echo("<BR><a href='". $_SERVER['PHP_SELF']. "'>Toetslijst</a><BR>");
		if(isset($_SESSION['SCTcnamerefresh']))
			$_GET['ctab'] = $_SESSION['SCTcnamerefresh'];
		$contab = new SCTconversion($_GET['ctab']);
		$contab->edit();
	}
	// close the page
  echo("</BODY></html>");
?>
