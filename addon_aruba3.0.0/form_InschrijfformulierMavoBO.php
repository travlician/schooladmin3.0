<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Copyrighted Add-on! Only to be used if licenced by Aim4me            |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  include ("schooladminconstants.php");
  include ("inputlib/inputclasses.php");

  echo ('<LINK rel="stylesheet" type="text/css" href="style_Inschrijf.css" title="style1">');
  
  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);

// Schoolname decides which groups are there!
	switch ($schoolname)
	{
		case "Colegio San Nicolas":
		case "Test LVS":
		    $yearlayers = array("CB1","CB2","H3","H4","H5","V3","HL1","HL2");
		break;
		case "Colegio Frere Bonifacius":
		case "Pius X school":
		case "Colegio Santa Filomena":
		    $yearlayers = array("1","2","3","4","5","6","P");
		break;
		default:
		    $yearlayers = array("1","2","3","4");
	}	
// Make a query style string for the yearlayers
  $yearlayerq = "SELECT '' AS id, '' AS tekst";
  foreach($yearlayers AS $yl)
    $yearlayerq .= " UNION SELECT '". $yl. "','". $yl. "'";
//
// Dit inschrijfformulier is gemaakt voor de mavo-scholen en de basisscholen
// en is bereikbaar via het schoolbord van de school.
//
  // Get the year
  $schoolyear = date("Y"). "-" .(date("Y")+1);

  // See if a id for the student has been filled to be used to edit the registration
  if(isset($_POST['IdenNr']))
  {
    $_POST['IdenNr'] = str_replace(".","",$_POST['IdenNr']);
    $_POST['IdenNr'] = str_replace(" ","",$_POST['IdenNr']);
	$nrexistqr = SA_loadquery("SHOW TABLES LIKE 'nieuwe_reg%'");
	if(isset($nrexistqr))
	{
	  $regidqr = SA_loadquery("SELECT regid FROM `nieuwe_registratie` WHERE IdenNr='". $_POST['IdenNr']. "'");
	  if(isset($regidqr['regid'][1]))
	  {
	    $regid = $regidqr['regid'][1];
	  }
	}
  }
  
  // Create the registration table in the database if it does not exist yet.
  mysql_query("CREATE TABLE IF NOT EXISTS `nieuwe_inschrijving` (
  `regid` INTEGER(11) NOT NULL,
  `IDLvs` TEXT,
  `PLvsLl` TEXT,
  `PLvsOuder` TEXT,
  `SPecialComm` TEXT,
  `MedProblems` TEXT,
  `LearningProblems` TEXT,
  `Checked` BOOLEAN DEFAULT NULL,
  `DocSchoolPaid` BOOLEAN DEFAULT NULL,
  `DocPreschoolPaid` BOOLEAN DEFAULT NULL,
  `DocAircoPaid` BOOLEAN DEFAULT NULL,
  `AmountAircoPaid` DOUBLE DEFAULT NULL,
  `DocExtract` BOOLEAN DEFAULT NULL,
  `DocMedic` BOOLEAN DEFAULT NULL,
  `DocDrugs` BOOLEAN DEFAULT NULL,
  `DocAZV` BOOLEAN DEFAULT NULL,
  `DocDIMAS` BOOLEAN DEFAULT NULL,
  `DocPicture` BOOLEAN DEFAULT NULL,
  `DocResultList` BOOLEAN DEFAULT NULL,
  `KlasPlaatsing` TEXT,
  `lastmodifiedat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastmodifiedby` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`regid`)
  ) ENGINE=InnoDB;");
  
  // If a registation id has been found and no record of it in new inschrijving exists, we create that now.
  if(isset($regid))
  {
    $existsis = SA_loadquery("SELECT * FROM nieuwe_inschrijving WHERE regid=". $regid);
	if(!isset($existsis))
	  mysql_query("INSERT INTO nieuwe_inschrijving(regid,lastmodifiedby) VALUES(". $regid. ",". $_SESSION['uid']. ")");
  }
// *****************************
// Het inschrijfformulier bestaat uit 7 PARAGRAAFEN. Elke PARAGRAAF heeft dezelfde koptekst om de gebruiker telkens erop te wijzen waar hij mee bezig is:
// KOPTEKST: Inschrijfformulier voor <naam-school> schooljaar met school-naam afhankelijk van de geselecteerde school + logo
// PARAGRAAF 1: Personalia invullen. Deze bestaat uit 5 blokken:
//		A. Naamgegevens B. Adresgegevens C. Sociaal/Medische/leerproblemen D. Verantwoordelijk persoongegevens E.Voorgeschiedenis scholen
// PARAGRAAF 2: Informatie over ouder - vader / voogd
// PARAGRAAF 3: Informatie over ouders - moeder / voogd
// PARAGRAAF 4: Informatie over het gezin
// PARAGRAAF 5: Informatie over bezochte scholen van de leerling
// PARAGRAAF 6: Informatie Informatie over bezochte scholen broers & zussen
// PARAGRAAF 7: Aanmelding invullen
// PARAGRAAF 8: Documenten die meegebracht moeten worden 
// PARAGRAAF 9: de pagina voor de inschrijfer
// *****************************
	echo("<form method=post action=". $_SERVER['PHP_SELF']. " name=zoeken id=zoeken>");
	echo("<input type=hidden name='tablename' value='nieuwe_registratie'>");
	
// Koptekst Inschrijfformilier op elke pagina - HOE DOE JE DAT??:
    echo("<html><head><title>Inschrijfformulier</title>");
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	echo("</head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Inschrijfformilier.css" title="style1">';
	
//	Nu de tekst en het logo:
	echo("<p class=Koptekst>Inschrijfformulier voor  ". $schoolname. " ". $schoolyear."</p>");
//	Gevolgd door het logo:
	if (isset($schoolname))
	{
		echo("<div align=center><img src=schoollogo.png width=100 align=middle></div>");
	}
	else
	{
		echo("Er is geen logo");
	};

// PARAGRAAF 1: Kiezen type onderwijs zoslS avond of middag ondewijs, mavo of havo
// OPGELET: Je kunt het formulier maar 1 keer invullen - dus helmaal - dan wordt het weggeschreven.
	echo("<p class=Opgelet>Dit formulier moet je in &eacute;&eacute;n keer helemaal invullen.<br>Pas aan het eind wordt alle informatie opgeslagen.</p>");
	echo("<table>");
// voorlopig Ned & Pap - later naar en Engels en Spaans te kiezen
// PARAGRAAF 1: 21 velden >> Personalia
// Lastname; Firstname; Sexe; Mankind; Bday; Bmonth; Byear; Religion; Baptised; AZVNr;
// BirthCountry; Nationality; LangHome; Address; District; PhoneHome; MobilePhone; EmailAddress;
// ResponsePersoon; EmergPhoneNr; InArubaSince; LiveAt;
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Personalia</td></tr>");
	if(!isset($regid))
	{ // no registration is linked to this for so allow for search
	  echo("<tr><td class=TekstRechtsID>Identiteitsnummer leerling / Number di cedula alumno:</td><td class=TekstLinksID><input type=text name=IdenNr size=14><input type=submit value=Zoeken></td></tr>");
	  if(isset($_POST['IdenNr']))
	    echo("<p class=Foutmelding>Geen registratie gevonden voor gegeven cedulanummer</p>");
	  echo("</FORM>");	  
	}
	else
	{
		echo("<tr><td class=TekstRechtsID>Identiteitsnummer leerling / Number di cedula alumno:</td><td class=TekstLinksID>");
	      $field= new inputclass_textfield("IdentNr","14",$userlink,"IdenNr","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Achternaam leerling / Fam alumno:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("Lastname","40",$userlink,"Lastname","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Voorna(a)m(en) <i>(voluit):</i> / Nomber(nan) completo</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("Firstname","40",$userlink,"Firstname","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Geslacht / Sexo:</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("Mankind","SELECT 'm' AS id, 'man' AS tekst UNION SELECT 'v','vrouw'",$userlink,"Mankind","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Geboortedatum / Fecha di nacemento:</td><td class=TekstLinks>
			Dag / Dia ");
		$daylist = "SELECT 1 AS id,'1' AS tekst";
		for($d=2;$d<32;$d++)
		  $daylist .= " UNION SELECT ". $d. ",'". $d. "'";
	      $field= new inputclass_listfield("Bday",$daylist,$userlink,"Bday","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("Maand / Lun ");
		  $monthlist = "SELECT 1 AS id,'jan' AS tekst UNION SELECT 2,'feb' UNION SELECT 3,'mrt' UNION SELECT 4,'apr' UNION SELECT 5,'mei' UNION SELECT 6,'jun' UNION SELECT 7,'jul' UNION SELECT 8,'aug' UNION SELECT 9,'sep' UNION SELECT 10,'okt' UNION SELECT 11,'nov' UNION SELECT 12,'dec'";
	      $field= new inputclass_listfield("Bmonth",$monthlist,$userlink,"Bmonth","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("Jaar / A&ntilde;a ");
		  $yearlist="SELECT '' AS id,'' AS tekst";
		  for($yr=date("Y")-18;$yr<date("Y")-5;$yr++)
		    $yearlist .= " UNION SELECT '". $yr. "','". $yr. "'";
	      $field= new inputclass_listfield("Byear",$yearlist,$userlink,"Byear","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
			echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Religie / Religion:</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("Religion","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_religion",$userlink,"Religion","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Gedoopt / Batisa</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("Baptised","SELECT 0 AS id,'Nee' AS tekst UNION SELECT 1,'Ja'",$userlink,"Baptised","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>A.Z.V. relatienummer / Number di A.Z.V.:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("AZVNr","40",$userlink,"AZVNr","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
	// Tussenregel:
		echo("<tr colspan=2 class=Tussenregel></tr>");
	//
		echo("<tr><td class=TekstRechts>Geboorteland / Pais di nacemento:</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("BirthCountry","SELECT * FROM landencodes",$userlink,"BirthCountry","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Nationaliteit / Nacionalidad:</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("Nationality","SELECT * FROM arubacom.c_nationality",$userlink,"Nationality","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Spreektaal thuis / Idioma na cas:</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("LangHome","SELECT * FROM arubacom.c_language",$userlink,"LangHome","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Adres:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("Address","40",$userlink,"Address","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>District / Districto:</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("District","SELECT * FROM arubacom.c_district",$userlink,"District","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Telefoon leerling thuis / Telefon alumno na cas:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("PhoneHome","7",$userlink,"PhoneHome","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Mobiel leerling / cellular di e mucha:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("MobilePhone","7",$userlink,"MobilePhone","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>eMail van de leerling / eMail di e alumna:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("EmailAddress","40",$userlink,"EmailAddress","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
	// Tussenregel:
		echo("<tr colspan=2 class=Tussenregel></tr>");
	//
		echo("<tr><td class=TekstRechtsID>Voogd / Verantwoordelijk persoon / Persona cu ta responsabel:</td><td class=TekstLinksID>");
	      $field= new inputclass_textfield("ResponsPersoon","60",$userlink,"ResponsPersoon","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Telefoon indien noodgeval / Telefon di emergencia:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("EmergPhoneNr","7",$userlink,"EmergPhoneNr","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Op Aruba woonachtig sinds / Biba na Aruba desde:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("InArubaSince","50",$userlink,"InArubaSince","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Leerling is woonachtig bij / Alumno ta bia cerca:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("LiveAt","30",$userlink,"LiveAt","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("</table><br>");
		
	// PARAGRAAF 2: 10 velden >> Informatie over ouder vader - voogd / Informacion di Tata
	// LastnameDad; FirstnameDad; AddressDad; DistrictDad; PhoneHomeDad; MobilePhoneDad; EmailAddressDad; ProfesionDad; CompagnyNameDad; PhoneCompagnyDad;
		echo("<table>");
		echo("<tr class=Paragraaftekst><td colspan=2>Informatie over ouders - Vader / Voogd / Tata</td></tr>");
		echo("<tr><td class=TekstRechts>Achternaam vader of voogd / Fam di tata:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("LastnameDad","40",$userlink,"LastnameDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Voorna(a)m(en) <i>(voluit):</i> / Nomber(nan) completo</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("FirstnameDad","60",$userlink,"FirstnameDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Adres:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("AddressDad","40",$userlink,"AddressDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>District / Districto:</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("DistrictDad","SELECT * FROM arubacom.c_district",$userlink,"DistrictDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Telefoon thuis / Telefon na cas:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("PhoneHomeDad","7",$userlink,"PhoneHomeDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Mobiel / cellular:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("MobilePhoneDad","7",$userlink,"MobilePhoneDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>eMail / eMail:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("EmailAddressDad","40",$userlink,"EmailAddressDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Beroep / Ocupacion:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("ProfesionDad","50",$userlink,"ProfesionDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Werkt bij / Ta traha na:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("CompagnyNameDad","50",$userlink,"CompagnyNameDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Telefoon op het werk / Telefon na trabou:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("PhoneCompagnyDad","40",$userlink,"PhoneCompagnyDad","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("</table><br>");

	// PARAGRAAF 3: 10 velden >> Informatie over ouder Moeder - voogd / Informacion di Mama
	// LastnameMom; FirstnameMom; AddressMom; DistrictMom; PhoneHomeMom; MobilePhoneMom; EmailAddressMom; ProfesionMom; CompagnyNameMom; PhoneCompagnyMom;
		echo("<table>");
		echo("<tr class=Paragraaftekst><td colspan=2>Informatie over ouders - Moeder / Voogd / Mama</td></tr>");
		echo("<tr><td class=TekstRechts>Achternaam moeder of voogd / Fam di mama:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("LastnamMom","40",$userlink,"LastnamMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Voorna(a)m(en) <i>(voluit):</i> / Nomber(nan) completo</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("FirstnameMom","60",$userlink,"FirstnameMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Adres:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("AddressMom","40",$userlink,"AddressMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>District / Districto:</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("DistrictMom","SELECT * FROM arubacom.c_district",$userlink,"DistrictMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Telefoon thuis / Telefon na cas:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("PhoneHomeMom","7",$userlink,"PhoneHomeMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Mobiel / cellular:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("MobilePhoneMom","7",$userlink,"MobilePhoneMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>eMail / eMail:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("EmailAddressMom","40",$userlink,"EmailAddressMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Beroep / Ocupacion:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("ProfesionMom","50",$userlink,"ProfesionMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Werkt bij / Ta traha na:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("CompagnyNameMom","50",$userlink,"CompagnyNameMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Telefoon op het werk / Telefon na trabou:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("PhoneCompagnyMom","7",$userlink,"PhoneCompagnyMom","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("</table><br>");

	// PARAGRAAF 4: 6 velden >> Informatie over het gezin / Informacion di famia
	// EstCivilFamily; RelegionFamily; HomeMD; FamilyForm; Botica
		echo("<table>");
		echo("<tr class=Paragraaftekst><td colspan=2>Informatie over het gezin / Informacion di famia</td></tr>");
		echo("<tr><td class=TekstRechts>Burgelijke staat ouders / Estado civil mayornan:</td><td class=TekstLinks>");
		  $cstates = "SELECT '' AS id,'' AS tekst UNION SELECT 'Gehuwd','Gehuwd' UNION SELECT 'Ongehuwd','Ongehuwd' UNION SELECT 'Gescheiden','Gescheiden' UNION SELECT 'Weduwe','Weduw(e)(naar)' UNION SELECT 'Samenwonend','Samenwonend'";
	      $field= new inputclass_listfield("EstCivilFamilty",$cstates,$userlink,"EstCivilFamily","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Religie / Religion:</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("RelegionFamily","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_religion",$userlink,"RelegionFamily","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Huisdokter / Docter di famia:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("HomeMD","40",$userlink,"HomeMD","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Samenstelling gezin / Constrlacion di famia</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("FamilyForm","40",$userlink,"FamilyForm","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Botica</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("Botica","40",$userlink,"Botica","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("</table><br>");

	// PARAGRAAF 5: 6 velden >> Informatie over bezochte scholen van de leerling
	// NurserySchool; Kindergarden; BO; FailBO; FailAVO
		echo("<table>");
		echo("<tr class=Paragraaftekst><td colspan=2>Informatie over bezochte scholen van de leerling / Scol cu alumo a bishita</td></tr>");
		echo("<tr><td class=TekstRechts>Peuterschool / Scol lushi:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("NurserySchool","40",$userlink,"NurserySchool","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Kleuterschool / Scol Preparatorio:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("Kindergarden","40",$userlink,"Kindergarden","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Basisschool / Scol Basico:<BR>(Naam andere school/Nomber otro scol)</td><td class=TekstLinks>");
	      $field= new inputclass_listfield("BO","SELECT * FROM arubacom.c_BOschool",$userlink,"BO","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		  echo("<BR>");
	      $field= new inputclass_textfield("AndereBasisschool","40",$userlink,"AndereBasisschool","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Gedoubleerd op basisschool klas / Keda sinta den klas:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("FailBO","40",$userlink,"FailBO","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechts>Gedoubleerd op AVO klas / Keda sinta den Scol Avansa klas:</td><td class=TekstLinks>");
	      $field= new inputclass_textfield("FailAVO","40",$userlink,"FailAVO","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("</table><br>");
		echo("</table><br>");

	// PARAGRAAF 5: 6 velden >> Informatie over bezochte scholen broers & zussen
	// NameBroSis1; SchoolBroSis1; ClassBroSis1; NameBroSis2; SchoolBroSis2; ClassBroSis2;
	// NameBroSis3; SchoolBroSis3; ClassBroSis3; NameBroSis4; SchoolBroSis4; ClassBroSis4;
		echo("<table>");
		echo("<tr class=Paragraaftekst><td colspan=2>Informatie over bezochte scholen broers & zussen / Scol di ruman(nan)</td></tr>");
		for($bs=1;$bs<5;$bs++)
		{
			echo("<tr><td class=TekstRechts>Naam broer of zus / Nomber di ruman:</td><td class=TekstLinks>");
			  $field= new inputclass_textfield("NameBroSis". $bs,"25",$userlink,"NameBroSis". $bs,"nieuwe_registratie",$regid,"regid","","datahandler.php");
			  $field->echo_html();
			echo("</td></tr>");
			echo("<tr><td class=TekstRechts>School broer of zus / Scol di e ruman:</td><td class=TekstLinks>");
			  $field= new inputclass_textfield("SchoolBroSis". $bs,"40",$userlink,"SchoolBroSis". $bs,"nieuwe_registratie",$regid,"regid","","datahandler.php");
			  $field->echo_html();
			echo("</td></tr>");
			echo("<tr><td class=TekstRechts>Klas:</td><td class=TekstLinks>");
			  $field= new inputclass_textfield("ClassBroSis". $bs,"40",$userlink,"ClassBroSis". $bs,"nieuwe_registratie",$regid,"regid","","datahandler.php");
			  $field->echo_html();
			echo("</td></tr>");
		}
		echo("</table><br>");

	// PARAGRAAF 8: Documenten die meegebracht moeten worden 
	// PARAGRAAF 8: geen velden
		echo("<table>");
		switch ($schoolname)
		{
			case "Maria College MAVO":
			case "Colegio San Nicolas":
			case "Mon Plaisir College MAVO":
			case "Filomena College MAVO":
				echo("<tr class=Paragraaftekst ><td colspan=2>Documenten / Documentonan</td></tr>");
				echo("<tr><td class=TekstRechts rowspan=8>Bij inschrijving meenemen:<br>Alumno mester bin cu:</td>
					<td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocSchoolPaid",0,$userlink,"DocSchoolPaid","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Ricibo di pago di schoolgeld / re&ccedil;u</i></td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocAircoPaid",0,$userlink,"DocAircoPaid","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Ricibo di pago di airco / re&ccedil;u</i></td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocExtract",0,$userlink,"DocExtract","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Uittreksel bevolkingsregister</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocMedic",0,$userlink,"DocMedic","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Eventueel medische documenten / eventual documentonan medico</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocDrugs",0,$userlink,"DocDrugs","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Drugsformulier</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocAZV",0,$userlink,"DocAZV","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;A.Z.V. documenten / Documentonan di A.Z.V.</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocPicture",0,$userlink,"DocPicture","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Pasfoto</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocDIMAS",0,$userlink,"DocDIMAS","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Eventual un documento di DIMAS</i></td></tr>");
			break;
			case "Colegio Frere Bonifacius":
				echo("<tr class=Paragraaftekst ><td colspan=2>Documenten / Documentonan</td></tr>");
				echo("<tr><td class=TekstRechts rowspan=4>Bij inschrijving meenemen:<br>Alumno mester bin cu:</td>
					<td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocPreschoolPaid",0,$userlink,"DocPreschoolPaid","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Betalingsbewijs kleuterschool SKOA</i></td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocSchoolPaid",0,$userlink,"DocSchoolPaid","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Betalingsbewijs komend schooljaar SKOA</i></td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocExtract",0,$userlink,"DocExtract","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Uittreksel bevolkingsregister</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocPicture",0,$userlink,"DocPicture","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Pasfoto</td></tr>");
				echo("<tr><td class='TekstRechts'>Betaald aircogeld:</td><td class=TekstLinks>Afl.&nbsp;");
				$field= new inputclass_amount("AmountAirocPaid",5,$userlink,"AmountAircoPaid","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("</td></tr>");
			break;
			case "Pius X school":
			case "Colegio Santa Filomena":
				echo("<tr class=Paragraaftekst ><td colspan=2>Documenten / Documentonan</td></tr>");
				echo("<tr><td class=TekstRechts rowspan=3>Bij inschrijving meenemen:<br>Alumno mester bin cu:</td>
					<td class=TekstLinks><i>");
				//echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocSchoolPaid",0,$userlink,"DocSchoolPaid","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Betalingsbewijs komend schooljaar SKOA</i></td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocExtract",0,$userlink,"DocExtract","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Uittreksel bevolkingsregister</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocPicture",0,$userlink,"DocPicture","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Pasfoto</td></tr>");
			break;
			case "MAVO Hato":
			case "Test LVS":
				echo("<tr class=Paragraaftekst ><td colspan=2>Documenten / Documentonan</td></tr>");
				echo("<tr><td class=TekstRechts rowspan=6>Bij inschrijving meenemen:<br>Alumno mester bin cu:</td>
					<td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocSchoolPaid",0,$userlink,"DocSchoolPaid","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Ricibo di pago di schoolgeld / re&ccedil;u</i></td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocExtract",0,$userlink,"DocExtract","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Uittreksel bevolkingsregister</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocMedic",0,$userlink,"DocMedic","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Eventueel medische documenten / eventual documentonan medico</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocAZV",0,$userlink,"DocAZV","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Kopie AZV kaart</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocPicture",0,$userlink,"DocPicture","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Pasfoto</td></tr>");
				echo("<tr><td class=TekstLinks><i>");
				$field= new inputclass_checkbox("DocDIMAS",0,$userlink,"DocDIMAS","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
				$field->echo_html();
				echo("&nbsp;Eventual un documento di DIMAS</i></td></tr>");
			break;
		}	
		echo("</table>");
		//echo("</form>");
		echo("<p class=Opgelet>Voordat je het formulier verstuurd, controleer alle informatie!<br></p>");

	// Als de inschrijfer (administratie en/of de systeembeheerder dit bekijkt, mag deze knop niet verschijnen:
	// if ...	
		// versturen
		//echo("<div align=center><img src=PNG/KnopVersturen.png width=100 align=middle onClick=inschrijving.submit()></div>");

	// Dit stukje is alleen voor de inschrijver - administratie en/of de systeembeheerder:	
	// PARAGRAAF 9: 5 velden >> Informatie over bezochte scholen van de leerling
	// IDLvs; PLvs; SpecialComm; MedProblems; LearningProblems; 
		echo("<p class=Opgelet>Intake gesprek door de inschrijver<br></p>");

		echo("<br><table>");
		echo("<tr class=Paragraaftekst><td colspan=2>Alleen bestemd voor de inschrijver van de school</td></tr>");
		echo("<tr><td class=TekstRechtsINFO>Leeftijd:</td><td class=TekstLinksINFO>");
	      $fieldm= new inputclass_textfield("Bmonthc","40",$userlink,"Bmonth","nieuwe_registratie",$regid,"regid","","datahandler.php");
	      $fieldd= new inputclass_textfield("Bdayc","40",$userlink,"Bday","nieuwe_registratie",$regid,"regid","","datahandler.php");
	      $fieldy= new inputclass_textfield("Byearc","40",$userlink,"Byear","nieuwe_registratie",$regid,"regid","","datahandler.php");
	      $fieldc= new inputclass_textfield("IdenNrc","40",$userlink,"IdenNr","nieuwe_registratie",$regid,"regid","","datahandler.php");
		  $bday = $fieldd->__toString();
		  $bmonth = $fieldm->__toString();
		  $byear = $fieldy->__toString();
		  if($bday < 10) $bday = "0". $bday;
		  if($bmonth < 10) $bmonth = "0". $bmonth;
		  $age = date("Y") - $byear;
		  if($bmonth > date("m") || ($bmonth == date("m") && $bday > date("d")))
		    $age--;
		  if($age >= 15)
		    echo("<font color=red>". $age. "</font>");
		  else
		    echo($age);
		  $cnumber = $fieldc->__toString();
		  if($bday != substr($cnumber,4,2) || $bmonth != substr($cnumber,2,2) || substr($byear,2,2) != substr($cnumber,0,2))
		    echo(" <font color=red>Geboortedatum <> cedulanummer!</font>");
		echo("</td></tr>");
		echo("<tr><td class=TekstRechtsINFO>IDnummer LVS:</td><td class=TekstLinksINFO>");
	      $field= new inputclass_textfield("IDLvs","40",$userlink,"IDLvs","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechtsINFO>Wachtwoord LVS Leerling:</td><td class=TekstLinksINFO>");
	      $field= new inputclass_textfield("PLvsLl","40",$userlink,"PLvsLl","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechtsINFO>Wachtwoord LVS Ouder:</td><td class=TekstLinksINFO>");
	      $field= new inputclass_textfield("PLvsOuder","40",$userlink,"PLvsOuder","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechtsINFO>Opmerkingen:</td><td class=TekstLinksINFO>");
	      $field= new inputclass_textarea("SpecialComm","30,*",$userlink,"SpecialComm","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechtsINFO>Medische problemen</td><td class=TekstLinksINFO>");
	      $field= new inputclass_textfield("MedProblems","40",$userlink,"MedProblems","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechtsINFO>Leerstoornissen</td><td class=TekstLinksINFO>");
	      $field= new inputclass_textfield("LearningProblems","40",$userlink,"LearningProblems","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechtsINFO>Deze leerling wordt geplaatst in klas</td><td class=TekstLinksINFO>");
	      $field= new inputclass_listfield("KlasPlaatsing",$yearlayerq,$userlink,"KlasPlaatsing","nieuwe_inschrijving",$regid,"regid","","datahandler.php");
		  $field->echo_html();
		echo("</td></tr>");
		echo("<tr><td class=TekstRechtsINFO>Akkoord voor inschrijving</td><td class=TekstLinksINFO>");
	      $field= new inputclass_checkbox("Checked",0,$userlink,"Checked","nieuwe_inschrijving",$regid,"regid","","datahandler.php","lastmodifiedby",$_SESSION['uid']);
		  $field->echo_html();
		echo("</td></tr>");
		echo("</table><br>");
	// Als de inschrijfer (administratie en/of de systeembeheerder akkoord is:
	// if ...	
		// versturen
		echo("<div align=center><img src=PNG/KnopInschrijven.png width=100 align=middle border=none onClick='menuSwitch(\"". $_SERVER['PHP_SELF']. "\");'></div>");
		
  }
  // close the page
  echo("</html>");
  echo("<SCRIPT> function menuSwitch(newUrl) { setTimeout('document.location=\"'+newUrl+'\"',500); } </SCRIPT>");
?>
