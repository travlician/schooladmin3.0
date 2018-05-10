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
  // Connect to the database
  inputclassbase::dbconnect($userlink);
	if(isset($_GET['byebye']))
	{
		echo("<H2>WIJ BEDANKEN U VOOR HET INVULLEN VAN DIT AANMELDINGSFORMULIER!<BR>Nos ta agradicido pa cu yenamento di e formulario aki!</H2>");
		exit;
	}
	
	// Array for conversion of register data to student data
	$reg2stud = array(	"geslacht" => "s_ASGender",
	"censoid" => "s_ASIdNr",
	"protestant" => "s_Protestant",
	"religionstudent" => "s_Religion",
	"baptised" => "s_ASBaptised",
	"medicalinsurancenumber" => "s_ASMedicalInsuranceNumber",
	"gebland" => "s_ASBirthCountry",
	"nationality" => "s_ASNationality",
	"homelanguage" => "s_ASHomeLanguage",
	"otherhomelanguages" => "s_ASHomeLanguagesOther",
	"adres" => "s_ASAddress",
	"district" => "s_ASDistrict",
	"responsableperson" => "s_ASResponsablePerson",
	"responsablealternative" => "s_ASResponsableOther",
	"emergencyphone" => "s_ASEmergyPhoneNr",
	"onarubasince" => "s_ASInArubaSince",
	"liveat" => "s_ASLiveAt",
	"liveatother" => "s_ASLiveAtOther",
	"lastnamemother" => "s_ASLastNameParent2",
	"firstnamesmother" => "s_ASFirstNameParent2",
	"religionmother" => "s_ASReligionParent2",
	"addressmother" => "s_ASAddressParent2",
	"phonehomemother" => "s_ASPhoneHomeParent2",
	"phonemobilemother" => "s_ASPhoneMobileParent2",
	"emailmother" => "s_ASEmailParent2",
	"professionmother" => "s_ASProfesionParent2",
	"employermother" => "s_ASEmployerParent2",
	"phoneworkmother" => "s_ASPhoneWorkParent2",
	"nationalitymother" => "s_ASNationalityParent2",
	"birthdatemother" => "s_ASBirthDateParent2",
	"lastnamefather" => "s_ASLastNameParent1",
	"firstnamesfather" => "s_ASFirstNameParent1",
	"religionfather" => "s_ASReligionParent1",
	"addressfather" => "s_ASAddressParent1",
	"phonehomefather" => "s_ASPhoneHomeParent1",
	"phonemobilefather" => "s_ASPhoneMobileParent1",
	"emailfather" => "s_ASEmailParent1",
	"professionfather" => "s_ASProfesionParent1",
	"employerfather" => "s_ASEmployerParent1",
	"phoneworkfather" => "s_ASPhoneWorkParent1",
	"nationalityfather" => "s_ASNationalityParent1",
	"birthdatefather" => "s_ASBirthDateParent1",
	"civilstateparents" => "s_ASCivilStateFamily",
	"familydoctor" => "s_ASHomeMedic",
	"familyconstellation" => "s_ASFamilyConstelation",
	//"pharmacy" => "s_ASPharamcy",
	"peuterschool" => "s_ASNurserySchool",
	"kleuterschool" => "s_ASKindergarten",
	"primaryschool" => "s_ASPrimarySchool",
	"primaryother" => "s_s_ASPrimarySchoolOther",
	"doublingclasses" => "s_ASFailedYearsPrimary",
	"brosname1" => "s_ASNameSibling1",
	"brosschool1" => "s_ASSchoolSibling1",
	"brosclass1" => "s_ASSchoolyearSibling1",
	"brosname2" => "s_ASNameSibling2",
	"brosschool2" => "s_ASSchoolSibling2",
	"brosclass2" => "s_ASSchoolyearSibling2",
	"brosname3" => "s_ASNameSibling3",
	"brosschool3" => "s_ASSchoolSibling3",
	"brosclass3" => "s_ASSchoolyearSibling3",
	"brosname4" => "s_ASNameSibling4",
	"brosschool4" => "s_ASSchoolSibling4",
	"brosclass4" => "s_ASSchoolyearSibling4",
	"email" => "s_ASEmailStudent",
	"motivation" => "s_ASPlacementMotivation",
	"medicalprobs" => "s_ASMedicalProblems",
	"developprobs" => "s_ASDevelopmentProblems",
	"testedwhen" => "s_ASTestedWhen",
	"testedby" => "s_ASTestedBy",
	"guidedby" => "s_ASGuidedBy",
	"allowtests" => "s_ASTestsAllowed",
	"remarks" => "s_ASSpecialComments",
	"registeredinclass" => "s_ASRegistrationClass",
	"paid" => "s_paid"
);
  
  // Create tables if do not exist
  $sqlquery = "CREATE TABLE IF NOT EXISTS `inschrijvingMB2` (
    `rid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
 	`year` VARCHAR(30),
  `sid` INTEGER(11) DEFAULT NULL,
  `firstname` TEXT DEFAULT NULL,
  `lastname` TEXT DEFAULT NULL,
	`geslacht` TEXT DEFAULT NULL,
	`gebdag` TEXT DEFAULT NULL,
	`gebmaand` TEXT DEFAULT NULL,
	`gebjaar` TEXT DEFAULT NULL,
	`censoid` TEXT DEFAULT NULL,
	`protestant` TEXT DEFAULT NULL,
	`religionstudent` TEXT DEFAULT NULL,
	`baptised` TEXT DEFAULT NULL,
	`medicalinsurancenumber` TEXT DEFAULT NULL,
	`gebland` TEXT DEFAULT NULL,
	`nationality` TEXT DEFAULT NULL,
	`homelanguage` TEXT DEFAULT NULL,
	`otherhomelanguages` TEXT DEFAULT NULL,
	`adres` TEXT DEFAULT NULL,
	`district` TEXT DEFAULT NULL,
	`responsableperson` TEXT DEFAULT NULL,
	`responsablealternative` TEXT DEFAULT NULL,
	`emergencyphone` TEXT DEFAULT NULL,
	`onarubasince` TEXT DEFAULT NULL,
	`liveat` TEXT DEFAULT NULL,
	`liveatother` TEXT DEFAULT NULL,
	`lastnamemother` TEXT DEFAULT NULL,
	`firstnamesmother` TEXT DEFAULT NULL,
	`religionmother` TEXT DEFAULT NULL,
	`addressmother` TEXT DEFAULT NULL,
	`phonehomemother` TEXT DEFAULT NULL,
	`phonemobilemother` TEXT DEFAULT NULL,
	`emailmother` TEXT DEFAULT NULL,
	`professionmother` TEXT DEFAULT NULL,
	`employermother` TEXT DEFAULT NULL,
	`phoneworkmother` TEXT DEFAULT NULL,
	`nationalitymother` TEXT DEFAULT NULL,
	`birthdatemother` TEXT DEFAULT NULL,
	`lastnamefather` TEXT DEFAULT NULL,
	`firstnamesfather` TEXT DEFAULT NULL,
	`religionfather` TEXT DEFAULT NULL,
	`addressfather` TEXT DEFAULT NULL,
	`phonehomefather` TEXT DEFAULT NULL,
	`phonemobilefather` TEXT DEFAULT NULL,
	`emailfather` TEXT DEFAULT NULL,
	`professionfather` TEXT DEFAULT NULL,
	`employerfather` TEXT DEFAULT NULL,
	`phoneworkfather` TEXT DEFAULT NULL,
	`nationalityfather` TEXT DEFAULT NULL,
	`birthdatefather` TEXT DEFAULT NULL,
	`civilstateparents` TEXT DEFAULT NULL,
	`familydoctor` TEXT DEFAULT NULL,
	`familyconstellation` TEXT DEFAULT NULL,
	`pharmacy` TEXT DEFAULT NULL,
	`peuterschool` TEXT DEFAULT NULL,
	`kleuterschool` TEXT DEFAULT NULL,
	`primaryschool` TEXT DEFAULT NULL,
	`primaryother` TEXT DEFAULT NULL,
	`doublingclasses` TEXT DEFAULT NULL,
	`brosname1` TEXT DEFAULT NULL,
	`brosschool1` TEXT DEFAULT NULL,
	`brosclass1` TEXT DEFAULT NULL,
	`brosname2` TEXT DEFAULT NULL,
	`brosschool2` TEXT DEFAULT NULL,
	`brosclass2` TEXT DEFAULT NULL,
	`brosname3` TEXT DEFAULT NULL,
	`brosschool3` TEXT DEFAULT NULL,
	`brosclass3` TEXT DEFAULT NULL,
	`brosname4` TEXT DEFAULT NULL,
	`brosschool4` TEXT DEFAULT NULL,
	`brosclass4` TEXT DEFAULT NULL,
	`email` TEXT DEFAULT NULL,
	`motivation` TEXT DEFAULT NULL,
	`medicalprobsyn` TEXT DEFAULT NULL,
	`medicalprobs` TEXT DEFAULT NULL,
	`developprobsyn` TEXT DEFAULT NULL,
	`developprobs` TEXT DEFAULT NULL,
	`testedyn` TEXT DEFAULT NULL,
	`testedwhen` TEXT DEFAULT NULL,
	`testedby` TEXT DEFAULT NULL,
	`guidedyn` TEXT DEFAULT NULL,
	`guidedby` TEXT DEFAULT NULL,
	`allowtests` TEXT DEFAULT NULL,
	`visus` TEXT DEFAULT NULL,
	`hearing` TEXT DEFAULT NULL,
	`medicineuse` TEXT DEFAULT NULL,
	`remarks` TEXT DEFAULT NULL,
	`proof` TEXT DEFAULT NULL,
	`accepted` TEXT DEFAULT NULL,
	`registeredinclass` TEXT DEFAULT NULL,
	`paid` TEXT DEFAULT NULL,
	`lvsid` TEXT DEFAULT NULL,
	`lvspassword` TEXT DEFAULT NULL,
  `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`rid`),
    UNIQUE KEY `sidyr` (`sid`, `year`)
    ) ENGINE=InnoDB CHARSET=utf8;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
	
	// Add the new columns
	mysql_query("ALTER TABLE inschrijvingMB2 ADD COLUMN proof TEXT DEFAULT NULL AFTER remarks", $userlink);
	mysql_query("ALTER TABLE inschrijvingMB2 ADD COLUMN schooltype TEXT DEFAULT NULL AFTER year", $userlink);
	mysql_query("ALTER TABLE inschrijvingMB2 ADD COLUMN fbaptised TEXT DEFAULT NULL AFTER religionfather", $userlink);
	mysql_query("ALTER TABLE inschrijvingMB2 ADD COLUMN mbaptised TEXT DEFAULT NULL AFTER religionmother", $userlink);
	mysql_query("ALTER TABLE inschrijvingMB2 ADD COLUMN brusonschool TEXT DEFAULT NULL AFTER remarks", $userlink);
	mysql_query("ALTER TABLE inschrijvingMB2 ADD COLUMN workSPCOA TEXT DEFAULT NULL AFTER brusonschool", $userlink);
    
  // Link with stylesheet
  echo ('<HTML><LINK rel="stylesheet" type="text/css" href="style_InschrijfMB.css" title="style1">');
  // At some place we need a list of months
  $montxt = array(1=>'januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december');
	
	if(isset($_POST['rid']))
	{ // existing record, get type and year to set
		$typeyrqr = inputclassbase::load_query("SELECT schooltype,year FROM inschrijvingMB2 WHERE rid=". $_POST['rid']);
		if(isset($typeyrqr['schooltype'][0]) && $typeyrqr['schooltype'][0] != "")
			$_GET[$typeyrqr['schooltype'][0]] = $typeyrqr['year'][0];		
	}
  
  // Get the year
	if(isset($_GET['basis']))
	{
		$schoolyear=$_GET['basis'];
		$schooltype="basis";
	}
	else if(isset($_GET['kleuter']))
	{
		$schoolyear=$_GET['kleuter'];
		$schooltype="kleuter";
	}
	else
	{
    $schoolyear = date("Y"). "-" .(date("Y")+1);
		$schooltype="basis";
	}
	$sjb = substr($schoolyear,0,4);
	if(isset($_GET['kleuter']))
	{
		echo("<P class=topheading><IMG width=40 src=mklogo.png>Kleuter Inschrijving Schooljaar ". $schoolyear. "<IMG width=40 src=schoollogo.png></p>");
		echo("<p style='clear: both;'>Welkom bij de inschrijvingen voor schooljaar ". $schoolyear. " op onze school. U kunt uw kleuter inschrijven wanneer hij/zij  in de periode vanaf 1 oktober ". ($sjb-6). " t/m 30 sept ". ($sjb - 4). " geboren is. Kinderen met een Protestants doopbewijs die geboren zijn in de periode vanaf 1 oktober t/m 31 december ". ($sjb-4). ", kunnen zich ook alvast registreren</p>");
		echo("<p>Op 31 oktober of 1 november  kunt u tussen  18:30 en 20:00 op de Mon Plaisir school langs komen met onderstaande documenten. Dan vindt de definitieve  registratie plaats. <B>Let op: Kinderen die alleen maar digitaal zijn geregistreerd en geen documenten hebben ingeleverd, komen niet in aanmerking voor selectie procedure om aangenomen te worden.</b></p>");
	}
	else
	{
		echo("<P class=topheading>AANMELDINGSFORMULIER<BR>Mon Plaisir Basisschool<BR>". $schoolyear. "</P>");
		echo("<IMG class=toplogo src=mklogo.png>");
		echo("<IMG class=toplogo src=schoollogo.png>");
	}
  $rid=0;
  if(isset($_GET['regcode']))
  { // Convert registration code to rid (record in registation table)
    $rid=base64_decode(urldecode($_GET['regcode']));
  }
  if(isset($_POST['rid']))
    $rid = $_POST['rid']; // Set by school administration
  else if(isset($_POST['userid']))
  {
    if($_POST['userid'] != "" && $_POST['userpw'] != "")
	{ // Check login for this student
	  $logindata = inputclassbase::load_query("SELECT sid FROM student WHERE altsid=\"". $_POST['userid']. "\" AND ppassword=\"". $_POST['userpw']. "\"");
	  if(isset($logindata['sid']))
	  { // student ID is retrieved from database based on login data, now check if a record already exists or create one
	    $ridqr = inputclassbase::load_query("SELECT rid,paid FROM inschrijvingMB2 WHERE sid=". $logindata['sid'][0]. " AND year='". $schoolyear. "'");
			if(isset($ridqr['rid']))
			{  // Known student and registration filed already
				if($ridqr['paid'][0] == 1)
				{  // Record has already been processed, student can not change data
					echo("<P class=errormsg>De aanmelding is al verwerkt, neem contact op met de adminsitratie om wijzigigen aan te brengen</p>");
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
						// Now the values to put in the inschrijvingMB2 records
						$bd = "'". $splitbd[0]. "'";
						$bm = "'". $splitbd[1]. "'";
						$by = "'". $splitbd[2]. "'";
					}			
				}
				$insrecq = "INSERT INTO inschrijvingMB2 (year,sid,firstname,lastname,gebdag,gebmaand,gebjaar,lvsid,lvspassword)";
				$insrecq .= " SELECT '". $schoolyear. "',student.sid,firstname,lastname,". $bd. ",". $bm. ",". $by. ",";
				$insrecq .= "altsid,ppassword FROM student";
				$insrecq .= " WHERE sid=". $logindata['sid'][0];
				mysql_query($insrecq,$userlink);
				echo(mysql_error($userlink));
				$rid = mysql_insert_id($userlink);
				// Now add the details
				foreach($reg2stud AS $ikey => $dkey)
				{
					$ddata = inputclassbase::load_query("SELECT data FROM `". $dkey. "` WHERE sid=". $logindata['sid'][0]);
					if(isset($ddata['data']) && $ddata['data'][0] != "")
					{
						mysql_query("UPDATE inschrijvingMB2 SET ". $ikey. "='". $ddata['data'][0]. "' WHERE rid=". $rid,$userlink);
						echo(mysql_error($userlink));
					}
				}
			}
	  }
	  else
	    echo("<P class=errormsg>Login gegevens zijn niet correct, probeer opnieuw!</p>");
	}
	else
	  echo("<P class=errormsg>Identificatiecode of wachtwoord niet ingevuld!</p>");
  }  

  if($rid == 0 && isset($_GET['Herinschrijving']))
  { // Enable login if not linked with an existing record yet (fully new students get record 0 to create new one)
    echo("<FIELDSET style='background-color:#FFCCCC'><LEGEND>Geregistreerde leerling? / Mucha registra caba?</legend>");
    echo("<P class=loginheading>Geef dan het identificatienummer en wachtwoord hieronder zodat we de bestaande gegevens al kunnen tonen.</p>");
    echo("<P class=loginheading>Si ta asina duna number di identificacion y wachtwoord pa haya datonan disponibel caba.</p>");
    echo("<form action=". $_SERVER['REQUEST_URI']. " METHOD=POST>");
    echo("<LABEL class=shortlabel>Identificatienummer: </LABEL><INPUT TYPE=TEXT SIZE=10 NAME=userid><BR>");
    echo("<LABEL class=shortlabel>Wachtwoord: </LABEL><INPUT TYPE=PASSWORD SIZE=10 NAME=userpw><BR>");
    echo("<INPUT TYPE=SUBMIT VALUE='Gegevens ophalen /Busca datonan'></FORM>");
		echo("Controleer de informatie en wijzig / vul aan / Controla e informacion y yena loke ta falta.");
		echo("</fieldset>");
  }
  if(isset($_POST['rid']))
		echo("<a href=form_Inschrijven_MB.php>Terug naar het zoekscherm</a>");

  echo("<DIV class=fieldarea>");
  
  echo("<FIELDSET style='background-color:#FFFFCC'><LEGEND>Personalia</legend>");
  echo("<LABEL>Achternaam leerling / Fam alumno:</LABEL> ");
  $lnlnfld = new inputclass_textfield("slname",40,NULL,"lastname","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnlnfld->set_extrafield("year",$schoolyear);
  $lnlnfld->set_extrafield("schooltype",$schooltype);
  $lnlnfld->echo_html();
  echo("<BR><LABEL>Voorna(a)m(en) (voluit) / Nomber(nan) completo:</LABEL> ");
  $lnfnfld = new inputclass_textfield("sfname",40,NULL,"firstname","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfnfld->echo_html();
  echo("<BR><LABEL>Geslacht / Sexo:</LABEL> ");
  $lnfld = new inputclass_listfield("sgender","SELECT '' AS id,'' AS tekst UNION SELECT 'M','Man' UNION SELECT 'V','Vrouw'",NULL,"geslacht","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Geboortedatum / Fecha di nacemento:</LABEL> ");
  $bdayq = "SELECT '' AS id, '' AS tekst";
  for($d=1;$d<=31;$d++)
    $bdayq .= " UNION SELECT '". ($d < 10 ? "0" : ""). $d. "','". ($d < 10 ? "0" : ""). $d. "'";
  $lnfld = new inputclass_listfield("sbday",$bdayq,NULL,"gebdag","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo(" ");
  $bmonq = "SELECT '' AS id, '' AS tekst";
  for($d=1;$d<=12;$d++)
    $bmonq .= " UNION SELECT '". $montxt[$d]. "','". $montxt[$d]. "'";
  $lnfld = new inputclass_listfield("sbmon",$bmonq,NULL,"gebmaand","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo(" ");
  $byrq = "SELECT '' AS id, '' AS tekst";
	if(isset($_GET['kleuter']))
	{
		for($d=4;$d<=6;$d++)
			$byrq .= " UNION SELECT '". ($sjb-$d). "','". ($sjb-$d). "'";
	}
	else
	{
		for($d=6;$d<=14;$d++)
			$byrq .= " UNION SELECT '". (date("Y")-$d). "','". (date("Y")-$d). "'";
	}
  $lnfld = new inputclass_listfield("sbyr",$byrq,NULL,"gebjaar","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Censo ID:</LABEL> ");
  $lnfld = new inputclass_textfield("scensoid",8,NULL,"censoid","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Protestant Christelijk:</LABEL> ");
  $lnfld = new inputclass_listfield("sprotestant","SELECT '' AS id, '' AS tekst UNION SELECT 'n' AS id, 'Nee/no' AS tekst UNION SELECT 'j','Ja/si'",NULL,"protestant","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Religie / Religion:</LABEL> ");
  $lnfld = new inputclass_textfield("sreligion",20,NULL,"religionstudent","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>A.Z.V. Relatienummer / Number di A.Z.V.:</LABEL> ");
  $lnfld = new inputclass_textfield("sazv",40,NULL,"medicalinsurancenumber","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Geboorteland / Pais di nacemento:</LABEL> ");
  $lnfld = new inputclass_listfield("sbplace","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_country ORDER BY tekst",NULL,"gebland","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Nationaliteit / Nacionalidad:</LABEL> ");
  $lnfld = new inputclass_listfield("snationality","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_nationality ORDER BY tekst",NULL,"nationality","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Meest gesproken taal thuis / Idioma mas papia na cas:</LABEL> ");
  $lnfld = new inputclass_listfield("shlanguage","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_language ORDER BY tekst",NULL,"homelanguage","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Andere talen di thuis gesproken worden / <BR>Otro idiomanan papia na cas:</LABEL> ");
  $lnfld = new inputclass_textfield("sotherhomelangs",40,NULL,"otherhomelanguages","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Adres:</LABEL> ");
  $lnfld = new inputclass_textfield("sadres",40,NULL,"adres","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>District / Districto:</LABEL> ");
  $lnfld = new inputclass_listfield("sdistrict","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_district ORDER BY tekst",NULL,"district","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Op Aruba woonachtig sinds / Biba na Aruba desde:</LABEL> ");
  $lnfld = new inputclass_textfield("sonarubasince",40,NULL,"onarubasince","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("</fieldset>");
  echo("<FIELDSET style='background-color:#CCFFFF'><LEGEND>Contactgegevens / Informacion di contacto</legend>");
	$liveatsel = "SELECT '' AS id, '' AS tekst UNION SELECT 'Beide ouders', 'Ouders / Mayornan' UNION SELECT 'Vader','Vader / Tata' UNION SELECT 'Moeder','Moeder / Mama' UNION SELECT 'Een voogd', 'Een voogd / Un voogd' UNION SELECT 'Voogden','Voogden' UNION SELECT 'Anders','Anders / Otro'";
  echo("<LABEL>Ouderlijk gezag ligt bij / Poder ta cerca:</LABEL> ");
  $lnfld = new inputclass_listfield("sresponsable",$liveatsel,NULL,"responsableperson","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo(" Anders / Otro: ");
  $lnfld = new inputclass_textfield("srespother",40,NULL,"responsablealternative","inschrijvingMB2",$rid,"rid","visibility: hidden;","inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Telefoon bij noodgeval / Telefon di emergencia:</LABEL> ");
  $lnfld = new inputclass_textfield("emergencyphone",40,NULL,"emergencyphone","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Leerling is woonachtig bij / Alumno ta biba cerca:</LABEL> ");
  $lnfld = new inputclass_listfield("sliveat",$liveatsel,NULL,"liveat","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo(" Anders / Otro: ");
  $lnfld = new inputclass_textfield("sliveatother",40,NULL,"liveatother","inschrijvingMB2",$rid,"rid","visibility: hidden;","inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>E-mail adres:</LABEL> ");
  $lnfld = new inputclass_textfield("semail",40,NULL,"email","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR>We sturen een link van dit aanmeldingsformulier naar uw e-mailadres / Na e email adres duna, nos ta manda un e-mail cu un link na e &quot;aanmeldingsformulier&quot; aki.<BR>");
	echo("Als u geen e-mail ontvangt, controleer dan de Spam/Junk mail en voeg noreply@myschoolresults.com toe aan de vertrouwde e-mail adressen.<BR>");
	echo("Si no ta recibi e e-mail, controla e Spam/Junk mail y agrega noreply@myschoolresults.com na e e-mail adresnan confia.");
  echo("</fieldset>");
  echo("<FIELDSET style='background-color:#CCEEFF'><LEGEND>Personalia Moeder / Voogd / Mama</legend>");
  echo("<LABEL>Achternaam moeder of voogd / Fam di mama:</LABEL> ");
  $lnfld = new inputclass_textfield("mfam",40,NULL,"lastnamemother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Voorna(a)m(en) (voluit) / Nomber(nan) completo:</LABEL> ");
  $lnfld = new inputclass_textfield("mfname",40,NULL,"firstnamesmother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Adres:</LABEL> ");
  $lnfld = new inputclass_textfield("maddress",40,NULL,"addressmother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Nationaliteit / Nacionalidad:</LABEL> ");
  $lnfld = new inputclass_listfield("mnationality","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_nationality ORDER BY tekst",NULL,"nationalitymother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Geboortedatum / Fecha di nacemento:</LABEL> ");
  $lnfld = new inputclass_textfield("mbirthdate",20,NULL,"birthdatemother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Religie / Religion:</LABEL> ");
  $lnfld = new inputclass_listfield("mreligion","SELECT '' AS id, '' AS tekst UNION SELECT * FROM arubacom.c_religion",NULL,"religionmother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Telefoon thuis / Telefon na cas:</LABEL> ");
  $lnfld = new inputclass_textfield("mhomephone",40,NULL,"phonehomemother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Mobiel / Celular:</LABEL> ");
  $lnfld = new inputclass_textfield("mmobilephone",40,NULL,"phonemobilemother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>E-mail:</LABEL> ");
  $lnfld = new inputclass_textfield("memail",40,NULL,"emailmother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Beroep / Ocupacion:</LABEL> ");
  $lnfld = new inputclass_textfield("mprofession",40,NULL,"professionmother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Werkt bij / Ta traha na:</LABEL> ");
  $lnfld = new inputclass_textfield("memployer",40,NULL,"employermother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Telefoon op het werk / Telefon na trabou:</LABEL> ");
  $lnfld = new inputclass_textfield("mworkphone",40,NULL,"phoneworkmother","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("</fieldset>");
  echo("<FIELDSET style='background-color:#CCDDFF'><LEGEND>Personalia Vader / Voogd 2 / Tata</legend>");
  echo("<LABEL>Achternaam vader of voogd 2 / Fam di tata:</LABEL> ");
  $lnfld = new inputclass_textfield("ffam",40,NULL,"lastnamefather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Voorna(a)m(en) (voluit) / Nomber(nan) completo:</LABEL> ");
  $lnfld = new inputclass_textfield("ffname",40,NULL,"firstnamesfather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Adres:</LABEL> ");
  $lnfld = new inputclass_textfield("faddress",40,NULL,"addressfather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Nationaliteit / Nacionalidad:</LABEL> ");
  $lnfld = new inputclass_listfield("fnationality","SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM arubacom.c_nationality ORDER BY tekst",NULL,"nationalityfather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Geboortedatum / Fecha di nacemento:</LABEL> ");
  $lnfld = new inputclass_textfield("fbirthdate",20,NULL,"birthdatefather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Religie / Religion:</LABEL> ");
  $lnfld = new inputclass_listfield("freligion","SELECT '' AS id, '' AS tekst UNION SELECT * FROM arubacom.c_religion",NULL,"religionfather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Telefoon thuis / Telefon na cas:</LABEL> ");
  $lnfld = new inputclass_textfield("fhomephone",40,NULL,"phonehomefather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Mobiel / Celular:</LABEL> ");
  $lnfld = new inputclass_textfield("fmobilephone",40,NULL,"phonemobilefather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>E-mail:</LABEL> ");
  $lnfld = new inputclass_textfield("femail",40,NULL,"emailfather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Beroep / Ocupacion:</LABEL> ");
  $lnfld = new inputclass_textfield("fprofession",40,NULL,"professionfather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Werkt bij / Ta traha na:</LABEL> ");
  $lnfld = new inputclass_textfield("femployer",40,NULL,"employerfather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Telefoon op het werk / Telefon na trabou:</LABEL> ");
  $lnfld = new inputclass_textfield("fworkphone",40,NULL,"phoneworkfather","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("</fieldset>");
  echo("<FIELDSET style='background-color:#FFFFCC'><LEGEND>Informatie over het gezin / Informacion di famia</legend>");
  echo("<LABEL>Burgerlijke staat ouders / Estado civil mayornan:</LABEL> ");
  $lnfld = new inputclass_listfield("famcivstate","SELECT '' AS id, '' AS tekst UNION SELECT * FROM arubacom.c_civilstate",NULL,"civilstateparents","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Huisarts / Docter di cas:</LABEL> ");
  $lnfld = new inputclass_textfield("fammedic",40,NULL,"familydoctor","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Kind woont samen met / E Mucha ta biba hunto cu:</LABEL> ");
  $lnfld = new inputclass_textfield("famconst",40,NULL,"familyconstellation","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  /*echo("<BR><LABEL>Botica:</LABEL> ");
  $lnfld = new inputclass_textfield("famfharmacy",40,NULL,"pharmacy","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html(); */
  echo("</fieldset>");
  echo("<FIELDSET style='background-color:#FFCCFF'><LEGEND>Bezochte scholen van de leerling / Scol cu alumno a bishita</legend>");
  echo("<BR><LABEL>Peuterschool / Scol lushi:</LABEL> ");
  $lnfld = new inputclass_textfield("speuter",40,NULL,"peuterschool","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Kleuterschool / Scol proparatorio</LABEL> ");
  $lnfld = new inputclass_textfield("skleuter",40,NULL,"kleuterschool","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Basisschool / Scol basico:</LABEL> ");
  $lnfld = new inputclass_listfield("sbaseschool","SELECT '' AS id, '' AS tekst UNION SELECT * FROM arubacom.c_BOschool",NULL,"primaryschool","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo(" Anders / Otro: ");
  $lnfld = new inputclass_textfield("sotherbase",40,NULL,"primaryother","inschrijvingMB2",$rid,"rid","visibility: hidden;","inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Gedoubleerd op basisschool klas / Keda sinta den klas:</LABEL> ");
  $lnfld = new inputclass_textfield("sdoubled",8,NULL,"doublingclasses","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("</fieldset>");
  echo("<FIELDSET style='background-color:#CCFFFF'><LEGEND>Bezochte scholen broers en zussen / Scol di ruman(nan)</legend>");
  echo("<BR><LABEL>Naam broer of zus (1) / Nomber di ruman (1):</LABEL> ");
  $lnfld = new inputclass_textfield("b1name",40,NULL,"brosname1","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>School broer of zus (1) / Scol di e ruman (1):</LABEL> ");
  $lnfld = new inputclass_textfield("b1school",40,NULL,"brosschool1","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Klas:</LABEL> ");
  $lnfld = new inputclass_textfield("b1class",8,NULL,"brosclass1","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Naam broer of zus (2) / Nomber di ruman (2):</LABEL> ");
  $lnfld = new inputclass_textfield("b2name",40,NULL,"brosname2","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>School broer of zus (2) / Scol di e ruman (2):</LABEL> ");
  $lnfld = new inputclass_textfield("b2school",40,NULL,"brosschool2","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Klas:</LABEL> ");
  $lnfld = new inputclass_textfield("b2class",8,NULL,"brosclass2","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Naam broer of zus (3) / Nomber di ruman (3):</LABEL> ");
  $lnfld = new inputclass_textfield("b3name",40,NULL,"brosname3","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>School broer of zus (3) / Scol di e ruman (3):</LABEL> ");
  $lnfld = new inputclass_textfield("b3school",40,NULL,"brosschool3","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Klas:</LABEL> ");
  $lnfld = new inputclass_textfield("b3class",8,NULL,"brosclass3","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Naam broer of zus (4) / Nomber di ruman (4):</LABEL> ");
  $lnfld = new inputclass_textfield("b4name",40,NULL,"brosname4","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>School broer of zus (4) / Scol di e ruman (4):</LABEL> ");
  $lnfld = new inputclass_textfield("b4school",40,NULL,"brosschool4","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Klas:</LABEL> ");
  $lnfld = new inputclass_textfield("b4class",8,NULL,"brosclass4","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("</fieldset>");
  echo("<FIELDSET style='background-color:#FFBBBB'><LEGEND>Ander informatie / Otro informacion</legend>");
	if(!isset($_GET['Herinschrijving']))
	{
		echo("<BR><LABEL>Waarom kiest U voor onze school? / Dicon a scohe nos scol?:</LABEL> ");
		$lnfld = new inputclass_textfield("motive",100,NULL,"motivation","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
		$lnfld->echo_html();
	}
  echo("<BR><LABEL>Is er een medisch probleem van belang voor de school / Tin problema medico cu e scol mester sa:</LABEL> ");
  $lnfld = new inputclass_listfield("smedicalyn","SELECT '' AS id, '' AS tekst UNION SELECT 'j', 'Ja / Si' UNION SELECT 'n','Nee / No'",NULL,"medicalprobsyn","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo("<DIV ID=medicaldetails ". ($lnfld->__toString() != "Ja / Si" ? "style='visibility:hidden'" : ""). ">");
  echo("<LABEL>Medische problemen / Problemanan medico:</LABEL> ");
  $lnfld = new inputclass_textfield("smedicalprobs",100,NULL,"medicalprobs","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo("</DIV>");

  echo("<LABEL>Is er een ontwikkelingsprobleem van belang voor de school / Tin problema den desaroyo cu e scol mester sa:</LABEL> ");
  $lnfld = new inputclass_listfield("sdevprobyn","SELECT '' AS id, '' AS tekst UNION SELECT 'j', 'Ja / Si' UNION SELECT 'n','Nee / No'",NULL,"developprobsyn","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo("<DIV ID=devdetails ". ($lnfld->__toString() != "Ja / Si" ? "style='visibility:hidden'" : ""). ">");
  echo("<LABEL>Ontwikkelingsproblemen / Problemanan den desaroyo:</LABEL> ");
  $lnfld = new inputclass_textfield("sdevprobs",100,NULL,"developprobs","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();

  echo("<LABEL>Is het kind hiervoor getest / A test e mucha pa cu esaki:</LABEL> ");
  $lnfld = new inputclass_listfield("stestedyn","SELECT '' AS id, '' AS tekst UNION SELECT 'j', 'Ja / Si' UNION SELECT 'n','Nee / No'",NULL,"testedyn","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo("<DIV ID=testdetails style='visibility:hidden'>");
	echo("Gelieve een kopie van het verslag in te leveren / Por fabor entrega un copia di e raportahe.<BR>");
  echo("<LABEL>In welk jaar is het kind getest / Den ki aña a test e mucha:</LABEL> ");
  $lnfld = new inputclass_textfield("stestedwhen",10,NULL,"testedwhen","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
  echo("<BR><LABEL>Door wie is het kind getest / Ken a test e mucha:</LABEL> ");
  $lnfld = new inputclass_textfield("stestedby",40,NULL,"testedby","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo("</DIV>");

  echo("<LABEL>Wordt het kind (nog) begeleid / E mucha (ainda) ta wordo guia:</LABEL> ");
  $lnfld = new inputclass_listfield("sguidedyn","SELECT '' AS id, '' AS tekst UNION SELECT 'j', 'Ja / Si' UNION SELECT 'n','Nee / No'",NULL,"guidedyn","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo("<DIV ID=guidedetails style='visibility:hidden'>");
  echo("<LABEL>Door wie wordt het kind begeleid / Ken ta guia e mucha:</LABEL> ");
  $lnfld = new inputclass_textfield("sguidedby",10,NULL,"guidedby","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();
	echo("</DIV>");

	echo("</DIV>");
  echo("<LABEL>Indien hiertoe aanleiding is zal, in overleg met de ouders, uw kind eventueel getest moeten worden. Geeft U hiervoor toestemming? :<BR>Si tin motibo mester, den comunicacion cu e mayor(nan), test e mucha. Ta di acuerdo cu esaki? :</LABEL> ");
  $lnfld = new inputclass_listfield("sallowtests","SELECT '' AS id, '' AS tekst UNION SELECT 'j', 'Ja / Si' UNION SELECT 'n','Nee / No'",NULL,"allowtests","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
  $lnfld->echo_html();

  echo("</fieldset>");
	
	if(isset($_GET['kleuter']))
	{
?>
<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0
 style='border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
 mso-yfti-tbllook:1184;mso-padding-alt:0in 5.4pt 0in 5.4pt'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span class=SpellE><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Lijst</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'> van <span
  class=SpellE>vereisten</span> op 31 <span class=SpellE>oktober</span> of 1 <span
  class=SpellE>november</span>.<o:p></o:p></span></p>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span class=SpellE><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Aanvrager</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif"'>MPS<o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:1'>
  <td width=798 colspan=3 valign=top style='width:6.65in;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#F2F2F2;mso-background-themecolor:background1;mso-background-themeshade:
  242;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Ten
  <span class=SpellE>behoeve</span> van <span class=SpellE>indiening</span><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:2'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
  mso-add-space:auto;text-indent:-.25in;line-height:normal;mso-list:l1 level1 lfo1'><![if !supportLists]><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif";mso-fareast-font-family:
  Verdana;mso-bidi-font-family:Verdana'><span style='mso-list:Ignore'>1.<span
  style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp; </span></span></span><![endif]><span
  class=SpellE><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Een</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'> print out van <span
  class=SpellE>deze</span> <span class=SpellE>digitale</span> <span
  class=SpellE>inschrijving</span><span style='mso-spacerun:yes'>  </span>met <span
  class=SpellE>ingevulde</span> <span class=SpellE>lijst</span> van <span
  class=SpellE>vereisten</span>.<o:p></o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:3'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
  mso-add-space:auto;text-indent:-.25in;line-height:normal;mso-list:l1 level1 lfo1'><![if !supportLists]><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif";mso-fareast-font-family:
  Verdana;mso-bidi-font-family:Verdana'><span style='mso-list:Ignore'>2.<span
  style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp; </span></span></span><![endif]><span
  class=SpellE><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Een</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'> <span
  class=SpellE>kopie</span> van <span class=SpellE>een</span> <u>Protestants</u>
  <span class=SpellE>doopbewijs</span> van het kind, <span class=SpellE>indien</span>
  in het <span class=SpellE>bezit</span>.<o:p></o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:4'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
  mso-add-space:auto;text-indent:-.25in;line-height:normal;mso-list:l1 level1 lfo1'><![if !supportLists]><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif";mso-fareast-font-family:
  Verdana;mso-bidi-font-family:Verdana'><span style='mso-list:Ignore'>3.<span
  style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp; </span></span></span><![endif]><span
  class=SpellE><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Een</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'> <span
  class=SpellE>kopie</span> van <span class=SpellE>een</span> <u>Protestants</u>
  <span class=SpellE>doopbewijs</span> van <span class=SpellE>ouder</span>(s), <span
  class=SpellE>indien</span> in het <span class=SpellE>bezit</span>.<o:p></o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:5'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoListParagraphCxSpFirst style='margin-bottom:0in;margin-bottom:
  .0001pt;mso-add-space:auto;text-indent:-.25in;line-height:normal;mso-list:
  l1 level1 lfo1'><![if !supportLists]><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-fareast-font-family:Verdana;
  mso-bidi-font-family:Verdana'><span style='mso-list:Ignore'>4.<span
  style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp; </span></span></span><![endif]><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Het <span
  class=SpellE>originele</span> <span class=SpellE>uittreksel</span> van het <span
  class=SpellE>bevolkingsregister</span> <o:p></o:p></span></p>
  <p class=MsoListParagraphCxSpLast style='margin-bottom:0in;margin-bottom:
  .0001pt;mso-add-space:auto;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif"'>(<span class=SpellE>Awg</span> 5 florin),
  van het kind.<o:p></o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:6'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
  mso-add-space:auto;text-indent:-.25in;line-height:normal;mso-list:l1 level1 lfo1'><![if !supportLists]><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif";mso-fareast-font-family:
  Verdana;mso-bidi-font-family:Verdana'><span style='mso-list:Ignore'>5.<span
  style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp; </span></span></span><![endif]><span
  class=SpellE><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Een</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'> <span
  class=SpellE>kopie</span> van de AZV <span class=SpellE>kaart</span>, van het
  kind.<o:p></o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:7'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
  mso-add-space:auto;text-indent:-.25in;line-height:normal;mso-list:l1 level1 lfo1'><![if !supportLists]><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif";mso-fareast-font-family:
  Verdana;mso-bidi-font-family:Verdana'><span style='mso-list:Ignore'>6.<span
  style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp; </span></span></span><![endif]><span
  class=SpellE><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Een</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'> <span
  class=SpellE>kopie</span> van het <span class=SpellE>paspoort</span> , van
  het kind.<o:p></o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:8;height:24.7pt'>
  <td width=798 colspan=3 valign=top style='width:6.65in;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#F2F2F2;mso-background-themecolor:background1;mso-background-themeshade:
  242;padding:0in 5.4pt 0in 5.4pt;height:24.7pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><o:p>&nbsp;</o:p></p>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span class=SpellE>Niet</span> <span class=SpellE>invullen</span>. <span
  class=SpellE>Alleen</span> <span class=SpellE>voor</span> MPS<span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:9'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
  mso-add-space:auto;text-indent:-.25in;line-height:normal;mso-list:l1 level1 lfo1'><![if !supportLists]><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif";mso-fareast-font-family:
  Verdana;mso-bidi-font-family:Verdana'><span style='mso-list:Ignore'>7.<span
  style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp; </span></span></span><![endif]><span
  class=SpellE><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Ondertekening</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'> <span
  class=SpellE>voor</span> <span class=SpellE>ontvangst</span> <span
  class=SpellE>documenten</span>.<o:p></o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:10'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
  mso-add-space:auto;text-indent:-.25in;line-height:normal;mso-list:l1 level1 lfo1'><![if !supportLists]><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif";mso-fareast-font-family:
  Verdana;mso-bidi-font-family:Verdana'><span style='mso-list:Ignore'>8.<span
  style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp; </span></span></span><![endif]><span
  class=SpellE><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Ondertekening</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'> <span
  class=SpellE>bereidshiedsverklaring</span>/ <span class=SpellE>verklaring</span>
  <span class=SpellE>geen</span> <span class=SpellE>bezwaar</span>.<o:p></o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:11'>
  <td width=579 valign=top style='width:347.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  <p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
  mso-add-space:auto;text-indent:-.25in;line-height:normal;mso-list:l1 level1 lfo1'><![if !supportLists]><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif";mso-fareast-font-family:
  Verdana;mso-bidi-font-family:Verdana'><span style='mso-list:Ignore'>9.<span
  style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp; </span></span></span><![endif]><span
  class=SpellE><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'>Ontvangst</span></span><span
  style='font-size:10.0pt;font-family:"Verdana","sans-serif"'> <span
  class=SpellE>informatiebrief</span> <span class=SpellE>omtrent</span> <span
  class=SpellE>vervolg</span> van de procedure.<o:p></o:p></span></p>
  </td>
  <td width=120 valign=top style='width:1.0in;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p>&nbsp;</o:p></span></p>
  </td>
  <td width=99 valign=top style='width:59.4pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal align=center style='margin-bottom:0in;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><span style='font-size:10.0pt;
  font-family:"Verdana","sans-serif";mso-bidi-font-family:Calibri;mso-bidi-theme-font:
  minor-latin'>&#9633;</span><span style='font-size:10.0pt;font-family:"Verdana","sans-serif"'><o:p></o:p></span></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:12'>
  <td width=798 colspan=3 valign=top style='width:6.65in;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#F2F2F2;mso-background-themecolor:background1;mso-background-themeshade:
  242;padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><o:p>&nbsp;</o:p></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:13;mso-yfti-lastrow:yes'>
  <td width=798 colspan=3 valign=top style='width:6.65in;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0in 5.4pt 0in 5.4pt'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><o:p>&nbsp;</o:p></p>
  <div style='mso-element:para-border-div;border-top:solid windowtext 1.5pt;
  border-left:none;border-bottom:solid windowtext 1.5pt;border-right:none;
  padding:1.0pt 0in 1.0pt 0in'>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal;border:none;mso-border-top-alt:solid windowtext 1.5pt;mso-border-bottom-alt:
  solid windowtext 1.5pt;padding:0in;mso-padding-alt:1.0pt 0in 1.0pt 0in'><o:p>&nbsp;</o:p></p>
  </div>
  <p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
  normal'><o:p>&nbsp;</o:p></p>
  </td>
 </tr>
</table>
<?
	}
	
	
  if(isset($_POST['rid']))
  {
    echo("<FIELDSET style='background-color: #FFFFFF'><LEGEND>Inschrijving Mon Plaisir Basis</LEGEND>");

		echo("<BR><LABEL>Leerling Protestants gedoopt / Alumno bautisa protestant:</LABEL> ");
		$lnfld = new inputclass_listfield("sbaptised","SELECT '' AS id,'' AS tekst UNION SELECT 'ja' AS id, 'Ja/Si' AS tekst UNION SELECT 'nee' AS id, 'Nee/No' AS tekst",NULL,"baptised","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
		$lnfld->echo_html();

		echo("<BR><LABEL>Vader Protestants gedoopt / Tata bautisa protestant:</LABEL> ");
		$lnfld = new inputclass_listfield("fbaptised","SELECT '' AS id,'' AS tekst UNION SELECT 'ja' AS id, 'Ja/Si' AS tekst UNION SELECT 'nee' AS id, 'Nee/No' AS tekst",NULL,"fbaptised","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
		$lnfld->echo_html();

		echo("<BR><LABEL>Moeder Protestants gedoopt / Mama bautisa protestant:</LABEL> ");
		$lnfld = new inputclass_listfield("mbaptised","SELECT '' AS id,'' AS tekst UNION SELECT 'ja' AS id, 'Ja/Si' AS tekst UNION SELECT 'nee' AS id, 'Nee/No' AS tekst",NULL,"mbaptised","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
		$lnfld->echo_html();

		echo("<BR><LABEL>Broer of zus op Mon Plaisirschool / Ruman na Mon Plaisirschool:</LABEL> ");
		$lnfld = new inputclass_listfield("brusonschool","SELECT '' AS id,'' AS tekst UNION SELECT 'ja' AS id, 'Ja/Si' AS tekst UNION SELECT 'nee' AS id, 'Nee/No' AS tekst",NULL,"brusonschool","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
		$lnfld->echo_html();

		echo("<BR><LABEL>Ouder werkt bij SPCOA / Mayor ta traha na SPCOA:</LABEL> ");
		$lnfld = new inputclass_listfield("workSPCOA","SELECT '' AS id,'' AS tekst UNION SELECT 'ja' AS id, 'Ja/Si' AS tekst UNION SELECT 'nee' AS id, 'Nee/No' AS tekst",NULL,"workSPCOA","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
		$lnfld->echo_html();

    $sidfld = new inputclass_checkbox("sidrq",0,NULL,"sid","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
    if($sidfld->__toString() == '' || $sidfld->__toString() == 0 || 1==1)
		{  
			// Allow coupling to an existing student
			$smatchqr = "SELECT '' AS id, '' AS tekst";
			$matchersq = "SELECT sid,firstname,lastname,groupname FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE firstname SOUNDS LIKE '". $lnfnfld->__toString(). "' OR lastname SOUNDS LIKE '". $lnlnfld->__toString(). "' ORDER BY lastname,firstname,groupname";
			$matchersqr = inputclassbase::load_query($matchersq);
			if(isset($matchersqr['sid']))
			{
				foreach($matchersqr['sid'] AS $msix => $msid)
				{
					$smatchqr .= " UNION SELECT ". $msid. ",'". $matchersqr['lastname'][$msix]. ", ". $matchersqr['firstname'][$msix]. " (". $matchersqr['groupname'][$msix]. ")'"; 
				}
				echo("<BR><LABEL>Komt overeen met:</LABEL> ");
				$mfld = new inputclass_listfield("smatch",$smatchqr,NULL,"sid","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
				$mfld->echo_html();
			}
			// Allow setting new ID and password
			echo("<BR><LABEL>Identificatienummer student:</LABEL> ");
			$lnfld = new inputclass_textfield("ssidnummer",10,NULL,"lvsid","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
			$lnfld->echo_html();
			echo(" (automatisch toegekend: ". (substr(date("Y"),2). str_pad($rid,4,"0",STR_PAD_LEFT)). ")");
			echo("<BR><LABEL>Wachtwoord student:</LABEL> ");
			$lnfld = new inputclass_textfield("sspasw",10,NULL,"lvspassword","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
			$lnfld->echo_html();
			echo(" (automatisch toegekend: ". str_pad(base_convert(($rid * 1313) % 32000,10,16),4,"0",STR_PAD_LEFT) .")");
			echo("<BR>");
		}
    echo("<LABEL>Bewijsstukken ingeleverd:</LABEL> ");
    $lnfld = new inputclass_checkbox("bewijs",0,NULL,"proof","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
    $lnfld->echo_html();
    echo("<BR><LABEL>Geaccepteerd:</LABEL> ");
    $lnfld = new inputclass_checkbox("geaccepteerd",0,NULL,"accepted","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
    $lnfld->echo_html();
    echo("<BR><LABEL>Betaald:</LABEL> ");
    $lnfld = new inputclass_checkbox("betaald",0,NULL,"paid","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
    $lnfld->echo_html();
    echo("<BR><LABEL>Opmerkingen:</LABEL> ");
    $lnfld = new inputclass_textarea("opmerkingen","60,*",NULL,"remarks","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
    $lnfld->echo_html();
    echo("<BR><LABEL>Plaatsen in leerjaar / lokatie:</LABEL> ");
    $lnfld = new inputclass_listfield("siljaar","SELECT '' AS id, '' AS tekst UNION SELECT gid,groupname FROM sgroup ORDER BY tekst",NULL,"registeredinclass","inschrijvingMB2",$rid,"rid",NULL,"inschrijfMBhandler.php");
    $lnfld->echo_html();
    echo("<DIV ID=placedgroup NAME=placedgroup>&nbsp;</DIV>");
		echo("<a href=form_Inschrijven_MB.php>Terug naar het zoekscherm</a>");
		echo("</fieldset>");
  }
  else
  {
		echo("<H2>De invuller heeft dit electronisch formulier naar waarheid ingevuld.<BR>E yenado di e formulario aki a yene na verdad.</H2>");
    echo("<BR><INPUT TYPE=SUBMIT VALUE='AFDRUKKEN/PRINT' onClick='window.print()'>U dient dit formulier, volledig ingevuld, af te drukken en mee te brengen naar school samen met de andere vereiste documenten / Mester print e formulier aki, completamente yena, y hibe scol hunto cu e otro documentnan exigi");
    echo("<BR><INPUT TYPE=SUBMIT VALUE='EXIT' onClick='location.href=\"RegistratieformulierMB.php?byebye=1\"'>");
  }
  
	
?>
<script>
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
    preptag = oXML.responseText.indexOf("<COPYADDRESS>");
		if(preptag > 0)
		{
			document.getElementById("maddress").value = document.getElementById("sadres").value;
			document.getElementById("faddress").value = document.getElementById("sadres").value;
    }	  
    preptag = oXML.responseText.indexOf("<REREGISTER>");
		if(preptag > 0)
		{
			alert("Dit is geen nieuwe registratie, open de registratie opnieuw via de link die via e-mail is verstuurd!\r\nEsaki no ta un registracion nobo, habri e regsitracion di nobo cu e link cu a wordo manda via e-mail!");
			var win = window.open("about:blank", "_self"); win.close();
		}
    preptag = oXML.responseText.indexOf("<HIDEFIELD>");
		if(preptag > 0)
		{
			document.getElementById(oXML.responseText.substr(preptag+11)).style.visibility='hidden';
			//alert("Hide " + oXML.responseText.substr(preptag+11));
		}
    preptag = oXML.responseText.indexOf("<SHOWFIELD>");
		if(preptag > 0)
		{
			document.getElementById(oXML.responseText.substr(preptag+11)).style.visibility='visible';
			//alert("Show " + oXML.responseText.substr(preptag+11));
		}

    preptag = oXML.responseText.indexOf("<REREGISTER2>");
		if(preptag > 0)
		{
			alert("Als bestaande student moet je gebruik maken van de aanmelding rechts boven op het formulier. Als je de inloggegevens niet hebt neem dan contact op met de school!\r\nManera e alumno ta registra caba, haci uzo di e dialogo ariba man drechi riba screen. Si no tin datonan pa login, tuma contacto cu nos scol!");
			var win = window.open("about:blank", "_self"); win.close();
		}
		if(oXML.responseText.substr(oXML.responseText.length - 7) == "REFRESH")
			document.location.reload(true);
  }
}

function send_xmlcb(fieldid,fieldobj)
{
  myConn[fieldid] = new XHConn(fieldobj);
  if (!myConn[fieldid]) alert("XMLHTTP not available. Try a newer/better browser.");
		if(fieldobj.checked == false)
			cbstat = 0;
		else
			cbstat = 1;
  myConn[fieldid].connect("inschrijfMBhandler.php", "POST", "fieldid="+fieldid+"&"+fieldobj.name+"="+cbstat, xmlconnDone);
}
</script>
<?
  // close the page
  echo("</html>");
?>
