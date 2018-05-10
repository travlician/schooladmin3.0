<?
/* vim: set expandtab tabstop=2 shiftwidth=2: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
//
// MUST load the classes before session_start()!
require_once("inputlib/inputclasses.php");
require_once("schooladminconstants.php");
session_start();
// echo("ERR"); // Force debug info'

// Reconnect with the database as we don't use persistent connections
inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);
$userlink = inputclassbase::$dbconnection;

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
  
// Catch names and birthdate, if exists as another record or in user database, need to inform user and let main form send mail about it.
if(($_POST['fieldid'] == "slname" && $_POST['slname'] != "") ||
   ($_POST['fieldid'] == "sfname" && $_POST['sfname'] != "") ||
   ($_POST['fieldid'] == "sbday" && $_POST['sbday'] != "") ||
   ($_POST['fieldid'] == "sbmon" && $_POST['sbmon'] != "") ||
   ($_POST['fieldid'] == "sbyr" && $_POST['sbyr'] != "") )
{ // First see if another record FOR THIS YEAR is already present
  $schoolyear = date("Y"). "-". (date("Y") + 1);
	// Get the values of the above items
	$sdata['slname'] = $_SESSION['inputobjects']['slname']->__toString();
	$sdata['sfname'] = $_SESSION['inputobjects']['sfname']->__toString();
	$sdata['sbday'] = $_SESSION['inputobjects']['sbday']->__toString();
	$sdata['sbmon'] = $_SESSION['inputobjects']['sbmon']->__toString();
	$sdata['sbyr'] = $_SESSION['inputobjects']['sbyr']->__toString();
	// Overwrite the current value
	$sdata[$_POST['fieldid']] = $_POST[$_POST['fieldid']];
  $ridsearch = inputclassbase::load_query("SELECT rid FROM inschrijvingMPC WHERE firstname LIKE \"". $sdata['sfname']. "\" AND lastname LIKE \"". $sdata['slname']. "\" AND gebdag=\"". $sdata['sbday']. "\" AND gebmaand=\"". $sdata['sbmon']. "\" AND gebjaar=\"". $sdata['sbyr']. "\" AND year='". $_SESSION['inputobjects']['slname']->get_extrafield('year'). "' ORDER BY rid");
  if(isset($ridsearch['rid'][0]) && $ridsearch['rid'][0] != $_SESSION['inputobjects'][$_POST['fieldid']]->get_key())
  {
    echo("OK<REREGISTER>");
		// Remove this record!
		mysql_query("DELETE FROM inschrijvingMPC WHERE rid=". $_SESSION['inputobjects']['semail']->get_key(), $userlink);
		sendmaillink($_POST['semail'],$ridsearch['rid'][0]);
		exit;
	}
  // Now see if the student is already registered as student and did not enter through the logon screen, if so, copy values and let parent send mail
  $sidsearch = inputclassbase::load_query("SELECT sid FROM student LEFT JOIN s_ASBirthDate USING(sid) WHERE firstname LIKE \"". $sdata['sfname']. "\" AND lastname LIKE \"". $sdata['slname']. "\" AND data=\"". ($sdata['sbday']. " ". $sdata['sbmon']. " ". $sdata['sbyr']). "\"");
  if(isset($sidsearch['sid'][0]))
  { // Student found! Copy data to this record and send mail
		$bd = "NULL"; $bm="NULL"; $by="NULL"; // Defaults in case conversion fails
		$bdqr = inputclassbase::load_query("SELECT data FROM s_ASBirthDate WHERE sid=". $sidsearch['sid'][0]);
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
				// Now the values to put in the inschrijvingMPC records
				$bd = "'". $splitbd[0]. "'";
				$bm = "'". $splitbd[1]. "'";
				$by = "'". $splitbd[2]. "'";
			}	
			$rid=$_SESSION['inputobjects']['semail']->get_key();
			$insrecq = "REPLACE INTO inschrijvingMPC (rid,year,sid,firstname,lastname,roepnaam,geslacht,gebdag,gebmaand,gebjaar,
									gebland,adres,email,telthuis,telmobile,laatsteschool,idnummer,wachtwoord)";
			$insrecq .= " SELECT ". $rid. ",'". $schoolyear. "',student.sid,firstname,lastname,s_roepnaam.data,s_ASGender.data,". $bd. ",". $bm. ",". $by. ",";
			$insrecq .= "s_ASBirthCountry.data,s_ASAddress.data,s_ASEmailStudent.data,s_ASPhoneHomeStudent.data,";
			$insrecq .= "s_ASMobilePhoneStudent.data,s_ASLastSchool.data,altsid,password FROM student";
			$insrecq .= " LEFT JOIN s_roepnaam USING(sid) LEFT JOIN s_ASGender USING(sid)";
			$insrecq .= " LEFT JOIN s_ASBirthCountry USING(sid) LEFT JOIN s_ASAddress USING(sid) LEFT JOIN s_ASEmailStudent USING(sid)";
			$insrecq .= " LEFT JOIN s_ASPhoneHomeStudent USING(sid) LEFT JOIN s_ASMobilePhoneStudent USING(sid)";
			$insrecq .= " LEFT JOIN s_ASLastSchool USING(sid)";
			$insrecq .= " WHERE sid=". $sidsearch['sid'][0];
			mysql_query($insrecq,$userlink);
			echo(mysql_error($userlink));
    }
    echo("OK<REREGISTER2>");
		sendmaillink($_POST['semail'],$rid);
		exit;
  }
}
// Catch "siljaar" as it means putting the student with all data in the database and indicated group
if($_POST['fieldid'] == "siljaar")
{
  // Process the record as usual
	//echo("ERR");
  include("inputlib/procinput.php");
  // Get the sid
  $rid = $_SESSION['inputobjects']['siljaar']->get_key();
  $sidfld = new inputclass_textfield("sidrq",0,NULL,"sid","inschrijvingMPC",$rid,"rid",NULL,"inschrijfAHhandler.php");
  if($sidfld->__toString() == '')
    $sid=0;
  else
    $sid=$sidfld->__toString();

  if($_POST['siljaar'] == "")
  { // Student no longer is in a group
    if($sid > 0)
		{ // Delete student from applicable groups
			mysql_query("DELETE FROM sgrouplink WHERE sid=". $sid, $userlink);
			echo(mysql_error($userlink));
		}
    echo("Deze student is <b>niet</b> geplaatst!");
  }
  else
  {
		// First see if we need to create a student record
		if($sid == 0)
		{
			// Need to get userid and password first
			$stbd = inputclassbase::load_query("SELECT lvsid,lvspassword,gebdag,gebmaand,gebjaar FROM inschrijvingMPC WHERE rid=". $rid);
			if(!isset($stbd['lvsid'][0]) || $stbd['idnummer'][0] == "")
			{ // no userid given, we contruct it
				$idnr = substr(date("Y"),2). str_pad($rid,4,"0",STR_PAD_LEFT);
			}
			else
				$idnr = $stbd['lvsid'][0];
			if(!isset($stbd['lvspassword'][0]) || $stbd['lvspassword'][0] == "")
			{ // no password given, we contruct is from the record number
				$pw = str_pad(base_convert(($rid * 1313) % 32000,10,16),4,"0",STR_PAD_LEFT);
			}
			else
				$pw = $stbd['lvspassword'][0];
				// Create the student record
			mysql_query("INSERT INTO student (altsid,password,ppassword) VALUES('". $idnr. "','". $pw. "','". $pw. "')", $userlink);
			$sid = mysql_insert_id($userlink);
			// Set this student id also in the rid record
			mysql_query("UPDATE inschrijvingMPC SET sid=". $sid. " WHERE rid=". $rid, $userlink);
		}
		// Update the student data from the registration
		$idata = inputclassbase::load_query("SELECT * FROM inschrijvingMPC WHERE rid=". $rid);
		if($idata['firstname'][0] != '')
		{
			mysql_query("UPDATE student SET firstname='". $idata['firstname'][0]. "' WHERE sid=". $sid);
			echo("UPDATE student SET firstname='". $idata['firstname'][0]. "' WHERE sid=". $sid);
		}
		if($idata['lastname'][0] != '')
			mysql_query("UPDATE student SET lastname='". $idata['lastname'][0]. "' WHERE sid=". $sid);
		if($idata['gebdag'][0] != '' && $idata['gebmaand'][0] != '' && $idata['gebjaar'][0] != '')
			mysql_query("REPLACE INTO s_ASBirthDate (sid,data) VALUES(". $sid. ",'". $idata['gebdag'][0]. " ".$idata['gebmaand'][0]. " ".$idata['gebjaar'][0]. "')", $userlink);
		// Update the other records
		foreach($reg2stud AS $ikey => $dkey)
		{
			if($idata[$ikey][0] != '')
				mysql_query("REPLACE INTO `". $dkey. "` (sid,data) VALUES(". $sid. ",'". $idata[$ikey][0]. "')", $userlink);
		}
		// Need to place student in group
		$gidqr = inputclassbase::load_query("SELECT groupname FROM sgroup WHERE gid='". $_POST['siljaar']. "'");
		// Now place the student in the group.
		mysql_query("INSERT INTO sgrouplink (sid,gid) VALUES(". $sid. ",". $_POST['siljaar']. ")", $userlink);
			echo("Deze student is plaatst in groep ". $gidqr['groupname'][0]);
  }
  exit;
}
// See if e-mail address is valid
if($_POST['fieldid'] == 'semail')
{
  $emailadr = $_POST['semail'];
	$spma = explode("@",$emailadr);
	//echo("Evaluating ". $emailadr);
	$fail=true;
	if(isset($spma[1]))
	{
		if(strpos($spma[1],".") > 1 && strpos($spma[1],".") < (strlen($spma[1]) - 1))
		{
			$fail = false;
		}
		//else
		//{
		//	echo("Failed dot location.");
		//}
	}
	//else
	//	echo("No @ found in address");
	if(isset($spma[2]))
		$fail=true;
	if($fail)
	{
		echo("E-mail adres is niet geldig, voer een geldig e-mail adres in!");
		exit;
	}
}


// Let the library page handle the data
include("inputlib/procinput.php");

// Just for demo purposes: show the fields posted(note that the library only shows an alert with this data if something went wrong)
foreach($_POST AS $parm => $val)
{
  //echo("\r\nPassed parameter: ". $parm. " = ". $val); 
}


// Check if e-mail address is given or changed, if so send a message with the encoded record id as link so it can be retrieved afterwards.
if($_POST['fieldid'] == 'semail')
{
  $emailadr = $_SESSION['inputobjects']['semail']->__toString();
  $recid = $_SESSION['inputobjects']['semail']->get_key();
  sendmaillink($emailadr,$recid);
}
// See if student address is set, if so, demand address copied to parents
echo("Fieldid=". $_POST['fieldid']);
if($_POST['fieldid'] == 'sadres')
	echo("<COPYADDRESS>");
if($_POST['fieldid'] == "sresponsable")
	if($_POST[$_POST['fieldid']] == "Anders")
	  echo("<SHOWFIELD>srespother");
	else
	  echo("<HIDEFIELD>srespother");
if($_POST['fieldid'] == "sliveat")
	if($_POST[$_POST['fieldid']] == "Anders")
	  echo("<SHOWFIELD>sliveatother");
	else
	  echo("<HIDEFIELD>sliveatother");
if($_POST['fieldid'] == "sbaseschool")
	if($_POST[$_POST['fieldid']] == 38)
	  echo("<SHOWFIELD>sotherbase");
	else
	  echo("<HIDEFIELD>sotherbase");
if($_POST['fieldid'] == "smedicalyn")
	if($_POST[$_POST['fieldid']] == 'j')
	  echo("<SHOWFIELD>medicaldetails");
	else
	  echo("<HIDEFIELD>medicaldetails");
if($_POST['fieldid'] == "sdevprobyn")
	if($_POST[$_POST['fieldid']] == 'j')
	  echo("<SHOWFIELD>devdetails");
	else
	  echo("<HIDEFIELD>devdetails");
if($_POST['fieldid'] == "stestedyn")
	if($_POST[$_POST['fieldid']] == 'j')
	  echo("<SHOWFIELD>testdetails");
	else
	  echo("<HIDEFIELD>testdetails");
if($_POST['fieldid'] == "sguidedyn")
	if($_POST[$_POST['fieldid']] == 'j')
	  echo("<SHOWFIELD>guidedetails");
	else
	  echo("<HIDEFIELD>guidedetails");
// Refresh if matchedwith an existing student
if($_POST['fieldid'] == "smatch")
	echo("REFRESH");

function sendmaillink($emailadr,$recid)
{
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=utf8' . "\r\n";
  $msgtxt = "<html>
             <head>
			   <title>Aanmelding Mon Plaisir College</title>
			 </head>
			 <body>
               Het aanmeldingformulier is opgenomen in het registratiesysteem van de Mon Plaisir College.<BR>
               De aanmelding kan gewijzigd worden zolang deze niet is verwerkt. U krijgt bericht als uw kind is geaccepteerd.<BR>
               E formulario a wordo registra den e sistema di Mon Plaisir College.<BR>
               Cu e link por cambio datonan te ora e formulario a wordo procesa. Ta wordo informa ora e mucha a wordo accepta.<BR><BR><BR>
							 <font color=blue>We hebben het formulier digitaal verwerkt. Wilt U de informatie controleren en aanvullen waar nodig; zeker invullen: &quot;Waarom kiest U voor onze school? / Dicon a scohe nos scol?&quot;</font> indien het een nieuwe leerling betreft.<BR><BR>
			   <a href='https://myschoolresults.com/MP/RegistratieformulierMPC.php?regcode=". urlencode(base64_encode($recid)). "'>Klik hier om te wijzigen / Click aki pa cambia</a>.<BR>
			   (of navigeer naar  / of naviga pa https://myschoolresults.com/MP/RegistratieformulierMPC.php?regcode=". 	urlencode(base64_encode($recid)). ")
			 </body>
			 </html>";
  if($emailadr != "")
    mail($emailadr,"Aanmelding Mon Plaisir College",$msgtxt,$headers,"-fnoreply@myschoolresults.com");
}
?>
