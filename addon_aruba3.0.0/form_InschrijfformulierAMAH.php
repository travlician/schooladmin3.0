<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2012 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  include ("schooladminconstants.php");
  include ("inputlib/inputclasses.php");

  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
  
  if($schoolname == "Openbare Avondleergangen Aruba UNIT AVO/mavo")
    $fielddata["Schoolkeuze"] =
			   array("Gekozen school" => array("stylesuffix"=>"INFO","fname"=>"SchoolChoice","ftype"=>"listfield","db"=>"nieuwe_registratie",
			          "fpar"=>"SELECT '' AS id, '' AS tekst UNION SELECT 'AMOS', 'Avondmavo Oranjestad' UNION SELECT 'AMSN','Avondmavo San Nicolas' UNION SELECT 'MMSN','Middagmavo San Nicolas'"));
 
  include("fielddata_AMAH.php");
  
  if($schoolname == "Openbare Avondleergangen Aruba UNIT AVO/mavo")
    $fielddata["Alleen bestemd voor de inschrijver van de school"] =
			   array("*infoline" => array("special"=>"Infoline"),
                     "De student heeft bij de inschrijving meegenomen" => array("stylesuffix"=>"INFO","fname"=>"DocExtract","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;Uittreksel bevolkingsregister<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><b>is gecheckt met registratie Personalia</b></i>"),
                     "Inschrijfgeld betaald" => array("stylesuffix"=>"INFO","fname"=>"PaidInschrijfgeld","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i>"),
                     "E&eacute;n pasfoto" => array("stylesuffix"=>"INFO","fname"=>"Pasfoto","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i>"),
                     "Certificaten" => array("stylesuffix"=>"INFO","fname"=>"CertificatenGecontroleerd","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>zijn gecontroleerd!</i>"),
                     "Eventual un documento di DIMAS" => array("stylesuffix"=>"INFO","fname"=>"DocDIMAS","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i>"),
                     "De student wordt geplaatst in" => array("stylesuffix"=>"INFO","fname"=>"KlasPlaatsing","ftype"=>"listfield","db"=>"nieuwe_inschrijving","noend"=>true,
					  "fpar"=>"SELECT '' AS id, '' AS tekst UNION SELECT 'Schakelklas','Schakelklas' UNION SELECT 'Klas1','Klas 1' UNION SELECT 'Klas2','Klas 2' UNION SELECT 'Klas3','Klas 3' UNION SELECT 'Klas4','Klas 4' "),
                     "*profiel" => array("stylesuffix"=>"INFO","fname"=>"ProfielInschrijving","ftype"=>"listfield","db"=>"nieuwe_inschrijving","prefix"=>"&nbsp;&nbsp;&nbsp;met Profiel:",
					  "fpar"=>$pakketA_J,"suffix"=>"<i><font color=red>controle</font></i>"),
                     "IDnummer LVS" => array("stylesuffix"=>"INFO","fname"=>"IDLvs","ftype"=>"textfield","db"=>"nieuwe_inschrijving","fpar"=>"20"),
                     "Wachtwoord LVS" => array("stylesuffix"=>"INFO","fname"=>"PLvsLl","ftype"=>"textfield","db"=>"nieuwe_inschrijving","fpar"=>"20"),
                     "Opmerkingen" => array("stylesuffix"=>"INFO","fname"=>"SpecialComm","ftype"=>"textarea","db"=>"nieuwe_inschrijving","fpar"=>"30,*"),
                     "Akkoord voor inschrijving" => array("stylesuffix"=>"INFO","fname"=>"Approved","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0"));

  if($schoolname == "Openbare Avondleergangen Aruba UNIT AVO/havo")
    $fielddata["Alleen bestemd voor de inschrijver van de school"] =
			   array("*infoline" => array("special"=>"Infoline"),
                     "IDnummer LVS" => array("stylesuffix"=>"INFO","fname"=>"IDLvs","ftype"=>"textfield","db"=>"nieuwe_inschrijving","fpar"=>"20"),
                     "Wachtwoord LVS" => array("stylesuffix"=>"INFO","fname"=>"PLvsLl","ftype"=>"textfield","db"=>"nieuwe_inschrijving","fpar"=>"20"),
                     "Opmerkingen" => array("stylesuffix"=>"INFO","fname"=>"SpecialComm","ftype"=>"textarea","db"=>"nieuwe_inschrijving","fpar"=>"30,*"),
                     "Akkoord voor inschrijving" => array("stylesuffix"=>"INFO","fname"=>"Approved","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0"));

  if($schoolname == "Avondhavo Aruba")
    $fielddata["Alleen bestemd voor de inschrijver van de school"] =
			   array("*infoline" => array("special"=>"Infoline"),
                     "De student heeft bij de inschrijving meegenomen" => array("stylesuffix"=>"INFO","fname"=>"DocExtract","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;Uittreksel bevolkingsregister<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><b>is gecheckt met registratie Personalia</b></i>"),
                     "Inschrijfgeld betaald" => array("stylesuffix"=>"INFO","fname"=>"PaidInschrijfgeld","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i>"),
                     "Profiel werkstuk behaald" => array("stylesuffix"=>"INFO","fname"=>"ProfielWerkstuk","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i>"),
                     "I&S behaald" => array("stylesuffix"=>"INFO","fname"=>"DocIS","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i>"),
                     "E&eacute;n pasfoto" => array("stylesuffix"=>"INFO","fname"=>"Pasfoto","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i>"),
                     "Certificaten" => array("stylesuffix"=>"INFO","fname"=>"CertificatenGecontroleerd","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>zijn gecontroleerd!</i>"),
                     "Eventual un documento di DIMAS" => array("stylesuffix"=>"INFO","fname"=>"DocDIMAS","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i>"),
                     "De student wordt geplaatst in" => array("stylesuffix"=>"INFO","fname"=>"KlasPlaatsing","ftype"=>"listfield","db"=>"nieuwe_inschrijving","noend"=>true,
					  "fpar"=>"SELECT '' AS id, '' AS tekst UNION SELECT 'Schakelklas','Schakelklas' UNION SELECT 'Klas1','Klas 1' UNION SELECT 'Klas2','Klas 2' UNION SELECT 'Klas3','Klas 3' UNION SELECT 'Klas4','Klas 4' "),
                     "*profiel" => array("stylesuffix"=>"INFO","fname"=>"ProfielInschrijving","ftype"=>"listfield","db"=>"nieuwe_inschrijving","prefix"=>"&nbsp;&nbsp;&nbsp;met Profiel:",
					  "fpar"=>$pakketAH,"suffix"=>"<i><font color=red>controle</font></i>"),
                     "IDnummer LVS" => array("stylesuffix"=>"INFO","fname"=>"IDLvs","ftype"=>"textfield","db"=>"nieuwe_inschrijving","fpar"=>"20"),
                     "Wachtwoord LVS" => array("stylesuffix"=>"INFO","fname"=>"PLvsLl","ftype"=>"textfield","db"=>"nieuwe_inschrijving","fpar"=>"20"),
                     "Opmerkingen" => array("stylesuffix"=>"INFO","fname"=>"SpecialComm","ftype"=>"textarea","db"=>"nieuwe_inschrijving","fpar"=>"30,*"),
                     "Akkoord voor inschrijving" => array("stylesuffix"=>"INFO","fname"=>"Approved","ftype"=>"checkbox","db"=>"nieuwe_inschrijving","fpar"=>"0"));

  echo ('<LINK rel="stylesheet" type="text/css" href="style_InschrijfAMAH.css" title="style1">');
  
//
// Dit inschrijfformulier is gemaakt voor de mavo-scholen en de basisscholen
// en is bereikbaar via het schoolbord vab de school.
//
  // Get the year
  $schoolyear = date("Y"). "-" .(date("Y")+1);

  // See if a id for the student has been filled to be used to edit the registration
  if(isset($_POST['IdenNr']))
  {
    $_POST['IdenNr'] = str_replace(".","",$_POST['IdenNr']);
    $_POST['IdenNr'] = str_replace(" ","",$_POST['IdenNr']);
	$regidqr = SA_loadquery("SELECT regid FROM `nieuwe_registratie` WHERE IdenNr='". $_POST['IdenNr']. "'");
	if(isset($regidqr['regid'][1]))
	{
	  $regid = $regidqr['regid'][1];
	}
  }
  
  // Create the registration table in the database if it does not exist yet.
  if($schoolname == "Openbare Avondleergangen Aruba UNIT AVO/mavo") 
	  mysql_query("CREATE TABLE IF NOT EXISTS `nieuwe_inschrijving` (
	  `regid` INTEGER(11) NOT NULL,
	  `DocExtract` BOOLEAN DEFAULT NULL,
	  `PaidInschrijfgeld` BOOLEAN DEFAULT NULL,
	  `Pasfoto` BOOLEAN DEFAULT NULL,
	  `CertificatenGecontroleerd` BOOLEAN DEFAULT NULL,
	  `DocDIMAS` BOOLEAN DEFAULT NULL,
	  `KlasPlaatsing` TEXT,
	  `ProfielInschrijving` TEXT,  
	  `IDLvs` TEXT,
	  `PLvsLl` TEXT,
	  `SpecialComm` TEXT,
	  `Approved` BOOLEAN DEFAULT NULL,
	  `lastmodifiedat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  `lastmodifiedby` int(11) unsigned DEFAULT NULL,
	  PRIMARY KEY (`regid`)
	  ) ENGINE=InnoDB;");
  
  if($schoolname == "Openbare Avondleergangen Aruba UNIT AVO/havo") 
	  mysql_query("CREATE TABLE IF NOT EXISTS `nieuwe_inschrijving` (
	  `regid` INTEGER(11) NOT NULL,
	  `IDLvs` TEXT,
	  `PLvsLl` TEXT,
	  `SpecialComm` TEXT,
	  `Approved` BOOLEAN DEFAULT NULL,
	  `lastmodifiedat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  `lastmodifiedby` int(11) unsigned DEFAULT NULL,
	  PRIMARY KEY (`regid`)
	  ) ENGINE=InnoDB;");
  
  if($schoolname == "Avondhavo Aruba") 
	  mysql_query("CREATE TABLE IF NOT EXISTS `nieuwe_inschrijving` (
	  `regid` INTEGER(11) NOT NULL,
	  `DocExtract` BOOLEAN DEFAULT NULL,
	  `PaidInschrijfgeld` BOOLEAN DEFAULT NULL,
	  `Profielwerkstuk` BOOLEAN DEFAULT NULL,
	  `DocIS` BOOLEAN DEFAULT NULL,
	  `Pasfoto` BOOLEAN DEFAULT NULL,
	  `CertificatenGecontroleerd` BOOLEAN DEFAULT NULL,
	  `KlasPlaatsing` TEXT,
	  `ProfielInschrijving` TEXT,  
	  `IDLvs` TEXT,
	  `PLvsLl` TEXT,
	  `SpecialComm` TEXT,
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
// Het inschrijfformulier bestaat uit 3 PARAGRAAFEN. Elke PARAGRAAF heeft dezelfde koptekst om de gebruiker telkens erop te wijzen waar hij mee bezig is:
// KOPTEKST: Inschrijfformulier voor <naam-school> schooljaar met school-naam afhankelijk van de geselecteerde school + logo
// PARAGRAAF 1: Keuze uit O'stad en San Nicolaas, havo of vwo

// PARAGRAAF 2: Informatie over de student
//		A. Naamgegevens B. Adresgegevens C. Voorgeschiedenis scholen D. Verantwoordelijk persoongegevens


// PARAGRAAF 3: Informatie over ouders - moeder / voogd
// PARAGRAAF 4: Informatie over het gezin
// PARAGRAAF 5: Informatie broers en zussen vwb school
// PARAGRAAF 6: Informatie invullen in geval van nood - 1 blok
// PARAGRAAF 7: Aanmelding invullen
// PARAGRAAF 8: Documenten die meegebracht moeten worden 
// PARAGRAAF 9: de pagina voor de inschrijfer
// *****************************
// Koptekst Inschrijfformilier als een form:
    echo("<html><head><title>Inschrijfformulier</title>");
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	echo("</head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Inschrijfformilier.css" title="style1">';
	
//	Nu de tekst en het logo:
	echo("<p class=Koptekst>Inschrijfformulier voor <br>". $schoolname. " ". $schoolyear."</p>");
//	Gevolgd door het logo:
	if (isset($schoolname))
	{
		echo("<div align=center><img src=schoollogo.png width=100 align=middle></div>");
	}
	else
	{
		echo("Er is geen logo");
	};
	echo("<form method=post action=". $_SERVER['PHP_SELF']. " name=zoeken id=zoeken>");
	echo("<input type=hidden name='tablename' value='nieuwe_registratie'>");

	// OPGELET: Je kunt het formulier maar 1 keer invullen - dus helmaal - dan wordt het weggeschreven.
//
if(!isset($regid))
{ // No record selected, so we just show the dialog to search the record
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Personalia</td></tr>");
	echo("<tr><td class=TekstRechtsID>Identiteitsnummer leerling / Number di cedula alumno:</td><td class=TekstLinksID><input type=text name=IdenNr size=14><input type=submit value=Zoeken></td></tr>");
	if(isset($_POST['IdenNr']))
	  echo("<p class=Foutmelding>Geen registratie gevonden voor gegeven cedulanummer</p>");
	echo("</FORM></table></html>");
    exit;	
}


// voorlopig Ned & Pap - later naar en Engels en Spaans te kiezen
// PARAGRAAF 1: 13 velden.
// Lastname; Firstname; Mankind; Bday; Bmonth; Byear;
// BirthCountry; Nationality; EstCivil;
// Address; PhoneHome; MobilePhone; EmailAddress;
foreach($fielddata AS $thead => $fields)
{
  if($thead == "Instroom-informatie")
  { // Here a table with just info is being inserted before the table with fields...
    // PARAGRAAF 6: de aangeboden profielen/pakketten op de Avondmavo & Avondhavo
    // geen velden
	echo("<table>");
	switch ($schoolname)
	{
		case "Openbare Avondleergangen Aruba UNIT AVO/mavo":
			echo("<tr class=Paragraaftekst><td colspan=10>De aangeboden profielen/pakketten op de Avondmavo</td></tr>");
			echo("<tr class=Pakkettekst><td colspan=9>Mens en Maatschappijwetenschappen (MM)</td><td>Humaniora (HU)</td></tr>");
			echo("<tr><td class=SoortPakket>Pakket A</td><td class=SoortPakket>Pakket B</td><td class=SoortPakket>Pakket C</td>
				<td class=SoortPakket>Pakket D</td><td class=SoortPakket>Pakket E</td><td class=SoortPakket>Pakket F</td>
				<td class=SoortPakket>Pakket G</td><td class=SoortPakket>Pakket H</td><td class=SoortPakket>Pakket I</td>
				<td class=SoortPakket>Pakket J</td></tr>");
			echo("<tr><td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td>
				<td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td>
				<td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td>
				<td class=SubTitelPakket>Verplicht deel</td></tr>");
			echo("<tr><td class=PakketVerpl>Nederlands<br>Engels</td><td class=PakketVerpl>Nederlands<br>Engels</td><td class=PakketVerpl>Nederlands<br>Engels</td>
				<td class=PakketVerpl>Nederlands<br>Engels</td><td class=PakketVerpl>Nederlands<br>Engels</td><td class=PakketVerpl>Nederlands<br>Engels</td>
				<td class=PakketVerpl>Nederlands<br>Engels</td><td class=PakketVerpl>Nederlands<br>Engels</td><td class=PakketVerpl>Nederlands<br>Engels</td>
				<td class=PakketVerpl>Nederlands<br>Engels</td></tr>");
			echo("<tr><td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td>
				<td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td>
				<td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td>
				<td class=SubTitelPakket>Profiel deel</td></td></tr>");
			echo("<tr><td class=PakketProfiel>Wiskunde-A<br>Economie<br>Geschiedenis</td><td class=PakketProfiel>Wiskunde-A<br>Economie<br>Geschiedenis</td><td class=PakketProfiel>Wiskunde-A<br>Economie<br>Geschiedenis</td>
				<td class=PakketProfiel>Wiskunde-A<br>Economie<br>Aardrijkskunde</td><td class=PakketProfiel>Wiskunde-A<br>Economie<br>Aardrijkskunde</td><td class=PakketProfiel>Wiskunde-A<br>Economie<br>Aardrijkskunde</td>
				<td class=PakketProfiel>Wiskunde-A<br>Aardrijkskunde<br>Geschiedenis</td><td class=PakketProfiel>Wiskunde-A<br>Aardrijkskunde<br>Geschiedenis</td><td class=PakketProfiel>Wiskunde-A<br>Aardrijkskunde<br>Geschiedenis</td>
				<td class=PakketProfiel>Spaans<br>Aardrijkskunde<br>Geschiedenis</td></tr>");
			echo("<tr><td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td>
				<td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td>
				<td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td>
				<td class=SubTitelPakket>Keuze deel</td></tr>");
			echo("<tr><td class=PakketKeuze>Spaans</td><td class=PakketKeuze>Biologie</td><td class=PakketKeuze>Papiaments</td>
				<td class=PakketKeuze>Biologie</td><td class=PakketKeuze>Spaans</td><td class=PakketKeuze>Papiaments</td>
				<td class=PakketKeuze>Spaans</td><td class=PakketKeuze>Biologie</td><td class=PakketKeuze>Papiaments</td>
				<td class=PakketKeuze>Economie</td></tr>");
		break;
		case "Openbare Avondleergangen Aruba UNIT AVO/havo":
			echo("<tr class=Pakkettekst><td colspan=9>Mens en Maatschappijwetenschappen (MM)</td><td>Humaniora (HU)</td></tr>");
			echo("<tr><td class=TekstRechts rowspan=4>Bij inschrijving meenemen:</td><td class=TekstLinks><i><input type=checkbox name=OSmavo>&nbsp;&nbsp;Ricibo&nbsp;di&nbsp;pago&nbsp;di&nbsp;schoolgeld&nbsp;/&nbsp;re&ccedil;u</i></td></tr>");
			echo("<tr>><td class=TekstLinks><i><input type=checkbox name=OSmavo> &nbsp;Uittreksel bevolkingsregister</td></tr>");
			echo("<tr><td class=TekstLinks><i><input type=checkbox name=OSmavo> &nbsp;Diploma(s)</td></tr>");
			echo("<tr><td class=TekstLinks><i><input type=checkbox name=OSmavo> &nbsp;Cijferlijst(en)</td></tr>");
		break;
		case "Avondhavo Aruba":
		echo("<tr class=Paragraaftekst><td colspan=14>De aangeboden profielen/pakketten op de Avondhavo</td></tr>");
			echo("<tr class=Pakkettekst><td colspan=10>Mens en Maatschappijwetenschappen (MM)</td><td colspan=2>Humaniora (HU)</td>
					<td colspan=2>Natuurwetenschappen (HU)</td></tr>");
			echo("<tr><td class=SoortPakket>MM01</td><td class=SoortPakket>MM02</td><td class=SoortPakket>MM03</td>
				<td class=SoortPakket>MM04</td><td class=SoortPakket>MM05</td><td class=SoortPakket>MM06</td>
				<td class=SoortPakket>MM07</td><td class=SoortPakket>MM08</td><td class=SoortPakket>MM09</td><td class=SoortPakket>MM10</td>
				<td class=SoortPakket>HU11</td><td class=SoortPakket>HU12</td>
				<td class=SoortPakket>NW13</td><td class=SoortPakket>NW14</td></tr>");
			echo("<tr><td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td>
				<td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td>
				<td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td>
				<td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td>
				<td class=SubTitelPakket>Verplicht deel</td><td class=SubTitelPakket>Verplicht deel</td></tr>");
			echo("<tr><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td>
				<td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td>
				<td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td>
				<td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td>
				<td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td><td class=PakketVerpl>Nederlands<br>Engels<br>I&S</td></tr>");
			echo("<tr><td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td>
				<td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td>
				<td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td>
				<td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td>
				<td class=SubTitelPakket>Profiel deel</td><td class=SubTitelPakket>Profiel deel</td></tr>");
			echo("<tr><td class=PakketProfiel>Wiskunde-A<br>Aardrijkskunde<br>Geschiedenis</td>
				<td class=PakketProfiel>Wiskunde-A<br>Economie<br>Geschiedenis</td>
				<td class=PakketProfiel>Wiskunde-A<br>Economie<br>Aardrijkskunde</td>
				<td class=PakketProfiel>Wiskunde-A<br>Aardrijkskunde<br>Geschiedenis</td>
				<td class=PakketProfiel>Wiskunde-A<br>Economie<br>Geschiedenis</td>
				<td class=PakketProfiel>Wiskunde-A<br>Economie<br>Aardrijkskunde</td>
				<td class=PakketProfiel>Wiskunde-A<br>Economie<br>Aardrijkskunde</td>
				<td class=PakketProfiel>Wiskunde-A<br>Aardrijkskunde<br>Geschiedenis</td>
				<td class=PakketProfiel>Wiskunde-A<br>Economie<br>Aardrijkskunde</td>
				<td class=PakketProfiel>Spaans<br>Aardrijkskunde<br>Geschiedenis</td>
				<td class=PakketProfiel>Spaans<br>Aardrijkskunde<br>Geschiedenis</td>
				<td class=PakketProfiel>Spaans<br>Aardrijkskunde<br>Geschiedenis</td>
				<td class=PakketProfiel>Wiskunde-A<br>Scheikunde<br>Biologie</td>
				<td class=PakketProfiel>Wiskunde-A<br>Scheikunde<br>Biologie</td></tr>");
			echo("<tr><td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td>
				<td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td>
				<td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td>
				<td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td>
				<td class=SubTitelPakket>Keuze deel</td><td class=SubTitelPakket>Keuze deel</td></tr>");
			echo("<tr><td class=PakketKeuze>Spaans</td><td class=PakketKeuze>Spaans</td><td class=PakketKeuze>Spaans</td>
				<td class=PakketKeuze>M & O</td><td class=PakketKeuze>M & O</td><td class=PakketKeuze>M & O</td>
				<td class=PakketKeuze>Geschiedenis</td><td class=PakketKeuze>Biologie</td><td class=PakketKeuze>Biologie</td>
				<td class=PakketKeuze>Biologie</td><td class=PakketKeuze>M & O</td><td class=PakketKeuze>Economie</td>
				<td class=PakketKeuze>Spaans</td><td class=PakketKeuze>Economie</td></tr>");
		break;
	}	
	echo("</table><br>");  
  }

  echo("<table align=center>");
  echo("<tr class=Paragraaftekst><td colspan=2>". $thead. "</td></tr>");
  foreach($fields AS $flabel => $fpars)
  {
    if(isset($fpars['special']))
	{
	  if($fpars['special'] == "TussenRegel")
        // Tussenregel:
		echo("<tr colspan=2 class=Tussenregel></tr>");
	  if($fpars['special'] == "Infoline")
	  { // Line with names student, age and result of verification of cedula /w birthdate
	    $FNField =  new inputclass_textfield("FirstnameRO","60",$userlink,"Firstname","nieuwe_registratie",$regid,"regid","","datahandler.php");
	    $LNField =  new inputclass_textfield("LastnameRO","40",$userlink,"Lastname","nieuwe_registratie",$regid,"regid","","datahandler.php");
		echo("<tr><td class=TekstRechtsINFO><i>". $FNField->__toString(). "</i> <b>". $LNField->__toString(). "</b></td><td class=TekstLinksINFO>Leeftijd: ");
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
		  if($bmonth < date("m") || ($bmonth == date("m") && $bday < date("d")))
		    $age--;
		    echo($age);
		  $cnumber = $fieldc->__toString();
		  if($bday != substr($cnumber,4,2) || $bmonth != substr($cnumber,2,2) || substr($byear,2,2) != substr($cnumber,0,2))
		    echo(" <font color=red>Geboortedatum <> cedulanummer!</font>");
		echo("</td></tr>");

	  }
	}
	else
	{
	  if(substr($flabel,0,1) != "*")
	  {
	    echo("<tr><td class=TekstRechts");
	    if(isset($fpars['stylesuffix']))
	      echo($fpars['stylesuffix']);
		if(isset($fpars['rowspan']))
		{
		  echo(" rowspan=". $fpars['rowspan']);
		  $spanrows = $fpars['rowspan'];
		}
	    echo(">". $flabel. ":</td><td class=TekstLinks");
	    if(isset($fpars['stylesuffix']))
	      echo($fpars['stylesuffix']);
	    echo(">");
	  }
	  else
	  {
	    if(isset($spanrows))
		{
		  echo("<tr><td class=TekstLinks");
	      if(isset($fpars['stylesuffix']))
	        echo($fpars['stylesuffix']);
	      echo(">");
		  $spanrows--;
		  if($spanrows <= 1)
		    unset($spanrows);
		}
	  }
	  if(isset($fpars['prefix']))
	    echo(" ". $fpars['prefix']. " ");
	  if($fpars['ftype'] == "checkmark")
	    echo("&#10003;");
	  else
	  {
	    $fieldclassname = "inputclass_". $fpars['ftype'];
	    $field = new $fieldclassname($fpars['fname'],$fpars['fpar'],$userlink,$fpars['fname'],$fpars['db'],$regid,"regid","","datahandler.php","lastmodifiedby",$_SESSION['uid']);
	    $field->echo_html();
	  }
	  if(isset($fpars['suffix']))
	    echo($fpars['suffix']);
	  if(!isset($fpars['noend']))
	    echo("</td></tr>");
    }
//
  }
  echo("</table><br>");
}

echo("<div align=center><img src=PNG/KnopInschrijven.png width=100 align=middle border=none onClick='menuSwitch(\"". $_SERVER['PHP_SELF']. "\");'></div>");
 
// close the page
  echo("</html>");
  echo("<SCRIPT> function menuSwitch(newUrl) { setTimeout('document.location=\"'+newUrl+'\"',500); } </SCRIPT>");
?>
