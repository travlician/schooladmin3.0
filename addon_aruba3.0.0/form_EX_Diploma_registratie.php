<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  require_once("inputlib/inputclasses.php");
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("student.php");
  require_once("group.php");
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  // Link input library with database
  inputclassbase::dbconnect($userlink);
	
	// See if data has been posted for AJAX funtions
	if(isset($_POST['fieldid']))
	{
		//echo("ERR");
		include("inputlib/procinput.php");
		if(substr($_POST['fieldid'],0,8) == "dipldate")
		{ // A date for the diploma has been set, now set this date for all others that have no date
			$setdateq = "INSERT INTO s_ASDiplomaDate (sid,data) SELECT sid,'". inputclassbase::nldate2mysql($_POST[$_POST['fieldid']]). "' FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) LEFT JOIN s_ASDiplomaDate USING(sid) WHERE groupname LIKE 'Exam%' AND data IS NULL";
			mysql_query($setdateq,$userlink);
			echo(mysql_error($userlink));
			if(mysql_affected_rows($userlink) > 0)
			  echo("REFRESH");			
		}
		exit;
	}

  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `s_ASDiplomaRegNr` (
    `sid` INTEGER(11) UNSIGNED NOT NULL,
    `data` TEXT DEFAULT NULL,
	  PRIMARY KEY (`sid`)
    ) ENGINE=InnoDB CHARSET='utf8';";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());

  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `s_ASDiplomaDate` (
    `sid` INTEGER(11) UNSIGNED NOT NULL,
    `data` TEXT DEFAULT NULL,
	  PRIMARY KEY (`sid`)
    ) ENGINE=InnoDB CHARSET='utf8';";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());

  echo ('<LINK rel="stylesheet" type="text/css" href="style_EX_Diploma_registratie.css" title="style1">');
  
  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
//  $schoolname = str_replace($schoolname,""," S.K.O.A.");
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get the remarks pur in the exam result entry form
  $remarksqr = SA_loadquery("SELECT * FROM examresult WHERE year='". $schoolyear. "'", $userlink);
  if(isset($remarksqr))
    foreach($remarksqr['sid'] AS $six => $sid)
	{
	  $remarks[$sid] = $remarksqr['xresult'][$six];
	}
  
 // Stylesheets:
  echo("<html><head><title>EX Diploma registratie</title></head><body link=blue vlink=blue>");
  //echo('<link rel="stylesheet" type="text/css" media="all" href="inputlib/datechooser.css">');

		$KlasLijst = group::group_list("Exam%");
	if(isset($KlasLijst))
    foreach ($KlasLijst AS $group)
	{
// 	Titel KlasKaart met gewenste gegevens van de leerling uit de betreffende klas:
//    echo("<P class = koptxt3>". substr($group->get_groupname(),6). " ". $schoolyear."<span class = tal1spaties></P>");
	$SoortOnderwijs = "";
	switch (substr($group->get_groupname(),4))
	{
		case "Mavo":
			$SoortOnderwijs = "MAVO - Middelbaar Algemeen Voortgezet Onderwijs";
		break;
		case "Havo":
			$SoortOnderwijs = "HAVO - Hoger Algemeen Voortgezet Onderwijs";
		break;
		case "Vwo":
			$SoortOnderwijs = "VWO - Voortgezet Wetenschappelijk Onderwijs";
		break;
	}

// 	Tabel met de gewenste gegevens van de leerling uit de betreffende klas:
	
		$Nr = 0;
	    $LLlijst = student::student_list($group);
		foreach ($LLlijst AS $student)
		{
		    if($Nr % 20 == 0)
			{
			  if($Nr != 0)
			    echo("</table><p class=pagebreak>Datum: ". date("d-m-Y"). "<span style='float: right'>Pagina ". ($Nr / 20). "</span></p>");
			  echo("<table><tr><td colspan=5  class = koptxt1a>School: ". $schoolname. "</td><td colspan=5 class = koptxt1b>Schooljaar: " . $schoolyear ."</td colspan=4></tr>
			        <tr><td colspan=5  class = koptxt2>Opleiding: AVO - Algemeen Voortgezet Onderwijs</td><td colspan=5 class=koptxt2>Studieduur: ");
				switch (substr($group->get_groupname(),4))
				{
					case "Mavo":
						echo("4");
					break;
					case "Havo":
						echo("5");
					break;
					case "Vwo":
						echo("6");
					break;
				}
				
							
				echo(" jaar</td></tr><tr><td colspan=5 class = koptxt2>Afdeling: ". $SoortOnderwijs . "</td><td colspan=5 class=koptxt2>Registratiedatum: 01 juli ". date("Y"). "</td></tr>
			        <tr><th class = koptxt3>Registratie Nr.</th><th class = koptxt3>Soort<BR>Reg.</th><th class = koptxt3>Profiel</th><th class = koptxt3>Achternaam</th><th class = koptxt3>Voornamen</th><th class = koptxt3>v/m</th>
			        <th class = koptxt3>Geboortedatum</th><th class = koptxt3>Geboorteland</th><th class = koptxt3>Datum<BR>Diploma/Cijferlijst</th><th class = koptxt3>Opmerkingen</th></tr>");
			}
			++$Nr;
			$subjpack = $student->get_student_detail("*package");
			$profile = substr($subjpack,0,2);
			$slst = strpos($subjpack,"(");
			$slend = strpos($subjpack,")");
			$sl = substr($subjpack,$slst+1,$slend-$slst-1);
			$slexpl = explode(",",$sl);
			$scnt = 0;
			foreach($slexpl AS $asubj)
			  if(strtolower($asubj) != "lo" && strtolower($asubj) != "rek")
					$scnt++;
			
			if(strpos($subjpack," : ") > 0)
			{
				$esl = substr($subjpack,strpos($subjpack," : ") + 2);
				$esle = explode(",",$esl);
				$scnt += count($esle);
			}
			
			$regnrfld = new inputclass_textfield("diplregnr". $student->get_id(),15,NULL,"data","s_ASDiplomaRegNr",$student->get_id(),"sid");

			echo("<tr><td class=opmaakcenter>");
			$regnrfld->echo_html();
			echo("</td><td class=opmaakcenter>D". $scnt. "</td><td class = opmaakCenter>". $profile);
			
			
			echo("</td><td class = opmaakLinks>". $student->get_lastname(). "</td><td class = opmaakLinks>". $student->get_firstname(). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASGender"). "</td>
					<td class = opmaaklinks>". fulldate($student->get_student_detail("s_ASBirthDate")). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASBirthCountry"). "</td>
					<td class = opmaakCenter>");
			$ddatfld = new inputclass_datefield("dipldate". $student->get_id(),NULL,NULL,"data","s_ASDiplomaDate",$student->get_id(),"sid");
			$ddatfld->echo_html();
			
			echo("</td><td class = opmaaklinks>". (isset($remarks[$student->get_id()]) ? $remarks[$student->get_id()] : "&nbsp;"). "</td></tr>") ;

		} // einde foreach student / leerling uit de klas
	    echo("</table><p class=pagebreak>Datum: ". date("d-m-Y"). "<span style='float: right'>Pagina ". ceil($Nr / 20). "</span></p>");
		echo("<SPAN class=pagebreak>&nbsp;</SPAN>");			
	} // einde foreach group / klas



    
  // close the page
  echo("</html>");
	function fulldate($adate)
	{
		$dtrans = array("01"=>"januari","02"=>"februari","03"=>"maart","04"=>"april","05"=>"mei","06"=>"juni","07"=>"juli","08"=>"augustus","09"=>"september","10"=>"oktober","11"=>"november","12"=>"december");
		if(substr($adate,2,1) == "-" && substr($adate,5,1) == "-")
		{
			$adate = substr($adate,0,2). " ". $dtrans[substr($adate,3,2)]. " ". substr($adate,6);
		}
		return $adate;
	}
?>
