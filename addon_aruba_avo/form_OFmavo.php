<?php
  session_start();
  require_once("schooladminfunctions.php");
  // LL name should be posted.. check
  if(!isset($_SESSION['OAFsid']))
  { // Error report and exit
    echo("Geen leerling ingevuld");
	exit;
  }
  require_once("student.php");
  // Link with database
  inputclassbase::dbconnect($userlink);
  $student = new student($_SESSION['OAFsid']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//CSS3//EN">
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

   <TITLE>Overdrachtsformulier SKOA voor mavo</TITLE>
   <link rel="stylesheet" type="text/css" href="styleOAFmavo.css">
<!-- Google CDN  (Content Delivery Network) -->
   <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<!-- Microsoft CDN  (Content Delivery Network) -->
   <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.min.js"></script>
<!-- Mozilla firefox CDN  (Content Delivery Network) -->

<!-- Safari CDN  (Content Delivery Network) -->

 </head>
<!--
// +----------------------------------------------------------------------------------------------------------+
// | Opzet Overdrachtsformulier SKOA / SPCOA / DPS                                              |
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
<body summary="Overdrachtsformulier SKOA voor mavo">
  <!-- Voorblad van het Overdrachts-Aanmeldingsformulier voor de mavo -->
  <img style="position:absolute; top:55px; left:110px; border="0" src="/PNG/LogoSKOA.jpg" width="100" alt="Logo";>
  <p class="TekstRechtsLogo">Copernicusstraat 11 P.O.Box 1065<br>
						     Oranjestad, Aruba.<br>
						     Tel.: (297) 582 1848, Fax (297) 582 0780<br>
						     E-mail: info@skoa.aw
  </p>
  <br><br><br><br><br><br><br><br>
  <hr color="#000000" width="80%" style="border-style: dotted"; align="center">
  <br><br><br><br><br><br>
<!-- <Tabel voorblad Overdrachtsformulier"> -->
  <table id=OFVoorBlad class="vBladtabelOF" style="border-radius: 25px";>
    <tr>
      <td>
		<br>
		<p class="KeuzeVblad">Schoolkeuze:</p>
	    <p class="KeuzeVblad">1<sup>ste</sup> keus: <input size="40" style="text-align:left" name="Keus1">
		        &nbsp;&nbsp;Broer/zus in klas: <input size="15" style="text-align:left" style="text-align:left""Keus1"></p>
	    <p class="KeuzeVblad">2<sup>de</sup> keus:&nbsp;&nbsp;<input size="40"  type="text" style="text-align:left" name="Keus1"></p>
		<br>
		<br>
		<br>
	    <p class="TitelVBlad">OVERDRACHTSFORMULIER<br>
	                         Voor het MAVO
		</p>
	    <p class="TitelVBlad">SKOA scholen<br>
		<!-- ******************************************************************************************* -->
		<!-- Intikken dan zoek het LVS de bijbehorende infomatie en plaatst deze meteen in het formulier -->
		<!-- ******************************************************************************************* -->
		<!-- Get the list of periods with their details -->
		<?php 
		   $periods = SA_loadquery("SELECT * FROM period ORDER BY id");
		   if(isset($periods['year']))
		     $curyear = $periods['year'][1];
		?>
		   <span class="SubTitelVBlad">Schooljaar</span><span class="SubTitelVBladUitDB"> <?php echo($curyear); ?></bold></span>
		</p>
	    <br>
		<center><span class="Schoolbestuur">Schoolbestuur: S.K.O.A.</span></center>  <!-- moet uit de database gehaald worden -->
	    <br>
	    <div class="dots"><span class="term">Naam van school:</span> <span class="InfoDB" >????</span></div> <!-- moet uit de database gehaald worden -->
	    <div class="dots"><span class="term">Hoofd van de school:</span> <span class="InfoDB" >????</span></div> <!-- moet uit de database gehaald worden -->
	    <div class="dots"><span class="term">Telefoonnummer(s) van school:</span> <span class="InfoDB" >??? ????<span style="clear:both";></span></span></div> <!-- moet uit de database gehaald worden -->
		<br>
		<br>
		<div class="LeerlingInfo">Naam van de leerling:<span class="InfoUitDB">&nbsp;&nbsp;<?php echo($student->get_lastname(). " ". $student->get_firstname());?></span>
							     &nbsp;&nbsp; Sexe: <span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASGender") == "" ? "????" : $student->get_student_detail("s_ASGender")); ?></span>
		</div>
		<BR>
		<!-- Get the mentor for this student: -->
		<?php
			//$CurrentUID = $_SESSION['uid'];
			$uid		= intval($_SESSION['uid']);
		?>
		<div class="LeerlingInfo">Ingevuld door docent: &nbsp;&nbsp; 
			<span class="InfoUitDB"><?php $tname = SA_loadquery("SELECT CONCAT(firstname, ' ', lastname) AS name FROM teacher WHERE tid='$uid' "); echo($tname['name'][1]);?></span>
		</div><BR>
<!-- Huidige datum en tijd bepalen wanneer het formulier is ingevuld: -->
		<?php
		  $CurDate  = getdate(date("U"));
		  $CurMonth = $CurDate["month"];
		  $CurDay   = $CurDate["weekday"];
		  $CurDayNr = $CurDate["mday"];
		  $CurYear  = $CurDate["year"];
		  switch ($CurMonth)
		  {
		    case "January";
			  $CurMonth = "januari";
			  Break;
		    case "February";
			  $CurMonth = "februari";
			  Break;
		    case "March";
			  $CurMonth = "maart";
			  Break;
		    case "April";
			  $CurMonth = "april";
			  Break;
		    case "May";
			  $CurMonth = "mei";
			  Break;
		    case "June";
			  $CurMonth = "juni";
			  Break;
		    case "July";
			  $CurMonth = "juli";
			  Break;
		    case "August";
			  $CurMonth = "augustus";
			  Break;
		    case "September";
			  $CurMonth = "september";
			  Break;
		    case "October";
			  $CurMonth = "oktober";
			  Break;
		    case "November";
			  $CurMonth = "november";
			  Break;
		    case "December";
			  $CurMonth = "december";
			  Break;
		  }
		  // dag bepalen:
		  switch ($CurDay)
		  {
		    case "Monday";
			  $CurDay = "maandag";
			  Break;
		    case "Tuesday";
			  $CurDay = "Dinsdag";
			  Break;
		    case "Wednesday";
			  $CurDay = "woensdag";
			  Break;
		    case "Thurday";
			  $CurDay = "Donderdag";
			  Break;
		    case "Friday";
			  $CurDay = "vrijdag";
			  Break;
		    case "Saturday";
			  $CurDay = "Zaterdag";
			  Break;
		    case "Sunday";
			  $CurDay = "Zondag";
			  Break;
		  }			  
		  ?>
			<div class="LeerlingInfo">Datum:&nbsp;&nbsp;&nbsp;
			<span class="InfoUitDB"><?php echo("$CurDay" . ",  " . "$CurDayNr" ." ". "$CurMonth" . " " .  "$CurYear"); ?></span></div>
		<br>
		<br>
	    <span class="Opm_VBlad">A.U.B. Aanvullende informatie als bijlage toevoegen<BR>
	    i.v.t. &nbsp;&nbsp;<input type="checkbox" name="Bijlage" value="none">&nbsp;&nbsp; aanklikken!</span>
	    <br>
      </td>
	</tr>
  </table>
  <!-- ********* -->
  <DIV style="page-break-after:always"></DIV>

<!-- ******************************************************************************************* -->
<!-- Blad 1, tabel 1: gaat over de persoonlijke gegevens van de leerling; UIT DE DATABASE        -->
<!-- Blad 1, tabel 2: gaat over de ouder/voogd gegevens van de leerling; UIT DE DATABASE         -->
<!-- ******************************************************************************************* -->
		
<!-- ************************** Koptekst/Header ************************************************ -->
  <br>
  <div class="pagina_nrs">Blad 1</div>
  <hr color="#000000" width="80%" style="border-style: dotted"; align="center">
<!-- ******************************************************************************************* -->
  <br>
  <br>
<!--
 Tabel met informatie over de leerling waarvan een aantal gegevens in het LVS staan
-->
  <table id=InfoLL class="LLGeg">
	<tr>
	  <td class="KopLLTabel" colspan="3">Gezondheidsinformatie over de leerling</td>
	</tr>
	<tr>
	  <td class="TxtOpm00">Achternaam: <span class="InfoUitDB"><?php echo($student->get_lastname());?></span></td>
	  <td class="TxtOpm50" colspan=2><span class="spannedlist1">Code:&nbsp;&nbsp;&nbsp;</span>
			<span class="spannedlist1">
					  <span class=".spannedlist1"><input type="radio" name="code" value="zorgleerling">&nbsp;zorgleerling<br>
					  <input type="radio" name="code" value="risicoleerling">&nbsp;risicoleerling<br>
					  <input type="radio" name="code" value="n.v.t.">&nbsp;n.v.t.
			</span>
	  </td>
	</tr>
	<tr>
	  <td class="TxtOpm10">Voornamen:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo( $student->get_firstname() == "" ? "????" : $student->get_firstname()); ?></span></td>
	  <td class="TxtOpm11">Roepnaam:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASNickname") == "" ? "????" : $student->get_student_detail("s_ASNickname"));?></span></td>
	  <td class="TxtOpm11">Klas:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("*sgroup.groupname") == "" ? "????" : $student->get_student_detail("*sgroup.groupname"));?></span></td> 
	</tr>
	<tr>
	  <td class="TxtOpm10">Geboortedatum:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASBirthDate") == "" ? "????" : $student->get_student_detail("s_ASBirthDate")); ?></span></td>
	  <td class="TxtOpm11">Geboorteland:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASBirthCountry") == "" ? "????" : $student->get_student_detail("s_ASBirthCountry")); ?></span></td>
	  <td class="TxtOpm11">Geslacht:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASGender") == "" ? "????" : $student->get_student_detail("s_ASGender")); ?></span></td>
	</tr>
	<tr>
	  <td class="TxtOpm10">Adres:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASAddress") == "" ? "????" : $student->get_student_detail("s_ASAddress")); ?></span></td>
	  <td class="TxtOpm11"colspan="2">District:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASDistrict") == "" ? "????" : $student->get_student_detail("s_ASDistrict")); ?></span></td>
	</tr>
	<tr>
	  <td class="TxtOpm10">Natonaliteit:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASNationality") == "" ? "????" : $student->get_student_detail("s_ASNationality")); ?></span></td>
	  <td class="TxtOpm11" colspan="2">Woont op Aruba sinds:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASInArubaSince") == "" ? "????" : $student->get_student_detail("s_ASInArubaSince")); ?></span></td>
	  </td>
	<tr>
	  <td class="TxtOpm10" colspan="3">Thuistaal /Moedertaal:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_voertaal") == "" ? "????" : $student->get_student_detail("s_voertaal")); ?></span></td>
	</tr>
	<tr>
	  <td class="TxtOpm10" colspan="3">Leerling is opgevoed door:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB">
	  </td>
	</tr>

	  <!--  Dit is een alternatief voor de volgende stap maar mooier is als het input-veld verschijnt indien noodzakelijk
			echo($student->get_student_detail("s_ASRaisedBy") == "" ? "????" : $student->get_student_detail("s_ASRaisedBy")); ?></span></td> -->
	<tr>
	  <td class="TxtOpm10" colspan="3">Leerling is opgevoed door:&nbsp;&nbsp;&nbsp;
		<form method="POST" action="/">
		  <fieldset>
			<div class="radio-group">
               <input type="radio" id="onderwerp" name="onderwerp" value="biologische ouders"/><label for="onderwerp">biologische ouders</label>
			</div>
			<div class="radio-group">
               <input type="radio" name="onderwerp" value="grootouders"/><span>grootouders</span>
			</div>
			<div class="radio-group">
               <input type="radio" name="onderwerp" value="familielid1"/><span>familielid (g&eacute;&eacute;n grootouders)</span>
			</div>
			<div class="radio-group">
               <input type="radio" name="onderwerp" value="familielid2"/><span>biologische + stiefouder</span>
			</div>
			<div class="radio-group">
               <input type="radio" name="onderwerp" value="familielid3"/><span>adoptieve / pleegouders</span>
			</div>
			<div class="radio-group">
               <input type="radio" name="onderwerp" value="familielid4"/><span>Onbekend</span>
			</div>
			<div class="radio-groupAnders">
               <input type="radio" name="onderwerp" value="anders" /><span>Anders namelijk...</span>
			</div>     
			<div class="anders-namelijk-textarea hidden">
               <textarea cols="70" rows="2"></textarea>
			</div>
		  </fieldset>
		</form>

<input type="checkbox" id="ossm" name="ossm"> 
<label for="ossm">CSS is Awesome</label>
		
		</td>
	</tr>
	<tr>
	  <td class="TxtOpmBG" colspan="3">Thuissituatie:<br> &nbsp;&nbsp; zijn er factoren in de thuissituatie die het functioneren van de leerling op een
										bijzondere wijze be&iuml;nvloeden?<br>
										 &nbsp;&nbsp;&nbsp;<select><option>????</option>
																   <option>Ja</option>
																   <option>Nee</option>
																   <option>Onbekend</option>
														   </select>
<!-- hoe wordt deze informatie gekoppeld aan een variabele? -->
										</td>
	</tr>
	<tr>
	  <td class="TxtOpmBG" colspan="3">Hoe is de relatie school ouders/verzorgers?<br>
					<textarea cols="70" rows="2" type="text" style="text-align:left" name="Relatie"></textarea>
	  </td>
	</tr>
	<tr>
	  <td class="TxtOpmBG" colspan="3">Gebruikt de leerling medicijnen waar onze school van op de hoogte moet zijn? &nbsp;&nbsp; <!-- moet uit de database gehaald worden -->
										<select><option>????</option>
												<option>Ja</option>
												<option>Nee</option>
												<option>Onbekend</option>
										</select>
	  </td>
	</tr>
 	<tr>
	  <td class="TxtOpmBG" colspan="3">Gezondheid:<br> &nbsp;&nbsp; heeft de leerling medische problemen die het volgen van onderwijs en/of leren bemoeilijken? &nbsp;&nbsp; <!-- moet uit de database gehaald worden -->
										<select><option>????</option>
												<option>Ja</option>
												<option>Nee</option>
												<option>Onbekend</option>
										</select>
	  </td>
	</tr>
 	<tr>
	  <td class="TxtOpmBG" colspan="3">Verzorgt de school brood voor de leerling? &nbsp;&nbsp; <!-- moet uit de database gehaald worden -->
										<select><option>????</option>
												<option>Ja</option>
												<option>Nee</option>
												<option>Onbekend</option>
										</select>
	  </td>
	</tr>
  </table>
  <br>
  <br>
  <br>
<!--
 Blad 1, Tabel 2: met informatie over de ouders waarvan een aantal gegevens in het LVS staan
-->
  <table id=InfoOuders class="OudersGeg">
	<tr>
	  <td class="KopOudersTabel" colspan="2">Informatie over de ouder(s)/verzorger(s)</td>
	</tr>
	<tr>
	  <td class="TxtOpm20" width=50% >Naam Ouder/Verzorger1 (de biologische (?) vader):<br>
										&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASLastNameParent1") == "" ? "????" : $student->get_student_detail("s_ASLastNameParent1")); ?></span></td>
	  <td class="TxtOpm21" width=50% >Naam Ouder/Verzorger2 (de biologische (?) moeder):<br>
										&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASLastNameParent2") == "" ? "????" : $student->get_student_detail("s_ASLastNameParent2")); ?></span></td>
	</tr>
	<tr>
	  <td class="TxtOpm20" >Geboorteland:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASBirthDate1") == "" ? "????" : $student->get_student_detail("s_ASBirthDate1")); ?></span></td>
	  <td class="TxtOpm21">Geboorteland:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASBirthDate2") == "" ? "????" : $student->get_student_detail("s_ASBirthDate2")); ?></span></td>
	</tr>
	<tr>
	  <td class="TxtOpm20" >Adres:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASAddressParent1") == "" ? "????" : $student->get_student_detail("s_ASAddressParent1")); ?></span></td>
	  <td class="TxtOpm21">Adres:&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASAddressParent2") == "" ? "????" : $student->get_student_detail("s_ASAddressParent2")); ?></span></td>
	</tr>
	<tr>
	  <td class="TxtOpm20" >Telefoonnummer(s):&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASPhoneHomeParent1") == "" ? "????" : $student->get_student_detail("s_ASPhoneHomeParent1")); ?></span></td>
	  <td class="TxtOpm21">Telefoonnummer(s):&nbsp;&nbsp;&nbsp;<span class="InfoUitDB"><?php echo($student->get_student_detail("s_ASPhoneHomeParent2") == "" ? "????" : $student->get_student_detail("s_ASPhoneHomeParent2")); ?></span></td>
	</tr>
	<tr>
	  <td class="TxtOpm50" colspan=2><span class="spannedlist">De biologische ouder(s):<br>
			<span class="Commentaar4">(aangeven wat van toepassing is)</span></span>
			<span class="spannedlist">
					  <input type="checkbox" name="code" value="BioOuders1">&nbsp;&nbsp; leven (on)gehuwd samen<br>
					  <input type="checkbox" name="code" value="BioOuders2">&nbsp;&nbsp; zijn gescheiden<br>
					  <input type="checkbox" name="code" value="BioOuders3">&nbsp;&nbsp; alleenstaande moeder / vader<br>
					  <input type="checkbox" name="code" value="BioOuders4">&nbsp;&nbsp; vader / moeder zijn overleden<br>
					  <input type="checkbox" name="code" value="BioOuders5">&nbsp;&nbsp; vader / moeder hebben geen voogdij over het kind meer<br>
					  <input type="checkbox" name="code" value="BioOuders6">&nbsp;&nbsp; afstand van kind gedaan</span>
	  </td>
	</tr>
	<tr>
	  <td class="TxtOpm50" colspan=2><span class="spannedlist">Het Ouderlijk gezag rust bij:<br>
			<span class="Commentaar4">(aangeven wat van toepassing is)</span></span>
	     <span class="spannedlist">
					  <input type="radio" name="code" value="Gezag">&nbsp;&nbsp; (adoptie/pleeg) ouders<br>
					  <input type="radio" name="code" value="Gezag">&nbsp;&nbsp; biologische / adoptieve vader / moeder<br>
					  <input type="radio" name="code" value="Gezag">&nbsp;&nbsp; grootouders / familielid<br>
					  <input type="radio" name="code" value="Gezag">&nbsp;&nbsp; anders: <input size="40"  type="text" style="text-align:left" name="Gezag"></SPAN>
	  </td>
	</tr>
	<tr>
	  <td class="TxtOpmBG50" colspan="2">Ontvangt de ouder(s)/verzorger(s) sociale hulp/bijstand van de Sociale Zaken? &nbsp;&nbsp;
										<select><option>????</option>
												<option>Ja</option>
												<option>Nee</option>
												<option>Onbekend</option>
										</select>
	  </td>
  </table>
  <!-- ********* -->
  <DIV style="page-break-after:always"></DIV>

		<!-- ******************************************************************************************* -->
		<!-- Blad 2 gaat over de persoonlijke gegevens van de ouders/verzorgers; UIT DE DATABASE         -->
		<!-- ******************************************************************************************* -->

		
<!-- ************************** Koptekst ******************************************************* -->
<br>
<div class="pagina_nrs">Blad 2</div>
<hr color="#000000" width="80%" style="border-style: dotted"; align="center">
<!-- ******************************************************************************************* -->
<br>
<!-- 
 Tabel met informatie over Sociaal Emotioneel Gebied van de leerling waarvan een aantal gegevens in het LVS staan:
-->
  <table id=SocEmotGeboed class="SocEmot">
	<tr><th class="KopTblSocEmot" colspan="2">Sociaal Emotioneel Gebied</th>
	</tr>
	<tr>
	  <td class="KopTekst" colspan="2">Positieve reacties</td>
	</tr>
	<tr>
	  <td class="Tekst1SocEmot" colspan="2">
	    <input type="checkbox" name="SocEmot1" value="Status">&nbsp;&nbsp; geeft blijk van samenhorigheidsgevoel: verhoogt status van anderen, geeft steun, is sociaal<br>
			<span class="Indent1">en is vriendelijk.</span><br>
		<input type="checkbox" name="SocEmot2" value="BioOuders">&nbsp;&nbsp; geeft blijk van ontspanning: maakt grappen, lacht, toont tevredenheid.<br>
		<input type="checkbox" name="SocEmot3" value="BioOuders">&nbsp;&nbsp; is het eens, geeft toe, werkt mee, toont interesse en instemming.<br>
		<br>
		<div class="Tekst2SocEmot">Wat zijn de positieve eigenschappen van de leerling, opvallende zaken in de omgang met<br>
		klasgenoten, of ten opzichte van de leerkracht:
		</div>	
		<textarea cols="69" rows="4" type="text" style="text-align:left" name="Relatie"></textarea>
		<br>
	  </td>
	</tr>
	<tr>
	  <td class="KopTekst1" colspan="2">Gedragsproblemen</td>
	</tr>
	<tr>
	  <td class="Tekst1SocEmot" colspan="2">
		<div class="Tekst2SocEmot">Zijn er problemen op het gebied van gedrag en omgang die het functioneren van de leerling op
		een bijzondere wijze beinvloeden? 
										<select><option>????</option>
												<option>Ja</option>
												<option>Nee</option>
												<option>Onbekend</option>
										</select><br>
<!-- Moet deze ja / nee worden opgeslagen?? -->
		Zo ja, welke kenmerken zijn van toepassing.
		</div>
	  </td>
	</tr>
	<tr>
	  <td class="Tekst1SocEmot">
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen1">&nbsp;&nbsp; teruggetrokken gedrag<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen2">&nbsp;&nbsp; angstig / depressief<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen3">&nbsp;&nbsp; lichamelijke klachten<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen4">&nbsp;&nbsp; aandachts- en concentratiestornis<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen5">&nbsp;&nbsp; impulsiviteit<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen6">&nbsp;&nbsp; sociale problemen<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen7">&nbsp;&nbsp; grensoverscheidend gedrag<br><br>
	  </td>
	  <td class="Tekst1SocEmot">
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen8">&nbsp;&nbsp; seksueel getint gedrag<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen9">&nbsp;&nbsp; slaapproblemen<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen10">&nbsp;&nbsp; relationele problemen<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen11">&nbsp;&nbsp; bewegingsstornissen; onhandigheid<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen12">&nbsp;&nbsp; hyperactiviteit/overbewegelijk<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen13">&nbsp;&nbsp; agessief gedrag<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen15">&nbsp;&nbsp; anders: <input type="text" size="30" name="anders"> <br>
	  </td>
	</tr>
	<tr>
	  <td class="KopTekst1" colspan="2">Tekortschietende opvoeding</td>
	</tr>
	<tr>
	  <td class="Tekst1SocEmot" colspan="2">
	    <input type="checkbox" name="SocEmot2" value="Opvoeding1">&nbsp;&nbsp; De ouders zijn niet bij machte de adequate verzorging en opvoeding te bieden.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding2">&nbsp;&nbsp; Belangrijke opvoedingstaken zoals het volgen van het doen enlaten van het kind, het geven van<br>
			<span class="Indent1">positieve versterking en het handteren van regels zijn onvoldoende aanwezig.</span><br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding3">&nbsp;&nbsp; Het kind wordt regelmatig alleen gelaten.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding4">&nbsp;&nbsp; Ernstige huwelijksconflicten.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding5">&nbsp;&nbsp; Ernstige psychigische problemen.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding6">&nbsp;&nbsp; Drug gebruik of alcoholgebruik bij ouders.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding7">&nbsp;&nbsp; Relatieproblemen tussen ouders.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding8">&nbsp;&nbsp; Zwak begaafdhetis bij (een van) de ouders.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding9">&nbsp;&nbsp; Financi&umlt;le omstandigheden spelen een rol.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding10">&nbsp;&nbsp; Ouder weigert kind toegang tot het huis. Het kind verblijft tijdelijk elders, bij familie of is zwervende.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding11">&nbsp;&nbsp; Kind is van huis weggelopen. Het kind verblijf tijdelijk elders, bij familie of is zwervende.<br>
	  </td>
	</tr>
	<tr>
	  <td class="KopTekst1" colspan="2">Kindernishandeling</td>
	</tr>
	<tr>
	  <td class="Tekst1SocEmot" colspan="2">
	    <input type="checkbox" name="SocEmot2" value="Opvoeding1">&nbsp;&nbsp; Bedreiging.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding3">&nbsp;&nbsp; Fysiekgeweld.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding4">&nbsp;&nbsp; Verbaalgeweld.<br>
	    <input type="checkbox" name="SocEmot2" value="Opvoeding4">&nbsp;&nbsp; Vermoedens van kindermishandeling.<br>
	  </td>
	</tr>
	<tr>
	  <td class="KopTekst1" colspan="2">Specifiek diagnostiseerde problematiek</td>
	</tr>
	<tr>
		<td class="Tekst3SocEmot" colspan="2">Indien er sprake is van  een specifieke problematiek of een vermoeden hiervan dit gaarne aangeven
		en indien mogelijk een verklaring van een deskundige bijvoegen.
		</td>
	</tr>
	<tr>
	  <td class="Tekst4SocEmot">
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen1">&nbsp;&nbsp; ADHD<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen2">&nbsp;&nbsp; PDD-NOS / autisme<br><br>
	  </td>
	  <td class="Tekst4SocEmot">
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen3">&nbsp;&nbsp; dyslexie<br>
	    <input type="checkbox" name="SocEmot2" value="GedragsProblemen4">&nbsp;&nbsp; of anderszins:
				<input type="text" size="25" name="Anderszins">
	  </td>
	</tr>
	<tr>
	  <td colspan="2"><span class="Tekst5SocEmot">Is het kind getest door MDC / JGZ?</span>&nbsp;&nbsp;
										<select><option>????</option>
												<option>Ja</option>
												<option>Nee</option>
												<option>Onbekend</option>
										</select><br>
       <span class="Tekst5SocEmot">(Kopie bijvoegen)</span>
	  </td>
	</tr>
  </table>
  <!-- ********* -->
  <DIV style="page-break-after:always"></DIV>
 
<!-- ************************** Koptekst ******************************************************* -->
<br>
<div class="pagina_nrs">Blad 3</div>
<hr color="#000000" width="80%" style="border-style: dotted"; align="center">
<!-- ******************************************************************************************* -->
<br><br>
<!--
 Tabel met informatie over schoolmanagement waarvan een aantal gegevens in het LVS staan.
-->

<!--
 Tabel met verzuim van leerling waarvan een aantal gegevens in het LVS staan
-->
  <table id=Verzuim class="VerzuimTbl">
	<tr><th class="KopVerzuimTbl" colspan="2">Verzuim</th></tr>
	<tr>
	  <td class="Inspringen8px" colspan="2"><br>Is er sprake van verzuim: ????
<!-- 
	Het antwoord dat hier moet komen heeft gradaties die niet bekend zijn.
	Zolang dat niet bekend is kan het LVS het antwoord niet produceren.
	Verder heeft het de schijn van subjectief zijn.
	De gradaties zijn:  * nee (geen probleem)  * ja, geoorloofd een enkele keer;
						* ja, geoorloofd vaak; * ja, ongeoorloofd een enkele keer;
						* ja, ongeoorloofd vaak;
-->
	  </td>
	</tr>
	<tr>
		<td class="Inspringen10px" colspan="2">Zo ja, hoeveel keer bedroeg het verzuim: ???? <br><!-- moet uit de database gehaald worden -->
		                               Opmerkingen betreffende schoolverzuim:<br>
									   (bijv. is dit gemeld bij de zorgteam/Bureau Leerplicht)
					<textarea cols="70" rows="2" type="text" style="text-align:left" name="MeldingVerzuim"></textarea>
		</td>
	</tr>
  </table>
  <br><br><br><br>
<!--
 Tabel met speciale begeleiding van leerling waarvan een aantal gegevens in het LVS staan
-->
  <table id=SpecBegeleiding class="SpecBeglTbl">
	<tr><th class="KopSpecBeglTbl">Speciale begeleiding</th></tr>
	<tr>
		<td>
		  <span class="Inspringen8pxB">Ontving de leerling speciale begeleiding of krijgt de leerling hulp op dit moment:</span>
		  <span class="Inspringen8px"><select><option>????</option>
											  <option>Ja</option>
											  <option>Nee</option>
											  <option>Onbekend</option>
									  </select><br>
		  <span class="Inspringen8pxB">Van wie?</span><br>
	      <span class="Inspringen10px"><input type="checkbox" name="SocEmot2" value="Opvoeding1">&nbsp;&nbsp; eigen leerkracht,</span><br>
	      <span class="Inspringen10px"><input type="checkbox" name="SocEmot2" value="Opvoeding3">&nbsp;&nbsp; interne begeleider,</span><br>
	      <span class="Inspringen10px"><input type="checkbox" name="SocEmot2" value="Opvoeding4">&nbsp;&nbsp; schoolmaatschappelijk werker,</span><br>
	      <span class="Inspringen10px"><input type="checkbox" name="SocEmot2" value="Opvoeding4">&nbsp;&nbsp; remedial teacher,</span><br>
	      <span class="Inspringen10px"><input type="checkbox" name="SocEmot2" value="Opvoeding4">&nbsp;&nbsp; extern, namelijk van: psycholoog, psychiator, logopedist, orthopedagoog of anders.</span><br>
		  <span class="Inspringen10pxB">Naam/functie begeleider: <input type="text" size="60px" name="NaamSpecialist"></span><br>
		  <span class="Inspringen10pxB">Vanwege: <textarea cols="70" rows="2" type="text" style="text-align:left" name="Vanwege"></textarea></span>
		</td>
	</tr>
	<tr>
		<td class="Tekst5" colspan="2">Heeft de speciale begeleiding voldoende effect gehad?<br>
					<textarea cols="70" rows="2" type="text" style="text-align:left" name="Effect"></textarea>
		</td>
	</tr>
	<tr>
		<td class="Tekst5" colspan="2">Heeft de leerling nog extra begeleiding nodig?<br>
									   Zo ja, op welk gebied en wat is daarbij de hulpvraag:
					<textarea cols="70" rows="2" type="text" style="text-align:left" name="Effect"></textarea>
		</td>
	</tr>
	<tr>
		<td class="Tekst5" colspan="2">Advies en eventuele opmerkingen van de ouders:<br>
					<textarea cols="70" rows="2" type="text" style="text-align:left" name="Effect"></textarea>
		</td>
	</tr>
  </table>
  <br>
  <br>
  <br>
  <span class="Indent51">Aanvullende toelichting:</span><br>
  <span class="Indent52">Indien dit formulier niet door de verwijzende school volledig wordt<br></span>
  <span class="Indent52">ingevuld, zal de leerling geen overplaatsing krijgen!</span>
  <br>
  <br>
  <br>
  <br>
</body>
</html>