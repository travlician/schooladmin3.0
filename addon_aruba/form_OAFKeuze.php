<?
  session_start();
require_once("schooladminfunctions.php");
require_once("inputlib/inputclass_autosuggest.php");
if(isset($_POST['fieldid']))
{ // If fieldid is posted, this means a field with Ajax methods is filled.
  if(isset($_POST['NaamLL']))
  {
    inputclassbase::dbconnect($userlink);
    $sidqr = inputclassbase:: load_query("SELECT sid FROM studentnames WHERE name='". $_POST['NaamLL']. "'");
    if(isset($sidqr['sid'][0]))
	{
	  $_SESSION['OAFsid'] = $sidqr['sid'][0];
	  echo("OK". $sidqr['sid'][0]);
      exit;
	}
	else
	{ 
	  if(strlen($_POST['NaamLL']) > 4)
	    echo("Leerling naam komt niet voor");
	  else
	    echo("OK");
	  exit;
	}
  }
  else
  {
    echo("Ander veld");
    exit;    
  }
}
// Create database view with student names
mysql_query("CREATE VIEW studentnames AS SELECT sid,CONCAT(lastname,' ',firstname) AS name FROM student", $userlink);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<!--/* vim: set expandtab tabstop=4 shiftwidth=4: */
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
-->
<html>
 <head>
   <META NAME="Author"	CONTENT="Owner">
   <META NAME="GENERATOR" CONTENT="">
   <META NAME="KEYWORDS" CONTENT="">
   <META NAME="DESCRIPTION" CONTENT="">
   <META NAME="HEADER" CONTENT="">

   <TITLE>Overdrachts-Aanmeldingsformulier SKOA voor mavo</TITLE>
   <link rel="stylesheet" type="text/css" href="styleOAFmavo.css">

<style>
  .SocEmotInfo input[type="checkbox"]{background: white; color: black;}
</style>

   </head>
<!--
// +----------------------------------------------------------------------------------------------------------+
// | Opzet Overdrachts - Aanmeldingsformulier SKOA / SPCOA / DPS                                              |
// +----------------------------------------------------------------------------------------------------------+
// | Overdrachtsformulier dient om leerlingeninfo over te dragen aan een andere school van gelijke signatuur. |
// | Aanmeldingsformulier dient om leerlingeninfo over te dragen aan een school met een andere signatuur.     |
// +----------------------------------------------------------------------------------------------------------+
// | De formulieren zijn nagenoeg hetzelfde. De invuller dient aan te geven om welke formulier het gaat.      |
// | Ook onderdelen van het formulier kunnen in / uitgeschakeld worden door de invuller.                      |
// +----------------------------------------------------------------------------------------------------------+
// | Dit formulier kan electronisch worden verzonden naar de betreffende school of kan worden afgedrukt.      |
// +----------------------------------------------------------------------------------------------------------+
-->
<body class=BodyOpmaakKeuzeAOF summary="Overdrachts-Aanmeldingsformulier SKOA voor mavo">
  <span class="KopieRechten">&reg;&nbsp; SchoolAdmin&nbsp;&nbsp;&copy;&nbsp;Aim4me</span>
  <!-- Keuze maken om welk formulier het betreft: -->
  <br>
  <br>
  <h1><center>Overdracht- en Aanmeldingsformulieren</center></h1>
  <br>
  <br>
<? // Aanmaken autosuggest veld
  $llnamefld = new inputclass_autosuggest("NaamLL",50,$userlink,"name","studentnames","sid",0,"text-align:left; border-width:0px 2px 2px 0px; border-color:#FF644D; font-size:20px;");
?>
   <span class="FormKeuzeTekst1">Om welke leerling gaat het:</span>&nbsp;&nbsp; <? $llnamefld->echo_html(); ?>&nbsp;&nbsp;
  <span class="Commentaar1">Achternaam Voorna(a)m(en)</span>
  <br>
  <br>
  <span class="FormKeuzeTekst1">Kies welk soort formulier u gaat invullen:</span>
  <br>
  <br>
   <input class="FormKeuzeTekst2" type="button" name="OForm" value="Klik hier" onclick="location.href='form_OFmavo.php'">
  <span class="FormKeuzeTekst3">&nbsp;&nbsp; Voor een S.K.O.A. OVERDRACHTsformulier.</span>
    <span class="Commentaar3">&nbsp;&nbsp; HINT: Waar ???? staat moet "Details vd Leerling" gecontroleerd worden.</span>
  <br>
  <span class="Commentaar2">&nbsp;&nbsp; (Overdrachtsformulier dient om leerlingeninfo over te dragen aan een andere school van gelijke signatuur)</span>
  <br>
  <br>
  <input class="FormKeuzeTekst2" type="button" name="OAForm" value="Klik hier" onclick="location.href='form_OAFmavo.php'">  
  <span class="FormKeuzeTekst3">&nbsp;&nbsp; Voor een S.K.O.A. OVERDRACHTs- AANMELDINGsformulier.</span>
    <span class="Commentaar3">&nbsp;&nbsp; HINT: Waar ???? staat moet "Details vd Leerling" gecontroleerd worden.</span>
  <br>
  <span class="Commentaar2">&nbsp;&nbsp; (Overdrachtsformulier dient om leerlingeninfo over te dragen aan een school met een andere signatuur)</span>
  <br>
  <br>
  <br>
  <input class="FormKeuzeTekst2" type="button" name="OForm" value="Klik hier" onclick="location.href='Form-OFmavo.html'">
  <span class="FormKeuzeTekst3">&nbsp;&nbsp; Voor een S.P.C.O.A. OVERDRACHTsformulier.</span>
    <span class="Commentaar3">&nbsp;&nbsp; HINT: Waar ???? staat moet "Details vd Leerling" gecontroleerd worden.</span>
  <br>
  <span class="Commentaar2">&nbsp;&nbsp; (Overdrachtsformulier dient om leerlingeninfo over te dragen aan een school met een andere signatuur)</span>
  <br>
  <br>
  <input class="FormKeuzeTekst2" type="button" name="OAForm" value="Klik hier" onclick="location.href='Form-OAFmavo.html'">  
  <span class="FormKeuzeTekst3">&nbsp;&nbsp; Voor een S.P.C.O.A. OVERDRACHTs- AANMELDINGsformulier.</span>
    <span class="Commentaar3">&nbsp;&nbsp; HINT: Waar ???? staat moet "Details vd Leerling" gecontroleerd worden.</span>
  <br>
  <span class="Commentaar2">&nbsp;&nbsp; (Overdrachtsformulier dient om leerlingeninfo over te dragen aan een school met een andere signatuur)</span>
  <br>
  <br>
  <span class="Commentaar5">&nbsp;&nbsp; Hier moeten komen O(A)F voor &nbsp;&nbsp; BO->BO, BO->EPB, BO->MAVO, BO->HAVO,</span><br>
  <span class="Commentaar6">&nbsp;&nbsp; MAVO->MAVO, MAVO->EPB, MAVO->HAVO, MAVO->EPI, HAVO->MAVO liefst zuil onafhankelijk.</span>
  <br>
  <br>
  <span class="Opm_Print">
    <u>Attentie</u>: nadat U het formulier heeft ingevuld kunt U deze
	<ol class="Lijst">
		<li>Afdrukken;</li>
		<li>opslaan als een .pdf bestand of</li>
		<li>opslaan als .pdf bestand en digitaal versturen naar de betreffende school.</li>
	</ol>
  </span>
</body>
</html>