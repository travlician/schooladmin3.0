<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
//
// Dit inschrijfformulier is gemaakt voor de Avond Havo
  session_start();

 // $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("schooladminconstants.php");
  require_once("inputlib/inputclasses.php");
  require_once("InschrijvingAHAfuncs.php");
  // Connect to the database
  inputclassbase::dbconnect($userlink);
  
  if(isset($_GET['showpackage']))
  { // This is just to show the user which subject packages are available.
    // Translation of profile code to profile name
		$protrans = array("MM"=>"Mens en Maatschappijwetenschappen","HU"=>"Humaniora","NW"=>"Natuurwetenschappen");
		$subpq = "SELECT SUBSTR(packagename,1,1) AS year, SUBSTR(packagename,2,2) AS profiel, SUBSTR(packagename, 4) AS packnr, GROUP_CONCAT(fullname) AS subjects";
		$subpq .= " FROM isubpack LEFT JOIN subject USING(mid)";
		$subpq .= " WHERE shortname <> 'I&S' AND shortname <> 'Re' AND (packagename LIKE '_MM%' OR packagename LIKE '_HU%' OR packagename LIKE '_NW%')";
		$subpq .= " GROUP BY packagename ORDER BY SUBSTR(packagename,1,1), SUBSTR(packagename,4,2)";
		$subpqr = inputclassbase::load_query($subpq);
    echo ('<HTML><LINK rel="stylesheet" type="text/css" href="style_InschrijfAHA.css" title="style1">');
		echo("<H1>Overzicht vakkenpakketten voor de Avond Havo</H1>");
		echo("<H3>Let op: Pakketten met Natuurkunde en/of Wiskunde B kunnen niet worden gekozen voor het eerste jaar!</h3>");
		echo("<H3>Pakketten met Wiskunde B, Informatica of Papiamento kunnen niet worden gekozen voor het tweede jaar!</h3>");
		echo("<H3>Pakketten met Informatica of Papiamento kunnen niet worden gekozen voor het derde jaar!</h3>");
		echo("<A href='JavaScript:window.close()'>Klik <B>hier</b> om terug te keren naar het aanmeldingsformulier</a>");
		echo("<TABLE border=1 class=packagetable><TR><TH>Jaar</TH><TH>Profiel</TH><TH>Nummer</TH><TH>Vakken</TH></TR>");
		foreach($subpqr['profiel'] AS $subpix => $subpr)
		{
			echo("<TR><TD>". $subpqr['year'][$subpix]. "</td><TD>". $protrans[$subpr]. "</td><TD><center>". $subpqr['packnr'][$subpix]. "</center></td>
								<TD>". $subpqr['subjects'][$subpix]. "</TD></TR>");
		}
		echo("</TABLE></HTML>");
    exit;
  }
  // Create tables if do not exist
  $sqlquery = "CREATE TABLE IF NOT EXISTS `inschrijvingAHA` (
    `rid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
 	`year` VARCHAR(30),
    `sid` INTEGER(11) DEFAULT NULL,
    `firstname` TEXT DEFAULT NULL,
    `lastname` TEXT DEFAULT NULL,
	`roepnaam` TEXT DEFAULT NULL,
	`geslacht` TEXT DEFAULT NULL,
	`gebdag` TEXT DEFAULT NULL,
	`gebmaand` TEXT DEFAULT NULL,
	`gebjaar` TEXT DEFAULT NULL,
	`aantalkinderen` TEXT DEFAULT NULL,
	`voertaal` TEXT DEFAULT NULL,
	`gebland` TEXT DEFAULT NULL,
	`adres` TEXT DEFAULT NULL,
	`email` TEXT DEFAULT NULL,
	`telthuis` TEXT DEFAULT NULL,
	`telmobile` TEXT DEFAULT NULL,
	`bankrekening` TEXT DEFAULT NULL,
	`werkzaambij` TEXT DEFAULT NULL,
	`laatsteschool` TEXT DEFAULT NULL,
	`leerjaar` TEXT DEFAULT NULL,
	`plaatsjaar` TEXT DEFAULT NULL,
	`pakket` TEXT DEFAULT NULL,
	`idnummer` TEXT DEFAULT NULL,
	`wachtwoord` TEXT DEFAULT NULL,
	`betaald` BOOLEAN DEFAULT NULL,
	`studiegids` BOOLEAN DEFAULT NULL,
	`boekenlijst` BOOLEAN DEFAULT NULL,
	`opmerkingen` TEXT DEFAULT NULL,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`rid`),
    UNIQUE KEY `sidyr` (`sid`, `year`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `inschrijvingCerts` (
    `rid` INTEGER(11) UNSIGNED,
    `mid` INTEGER(11) DEFAULT NULL,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`rid`,`mid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `inschrijvingVrijst` (
    `rid` INTEGER(11) UNSIGNED,
    `mid` INTEGER(11) DEFAULT NULL,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`rid`,`mid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `inschrijvingPakket` (
    `rid` INTEGER(11) UNSIGNED,
    `mid` INTEGER(11) DEFAULT NULL,
    `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`rid`,`mid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  // Link with stylesheet
  echo ('<HTML><LINK rel="stylesheet" type="text/css" href="style_InschrijfAHA.css" title="style1">');
  // At some place we need a list of months
  $montxt = array(1=>'januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december');
  
  // Get the year
  $schoolyear = date("Y"). "-" .(date("Y")+1);
  echo("<P class=topheading>AANMELDINGSFORMULIER<BR>AVOND HAVO<BR>". $schoolyear. "</P>");
  echo("<IMG class=toplogo src=schoollogo.png>");
  $rid=0;
  if(isset($_GET['regcode']))
  { // Convert registration code to rid (record in registation table)
    $rid=base64_decode(urldecode($_GET['regcode']));
  }
  if(isset($_POST['rid']))
    $rid = $_POST['rid']; // Set by school adminisration
  else if(isset($_POST['userid']))
  {
    if($_POST['userid'] != "" && $_POST['userpw'] != "")
	{ // Check login for this student
	  $logindata = inputclassbase::load_query("SELECT sid FROM student WHERE altsid=\"". $_POST['userid']. "\" AND password=\"". $_POST['userpw']. "\"");
	  if(isset($logindata['sid']))
	  { // student ID is retrieved from database based on login data, now check if a record already exists or create one
	    $ridqr = inputclassbase::load_query("SELECT rid,betaald FROM inschrijvingAHA WHERE sid=". $logindata['sid'][0]. " AND year='". $schoolyear. "'");
		if(isset($ridqr['rid']))
		{  // Known student and registration filed already
		  if($ridqr['betaald'][0] == 1)
		  {  // Record has already been processed, student can not change data
		    echo("<P class=errormsg>Je aanmelding is al verwerkt, neem contact op met de adminsitratie om wijzigigen aan te brengen</p>");
			exit;
		  }
		  else // Known student and record is alterable
		  {
	        $rid= $ridqr['rid'][0];
		  }
		}
		else
		{ // Known student but no registration record filed yet, so create a new record with base info
		  // Since birth dates have been an issue, we now need to do some smart conversion on it before we proceed.
		  $bd = "NULL"; $bm="NULL"; $by="NULL"; // Defaults in case conversion fails
		  $bdqr = inputclassbase::load_query("SELECT data FROM s_ASBirthDate WHERE sid=". $logindata['sid'][0]);
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
		  }
		  $insrecq = "INSERT INTO inschrijvingAHA (year,sid,firstname,lastname,roepnaam,geslacht,gebdag,gebmaand,gebjaar,gebland,adres,email,telthuis,telmobile,laatsteschool,idnummer,wachtwoord,bankrekening,aantalkinderen,voertaal,werkzaambij)";
		  $insrecq .= " SELECT '". $schoolyear. "',student.sid,firstname,lastname,s_roepnaam.data,s_ASGender.data,". $bd. ",". $bm. ",". $by. ",";
		  $insrecq .= "s_ASBirthCountry.data,s_ASAddress.data,s_ASEmailStudent.data,s_ASPhoneHomeStudent.data,";
		  $insrecq .= "s_ASMobilePhoneStudent.data,s_ASLastSchool.data,altsid,password,s_banknummer.data,s_aantalkinderen.data,s_ASHomeLanguage.data,s_werkzaambij.data  FROM student";
		  $insrecq .= " LEFT JOIN s_roepnaam USING(sid) LEFT JOIN s_ASGender USING(sid)";
		  $insrecq .= " LEFT JOIN s_ASBirthCountry USING(sid) LEFT JOIN s_ASAddress USING(sid) LEFT JOIN s_ASEmailStudent USING(sid)";
		  $insrecq .= " LEFT JOIN s_ASPhoneHomeStudent USING(sid) LEFT JOIN s_ASMobilePhoneStudent USING(sid)";
		  $insrecq .= " LEFT JOIN s_ASLastSchool USING(sid) LEFT JOIN s_banknummer USING(sid) LEFT JOIN s_aantalkinderen USING(sid) LEFT JOIN s_ASHomeLanguage USING(sid) LEFT JOIN s_werkzaambij USING(sid)";
		  $insrecq .= " WHERE sid=". $logindata['sid'][0];
      mysql_query($insrecq,$userlink);
      echo(mysql_error($userlink));
		  $rid = mysql_insert_id($userlink);		  
		  // Now see if a valid package is active for the student and if so, set it in the record
		  $pkqr = inputclassbase::load_query("SELECT packagename FROM s_package 
		                                      WHERE (packagename LIKE 'MM%' OR packagename LIKE 'HU%' OR packagename LIKE 'NW%') 
											  AND sid=". $logindata['sid'][0]);
		  if(isset($pkqr['packagename']))
		  {
		    mysql_query("UPDATE inschrijvingAHA SET pakket='". substr($pkqr['packagename'][0],3,2). "' WHERE rid=". $rid, $userlink);
			// Also must put the subjects in the table with the package
			$s2storeqr = inputclassbase::load_query("SELECT mid FROM isubpack WHERE packagename='". $pkqr['packagename'][0]. "'");
			if(isset($s2storeqr['mid']))
			  foreach($s2storeqr['mid'] AS $imid)
			    mysql_query("INSERT INTO inschrijvingPakket (rid,mid) VALUES(". $rid. ",". $imid. ")", $userlink);
		  }
		}
	  }
	  else
	    echo("<P class=errormsg>Login gegevens zijn niet correct, probeer opnieuw!</p>");
	}
	else
	  echo("<P class=errormsg>Identificatiecode of wachtwoord niet ingevuld!</p>");
  }  

  if($rid == 0)
  { // Enable login if not linked with an existing record yet (fully new students get record 0 to create new one)
    echo("<FIELDSET style='background-color:#FFCCCC'><LEGEND>AHA-Student?</legend>");
    echo("<P class=loginheading>Geef dan je identificatienummer en wachtwoord hieronder zodat we de bestaande gegevens al kunnen tonen.</p>");
    echo("<form action=". $_SERVER['REQUEST_URI']. " METHOD=POST>");
    echo("<LABEL class=shortlabel>Identificatienummer: </LABEL><INPUT TYPE=TEXT SIZE=10 NAME=userid><BR>");
    echo("<LABEL class=shortlabel>Wachtwoord: </LABEL><INPUT TYPE=PASSWORD SIZE=10 NAME=userpw><BR>");
    echo("<INPUT TYPE=SUBMIT VALUE='Gegevens ophalen'></FORM>");
	echo("Controleer de informatie en wijzig / vul aan");
	echo("</fieldset>");
  }

  echo("<DIV class=fieldarea>");
  
  echo("<FIELDSET style='background-color:#FFFFCC'><LEGEND>Persoonsgegevens</legend>");
  echo("<LABEL>Achternaam:</LABEL> ");
  $lnfld = new inputclass_textfield("slname",40,NULL,"lastname","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->set_extrafield("year",$schoolyear);
  $lnfld->echo_html();
  echo("<BR><LABEL>Voornamen (voluit):</LABEL> ");
  $lnfld = new inputclass_textfield("sfname",40,NULL,"firstname","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  //echo("<BR><LABEL>Roepnaam:</LABEL> ");
  //$lnfld = new inputclass_textfield("srname",20,NULL,"roepnaam","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  //$lnfld->echo_html();
  echo("<BR><LABEL>Geslacht:</LABEL> ");
  $lnfld = new inputclass_listfield("sgender","SELECT '' AS id,'' AS tekst UNION SELECT 'm','Man' UNION SELECT 'v','Vrouw'",NULL,"geslacht","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Geboortedatum:</LABEL> ");
  $bdayq = "SELECT '' AS id, '' AS tekst";
  for($d=1;$d<=31;$d++)
    $bdayq .= " UNION SELECT '". ($d < 10 ? "0" : ""). $d. "','". ($d < 10 ? "0" : ""). $d. "'";
  $lnfld = new inputclass_listfield("sbday",$bdayq,NULL,"gebdag","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo(" ");
  $bmonq = "SELECT '' AS id, '' AS tekst";
  for($d=1;$d<=12;$d++)
    $bmonq .= " UNION SELECT '". $montxt[$d]. "','". $montxt[$d]. "'";
  $lnfld = new inputclass_listfield("sbmon",$bmonq,NULL,"gebmaand","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo(" ");
  $byrq = "SELECT '' AS id, '' AS tekst";
  for($d=15;$d<=100;$d++)
    $byrq .= " UNION SELECT '". (date("Y")-$d). "','". (date("Y")-$d). "'";
  $lnfld = new inputclass_listfield("sbyr",$byrq,NULL,"gebjaar","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Geboorteplaats (gebruik achtervoegsel N.A. indien geboren voor 1970):</LABEL> ");
  $lnfld = new inputclass_listfield("sbplace","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_country ORDER BY tekst",NULL,"gebland","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Voertaal:</LABEL> ");
  $lnfld = new inputclass_listfield("slang","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_language ORDER BY tekst",NULL,"voertaal","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Aantal kinderen:</LABEL> ");
  $lnfld = new inputclass_listfield("skind","SELECT '' AS id,'' AS tekst UNION SELECT 0,'geen' UNION SELECT 1,1 UNION SELECT 2,2 UNION SELECT 3,3 UNION SELECT 4,4 UNION SELECT 5,5 UNION SELECT 6,6",NULL,"aantalkinderen","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("</fieldset>");
  echo("<FIELDSET style='background-color:#CCFFFF'><LEGEND>Contactgegevens</legend>");
  echo("<LABEL>Adres:</LABEL> ");
  $lnfld = new inputclass_textfield("sadres",40,NULL,"adres","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>E-mail adres:</LABEL> ");
  $lnfld = new inputclass_textfield("semail",40,NULL,"email","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo(" We sturen je een link naar je aanmeldingsformulier.");
  echo("<BR><LABEL>Telefoonnummer thuis:</LABEL> ");
  $lnfld = new inputclass_textfield("stelthuis",40,NULL,"telthuis","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Telefoonnummer mobiel:</LABEL> ");
  $lnfld = new inputclass_textfield("stelcel",40,NULL,"telmobile","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
	//echo("<DIV style='display: none'>");
  echo("<BR><LABEL>Bank en rekeningnummer:</LABEL> ");
  $lnfld = new inputclass_textfield("stelbank",30,NULL,"bankrekening","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo(" Bijvoorbeeld: `CMB 617.168.00`.");
	//echo("</div>");
  echo("<BR><LABEL>Werkzaam bij:</LABEL> ");
  $lnfld = new inputclass_textfield("sworkat",40,NULL,"werkzaambij","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("</fieldset>");
  echo("<FIELDSET style='background-color:#CCFFCC' ID='studydata'><LEGEND>Studiegegevens</legend>");
  echo("<LABEL>Laatste school:</LABEL> ");
  $lnfld = new inputclass_listfield("slschool","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM c_AVOschool ORDER BY tekst",NULL,"laatsteschool","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Gewenst leerjaar / lokatie:</LABEL> ");
  $yearquery = "SELECT '' AS id,'' AS tekst UNION SELECT '1O','1 / Oranjestad' 
                UNION SELECT '2O','2 / Oranjestad' UNION SELECT '3O','3 / Oranjestad' 
				UNION SELECT '1S','1 / San Nicolas' 
                UNION SELECT '2S','2 / San Nicolas' UNION SELECT '3S','3 / San Nicolas'
				UNION SELECT 'VWO','VWO / Oranjestad'";
  $lnfld = new inputclass_listfield("sljaar",$yearquery,NULL,"leerjaar","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
  $lnfld->echo_html();
  // Subject package choice, starts with loading the existig subject packages
  echo("<BR><LABEL>Vakkenpakket:</LABEL> ");
  $selpack = "SELECT '' AS id, 'Kies eerst het gewenste leerjaar!' AS tekst UNION SELECT CONCAT(SUBSTR(packagename,1,1),SUBSTR(packagename,4,2)), 
               CONCAT(IF(packagename LIKE '_MM%','Mens en Maatschappij',IF(packagename LIKE '_HU%','Humaniora',
			   'Natuur en Wetenschap')),' ',SUBSTR(packagename,4,2), ' (',
			   GROUP_CONCAT(fullname),')') FROM isubpack LEFT JOIN subject USING(mid)
			   WHERE (packagename LIKE '_MM%' OR packagename LIKE '_HU%' OR packagename LIKE '_NW%') 
			   AND shortname <> 'I&S' AND shortname <> 'Pfw' AND shortname <> 'Re' 
			   GROUP BY packagename 
			   UNION SELECT 'XX','Ik kies zelf mijn vakken voor certificaten (ook bij pakket met CKV)' ORDER BY id";
  $lnfld = new inputclass_listfield("selpack",$selpack,NULL,"pakket","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
	$lnfld->set_readonly();
  $lnfld->echo_html();
  // Now we allow for choosing subjects
  echo("<DIV ID=manualentry style='display : none'>Hieronder moet je de gewenste vakken aanvinken.
        <BR>CKV wordt niet gegeven op de Avond Havo maar kan wel meetellen voor het diploma als je daarvoor een certificaat hebt. 
		<BR>Individu en Samenleving wordt alleen in het tweede jaar gegeven en moet je gevolgd hebben om in het derde jaar examen te kunnen doen.
		<BR>Het profielwerkstuk is verplicht voor het derde (examen) jaar.");
  echo("<BR><A href='JavaScript:newWindow(\"RegistratieformulierAHA.php?showpackage=1\")'>Klik <B>hier</b> voor een overzicht van de vakkenpakketten.</a>");
  echo("<BR>Gewenste vakken (ook die waarvoor certificaten of vrijstelling is verleend): ");
  $subsquery  = "SELECT mid AS id, fullname AS tekst, 
                 IF(shortname = 'Ne' OR shortname='En' OR shortname='Sp','1',
				 IF(shortname='Wi-A' OR shortname='Wi-B' OR shortname='Sk' OR shortname='Na' OR shortname='Bio','2',
				 IF(shortname='M&O' OR shortname='Ec' OR shortname='Ak' OR shortname='Gs','3','4')))  AS cat
				 FROM subject";
  $subqsort =  " ORDER BY cat,FIELD(shortname,'Ne','En','Sp','Wi-A','Wi-B','Sk','Na','Bio','M&O','Ec','Gs','Ak','I&S','Pfw')";
  $lnfld = new inputclass_catmultiselect("ssubs",$subsquery. $subqsort,NULL,"mid","inschrijvingPakket",$rid,"rid","class=subjectlist","inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("</DIV><DIV ID=excemptions style='display:none'>");
  echo("<DIV ID=packagereport NAME=packagereport>&nbsp;</DIV>");
  echo("Vakken waarvoor je al een geldig HAVO of VWO certificaat hebt: ");
  $lnfld = new inputclass_catmultiselect("sscerts",$subsquery. " WHERE shortname <> 'I&S' AND shortname <> 'Pfw' AND shortname <> 'Re'". $subqsort,NULL,"mid","inschrijvingCerts",$rid,"rid","class=subjectlist","inschrijfAHhandler.php");
  $lnfld->echo_html();
  // Build the string with the current (aka previous) year
  $prvyear = (date("Y")-1). "-". date("Y");
  echo("Vakken waarvoor je een vrijstelling hebt behaald in ". $prvyear. " of die je om een andere reden niet hoeft te volgen: ");
  $lnfld = new inputclass_catmultiselect("ssvrijst",$subsquery. $subqsort,NULL,"mid","inschrijvingVrijst",$rid,"rid","class=subjectlist","inschrijfAHhandler.php");
  $lnfld->echo_html();
  echo("</DIV></DIV>");
  echo("</fieldset>");
  echo("<DIV ID=feereport NAME=feereport style='margin-top: 30px; margin-bottom: 30px;'>");
  if($rid > 0)
  {
    showfee($rid, false);
  }
  echo("</DIV>");
  if(isset($_POST['rid']))
  {
    echo("<FIELDSET style='background-color: #FFFFFF'><LEGEND>Inschrijving AHA</LEGEND>");
    $sidfld = new inputclass_checkbox("sidrq",0,NULL,"sid","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
    if($sidfld->__toString() == '')
	{  // Allow setting new ID and password
      echo("<LABEL>Identificatienummer student:</LABEL> ");
      $lnfld = new inputclass_textfield("ssidnummer",10,NULL,"idnummer","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
      $lnfld->echo_html();
	  echo(" (automatisch toegekend: ". (substr(date("Y"),2). str_pad($rid,4,"0",STR_PAD_LEFT)). ")");
      echo("<BR><LABEL>Wachtwoord student:</LABEL> ");
      $lnfld = new inputclass_textfield("sspasw",10,NULL,"wachtwoord","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
      $lnfld->echo_html();
	  echo(" (automatisch toegekend: ". str_pad(base_convert(($rid * 1313) % 32000,10,16),4,"0",STR_PAD_LEFT) .")");
	  echo("<BR>");
	}
    echo("<LABEL>Betaald:</LABEL> ");
    $lnfld = new inputclass_checkbox("betaald",0,NULL,"betaald","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
    $lnfld->echo_html();
    echo("<BR><LABEL>Studiegids uitgereikt:</LABEL> ");
    $lnfld = new inputclass_checkbox("studiegids",0,NULL,"studiegids","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
    $lnfld->echo_html();
    echo("<BR><LABEL>Boekenlijst uitgereikt:</LABEL> ");
    $lnfld = new inputclass_checkbox("boekenlijst",0,NULL,"boekenlijst","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
    $lnfld->echo_html();
    echo("<BR><LABEL>Opmerkingen:</LABEL> ");
    $lnfld = new inputclass_textarea("opmerkingen","60,*",NULL,"opmerkingen","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
    $lnfld->echo_html();
    echo("<BR><LABEL>Plaatsen in leerjaar / lokatie:</LABEL> ");
    $lnfld = new inputclass_listfield("siljaar",$yearquery,NULL,"plaatsjaar","inschrijvingAHA",$rid,"rid",NULL,"inschrijfAHhandler.php");
    $lnfld->echo_html();
    echo("<DIV ID=placedgroup NAME=placedgroup>&nbsp;</DIV>");
	echo("<a href=form_Inschrijven_AHA.php>Terug naar het zoekscherm</a>");
	echo("</fieldset>");
  }
  else
  {
    echo("<B>Stort het vermelde bedrag op het CMB rekeningnummer 617.168.00<BR>
	Dit formulier uitprinten en samen met het betalingsbewijs, een kopie van een geldig identificatie document (paspoort of rijbewijs), een kopie van een diploma of rapport, een censo formulier van afl5.- op de Avond Havo inleveren voor 10 juli 2018.</b>");
    echo("<BR><INPUT TYPE=SUBMIT VALUE='PRINT' onClick='printform()'>");
    echo("<BR><INPUT TYPE=SUBMIT VALUE='KLAAR' onClick='closeform()'>");
  }
  
	
?>
<script>
// Action when printing
function printform()
{
	bankfield=document.getElementById("stelbank");
	if(bankfield.value == "")
		alert("Er moet een bankrekeningnummer worden ingevuld!");
	else
		window.print();
}

function closeform()
{
	bankfield=document.getElementById("stelbank");
	if(bankfield.value == "")
		alert("Er moet een bankrekeningnummer worden ingevuld!");
	else
	{
		var win = window.open("about:blank", "_self"); 
		win.close();
	}
}

// Popup window code
function newWindow(url) {
	popupWindow = window.open(
		url,
		'popUpWindow','height=600,width=900,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,directories=no,status=no')
}
function xmlconnDone(oXML,fieldobj)
{
  fieldobj.style.backgroundColor='white';
  if(oXML.responseText.substring(0,2) != "OK" && typeof oXML.responseText != "undefined")
    alert(oXML.responseText);
  else
  {
    var preptag = oXML.responseText.indexOf("<PACKAGERESULT>");
		if(preptag > 0)
		{
			var reptxt = oXML.responseText.substr(preptag + 16);
			preptag = reptxt.indexOf("}");
			if(preptag > 0)
				reptxt = reptxt.substr(0,preptag);
				document.getElementById("packagereport").innerHTML = reptxt; 
    }	  
    preptag = oXML.responseText.indexOf("<ADMINFEE>");
		if(preptag > 0)
		{
			var reptxt = oXML.responseText.substr(preptag + 11);
			preptag = reptxt.indexOf("}");
			if(preptag > 0)
				reptxt = reptxt.substr(0,preptag);
				document.getElementById("feereport").innerHTML = reptxt; 
		}	  
    preptag = oXML.responseText.indexOf("<PLACEDGROUP>");
		if(preptag > 0)
		{
			var reptxt = oXML.responseText.substr(preptag + 14);
			preptag = reptxt.indexOf("}");
			if(preptag > 0)
				reptxt = reptxt.substr(0,preptag);
				document.getElementById("placedgroup").innerHTML = reptxt; 
    }	  
    preptag = oXML.responseText.indexOf("<ENABLEMANUAL>");
		if(preptag > 0)
		{
			document.getElementById("manualentry").style.display = 'block';
			document.getElementById("excemptions").style.display = 'block';
			clearsubjects();
			enableallsubs();	  
    }	  
		preptag = oXML.responseText.indexOf("<NOEXCEMPTIONS>");
		if(preptag > 0)
		{
      document.getElementById("excemptions").style.display = 'none';
    }	  
    preptag = oXML.responseText.indexOf("<DISABLEMANUAL>");
		if(preptag > 0)
		{
      document.getElementById("manualentry").style.display = 'none'; 
      clearsubjects();	  
    }	  
    preptag = oXML.responseText.indexOf("<SELECTEDSUBS>");
		if(preptag > 0)
		{
      document.getElementById("excemptions").style.display = 'block';
	  var reptxt = oXML.responseText.substr(preptag + 15);
	  preptag = reptxt.indexOf("}");
	  if(preptag > 0)
	    enablesubs(reptxt.substr(0,preptag));
    }	  
    preptag = oXML.responseText.indexOf("<VWOSWITCH>");
		if(preptag > 0)
		{
			var selpops = document.getElementById("selpack").getElementsByTagName("option");
			for(var spi=0; spi < selpops.length; spi++)
			{ // Disable all options, for VWO can only manual select!
				selpops[spi].disabled = true;
			}
      document.getElementById("excemptions").style.display = 'block';
      document.getElementById("manualentry").style.display = 'block';
			document.getElementById("selpack").value='XX';
			var reptxt = oXML.responseText.substr(preptag + 12);
			preptag = reptxt.indexOf("}");
			if(preptag > 0)
				enablesubs(reptxt.substr(0,preptag));
			// Disable choice of no year/location
			var myrs = document.getElementById("sljaar").getElementsByTagName("option");
			myrs[0].disabled=true;
    }	  
    preptag = oXML.responseText.indexOf("<HAVOSEL");
		if(preptag > 0)
		{
			// Enable subject selection
			document.getElementById("selpack").disabled = false;
      document.getElementById("excemptions").style.display = 'none';
      document.getElementById("manualentry").style.display = 'none';
			var jsel = document.getElementById("sljaar").value.substr(0,1);
			var selpops = document.getElementById("selpack").getElementsByTagName("option");
			for(var spi=0; spi < selpops.length; spi++)
			{ // Enable only the options matching the year and XX
				if(selpops[spi].value.substr(0,1) == jsel)
				  selpops[spi].disabled = false;
				else
				{
					if(selpops[spi].value == "XX" && jsel == '3')
						selpops[spi].disabled = false;
					else if (selpops[spi].value == "")
					{
						selpops[spi].text = "Kies je vakkenpakket";
						selpops[spi].selected = true;
						selpops[spi].disabled = true;
					}
					else
					{
						selpops[spi].disabled = true;
					}
				}
			}
			// Disable choice of no year/location
			var myrs = document.getElementById("sljaar").getElementsByTagName("option");
			myrs[0].disabled=true;
    }	  
    preptag = oXML.responseText.indexOf("<CHECKEXCEMPT>");
		if(preptag > 0)
		{
			var reptxt = oXML.responseText.substr(preptag + 15);
			preptag = reptxt.indexOf("}");
			if(preptag > 0)
				document.getElementsByName('cbssvrijst' + reptxt.substr(0,preptag))[0].checked = true;
    }	  
    preptag = oXML.responseText.indexOf("<REREGISTER>");
		if(preptag > 0)
		{
			alert("Dit is geen nieuwe registratie, open de registratie opnieuw via de link die via e-mail is verstuurd!");
			var win = window.open("about:blank", "_self"); win.close();
		}
    preptag = oXML.responseText.indexOf("<REREGISTER2>");
		if(preptag > 0)
		{
			alert("Als bestaande student moet je gebruik maken van de aanmelding rechts boven op het formulier. Als je de inloggegevens niet hebt neem dan contact op met de school!");
			var win = window.open("about:blank", "_self"); win.close();
		}
  }
}
function clearsubjects()
{
  var checkBoxes=document.getElementById("studydata").getElementsByTagName("input");
  for(var i=0;i<checkBoxes.length;i++)
  {
    if(checkBoxes[i].type == 'checkbox')
		checkBoxes[i].checked = false; 
  }
}
function enablesubs(sublist)
{
  var checkBoxes=document.getElementById("studydata").getElementsByTagName("input");
  var slist = sublist.split(",");
  for(var i=0;i<checkBoxes.length;i++)
  {
    if(checkBoxes[i].type == 'checkbox')
		{
      var enable = false;
			for(var j=0;j<slist.length;j++)
			{
				if(checkBoxes[i].name == 'cbsscerts' + slist[j] || checkBoxes[i].name == 'cbssvrijst' + slist[j] || checkBoxes[i].name == 'cbssubs' + slist[j])
				enable = true;
			}
			if(enable)
				checkBoxes[i].style.display = 'inline-block';
				else
					checkBoxes[i].style.display	= 'none';  
		}
  }
  checkBoxes=document.getElementById("studydata").getElementsByTagName("span");
  for(var i=0;i<checkBoxes.length;i++)
  {
    var enable = false;
	  for(var j=0;j<slist.length;j++)
	  {
	    if(checkBoxes[i].id == 'cbsscerts' + slist[j] || checkBoxes[i].id == 'cbssvrijst' + slist[j] || checkBoxes[i].id == 'cbssubs' + slist[j])
		  enable = true;
	  }
	  if(enable)
	    checkBoxes[i].style.display = '';
    else
      checkBoxes[i].style.display	= 'none';  
  }
}
function enableallsubs()
{
  var checkBoxes=document.getElementById("studydata").getElementsByTagName("input");
  for(var i=0;i<checkBoxes.length;i++)
  {
    if(checkBoxes[i].type == 'checkbox')
	    checkBoxes[i].style.display = 'inline-block';
  }
  checkBoxes=document.getElementById("studydata").getElementsByTagName("span");
  for(var i=0;i<checkBoxes.length;i++)
  {
	  checkBoxes[i].style.display = '';
  }
}
function send_xmlcb(fieldid,fieldobj)
{
  document.getElementById("packagereport").innerHTML = "&nbsp;"; 
  document.getElementById("feereport").innerHTML = "&nbsp;"; 
  myConn[fieldid] = new XHConn(fieldobj);
  if (!myConn[fieldid]) alert("XMLHTTP not available. Try a newer/better browser.");
		if(fieldobj.checked == false)
			cbstat = 0;
		else
			cbstat = 1;
  myConn[fieldid].connect("inschrijfAHhandler.php", "POST", "fieldid="+fieldid+"&"+fieldobj.name+"="+cbstat, xmlconnDone);
}
</script>
<?
  // Show subjects to select if subjectpackage is set to manual
  if($rid > 0)
  {
    $selpqr = inputclassbase::load_query("SELECT pakket FROM inschrijvingAHA WHERE rid=". $rid);
		if(isset($selpqr['pakket']))
		{
			if($selpqr['pakket'][0] == 'XX')
			{ // All manual, show all entry areas
				echo("<SCRIPT> document.getElementById('manualentry').style.display = 'block'; </script>");
				echo("<SCRIPT> document.getElementById('excemptions').style.display = 'block'; </script>");
			}
			else if($selpqr['pakket'][0] != '')
			{  // Existing subject package chosen, make adjustments so excemptions can be filled
				$subsqr = inputclassbase::load_query("SELECT GROUP_CONCAT(mid) AS slist FROM inschrijvingPakket WHERE rid=". $rid);
				echo("<SCRIPT> document.getElementById('excemptions').style.display = 'block'; </script>");
				echo("<SCRIPT> enablesubs('". $subsqr['slist'][0]. "'); </script>");		
			}
		}
    // If VWO, must limit the ampout of subjects available
    $ljqr = inputclassbase::load_query("SELECT leerjaar FROM inschrijvingAHA WHERE rid=". $rid);
    if(isset($ljqr['leerjaar']) && $ljqr['leerjaar'][0] == "VWO")
    { // So it's VWO so get the applicable mids and disable all other subjects
      $vslqr = inputclassbase::load_query("SELECT GROUP_CONCAT(mid) AS slist FROM subject 
                                           WHERE shortname = 'Ne' OR shortname = 'En' OR shortname = 'Sp' OR shortname = 'Gs'");
      if(isset($vslqr['slist']))
        echo("<SCRIPT> enablesubs('". $vslqr['slist'][0]. "'); </script>");
    }	
  }
  // close the page
  echo("</html>");
?>
