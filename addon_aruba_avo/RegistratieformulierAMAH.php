<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | This program is free software.  You can redistribute in and/or       |
// | modify it under the terms of the GNU General Public License Version  |
// | 2 as published by the Free Software Foundation.                      |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY, without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program;  If not, write to the Free Software         |
// | Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.            |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
 // $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  include ("schooladminconstants.php");
  include ("inputlib/inputclasses.php");

  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
  include("fielddata_AMAH.php");
  echo ('<LINK rel="stylesheet" type="text/css" href="style_InschrijfAMAH.css" title="style1">');
  
//
// Dit inschrijfformulier is gemaakt voor de mavo-scholen en de basisscholen
// en is bereikbaar via het schoolbord vab de school.
//
  // Get the year
  $schoolyear = date("Y"). "-" .(date("Y")+1);

  // Get a list of groups
  $groepfilter = "3%";
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND groupname LIKE '". $groepfilter. "' ORDER BY groupname");

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
	// Form statement, include hidden field for table name and a non-displayed radio button for no school selected
	echo("<form method=post action=handle_inschrijvingAMAH.php name=inschrijving id=inschrijving>");
	echo("<input type=hidden name='tablename' value='nieuwe_registratie'>");
	echo("<input type=radio name=SchoolChoice value='' style='display: none' checked>");

	// OPGELET: Je kunt het formulier maar 1 keer invullen - dus helmaal - dan wordt het weggeschreven.
//
// PARAGRAAF 1: 10 velden >> Kiezen type onderwijs zoalS avond of middag ondewijs, mavo of havo
// OShavo; SNhavo; OSvwo;
	echo("<p class=Opgelet>Dit formulier moet je in &eacute;&eacute;n keer helemaal invullen.<br>Pas aan het eind wordt alle informatie opgeslagen.</p>");
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=3>Aanmelding</td></tr>");
	switch ($schoolname)
	{
		case "Openbare Avondleergangen Aruba UNIT AVO/mavo":
			echo("<tr><td rowspan=3>Ik wil mij schrijven voor:</td><td class=KopTekstTabel>Oranjestad</td><td class=KopTekstTabel>San Nicolaas</td></tr>");
			echo("<tr><td rowspan=2><input type=radio name=SchoolChoice value='AMOS'>&nbsp;Avondmavo</td>
				<td><input type=radio name=SchoolChoice value='AMSN'>&nbsp;Avondmavo</td></tr>");
			echo("<tr><td><input type=radio name=SchoolChoice value='MMSN'>&nbsp;Middagmavo</td></tr>");
		break;
		case "Avondhavo Aruba":
			echo("<tr><td rowspan=3>Ik wil mij schrijven voor:</td><td>Oranjestad</td><td>San Nicolaas</td></tr>");
			echo("<tr><td><input type=radio name=SchoolChoice value='AHOS'>&nbsp;havo</td><td><input type=radio name=SchoolChoice value='AHSN'>&nbsp;havo</td>></tr>");
			echo("<tr><td><input type=radio name=SchoolChoice value='AVWOOS'>&nbsp;vwo</td><td></td></tr>");
		break;
	}
	echo("</table><br>");
	echo("<table>");
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
	    $field = new $fieldclassname($fpars['fname'],$fpars['fpar'],$userlink,$fpars['fname'],NULL,0,"regid");
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

	echo("<p class=Opgelet>Voordat je het formulier verstuurd, controleer alle informatie!<br></p>");
// versturen
	echo("<div align=center><img src=PNG/KnopVersturen.png width=100 align=middle  onClick=inschrijving.submit()></div>");
/*
// *******************************************************************************************************************************
// Dit stukje is alleen voor de inschrijver - administratie en/of de systeembeheerder:	
// PARAGRAAF 8: 8 velden voor de Avonmavo >> Informatie over gebrachte documenten zoals betalingen en certificaten
// IDLvs; PLvs; SpecialComm; 
// UittrekseBReg; PaidInschrijfgeld; Pasfoto; DocDIMAS; Klas

// PARAGRAAF 8: 8 velden voor de Avonhavo >> Informatie over gebrachte documenten zoals diploma's cijferlijsten en certificaten
// IDLvs; PLvs; SpecialComm; 
// UittrekseBReg; PaidInschrijfgeld; Pasfoto; CertificatenGecontroleerd; DocDIMAS; 

	echo("<p class=Opgelet>Intake gesprek door de inschrijver<br></p>");

	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Alleen bestemd voor de inschrijver van de school</td></tr>");
	switch ($schoolname)
	{
		case "Openbare Avondleergangen Aruba UNIT AVO/mavo":
			echo("<tr><td class=TekstRechtsINFO><i>Lastname, Firstname generen</i></td><td class=TekstLinksINFO><i>Leeftijd genereren</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>IDnummer LVS:</td><td class=TekstLinksINFO><input type=text name=IDLvs; size=40></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Wachtwoord LVS:</td><td class=TekstLinksINFO><input type=text name=PLvs; size=40></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Opmerkingen:</td><td class=TekstLinksINFO><TEXTAREA NAME=SpecialComm cols=30 rows=3></textarea></td></tr>");
			echo("<tr><td class=TekstRechtsINFO rowspan=5>De student heeft bij de inschrijving meegenomen:</td><td class=TekstLinksINFO><i><input type=checkbox name=UittrekseBReg>&nbsp;&nbsp;Uittreksel bevolkingsregister</i></td></tr>");
			echo("<tr><td class=TekstLinksINFO><i><input type=checkbox name=PaidInschrijfgeld>&nbsp;&nbsp;Inschrijfgeld betaald</td></tr>");
			echo("<tr><td class=TekstLinksINFO><i><input type=checkbox name=Pasfoto>&nbsp;&nbsp;pasfoto</td></tr>");
			echo("<tr><td class=TekstLinksINFO><input type=checkbox name=CertificatenGecontroleerd>&nbsp;&nbsp;<i>Certificaten gecontroleerd!</i></td></tr>");
			echo("<tr><td class=TekstLinksINFO><i><input type=checkbox name=CertificatenGecontroleerd>&nbsp;&nbsp;Eventual un documento di DIMAS</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>De student wordt geplaatst in:</td><td class=TekstLinksINFO><select name=Klas><option></option>
				<option>Schakelklas</option><option>klas 1</option><option>klas 2</option><option>klas 3</option><option>klas 4</option></select></td></tr>");
		break;
		case "Avond havo":
			echo("<tr><td class=TekstRechtsINFO>IDnummer LVS:</td><td class=TekstLinksINFO><input type=text name=IDLvs; size=40></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Wachtwoord LVS:</td><td class=TekstLinksINFO><input type=text name=PLvs; size=40></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Opmerkingen:</td><td class=TekstLinksINFO><TEXTAREA NAME=SpecialComm cols=30 rows=3></textarea></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Medische problemen</td><td class=TekstLinksINFO><select name=MedProblems><option>Asma</option>
				<option>Autisme Spectrum Stoornis</option><option>Suikerziekte</option><option>Geheugenstoornis</option>
				<option>Problemen met gymnastiek</option><option>Schrijfproblemen</option><option>Dominantie vertraging</option></selecht></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Leerstoornissen</td><td class=TekstLinksINFO><select name=LearningProblems><option>Dysorthografie</option>
				<option>dyslexie</option><option>Dyscalculie</option><option>N.L.D. Non-Verbal Learning Disorder</option><option>A.D.(H).D</option>
				</selecht></td></tr>");
		break;
	}
	echo("</table><br>");
// Als de inschrijfer (administratie en/of de systeembeheerder akkoord is:
// if ...	

// versturen
	echo("<div align=center><img src=PNG/KnopInschrijven.png width=100 align=middle></div>");
	echo("</form>");
*/ 
// close the page
  echo("</html>");
  // Javasript functions to overwrite input library attemps to send changed data to server (we simply post a form here...)
  ?>
  <SCRIPT>
  function send_xml(fieldid,fieldobj)
  {
  }
  function send_xmlsl(fieldid,fieldobj)
  {
  }
  function send_xmlcb(fieldid,fieldobj)
  {
  }
  </SCRIPT>
