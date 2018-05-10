<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.com)	      |
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

  echo ('<LINK rel="stylesheet" type="text/css" href="style_Inschrijf.css" title="style1">');
  
  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
//
// Dit inschrijfformulier is gemaakt voor de mavo-scholen en de basisscholen
// en is bereikbaar via het schoolbord vab de school.
//
  // Get the year
  $schoolyear = date("Y"). "-" .(date("Y")+1);

  // Get a list of groups
  $groepfilter = "3%";
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE groupname LIKE '". $groepfilter. "' ORDER BY groupname");

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
	echo("<form method=post action=handle_inschrijving.php name=inschrijving id=inschrijving>");
	echo("<input type=hidden name='tablename' value='nieuwe_registratie'>");
	
// Koptekst Inschrijfformilier op elke pagina - HOE DOE JE DAT??:
    echo("<html><head><title>Inschrijfformilier</title>");
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
	echo("<tr><td class=TekstRechtsID>Identiteitsnummer leerling / Number di cedula alumno:</td><td class=TekstLinksID><input type=text name=IdenNr size=14></td></tr>");
	echo("<tr><td class=TekstRechts>Achternaam leerling / Fam alumno:</td><td class=TekstLinks><input type=text name=Lastname size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Voorna(a)m(en) <i>(voluit):</i> / Nomber(nan) completo</td><td class=TekstLinks><input type=text name=Firstname size=60></td></tr>");
	echo("<tr><td class=TekstRechts>Geslacht / Sexo:</td><td class=TekstLinks><select name=Mankind><option value=m>man / masculino</option><option value=v>vrouw / femenino</option></select></td></tr>");
	echo("<tr><td class=TekstRechts>Geboortedatum / Fecha di nacemento:</td><td class=TekstLinks>
		Dag / Dia <select name=Bday><option>1</option><option>2</option>
		<option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option>
		<option>10</option><option>11</option><option>12</option><option>13</option><option>14</option><option>15</option>
		<option>16</option><option>17</option><option>18</option><option>19</option><option>20</option><option>21</option>
		<option>22</option><option>23</option><option>24</option><option>25</option><option>26</option><option>27</option>
		<option>28</option><option>29</option><option>30</option><option>31</option></select>
		Maand / Lun <select name=Bmonth><option value=1>jan</option><option value=2>feb</option>
		<option value=3>mrt</option><option value=4>apr</option><option value=5>mei</option><option value=6>jun</option><option value=7>jul</option><optionvalue=8>aug</option>
		<option value=9>sep</option><option value=10>okt</option><option value=11>nov</option><option value=12>dec</option></select>
		Jaar / A&ntilde;a <select name=Byear><option>1997</option><option>1998</option>
		<option>1999</option><option>2000</option><option>2001</option><option>2002</option><option>2003</option><option>2004</option></select></td></tr>");
	echo("<tr><td class=TekstRechts>Religie / Religion:</td><td class=TekstLinks><input type=text name=Religion size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Gedoopt / Batisa</td><td class=TekstLinks><select name=Baptised><option value=0>nee/no</option><option value=1>ja/si</option></select></td></tr>");
	echo("<tr><td class=TekstRechts>A.Z.V. relatienummer / Number di A.Z.V.:</td><td class=TekstLinks><input type=text name=AZVNr size=40></td></tr>");
// Tussenregel:
	echo("<tr colspan=2 class=Tussenregel></tr>");
//
	echo("<tr><td class=TekstRechts>Geboorteland / Pais di nacemento:</td><td class=TekstLinks>");
	$countrylist = SA_loadquery("SELECT * FROM landencodes ORDER BY tekst");
	echo("<SELECT name=BirthCountry><OPTION></OPTION>");
	foreach($countrylist['id'] AS $cnix => $cntryid)
	{
	  echo("<OPTION VALUE=". $cntryid. ">". $countrylist['tekst'][$cnix]. "</option>");
	}
	echo("</SELECT>");
	echo("</td></tr>");
	echo("<tr><td class=TekstRechts>Nationaliteit / Nacionalidad:</td><td class=TekstLinks><input type=text name=Nationality size=30></td></tr>");
	echo("<tr><td class=TekstRechts>Spreektaal thuis / Idioma na cas:</td><td class=TekstLinks><input type=text name=LangHome size=50></td></tr>");
	echo("<tr><td class=TekstRechts>Adres:</td><td class=TekstLinks><input type=text name=Address size=40></td></tr>");
	echo("<tr><td class=TekstRechts>District / Districto:</td><td class=TekstLinks><input type=text name=District size=30></td></tr>");
	echo("<tr><td class=TekstRechts>Telefoon leerling thuis / Telefon alumno na cas:</td><td class=TekstLinks><input type=text name=PhoneHome size=7></td></tr>");
	echo("<tr><td class=TekstRechts>Mobiel leerling / cellular di e mucha:</td><td class=TekstLinks><input type=text name=MobilePhone size=7></td></tr>");
	echo("<tr><td class=TekstRechts>eMail van de leerling / eMail di e alumna:</td><td class=TekstLinks><input type=text name=EmailAddress size=40></td></tr>");
// Tussenregel:
	echo("<tr colspan=2 class=Tussenregel></tr>");
//
	echo("<tr><td class=TekstRechts>Verantwoordelijk persoon / Persona cu ta responsabel:</td><td class=TekstLinks><input type=text name=ResponsPersoon size=60></td></tr>");
	echo("<tr><td class=TekstRechts>Telefoon indien noodgeval / Telefoon di emergencia:</td><td class=TekstLinks><input type=text name=EmergPhoneNr size=7></td></tr>");
	echo("<tr><td class=TekstRechts>Op Aruba woonachtig sinds / Biba na Aruba desde:</td><td class=TekstLinks><input type=text name=InArubaSince size=50></td></tr>");
	echo("<tr><td class=TekstRechts>Leerling is woonachtig bij / Alumno ta bia cerca:</td><td class=TekstLinks><input type=text name=LiveAt size=30></td></tr>");
	echo("</table><br>");
	
// PARAGRAAF 2: 10 velden >> Informatie over ouder vader - voogd / Informacion di Tata
// LastnameDad; FirstnameDad; AddressDad; DistrictDad; PhoneHomeDad; MobilePhoneDad; EmailAddressDad; ProfesionDad; CompagnyNameDad; PhoneCompagnyDad;
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Informatie over ouders - Vader / Voogd / Tata</td></tr>");
	echo("<tr><td class=TekstRechts>Achternaam vader of voogd / Fam di tata:</td><td class=TekstLinks><input type=text name=LastnameDad size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Voorna(a)m(en) <i>(voluit):</i> / Nomber(nan) completo</td><td class=TekstLinks><input type=text name=FirstnameDad size=60></td></tr>");
	echo("<tr><td class=TekstRechts>Adres:</td><td class=TekstLinks><input type=text name=AddressDad size=40></td></tr>");
	echo("<tr><td class=TekstRechts>District / Districto:</td><td class=TekstLinks><input type=text name=DistrictDad size=30></td></tr>");
	echo("<tr><td class=TekstRechts>Telefoon thuis / Telefon na cas:</td><td class=TekstLinks><input type=text name=PhoneHomeDad size=7></td></tr>");
	echo("<tr><td class=TekstRechts>Mobiel / cellular:</td><td class=TekstLinks><input type=text name=MobilePhoneDad size=7></td></tr>");
	echo("<tr><td class=TekstRechts>eMail / eMail:</td><td class=TekstLinks><input type=text name=EmailAddressDad size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Beroep / Ocupacion:</td><td class=TekstLinks><input type=text name=ProfesionDad size=50></td></tr>");
	echo("<tr><td class=TekstRechts>Werkt bij / Ta traha na:</td><td class=TekstLinks><input type=text name=CompagnyNameDad size=50></td></tr>");
	echo("<tr><td class=TekstRechts>Telefoon op het werk / Telefon na trabou:</td><td class=TekstLinks><input type=text name=PhoneCompagnyDad size=7></td></tr>");
	echo("</table><br>");

// PARAGRAAF 3: 10 velden >> Informatie over ouder Moeder - voogd / Informacion di Mama
// LastnameMom; FirstnameMom; AddressMom; DistrictMom; PhoneHomeMom; MobilePhoneMom; EmailAddressMom; ProfesionMom; CompagnyNameMom; PhoneCompagnyMom;
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Informatie over ouders - Moeder / Voogd / Mama</td></tr>");
	echo("<tr><td class=TekstRechts>Achternaam moeder of voogd / Fam di mama:</td><td class=TekstLinks><input type=text name=LastnamMom size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Voorna(a)m(en) <i>(voluit):</i> / Nomber(nan) completo</td><td class=TekstLinks><input type=text name=FirstnameMom size=60></td></tr>");
	echo("<tr><td class=TekstRechts>Adres:</td><td class=TekstLinks><input type=text name=AddressMom size=40></td></tr>");
	echo("<tr><td class=TekstRechts>District / Districto:</td><td class=TekstLinks><input type=text name=DistrictMom size=30></td></tr>");
	echo("<tr><td class=TekstRechts>Telefoon thuis / Telefon na cas:</td><td class=TekstLinks><input type=text name=PhoneHomeMom size=7></td></tr>");
	echo("<tr><td class=TekstRechts>Mobiel / cellular:</td><td class=TekstLinks><input type=text name=MobilePhoneMom size=7></td></tr>");
	echo("<tr><td class=TekstRechts>eMail / eMail:</td><td class=TekstLinks><input type=text name=EmailAddressMom size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Beroep / Ocupacion:</td><td class=TekstLinks><input type=text name=ProfesionMom size=50></td></tr>");
	echo("<tr><td class=TekstRechts>Werkt bij / Ta traha na:</td><td class=TekstLinks><input type=text name=CompagnyNameMom size=50></td></tr>");
	echo("<tr><td class=TekstRechts>Telefoon op het werk / Telefon na trabou:</td><td class=TekstLinks><input type=text name=PhoneCompagnyMom size=7></td></tr>");
	echo("</table><br>");

// PARAGRAAF 4: 6 velden >> Informatie over het gezin / Informacion di famia
// EstCivilFamily; RelegionFamily; HomeMD; FamilyForm; Botica
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Informatie over het gezin / Informacion di famia</td></tr>");
	echo("<tr><td class=TekstRechts>Burgelijke saat ouders / Estado civil mayornan:</td><td class=TekstLinks><select name=EstCivilFamily><option value=Gehuwd>gehuwd / casa</option><option value=Ongehuwd>ongehuwd / no casa</option><option value=Gescheiden>gescheiden / divorsia</option><option value=Weduwe>Weduw(e)(naar) / Biud(a)(o)</option></select></td></tr>");
	echo("<tr><td class=TekstRechts>Religie / Religion:</td><td class=TekstLinks><input type=text name=RelegionFamily size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Huisdokter / Docter di famia:</td><td class=TekstLinks><input name=HomeMD size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Samenstelling gezien / Constrlacion di famia</td><td class=TekstLinks><input type=text name=FamilyForm size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Botica</td><td class=TekstLinks><input type=text name=Botica size=40></td></tr>");
	echo("</table><br>");

// PARAGRAAF 5: 6 velden >> Informatie over bezochte scholen van de leerling
// NurserySchool; Kindergarden; BO; FailBO; FailAVO
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Informatie over bezochte scholen van de leerling / Scol cu alumo a bishita</td></tr>");
	echo("<tr><td class=TekstRechts>Peuterschool / Scol lushi:</td><td class=TekstLinks><input type=text name=NurserySchool size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Kleuterschool / Scol Preparatorio:</td><td class=TekstLinks><input type=text name=Kindergarden size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Basisschool / Scol Basico:</td><td class=TekstLinks><input type=text name=BO size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Gedoubleerd in basisschool klas / Keda sinta den klas:</td><td class=TekstLinks><input type=text name=FailBO size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Gedoubleerd in AVO klas / Keda sinta den Scol Avansa klas:</td><td class=TekstLinks><input type=text name=FailAVO size=40></td></tr>");
	echo("</table><br>");
	echo("</table><br>");

// PARAGRAAF 5: 6 velden >> Informatie over bezochte scholen broers & zussen
// NameBroSis1; SchoolBroSis1; ClassBroSis1; NameBroSis2; SchoolBroSis2; ClassBroSis2;
// NameBroSis3; SchoolBroSis3; ClassBroSis3; NameBroSis4; SchoolBroSis4; ClassBroSis4;
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Informatie over bezochte scholen broers & zussen / Scol di ruman(nan)</td></tr>");
	echo("<tr><td class=TekstRechts>Naam broer of zus / Nomber di ruman:</td><td class=TekstLinks><input type=text name=NameBroSis1 size=25></td></tr>");
	echo("<tr><td class=TekstRechts>School broer of zus / Scol di e ruman:</td><td class=TekstLinks><input type=text name=SchoolBroSis1 size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Klas:</td><td class=TekstLinks><input type=text name=ClassBroSis1 size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Naam broer of zus / Nomber di ruman:</td><td class=TekstLinks><input type=text name=NameBroSis2 size=25></td></tr>");
	echo("<tr><td class=TekstRechts>School broer of zus / Scol di e ruman:</td><td class=TekstLinks><input type=text name=SchoolBroSis2 size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Klas:</td><td class=TekstLinks><input type=text name=ClassBroSis2 size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Naam broer of zus / Nomber di ruman:</td><td class=TekstLinks><input type=text name=NameBroSis3 size=25></td></tr>");
	echo("<tr><td class=TekstRechts>School broer of zus / Scol di e ruman:</td><td class=TekstLinks><input type=text name=SchoolBroSis3 size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Klas:</td><td class=TekstLinks><input type=text name=ClassBroSis3 size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Naam broer of zus / Nomber di ruman:</td><td class=TekstLinks><input type=text name=NameBroSis4 size=25></td></tr>");
	echo("<tr><td class=TekstRechts>School broer of zus / Scol di e ruman:</td><td class=TekstLinks><input type=text name=SchoolBroSis4 size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Klas:</td><td class=TekstLinks><input type=text name=ClassBroSis4 size=40></td></tr>");
	echo("</table><br>");

// PARAGRAAF 8: Documenten die meegebracht moeten worden 
// PARAGRAAF 8: geen velden
	echo("<table>");
	switch ($schoolname)
	{
		case "MAVO":
			echo("<tr class=Paragraaftekst ><td colspan=2>Documenten / Documentonan</td></tr>");
			echo("<tr><td class=TekstRechts rowspan=8>Bij inschrijving meenemen:<br>Alumno mester bin cu:</td>
				<td class=TekstLinks><i>&#9745;&nbsp;Ricibo&nbsp;di&nbsp;pago&nbsp;di&nbsp;schoolgeld&nbsp;/&nbsp;re&ccedil;u</i></td></tr>");
			echo("<tr><td class=TekstLinks><i>&#9745;&nbsp;Ricibodi&nbsp;pago&nbsp;di&nbsp;airco&nbsp;/&nbsp;re&ccedil;u</i></td></tr>");
			echo("<tr><td class=TekstLinks><i>&#9745;&nbsp;Uittreksel bevolkingsregister</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#9745;&nbsp;Eventueel medische documenten / eventual documentonan medico</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#9745;&nbsp;Drugsformulier</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#9745;&nbsp;A.Z.V. documenten / Documentonan di A.Z.V.</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#9745;&nbsp;Pasfoto</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#9745;&nbsp;Eventual un documento di DIMAS</i></td></tr>");
		break;
		case "BO":
			echo("<tr class=Paragraaftekst><td colspan=2>Documenten</td></tr>");
			echo("<tr><td class=TekstRechts rowspan=4>Bij inschrijving meenemen:</td><td class=TekstLinks><i>Ricibo&nbsp;di&nbsp;pago&nbsp;di&nbsp;schoolgeld&nbsp;/&nbsp;re&ccedil;u</i></td></tr>");
			echo("<tr>><td class=TekstLinks><i>Uittreksel bevolkingsregister</td></tr>");
			echo("<tr>><td class=TekstLinks><i>Drugsformulier</td></tr>");
			echo("<tr><td class=TekstLinks><i>Pasfoto</td></tr>");
			echo("<tr><td class=TekstLinks><i>Cijferlijst(en)</td></tr>");
		break;
	}	
	echo("</table>");
	echo("</form>");
	echo("<p class=Opgelet>Voordat je het formulier verstuurd, controleer alle informatie!<br></p>");

// Als de inschrijfer (administratie en/of de systeembeheerder dit bekijkt, mag deze knop niet verschijnen:
// if ...	
	// versturen
	echo("<div align=center><img src=PNG/KnopVersturen.png width=100 align=middle onClick=inschrijving.submit()></div>");
/*
// Dit stukje is alleen voor de inschrijver - administratie en/of de systeembeheerder:	
// PARAGRAAF 9: 5 velden >> Informatie over bezochte scholen van de leerling
// IDLvs; PLvs; SpecialComm; MedProblems; LearningProblems; 
	echo("<p class=Opgelet>Intake gesprek door de inschrijver<br></p>");

	echo("<br><table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Alleen bestemd voor de inschrijver van de school</td></tr>");
	echo("<tr><td class=TekstRechtsINFO>IDnummer LVS:</td><td class=TekstLinksINFO><input type=text name=IDLvs; size=40></td></tr>");
	echo("<tr><td class=TekstRechtsINFO>Wachtwoord LVS:</td><td class=TekstLinksINFO><input type=text name=PLvs; size=40></td></tr>");
	echo("<tr><td class=TekstRechtsINFO>Opmerkingen:</td><td class=TekstLinksINFO><TEXTAREA NAME=SpecialComm cols=30 rows=3></textarea></td></tr>");
	echo("<tr><td class=TekstRechtsINFO>Medische problemen</td><td class=TekstLinksINFO><select name=MedProblems><option>Asma</option>
		<option>Autisme Spectrum Stoornis</option><option>Suikerziekte</option><option>Geheugenstoornis</option>
		<option>Problemen met gymnastiek</option><option>Schrijfproblemen</option><option>Dominantie vertraging</option></selecht></td></tr>");
	echo("<tr><td class=TekstRechtsINFO>Leerstoornissen</td><td class=TekstLinksINFO><select name=LearningProblems><option>Dysorthografie</option>
		<option>dyslexie</option><option>Dyscalculie</option><option>N.L.D. Non-Verbal Learning Disorder</option><option>A.D.(H).D</option>
		</selecht></td></tr>");
	echo("</table><br>");
// Als de inschrijfer (administratie en/of de systeembeheerder akkoord is:
// if ...	
	// versturen
	echo("<div align=center><img src=PNG/KnopInschrijven.png width=100 align=middle></div>");
*/	
  // close the page
  echo("</html>");
?>
