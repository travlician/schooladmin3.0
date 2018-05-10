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

  echo ('<LINK rel="stylesheet" type="text/css" href="style_InschrijfAMAH.css" title="style1">');
  
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
// Het inschrijfformulier bestaat uit 3 PARAGRAAFEN. Elke PARAGRAAF heeft dezelfde koptekst om de gebruiker telkens erop te wijzen waar hij mee bezig is:
// KOPTEKST: Inschrijfformulier voor <naam-school> schooljaar met school-naam afhankelijk van de geselecteerde school + logo
// PARAGRAAF 1: Aanmelding - Keuze uit O'stad en San Nicolaas, mavo, havo of vwo afhankelijk van de schoolorganisatie
// PARAGRAAF 2: Personalia - Informatie over de student
// PARAGRAAF 3: Noodsituatie - Informatie over de contactpersoon indien noodgeval
// PARAGRAAF 4: Informatie over de vooropleiding
// PARAGRAAF 5: Informatie over werk en werkgever indien van toepassing
// PARAGRAAF 6: Informatie de profieln/vakkenpakketten
// PARAGRAAF 7: Instroominformatie
// PARAGRAAF 8: Documenten die meegebracht moeten worden 
// PARAGRAAF 9: De pagina voor de inschrijfer
// *****************************
	echo("<form>");
	
// Koptekst Inschrijfformilierals een form:
    echo("<html><head><title>Inschrijfformilier</title>");
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

// OPGELET: Je kunt het formulier maar 1 keer invullen - dus helmaal - dan wordt het weggeschreven.
//
// PARAGRAAF 1: 10 velden >> Kiezen type onderwijs zoalS avond of middag ondewijs, mavo of havo
// Avond mavo:	OSAvondmavo; SNAvondmavo; SNMiddagmavo; 
// Middag havo:	
// Avond havo:	OSAvondhavo; SNAvondhavo; OSAvondvwo;
	echo("<p class=Opgelet>Dit formulier moet je in &eacute;&eacute;n keer helemaal invullen.<br>Pas aan het eind wordt alle informatie opgeslagen.</p>");
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=3>Aanmelding</td></tr>");
	switch ($schoolname)
	{
		case "Openbare Avondleergangen Aruba UNIT AVO/mavo":
			echo("<tr><td rowspan=4>Ik wil mij schrijven voor:</td><td class=KopTekstTabel>Oranjestad</td><td class=KopTekstTabel>San Nicolaas</td></tr>");
			echo("<tr><td rowspan=2><input type=checkbox name=OSAvondmavo>&nbsp;Avondmavo</td>
				<td><input type=checkbox name=SNAvondmavo>&nbsp;Avondmavo</td>></tr>");
			echo("<tr><td><input type=checkbox name=SNMiddagmavo>&nbsp;Middagmavo</td>></tr>");
//			echo("<tr bgcolor=blue><td><input type=checkbox name=OSmavo>&nbsp;Middaghavo</td></td><td><input type=checkbox name=SNmmavo>&nbsp;Middaghavo</td></tr>");
		break;
		case "Openbare Avondleergangen Aruba UNIT AVO/havo":
			echo("<tr class=Paragraaftekst><td colspan=9>Mens en Maatschappijwetenschappen (MM)</td><td>Humaniora (HU)</td></tr>");
			echo("<tr><td class=TekstRechts rowspan=4>Bij inschrijving meenemen:</td><td class=TekstLinks><i><input type=checkbox name=OSmavo>&nbsp;&nbsp;Ricibo&nbsp;di&nbsp;pago&nbsp;di&nbsp;schoolgeld&nbsp;/&nbsp;re&ccedil;u</i></td></tr>");
			echo("<tr>><td class=TekstLinks><i><input type=checkbox name=OSmavo> &nbsp;Uittreksel bevolkingsregister</td></tr>");
			echo("<tr><td class=TekstLinks><i><input type=checkbox name=OSmavo> &nbsp;Diploma(s)</td></tr>");
			echo("<tr><td class=TekstLinks><i><input type=checkbox name=OSmavo> &nbsp;Cijferlijst(en)</td></tr>");
		break;
		case "Avondhavo Aruba":
			echo("<tr><td rowspan=3>Ik wil mij schrijven voor:</td><td>Oranjestad</td><td>San Nicolaas</td></tr>");
			echo("<tr><td><input type=checkbox name=OSAvondhavo>&nbsp;havo</td><td><input type=checkbox name=SNAvondhavo>&nbsp;havo</td>></tr>");
			echo("<tr><td><input type=checkbox name=OSAvondvwo>&nbsp;wvo</td><td></td></tr>");
		break;
	}
	echo("</table><br>");
	echo("<table>");
// voorlopig Ned & Pap - later naar en Engels en Spaans te kiezen
// PARAGRAAF 2: 13 velden >> Personalia.
// Lastname; Firstname; Mankind; Bday; Bmonth; Byear;
// BirthCountry; Nationality; EstCivil;
// Address; PhoneHome; MobilePhone; EmailAddress;
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Personalia</td></tr>");
	echo("<tr><td class=TekstRechtsID>Identiteitsnummer leerling / Number di cedula alumno:</td><td colspan=2 class=TekstLinksID><input type=text name=IdenNr size=14></td></tr>");
	echo("<tr><td class=TekstRechts>Achternaam student / Fam studiante:</td><td class=TekstLinks><input type=text name=Lastname size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Voorna(a)m(en) <i>(voluit):</i> / Nomber(nan) completo</td><td class=TekstLinks><input type=text name=Firstname size=60></td></tr>");
	echo("<tr><td class=TekstRechts>Geslacht / Sexo:</td><td class=TekstLinks><select name=Mankind><option>man / mascullino</option><option>vrouw / femenino</option></select></td></tr>");
	echo("<tr><td class=TekstRechts>Geboortedatum / Fecha di nacemento:</td><td class=TekstLinks>
		Dag / Dia <select name=Bday><option>1</option><option>2</option>
		<option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option>
		<option>10</option><option>11</option><option>12</option><option>13</option><option>14</option><option>15</option>
		<option>16</option><option>17</option><option>18</option><option>19</option><option>20</option><option>21</option>
		<option>22</option><option>23</option><option>24</option><option>25</option><option>26</option><option>27</option>
		<option>28</option><option>29</option><option>30</option><option>31</option></select>
		Maand / Lun <select name=Bmonth><option>jan</option><option>feb</option>
		<option>mrt</option><option>apr</option><option>mei</option><option>jun</option><option>jul</option><option>aug</option><option>sep</option>
		<option>okt</option><option>nov</option><option>dec</option></select>
		Jaar / A&ntilde;a <select name=Byear><option>1968</option><option>1969</option><option>1970</option><option>1971</option><option>1972</option>
		<option>1973</option><option>1974</option><option>1975</option><option>1976</option><option>1977</option>
		<option>1978</option><option>1979</option><option>1980</option><option>1981</option><option>1982</option>
		<option>1983</option><option>1984</option><option>1985</option><option>1986</option><option>1987</option>
		<option>1988</option><option>1989</option><option>1990</option><option>1991</option><option>1992</option>
		<option>1993</option><option>1994</option><option>1995</option><option>1996</option><option>1997</option>
		<option>1998</option><option>1999</option><option>2000</option><option>2001</option><option>2002</option><option>2003</option>
		<option>2004</option></select></td></tr>");
	echo("<tr><td class=TekstRechts>Geboorteland / Pais di nacemento:</td><td class=TekstLinks><input type=text name=BirthCountry></td></tr>");
	echo("<tr><td class=TekstRechts>Nationaliteit / Nacionalidad:</td><td class=TekstLinks><input type=text name=Nationality size=30></td></tr>");
	echo("<tr><td class=TekstRechts>Burgelijke staat student / Estado civil studiante:</td>
		<td class=TekstLinks><select name=EstCivil><option>gehuwd / casa</option><option>ongehuwd / no casa</option></select></td></tr>");
// Tussenregel:
		echo("<tr colspan=2 class=Tussenregel></tr>");
//
	echo("<tr><td class=TekstRechts>Adres:</td><td class=TekstLinks><input type=text name=Address size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Telefoon student thuis / Telefon studiante na cas:</td><td class=TekstLinks><input type=text name=PhoneHome size=7></td></tr>");
	echo("<tr><td class=TekstRechts>Mobiel student / cellular di e studiante:</td><td class=TekstLinks><input type=text name=MobilePhone size=7></td></tr>");
	echo("<tr><td class=TekstRechts>eMail van de student / eMail di e studiante:</td><td class=TekstLinks><input type=text name=EmailAddress size=40></td></tr>");
	echo("</table><br>");

// PARAGRAAF 3: 4 velden >> In geval van nood waarschuwen.
// PersonResponsible; EmergPhoneNr; MobilePhoneRespPers; AddressPersResp; 
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Ingeval van nood waarschuwen</td></tr>");
	echo("<tr><td class=TekstRechtsID>Naam / Nomber:</td><td class=TekstLinksID><input type=text name=PersonResponsible size=60></td></tr>");
	echo("<tr><td class=TekstRechts>Telefoon indien noodgeval / Telefoon di emergencia:</td><td class=TekstLinks><input type=text name=EmergPhoneNr size=7></td></tr>");
	echo("<tr><td class=TekstRechts>Mobiel / Cellular :</td><td class=TekstLinks><input type=text name=MobilePhoneRespPers size=7></td></tr>");
	echo("<tr><td class=TekstRechts>Adres:</td><td class=TekstLinks><input type=text name=AddressPersResp size=40></td></tr>");
	echo("</table><br><br>");

// PARAGRAAF 4: 2 velden >> Informatie over vooropleiding.
// LastSchool ; GainDiploma
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Informatie over vooropleiding</td></tr>");
	echo("<tr><td class=TekstRechts>Laatst bezochte school:</td><td class=TekstLinks><input type=text name=LastSchool size=60></td></tr>");
	echo("<tr><td class=TekstRechts>Diploma gehaald:</td><td class=TekstLinks><select name=GainDiploma><option>nee</option><option>ja</option></select>
			<i>&nbsp;&nbsp;&nbsp;Indien 'Ja', diploma en cijferlijst meenemen.</i></td></tr>");
	echo("</table><br><br>");

// PARAGRAAF 5: 5 velden >> Informatie over werk & werkgever
// Employed; Compagny; AddressCompagny; WorkTimeFrom; WorkTimeTill WorkPhoneNr; 
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Informatie over werk & werkgever indien van toepassing</td></tr>");
	echo("<tr><td class=TekstRechts>Werkzaam:</td><td class=TekstLinks><select name=Employed><option>nee</option><option>ja</option></select>&nbsp;&nbsp;&nbsp;<i>Indien nee, ga verder met Instroom-Informatie</i></td></tr>");
	echo("<tr><td class=TekstRechts>Bedrijf of Werkplaats:</td><td class=TekstLinks><input type=text name=Compagny size=60></td></tr>");
	echo("<tr><td class=TekstRechts>Adres werkgever:</td><td class=TekstLinks><input type=text name=AddressCompagny size=40></td></tr>");
	echo("<tr><td class=TekstRechts>Werktijden:</td><td class=TekstLinks>Van <input type=text name=WorkTimeFrom size=10>Tot <input type=text name=WorkTimeTill size=10></td></tr>");
	echo("<tr><td class=TekstRechts>Telefoon op het werk:</td><td class=TekstLinks><input type=text name=WorkPhoneNr size=7></td></tr>");
	echo("</table><br><br>");

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

// PARAGRAAF 7: 18 velden >> Instroominfomatie bestaat uit 3 delen - Avondmavo / Middaghavo / Avondhavo
// Avondmavo
// InstapToets; SchakelKlas; AMKlas; ProfielAMKlas; AMKlas4; ProfielAMKlas4;
// Vrijstelling1; Vrijstelling2; Vrijstelling3; Vrijstelling4; Vrijstelling5;
// VolgtVak1; VolgtVak2; VolgtVak3; VolgtVak4; VolgtVak5;
// Middaghavo
//
// Avondhavo
// AHKlas; ProfielAH
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Instroom-informatie</td></tr>");
	switch ($schoolname)
	{
		case "Openbare Avondleergangen Aruba UNIT AVO/mavo":
			echo("<tr><td class=TekstRechts>Ik kies voor de Instaptoets:</td><td class=TekstLinks><select name=InstapToets><option>nee</option><option>ja</option></select></td></tr>");
			echo("<tr><td class=TekstRechts>Ik kies voor de Schakelklas:</td><td class=TekstLinks><select name=Schakelklas><option>nee</option><option>ja</option></select></td></tr>");
			echo("<tr><td class=TekstRechts>Ik kies voor:</td><td class=TekstLinks><select name=AMKlas>
				<option></option><option>Klas 2</option><option>Klas 3</option></select>
				&nbsp;&nbsp;Pakket: <select name=ProfielAMKlas><option></option><option>A</option><option>B</option><option>C</option>
				<option>D</option><option>E</option><option>F</option><option>G</option><option>H</option><option>I</option><option>J</option></select></td></tr>");
			echo("<tr><td class=TekstRechtsKlas4>Ik kies voor Klas 4:<br>Vrijstellingen<br>Ik volg het (de) vak(ken)</td>
				<td class=TekstLinksKlas4><select name=AMKlas4><option>nee</option><option>ja</option></select>
				&nbsp;&nbsp;Pakket: <select name=ProfielAMKlas4><option></option><option>A</option><option>B</option><option>C</option>
				<option>D</option><option>E</option><option>F</option><option>G</option><option>H</option><option>I</option><option>J</option></select><br>
				
				<select name=Vrijstelling1><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>NA</option><option>SK</option><option>BIO</option><option>AK</option>
				<option>GS</option><option>IK</option></select>
				<select name=Vrijstelling2><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>NA</option><option>SK</option><option>BIO</option><option>AK</option>
				<option>GS</option><option>IK</option></select>
				<select name=Vrijstelling3><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>NA</option><option>SK</option><option>BIO</option><option>AK</option>
				<option>GS</option><option>IK</option></select>
				<select name=Vrijstelling4><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>NA</option><option>SK</option><option>BIO</option><option>AK</option>
				<option>GS</option><option>IK</option></select>
				<select name=Vrijstelling5><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>NA</option><option>SK</option><option>BIO</option><option>AK</option>
				<option>GS</option><option>IK</option></select><br>

				<select name=VolgtVak1><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>BIO</option><option>AK</option><option>GS</option></select>
				<select name=VolgtVak2><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>BIO</option><option>AK</option><option>GS</option></select>
				<select name=VolgtVak3><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>BIO</option><option>AK</option><option>GS</option></select>
				<select name=VolgtVak4><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>BIO</option><option>AK</option><option>GS</option></select>
				<select name=VolgtVak5><option></option><option>NE</option><option>EN</option><option>SP</option><option>PA</option><option>WI-A</option>
				<option>EC</option><option>BIO</option><option>AK</option><option>GS</option></select>
				</td></tr>");
		break;
		case "Openbare Avondleergangen Aruba UNIT AVO/havo":
			echo("<tr><td class=TekstRechts>Voor Klas 1:</td><td class=TekstLinks><select name=AMKlas2><option>nee</option><option>ja</option></select>
				&nbsp;&nbsp;Profiel: <select name=ProfielAMKlas2><option>Kies</option><option>HU</option><option>MM</option><option>NT</option></select></td></tr>");
			echo("<tr><td class=TekstRechts>Voor Klas 2:</td><td class=TekstLinks><select name=AMKlas3><option>nee</option><option>ja</option></select>
				&nbsp;&nbsp;Profiel: <select name=ProfielAMKlas2><option>Kies</option><option>HU</option><option>MM</option><option>NT</option></select></td></tr>");
			echo("<tr><td class=TekstRechts>Voor Klas 3:</td><td class=TekstLinks><select name=AMKlas4><option>nee</option><option>ja</option></select>
				&nbsp;&nbsp;Profiel: <select name=ProfielAMKlas2><option>Kies</option><option>HU</option><option>MM</option><option>NT</option></select></td></tr>");
		break;
		case "Avondhavo Aruba":
			echo("<tr><td class=TekstRechts>Registratie voor:</td><td class=TekstLinks><select name=AHKlas>
				<option>Klas 1</option><option>Klas 2</option><option>Klas 3</option></select>
				&nbsp;&nbsp;Profiel: <select name=ProfielAH>
				<option>MM01</option><option>MM02</option><option>MM03</option><option>MM04</option><option>MM05</option>
				<option>MM06</option><option>MM07</option><option>MM08</option><option>MM09</option><option>MM10</option>
				<option>HU11</option><option>HU12</option><option>NW13</option><option>NW14</option></select></td></tr>");
		break;
	}
	echo("</table><br>");
	
// PARAGRAAF 8: geen velden >> Documenten in orde?
	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Documenten</td></tr>");
	switch ($schoolname)
	{
		case "Openbare Avondleergangen Aruba UNIT AVO/mavo":
			echo("<tr><td class=TekstRechts rowspan=6>Bij inschrijving meenemen:</td><td class=TekstLinks><i>&#10003
					&nbsp;&nbsp;Ricibo&nbsp;di&nbsp;pago&nbsp;di inschrijfgeldgeld / re&ccedil;u overleggen</i><br>
					<center>Te betalen op rekening van:<br>
					<del>AVONDHAVO/AVONDVWO<br>SHAKESPEARSTRAAT 17<br><b>CMB 617.168.00</del></b></center></td></tr>");
			echo("<tr>><td class=TekstLinks><i>&#10003&nbsp;Uittreksel bevolkingsregister</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Document profielwerkstuk</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Document I&S</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Diploma(s) / Cerfifica(a)t(en)</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Cijferlijst(en)</td></tr>");
		break;
		case "Openbare Avondleergangen Aruba UNIT AVO/havo":
			echo("<tr><td class=TekstRechts rowspan=6>Bij inschrijving meenemen:</td><td class=TekstLinks><i>&#10003
					&nbsp;&nbsp;Ricibo&nbsp;di&nbsp;pago&nbsp;di inschrijfgeldgeld / re&ccedil;u overleggen</i><br>
					<center>Te betalen op rekening van:<br>
					<del>AVONDHAVO/AVONDVWO<br>SHAKESPEARSTRAAT 17<br><b>CMB 617.168.00</del></b></center></td></tr>");
			echo("<tr>><td class=TekstLinks><i>&#10003&nbsp;Uittreksel bevolkingsregister</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Document profielwerkstuk</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Document I&S</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Diploma(s) / Cerfifica(a)t(en)</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Cijferlijst(en)</td></tr>");
		break;
		case "Avondhavo Aruba":
			echo("<tr><td class=TekstRechts rowspan=6>Bij inschrijving meenemen:</td><td class=TekstLinks><i>&#10003
					&nbsp;&nbsp;Ricibo&nbsp;di&nbsp;pago&nbsp;di schoolgeld / re&ccedil;u overleggen</i><br>
					<center>Te betalen op rekening van:<br>
					AVONDHAVO/AVONDVWO<br>SHAKESPEARSTRAAT 17<br><b>CMB 617.168.00</b></center></td></tr>");
			echo("<tr>><td class=TekstLinks><i>&#10003&nbsp;Uittreksel bevolkingsregister</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Document profielwerkstuk</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Document I&S</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Diploma(s) / Cerfifica(a)t(en)</td></tr>");
			echo("<tr><td class=TekstLinks><i>&#10003&nbsp;Cijferlijst(en)</td></tr>");
		break;
	}	
	echo("</table>");
	echo("<p class=Opgelet>Voordat je het formulier verstuurd, controleer alle informatie!<br></p>");
// versturen
	echo("<div align=center><img src=KnopVersturen.png width=100 align=middle></div>");

// *******************************************************************************************************************************
// Dit stukje is alleen voor de inschrijver - administratie en/of de systeembeheerder:	
// PARAGRAAF 9: 8 velden voor de Avondmavo >> Informatie over gebrachte documenten zoals betalingen en certificaten
// IDLvs; PLvs; SpecialComm; 
// UittrekseBReg; PaidInschrijfgeld; Pasfoto; DocDIMAS; Klas

// 8 velden voor de Avondhavo >> Informatie over gebrachte documenten zoals diploma's cijferlijsten en certificaten
// IDLvs; PLvs; SpecialComm; 
// UittrekseBReg; PaidInschrijfgeld; Pasfoto; CertificatenGecontroleerd; DocDIMAS; 

	echo("<p class=Opgelet>Intake gesprek door de inschrijver<br></p>");

	echo("<table>");
	echo("<tr class=Paragraaftekst><td colspan=2>Alleen bestemd voor de inschrijver van de school</td></tr>");
	switch ($schoolname)
	{
		case "Openbare Avondleergangen Aruba UNIT AVO/mavo":
			echo("<tr><td class=TekstRechtsINFO><i>Lastname, Firstname generen</i></td><td class=TekstLinksINFO><i>Leeftijd genereren + <font color=red>controle</font></i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>De student heeft bij de inschrijving meegenomen:</td><td class=TekstLinksINFO>
				<input type=checkbox name=UittrekseBReg>&nbsp;&nbsp;Uittreksel bevolkingsregister<br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><b>is gecheckt met registratie Personalia</b></i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Inschrijfgeld betaald:</td>
				<td class=TekstLinksINFO><input type=checkbox name=PaidInschrijfgeld>&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>E&eacute;n pasfoto</td>
				<td class=TekstLinksINFO><input type=checkbox name=Pasfoto>&nbsp;&nbsp;-&nbsp;&nbsp;<i>is meegebracht!</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Certificaten</td>
				<td class=TekstLinksINFO><input type=checkbox name=CertificatenGecontroleerd>&nbsp;&nbsp;-&nbsp;&nbsp;<i>zijn gecontroleerd!</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Eventual un documento di DIMAS</td>
				<td class=TekstLinksINFO><i><input type=checkbox name=DocDIMAS>&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>De student wordt geplaatst in:</td><td class=TekstLinksINFO><select name=Klas><option></option>
				<option>Schakelklas</option><option>klas 1</option><option>klas 2</option><option>klas 3</option><option>klas 4</option></select>
				&nbsp;&nbsp;&nbsp;met Profiel:<select name=ProfielInschrijving><option></option>
				<option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
				<option>F</option><option>G</option><option>H</option><option>I</option><option>J</option>
//				<option>HU11</option><option>HU12</option><option>NW13</option><option>NW14</option></select>
				<i><font color=red>controle</font></i></td></tr>");
				// kan zijn ok of anders!
			echo("<tr><td class=TekstRechtsINFO>IDnummer LVS:</td><td class=TekstLinksINFO><input type=text name=IDLvs; size=20></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Wachtwoord LVS:</td><td class=TekstLinksINFO><input type=text name=PLvs; size=20></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Opmerkingen:</td><td class=TekstLinksINFO><TEXTAREA NAME=SpecialComm cols=30 rows=3></textarea></td></tr>");
		break;
		case "Openbare Avondleergangen Aruba UNIT AVO/havo":
			echo("<tr><td class=TekstRechtsINFO><i>Lastname, Firstname generen</i></td><td class=TekstLinksINFO><i>Leeftijd genereren + controle</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>IDnummer LVS:</td><td class=TekstLinksINFO><input type=text name=IDLvs; size=20></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Wachtwoord LVS:</td><td class=TekstLinksINFO><input type=text name=PLvs; size=20></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Opmerkingen:</td><td class=TekstLinksINFO><TEXTAREA NAME=SpecialComm cols=30 rows=3></textarea></td></tr>");
		break;
		case "Avondhavo Aruba":
			echo("<tr><td class=TekstRechtsINFO><i>Lastname, Firstname generen</i></td><td class=TekstLinksINFO><i>Leeftijd genereren + <font color=red>controle</font></i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>De student heeft bij de inschrijving meegenomen:</td><td class=TekstLinksINFO>
				<input type=checkbox name=UittrekseBReg>&nbsp;&nbsp;Uittreksel bevolkingsregister<br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><b>is gecheckt met registratie Personalia</b></i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Inschrijfgeld betaald:</td>
				<td class=TekstLinksINFO><input type=checkbox name=PaidInschrijfgeld>&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Profiel werkstuk behaald:</td>
				<td class=TekstLinksINFO><input type=checkbox name=ProfielWerkstuk>&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>I&S behaald:</td><td class=TekstLinksINFO>
				<input type=checkbox name=I&S>&nbsp;&nbsp;-&nbsp;&nbsp;<i>is gecontroleerd!</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>E&eacute;n pasfoto</td>
				<td class=TekstLinksINFO><input type=checkbox name=Pasfoto>&nbsp;&nbsp;-&nbsp;&nbsp;<i>is meegebracht!</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Certificaten</td>
				<td class=TekstLinksINFO><input type=checkbox name=CertificatenGecontroleerd>&nbsp;&nbsp;-&nbsp;&nbsp;<i>zijn gecontroleerd!</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO><td class=TekstLinksINFO><i><input type=checkbox name=CertificatenGecontroleerd>&nbsp;&nbsp;Eventual un documento di DIMAS</i></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>De student wordt geplaatst in:</td><td class=TekstLinksINFO><select name=Klas><option></option>
				<option>klas 1</option><option>klas 2</option><option>klas 3</option></select>
				&nbsp;&nbsp;&nbsp;met Profiel:<select name=ProfielInschrijving><option></option>
				<option>MM01</option><option>MM02</option><option>MM03</option><option>MM04</option><option>MM05</option>
				<option>MM06</option><option>MM07</option><option>MM08</option><option>MM09</option><option>MM10</option>
				<option>HU11</option><option>HU12</option><option>NW13</option><option>NW14</option></select>
				<font color=red>controle</font></td></tr>");
				// kan zijn ok of anders!
			echo("<tr><td class=TekstRechtsINFO>IDnummer LVS:</td><td class=TekstLinksINFO><input type=text name=IDLvs; size=20></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Wachtwoord LVS:</td><td class=TekstLinksINFO><input type=text name=PLvs; size=20></td></tr>");
			echo("<tr><td class=TekstRechtsINFO>Opmerkingen:</td><td class=TekstLinksINFO><TEXTAREA NAME=SpecialComm cols=30 rows=3></textarea></td></tr>");
		break;
	}
	echo("</table><br>");
// Als de inschrijfer (administratie en/of de systeembeheerder akkoord is:
// if ...	

// versturen
	echo("<div align=center><img src=KnopInschrijven.png width=100 align=middle></div>");
	echo("</form>");
 
// close the page
  echo("</html>");
?>
