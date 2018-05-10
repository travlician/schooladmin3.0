<?php
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.0                                           |
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
// | Authors: Carlos kelkboom / Wilfred van Weert - aim4me.com            |
// +----------------------------------------------------------------------+
//
  session_start();
  require_once("schooladminfunctions.php");
  require_once("student.php");
  // Link with database
  inputclassbase::dbconnect($userlink);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//CSS3//EN">
<html>
 <head>
   <META NAME="Author"	CONTENT="Owner">
   <META NAME="GENERATOR" CONTENT="">
   <META NAME="KEYWORDS" CONTENT="">
   <META NAME="DESCRIPTION" CONTENT="">
   <META NAME="HEADER" CONTENT="">

   <TITLE>FORM: Opstellen VakkenPakket Decanen</TITLE>
   <link rel="stylesheet" type="text/css" href="style_VPK_AVO.css">

   </head>
<!--
// +----------------------------------------------------------------------------------------------------------+
// | Opzet vakkenpakket voor de AVO-leerlingen                                                                |
// +----------------------------------------------------------------------------------------------------------+
// | Stap1: de decanen die het LVS gebruiken vullen een database met de noodzakelijke informatie over         |
// | beroepen die leerlingen moeten hebben om verder te studeren; informatie zoals profiel, enzovoorts.       |
// +----------------------------------------------------------------------------------------------------------+
// | Stap2: scholen krijgen toegang op deze informatiebron en leerlingen kunnen altijd zich orienteren op de  |
// | informatie die aanwezig is. Deze tool moet gezien worden als een coachinstrument voor de leerlingen.     |
// +----------------------------------------------------------------------------------------------------------+
// | Voor de decanen zijn lijsten beschikbaar om hun beleid vorm te geven in het kander van VP-keuze.         |
// +----------------------------------------------------------------------------------------------------------+
-->
<body summary="Invulformulier voor Decanen t.a.v. vakkenpakketkeuze" class="BodyOpmaak_VPK" >
  <!-- PR voor deze aktieviteit -->
  <img style="position:absolute; top:8px; left:110px; border="0" src="/PNG/LogoAim4meBlueWhite.PNG" width="100px" alt="Logo";>
  <hr color="#000000" width="80%" style="border-style: dotted"; style="position:absolute; top:155px; left:110px;">
  <DIV class="Koptekst">Vakkenpakketten AVO</DIV>
  <DIV class="SubKoptekst">gekoppeld aan beroepen</DIV>
  <br>

  <!-- Bericht Algemene Informatie voor de Decaan -->
  <table id=Opmerking class="OpmVKP" style="border-radius: 25px";>
    <tr>
      <td>Opmerking: alle eigenschappen van een beroep worden door de decanen vastgelegd met de voorwaarden
		<ol type="1">
		  <li>Een optimale vakkenpakket voor het beroep;</li>
		  <li>Vaste profielvakken (<i>leerling kan deze niet wijzigen</i>);</li>
		  <li>De Keuzevakken/Vrije vakken waaruit de leerling &eacute;&eacute;n of twee vakken kan kiezen, worden aangegeven.</li>
		</ol>    
	 </td>
	</tr>
  </table>
  <br>
  <!-- Tabel Algemene Informatie vh Beroep; celKR -->
  <table align="center">
    <tr>
      <td class="Cel11Beroep"> <b>Naam van het beroep: </b></td>
      <td class="Cel21Beroep"> <i>Decaan maakt keuze uit de lijst van beroepen</i></td>
	</tr>
    <tr>
      <td class="Cel12Beroep"><b>Beroepssector: </b></td>
      <td class="Cel22Beroep"> <i>Decaan maakt keuze uit de lijst van beroepssectoren</i></td>
	</tr>
    <tr>
      <td class="Cel13Beroep"><b>Wat houdt het beroep in: </b></td>
      <td class="Cel23Beroep"> <i><u>Link neerzetten door decaan</u></i></td>
	</tr>
    <tr>
      <td class="Cel14Beroep"><b> Waar kun je werken: </b></td>
      <td class="Cel24Beroep"> <i><u>Link neerzetten door decaan</u></i></td>
	</tr>
    <tr>
      <td class="Cel14Beroep"><b>Ook op Aruba over vijf jaar: </b></td>
      <td class="Cel24Beroep"> <i><u>nog niet van toepassing</u></i></td>
	</tr>
  </table>
  <br>
  <!-- Tabel Informatie over onderwijstyep Beroep -->
 <table align="center">
     <tr>
	  <!-- Er zijn 6 keuze mogelijkheden: alleen mbo (b.v EPI)| alleen hbo | alleen wo | mbo & hbo | mbo & hbo & wo |  hbo & wo --> 
      <td class="Cel11Onderwijstype"> <b>Dit beroep kun je studeren aan een: </b></td>
      <td class="Cel21Beroep"> <i>Decaan maakt keuze uit de lijst van onderwijstypen</i></td>
	</tr>
    <tr>
	  <!-- Er zijn 3 keuze mogelijkheden: mavo-diploma| havo-diploma | vwo-diploma --> 
      <td class="Cel12Onderwijstype"> <b>Om aan de studie te beginnen moet je een: </b></td>
      <td class="Cel22Beroep"> <i>Decaan maakt keuze uit de lijst van diploma's</i></td>
	</tr>
    <tr>
	  <!-- Er zijn  keuze mogelijkheden: mavo-diploma| mavo+havo-diploma OF mavo+mbo-diploma| mavo+havo+vwo-diploma OF mavo+mbo+hbo-diploma | havo+vwo-diploma | havo+hbo-diploma | vwo-diploma --> 
      <td class="Cel13Onderwijstype"> <b>De te behalen diploma's zijn dus: </b></td>
      <td class="Cel23Beroep"> <i>Decaan maakt keuze uit de lijst van trajecten</i></td>
	</tr>
  </table>
  <br>
  <span class="Tekst"><center><b>Welk profiel en welke vakken moeten worden gekozen voor dit beroep:</b></center></span>
 <table align="center">
     <tr>
	  <!-- Er zijn 3 profiel mogelijkheden: Humaniora | Mens en Maatschappij | Natuur en Techniek --> 
      <td class="Cel11VPKeuze" colspan="2" > <b>Profiel: </b></td>
      <td class="Cel31VPKeuze"> <i>Decaan maakt keuze uit de lijst profielen</i></td>
	</tr>
    <tr>
	  <!-- Er is verschil tussen mavo / havo voor de algemene vakken ~ zijn vastgestelde vakken door de wet--> 
      <td class="Cel12VPKeuze"> <b>mavo </b></td>
      <td class="Cel22VPKeuze"> <b>Algemene vakken: </b></td>
      <td class="Cel32VPKeuze">  <b>Ned - Eng - LO</b> </td>
	</tr>
    <tr>
	  <!-- Dit zijn vastgestelde profiel-vakken door de decaan --> 
      <td class="Cel13VPKeuze" colspan="2"> <b>Profiel vakken: </b></td>
      <td class="Cel33VPKeuze">  <i>Decaan bepaalt de 3 vakken!</i> </td>
	</tr>
    <tr>
	  <!-- Dit is het vastgestelde keuze-vak door de decaan --> 
      <td class="Cel14VPKeuze" colspan="2"> <b>Het keuze vak: </b></td>
      <td class="Cel34VPKeuze">  <i>Decaan bepaalt het vak uit de overige vakken!</i> </td>
	</tr>
    <tr>
	  <!-- Er is verschil tussen mavo / havo voor de algemene vakken ~ zijn vastgestelde vakken door de wet--> 
      <td class="Cel15VPKeuze"> <b>havo </b></td>
      <td class="Cel25VPKeuze"> <b>Algemene vakken: </b></td>
      <td class="Cel35VPKeuze">  <b>Ned - Eng - LO - I&amp;S - PWS</b> </td>
	</tr>
    <tr>
	  <!-- Dit zijn vastgestelde profiel-vakken door de decaan --> 
      <td class="Cel16VPKeuze" colspan="2"> <b>Profiel vakken: </b></td>
      <td class="Cel36VPKeuze">  <i>Decaan bepaalt de 3 vakken!</i> </td>
	</tr>
    <tr>
	  <!-- Dit is het vastgestelde keuze-vak door de decaan --> 
      <td class="Cel17VPKeuze" colspan="2"> <b>De twee keuze vakken: </b></td>
      <td class="Cel37VPKeuze">  <i>Decaan bepaalt 2 vakken uit de overige vakken!</i> </td>
	</tr>
   <tr>
	  <!-- Dit is het gedeelte waarin de leerling maximaal 2 vrije vakken kan kiezen --> 
      <td class="Cel18VPKeuze" colspan="2"> <b>mavo / havo <br>(maximaal twee) vrije vakken:</b></td>
      <td class="Cel38VPKeuze">  <i>Leerling bepaalt evt 2 vakken uit de overige vakken!</i> </td>
	</tr>
  </table>
  <br>
  <br>
  
  <!-- ********* -->
  <DIV style="page-break-after:always"></DIV>
 </body>
</html>