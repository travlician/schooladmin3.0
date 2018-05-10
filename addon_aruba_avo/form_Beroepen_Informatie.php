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
  require_once("inputlib/inputclasses.php");
  session_start();
  //error_reporting(E_STRICT);
	require_once("schooladminconstants.php");

  inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);
  $userlink = inputclassbase::$dbconnection;
	/*// Debug: show all posted values
	if(isset($_POST))
		foreach($_POST AS $key => $val)
			echo("Posted ". $key. "=". $val. "<BR>"); */
			
	$dtext = $_SESSION['dtext'];
	
	if(isset($_POST['newprof']) && isset($_SESSION['CurrentProfession']) && $_POST['newprof'] == $_SESSION['CurrentProfession'])
	{
	  unset($_SESSION['CurrentProfession']);
	}
	
	// handling input
	if(isset($_POST['fieldid']))
	{
		if($_POST['fieldid'] == "professionsearch")
		{ // A profession to search for or change it's data has been defined...
	      $_SESSION['CurrentProfession'] = $_POST[$_POST['fieldid']];
	    echo("OK REFRESH");
			exit;
		}
		// Other data, to be handled by library
    // Let the library page handle the data
		include("inputlib/procinput.php");
    // Put refresh if sector added
    if($_POST['fieldid'] == "bsec0")	
			echo(" REFRESH");
		exit;
	}
	if(isset($_GET['delsec']))
		mysql_query("DELETE FROM arubacom.beroepensector WHERE id=". $_GET['delsec']);
 
  // Subject translation tables
  $offsubjects = array(1 => "Lo","Ne","En","Sp","Pa","Wi-A","Wi-B","Na","Sk","Bio","Ec","M&O","Ak","Gs","CKV");
	$studylevel = array(1=>"MBO","MBO of HBO","MBO, HBO of WO", "HBO", "HBO of WO","WO");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $uid = intval($uid);
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `arubacom`.`beroepeninfo` (
    `beroepid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `beroepnaam` VARCHAR(255) DEFAULT NULL,
    `sectorid` INTEGER(11) UNSIGNED DEFAULT NULL,
    `omschrijving` TEXT DEFAULT NULL,
    `omschrijvinglink` TEXT DEFAULT NULL,
    `waarwerk` TEXT DEFAULT NULL,
    `waarwerklink` TEXT DEFAULT NULL,
    `waarstuderen` TEXT DEFAULT NULL,
		`opleidingsniveau` INTEGER(11) DEFAULT NULL,";
	
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `arubacom`.`beroepensector` (
    `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `tekst` VARCHAR(255) DEFAULT NULL,
	   PRIMARY KEY (`id`)
    ) ENGINE=InnoDB CHARSET=utf8;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());

  $sqlquery = "CREATE TABLE IF NOT EXISTS `beroepkeuze_vakmatch` (
    `orgvak` INTEGER(11) NOT NULL,
    `mid` INTEGER(11) NOT NULL,
	  PRIMARY KEY (`orgvak`)
    ) ENGINE=InnoDB CHARSET=utf8;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
    

  // First part of the page
  echo("<html><head><title>Beroepen en vakken invoer</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
?>
  <style>
	LABEL
	{
		text-align: left;
		width: 30%;
		display: inline-block;
	}
	</style>
<?
  echo("<font size=+2><center>Beroepen informatie</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a></center><br>");
	if(isset($_GET['sector']))
	{
		// Editing sectors...
		$seclist = inputclassbase::load_query("SELECT id FROM arubacom.beroepensector ORDER BY tekst");
		if(isset($seclist['id']))
		{
			foreach($seclist['id'] AS $secid)
			{
				echo("<BR>");
				$secitem = new inputclass_textfield("bsec". $secid,40,$userlink,"tekst","arubacom.beroepensector",$secid,"id","","form_Beroepen_Informatie.php");
				$secitem->echo_html();
				echo("<a href='". $_SERVER['REQUEST_URI']. "&delsec=". $secid. "'><img src=PNG/action_delete.png border=0></a>");
			}
		}
		echo("<BR>");
		$secitem = new inputclass_textfield("bsec0",40,$userlink,"tekst","arubacom.beroepensector",0,"id","","form_Beroepen_Informatie.php");
		$secitem->echo_html();
		echo("<img src=PNG/action_add.png>");
	}
  
	// If not profession is selected / entered, now we show the field to enter the search or entry
	else if(!isset($_SESSION['CurrentProfession']) && isset($_POST['subx']))
	{ // Allow setting of school specific subjects related to the official subjects.
    echo("Instelling vakken eigen school voor officiele vakkenlijst");
		foreach($offsubjects AS $asix=>$asubj)
		{
			echo("<BR><LABEL>". $asubj. "</label>");
			$sselfld = new inputclass_listfield("subx". $asix,"SELECT 0 AS id, '' AS tekst UNION SELECT mid,shortname FROM subject",$userlink,"mid","beroepkeuze_vakmatch",$asix,"orgvak","","form_Beroepen_Informatie.php");
			$sselfld->echo_html();
		}
		
	}
	else if(!isset($_SESSION['CurrentProfession']))
	{
		echo("<BR><FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' ID=subxform><input type=hidden name=subx value=1></FORM><A href=# onClick='document.getElementById(\"subxform\").submit();'>Instelling vakken eigen school</a><BR>");
		echo("<a href='". $_SERVER['REQUEST_URI']. "?sector=1'> Bewerk lijst sectoren</a><BR>");
		echo("Beroep: ");
    $searchfield = new inputclass_autosuggest("professionsearch",40,$userlink,"beroepnaam","arubacom.beroepeninfo",0,"beroepid","","form_Beroepen_Informatie.php");
		$searchfield->echo_html();
	}
	else
	{
		// Allow choice for another profession
		echo("<BR><FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' ID=newprofform><input type=hidden name=newprof value='". $_SESSION['CurrentProfession']. "'></FORM><A href=# onClick='document.getElementById(\"newprofform\").submit();'>Ander beroep</a><BR><BR>");
		// See if we got a new profession or need to edit an existing one
		$profqr = inputclassbase::load_query("SELECT beroepid FROM arubacom.beroepeninfo WHERE beroepnaam=\"". $_SESSION['CurrentProfession']. "\"");
		if(isset($profqr['beroepid']))
		{
			$prid = $profqr['beroepid'][0];
		}
		else
			$prid = 0;
		// Now add the fields used for a profession
		echo("<label>Beroep: </label>");
		$bnfld = new inputclass_textfield("bn",40,$userlink,"beroepnaam","arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
		if($prid == 0)
			$bnfld->set_initial_value($_SESSION['CurrentProfession']);
		$bnfld->echo_html();
		echo("<BR><label>Beroepssector: </label>");
		$scfld = new inputclass_listfield("bs","SELECT '' AS id,'' AS tekst UNION SELECT * FROM arubacom.beroepensector ORDER BY tekst",$userlink,"sectorid","arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
		if($prid == 0)
			$scfld->set_extrafield("beroepnaam",$_SESSION['CurrentProfession']);
		$scfld->echo_html();
		echo("<BR><label>Omschrijving: </label>");
		$descfld = new inputclass_ckeditor("desc","40,4",$userlink,"omschrijving","arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
		$descfld->echo_html();
		echo("<BR><label>Link Omschrijving: </label>");
		$desclnkfld = new inputclass_textfield("desclnk",64,$userlink,"omschrijvinglink","arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
		$desclnkfld->echo_html();
		echo("<BR><label>Waar kun je werken: </label>");
		$workfld = new inputclass_ckeditor("work","40,4",$userlink,"waarwerk","arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
		$workfld->echo_html();
		echo("<BR><label>Waar kun je werken link: </label>");
		$worklnkfld = new inputclass_textfield("worklnk",64,$userlink,"waarwerklink","arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
		$worklnkfld->echo_html();
		echo("<BR><label>Vereist opleidingniveau: </label>");
		$stlevelqr = "SELECT 0 AS id, '' AS tekst";
		foreach($studylevel AS $stix => $sttxt)
		  $stlevelqr .= " UNION SELECT ". $stix. ",'". $sttxt. "'";
		$studylevelfld = new inputclass_listfield("studylvl",$stlevelqr,$userlink,"opleidingsniveau","arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
		$studylevelfld->echo_html();
		echo("<BR><label>Waar kun je studeren: </label>");
		$studyfld = new inputclass_ckeditor("study","40,4",$userlink,"waarstuderen","arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
		$studyfld->echo_html();
		echo("<BR><label>Vereiste vakken voor dit beroep: </label>");
		foreach($offsubjects AS $asubj)
		{
		  $subjectfld = new inputclass_checkbox("offsubjsel". $asubj,NULL,$userlink,$asubj,"arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
		  $subjectfld->echo_html();
			echo(" ". $asubj. "<BR><label>&nbsp;</label>");		
		}
		
		// List the avaliable subject packages for this school
		echo("<BR><LABEL>Beschikbare vakkenpakketten op de school:</label>");
		$packlist = inputclassbase::load_query("SELECT packagename,GROUP_CONCAT(orgvak) AS ovk, GROUP_CONCAT(shortname) AS ssn FROM subjectpackage LEFT JOIN subject USING(mid) LEFT JOIN beroepkeuze_vakmatch USING(mid) GROUP BY packagename ORDER BY packagename");
		$haveshown = false;
		foreach($packlist['packagename'] AS $pkix => $pkname)
		{
			$validate = true;
			$orgvks = explode(",",$packlist['ovk'][$pkix]);
			foreach($offsubjects AS $osix => $asubj)
			{
				$chkfld = $subjectfld = new inputclass_checkbox("xx". $asubj,NULL,$userlink,$asubj,"arubacom.beroepeninfo",$prid,"beroepid","","form_Beroepen_Informatie.php");
				if($chkfld->__toString() == "1")
				{
					if(!in_array($osix,$orgvks))
						$validate = false;
				}
			}
			if($validate)
			{
			  echo("<BR><LABEL>&nbsp;</label>". $pkname. " (". $packlist['ssn'][$pkix]. ")");
				$haveshown = true;
			}
		}
		if(!$haveshown)
			echo("Er zijn geen vakkenpakketten beschikbaar voor dit beroep.");
	}
  // close the page
  echo("</html>");
?>
