<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014  Aim4Me N.V.  (http://www.aim4me.info)       |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert  -  travlician@bigfoot.com                |
// +----------------------------------------------------------------------+
//
session_start();

//$login_qualify = 'A';
require_once("schooladminfunctions.php");
// This script may take more time, so extend time
set_time_limit(500);
$transtable = array("Mankind" => "s_ASGender",
					"IdenNr" => "s_IDcenso",
					"Religion" => "s_ASReligion", //"s_ASReligion"
///					"Baptised" => "s_ASBaptised", // ja / nee 0 /1
					"AZVNr" => "s_ASMedicalInsuranceNumber",
//					"BirthCountry" => "s_ASBirthCountry", // s_s_Geboorteland AW,CO,VE,NL -> 14,45,236,154
					"Nationality" => "s_ASNationality", // "s_ASNationality"
					"LangHome" => "s_ASHomeLanguage", //  "s_ASHomeLanguage"
					"Address" => "s_ASAddress",
//					"District" => "s_ASDistrict", // Fix choicelist for next time!
					"PhoneHome" => "s_ASPhoneHomeStudent",
					"MobilePhone" => "s_ASMobilePhoneStudent",
					"EmailAddress" => "s_ASEmailStudent",
					"ResponsPersoon" => "s_ASResponsablePerson",
					"EmergPhoneNr" => "s_ASEmergyPhoneNr",
					"InArubaSince" => "s_ASInArubaSince",
					"LiveAt" => "s_ASLiveAt",
					"LastnameDad" => "s_ASLastNameParent1",
					"FirstnameDad" => "s_ASFirstNameParent1",
					"AddressDad" => "s_ASAddressParent1",
//					"DistrictDad" => "s_ASDistrictParent1",
					"PhoneHomeDad" => "s_ASPhoneHomeParent1",
					"MobilePhoneDad" => "s_ASPhoneMobileParent1",
					"EmailAddressDad" => "s_ASEmailParent1",
					"ProfesionDad" => "s_ASProfesionParent1",
					"CompagnyNameDad" => "s_ASEmployerParent1",
					"PhoneCompagnyDad" => "s_ASPhoneWorkParent1",
					"LastnamMom" => "s_ASLastNameParent2",
					"FirstnameMom" => "s_ASFirstNameParent2",
					"AddressMom" => "s_ASAddressParent2",
//					"DistrictMom" => "s_ASDistrictParent2",
					"PhoneHomeMom" => "s_ASPhoneHomeParent2",
					"MobilePhoneMom" => "s_ASPhoneMobileParent2",
					"EmailAddressMom" => "s_ASEmailParent2",
					"ProfesionMom" => "s_ASProfesionParent2",
					"CompagnyNameMom" => "s_ASEmployerParent2",
					"PhoneCompagnyMom" => "s_ASPhoneWorkParent2",
					"EstCivilFamily" => "s_ASCivilStateFamily",
//					"RelegionFamily" => "s_ASRelegionFamily",
					"HomeMD" => "s_ASHomeMedic",
					"FamilyForm" => "s_ASFamilyConstelation",
					"Botica" => "s_ASPharamcy",
					"NurserySchool" => "s_ASNurserySchool",
					"Kindergarden" => "s_ASKindergarten",
					"BO" => "s_ASPrimarySchool", // "s_ASPrimarySchool"
					"AndereBasisschool" => "s_Andere_basisschool",
					"FailBO" => "s_ASFailedYearsPrimary",
					"FailAVO" => "s_ASFailYearsSecondary",
					"NameBroSis1" => "s_ASNameSibling1",
					"SchoolBroSis1" => "s_ASSchoolSibling1",
					"ClassBroSis1" => "s_ASSchoolyearSibling1",
					"NameBroSis2" => "s_ASNameSibling2",
					"SchoolBroSis2" => "s_ASSchoolSibling2",
					"ClassBroSis2" => "s_ASSchoolyearSibling2",
					"NameBroSis3" => "s_ASNameSibling3",
					"SchoolBroSis3" => "s_ASSchoolSibling3",
					"ClassBroSis3" => "s_ASSchoolyearSibling3",
					"NameBroSis4" => "s_ASNameSibling4",
					"SchoolBroSis4" => "s_ASSchoolSibling4",
					"ClassBroSis4" => "s_ASSchoolyearSibling4",
					"SPecialComm" => "s_ASSpecialComments",
					"MedProblems" => "s_ASMedicalProblems",
					//"AmountAircoPaid" => "s_AmountAircoPaid",
					"LearningProblems" => "s_ASLearningProblems");
// Create a table with last run transfer data
  $sqlquery = "CREATE TABLE IF NOT EXISTS `lasttransfer` (
  `lastmodifiedat` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB CHARSET=utf8;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());

$lasttransqr = SA_loadquery("SELECT lastmodifiedat FROM lasttransfer");
if(!isset($lasttransqr['lastmodifiedat']))
{
	mysql_query("INSERT INTO lasttransfer VALUES('2016-01-01')", $userlink);
	$lasttrans = '2016-01-01';
}
else
	$lasttrans=$lasttransqr['lastmodifiedat'][1];

// Assign an ID for new registration where no ID has been set as yet.
mysql_query("UPDATE nieuwe_inschrijving SET IDLvs=CONCAT('N',YEAR(CURDATE()),LPAD(regid,4,'0')) WHERE IDLvs IS NULL", $userlink);
echo(mysql_error($userlink));
					
// Get the new "inschrijving"
//$newstuds = SA_loadquery("SELECT * FROM nieuwe_inschrijving LEFT JOIN nieuwe_registratie USING(regid) WHERE Checked=1");
$newstuds = SA_loadquery("SELECT * FROM nieuwe_inschrijving LEFT JOIN nieuwe_registratie USING(regid) WHERE lastmodifiedat > '". $lasttrans. "'");
if(isset($newstuds['regid']))
{
  $studcount=0;
  foreach($newstuds['regid'] AS $six => $regid)
  {
    // echo("Toevoegen student ". $newstuds['Firstname'][$six]. " ". $newstuds['Lastname'][$six]. "<BR>");
		// First see if student already exists
		$sexist = SA_loadquery("SELECT sid FROM student WHERE altsid='". $newstuds['IDLvs'][$six]. "'");
		if(isset($sexist['sid']))
		{
			echo("Student ". $newstuds['Firstname'][$six]. " ". $newstuds['Lastname'][$six]. " met ID ". $newstuds['IDLvs'][$six]. " bestaat al, wordt geupdate! <BR>");
			$sid=$sexist['sid'][1];
		}
		else
		{
			echo("Student ". $newstuds['Firstname'][$six]. " ". $newstuds['Lastname'][$six]. " met ID ". $newstuds['IDLvs'][$six]. " wordt toegvoegd! <BR>");
			$addstq = "INSERT INTO student (lastname,firstname,password,altsid,ppassword) VALUES(\"". $newstuds['Lastname'][$six]. "\",\"". $newstuds['Firstname'][$six].
								 "\",\"". $newstuds['PLvsLl'][$six]. "\",\"". $newstuds['IDLvs'][$six]. "\",\"". $newstuds['PLvsOuder'][$six]. "\")";
			mysql_query($addstq, $userlink);
			echo(mysql_error($userlink));
			$sid = mysql_insert_id($userlink);
		}
		// See if a gid already exists for new group
		$newgid = SA_loadquery("SELECT gid FROM sgroup WHERE active=1 AND groupname='ins". date("Y"). $newstuds['KlasPlaatsing'][$six]. "'");
		if(isset($newgid['gid']))
			$ngid = $newgid['gid'][1];
		else
		{ // Group needs to be created
			echo("Creating new group<BR>");
			mysql_query("INSERT INTO sgroup (groupname, tid_mentor) VALUES('ins". date("Y"). $newstuds['KlasPlaatsing'][$six]. "',1)", $userlink);
			$ngid = mysql_insert_id($userlink); 
		}
	  // Add student to group for new registrations
	  mysql_query("INSERT INTO sgrouplink (sid,gid) VALUES (". $sid. ",". $ngid. ")", $userlink);
	  echo(mysql_error($userlink));
	
		// Now add the data for the students
		// Start with the specials
		// Birth date
		mysql_query("REPLACE INTO s_ASBirthDate (sid,data) VALUES(". $sid. ",'". $newstuds['Bday'][$six]. "-". $newstuds['Bmonth'][$six]. "-". $newstuds['Byear'][$six]. "')", $userlink);
		echo(mysql_error($userlink));
		// Baptised
		//mysql_query("REPLACE INTO s_ASBaptised (sid,data) VALUES(". $sid. ",'". ($newstuds['Baptised'][$six] == 1 ? "ja" : "nee"). "')", $userlink);
		echo(mysql_error($userlink));	
		// Birth country
		unset($bc);
			if($newstuds['BirthCountry'][$six] == "AW")
				$bc = 14;
			else if($newstuds['BirthCountry'][$six] == "CO")
				$bc = 45;
			else if($newstuds['BirthCountry'][$six] == "NL")
				$bc = 154;
			else if($newstuds['BirthCountry'][$six] == "VE")
				$bc = 236;
			else if($newstuds['BirthCountry'][$six] == "PH")
				$bc = 69;
			else if($newstuds['BirthCountry'][$six] == "DO")
				$bc = 57;
			else if($newstuds['BirthCountry'][$six] == "CW")
				$bc = 52;
			else if($newstuds['BirthCountry'][$six] == "PE")
				$bc = 178;
			else if($newstuds['BirthCountry'][$six] == "GT")
				$bc = 85;
		if(isset($bc))
		{
			mysql_query("REPLACE INTO s_ASBirthCountry (sid,data) VALUES(". $sid. ",'". $bc. "')", $userlink);
			echo(mysql_error($userlink));	
		}
		// All other fields based on the array!
		foreach($transtable AS $fieldname => $tablename)
		{
			if(isset($newstuds[$fieldname][$six]) && $newstuds[$fieldname][$six] != "")
			{
				mysql_query("REPLACE INTO ". $tablename. " (sid,data) VALUES(". $sid. ",\"". str_replace("\"","'",$newstuds[$fieldname][$six]). "\")", $userlink);
				echo("REPLACE INTO ". $tablename. " (sid,data) VALUES(". $sid. ",\"". str_replace("\"","'",$newstuds[$fieldname][$six]). "\")<BR>");
				echo(mysql_error($userlink));	
			}
		}
	
	$studcount++;
  }
  echo($studcount. " studenten verwerkt<BR>");
}
else
  echo("Geen nieuwe inschrijvingen gevonden");
mysql_query("UPDATE lasttransfer SET lastmodifiedat=NOW()");
?>

