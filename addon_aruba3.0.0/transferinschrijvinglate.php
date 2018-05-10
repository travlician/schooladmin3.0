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
$transtable = array("Mankind"=>"s_ASGender","Bday"=>"s_ASBirthDate",
	"Religion"=>"s_ASReligion","Baptised"=>"s_ASBaptised","AZVNr"=>"s_ASMedicalInsuranceNumber",
	"BirthCountry"=>"s_ASBirthCountry","Nationality"=>"s_ASNationality","LangHome"=>"s_ASHomeLanguage",
	"Address"=>"s_ASAddress","District"=>"s_ASDistrict","PhoneHome"=>"s_ASPhoneHomeStudent",
	"MobilePhone"=>"s_ASMobilePhoneStudent","EmailAddress"=>"s_ASEmailStudent","ResponsPersoon"=>"s_ASResponsablePerson",
	"EmergPhoneNr"=>"s_ASEmergyPhoneNr","InArubaSince"=>"s_ASInArubaSince","LiveAt"=>"s_ASLiveAt",
	"LastnameDad"=>"s_ASLastNameParent1","FirstnameDad"=>"s_ASFirstNameParent1","AddressDad"=>"s_ASAddressParent1",
	"DistrictDad"=>"s_ASDistrictParent1","PhoneHomeDad"=>"s_ASPhoneHomeParent1","MobilePhoneDad"=>"s_ASPhoneMobileParent1",
	"EmailAddressDad"=>"s_ASEmailParent1","ProfesionDad"=>"s_ASProfesionParent1","CompagnyNameDad"=>"s_ASEmployerParent1",
	"PhoneCompagnyDad"=>"s_ASPhoneWorkParent1","LastnamMom"=>"s_ASLastNameParent2","FirstnameMom"=>"s_ASFirstNameParent2",
	"AddressMom"=>"s_ASAddressParent2","DistrictMom"=>"s_ASDistrictParent2","PhoneHomeMom"=>"s_ASPhoneHomeParent2",
	"MobilePhoneMom"=>"s_ASPhoneMobileParent2","EmailAddressMom"=>"s_ASEmailParent2","ProfesionMom"=>"s_ASProfesionParent2",
	"CompagnyNameMom"=>"s_ASEmployerParent2","PhoneCompagnyMom"=>"s_ASPhoneWorkParent2",
	"EstCivilFamily"=>"s_ASCivilStateFamily","RelegionFamily"=>"s_ASRelegionFamily","HomeMD"=>"s_ASHomeMedic",
	"FamilyForm"=>"s_ASFamilyConstelation","Botica"=>"s_ASPharamcy","NurserySchool"=>"s_ASNurserySchool",
	"Kindergarden"=>"s_ASKindergarten","BO"=>"s_ASPrimarySchool","FailBO"=>"s_ASFailedYearsPrimary",
	"FailAVO"=>"s_ASFailYearsSecondary","NameBroSis1"=>"s_ASNameSibling1","SchoolBroSis1"=>"s_ASSchoolSibling1",
	"ClassBroSis1"=>"s_ASSchoolyearSibling1","NameBroSis2"=>"s_ASNameSibling2","SchoolBroSis2"=>"s_ASSchoolSibling2",
	"ClassBroSis2"=>"s_ASSchoolyearSibling2","NameBroSis3"=>"s_ASNameSibling3","SchoolBroSis3"=>"s_ASSchoolSibling3",
	"ClassBroSis3"=>"s_ASSchoolyearSibling3","NameBroSis4"=>"s_ASNameSibling4","SchoolBroSis4"=>"s_ASSchoolSibling4",
	"ClassBroSis4"=>"s_ASSchoolyearSibling4","SPecialComm"=>"s_ASSpecialComments","MedProblems"=>"s_ASMedicalProblems",
	"LearningProblems"=>"s_ASLearningProblems");





// Get the new "inschrijving"
$newstuds = SA_loadquery("SELECT * FROM nieuwe_inschrijving LEFT JOIN nieuwe_registratie USING(regid) WHERE Checked=1");
if(isset($newstuds['regid']))
{
  $studcount=0;
  $failcount=0;
  foreach($newstuds['regid'] AS $six => $regid)
  {
    echo("Toevoegen student data ". $newstuds['Firstname'][$six]. " ". $newstuds['Lastname'][$six]. " (record ". $regid. ")<BR>");
	// First see if student already exists
	$sexist = SA_loadquery("SELECT sid FROM student WHERE firstname='". $newstuds['Firstname'][$six]. "' AND lastname='". $newstuds['Lastname'][$six]. "'");
	// Student does not exist, do a looser search...
	if(!isset($sexist['sid']))
	{
	  $loosefn = explode(" ",$newstuds['Firstname'][$six]);
	  $loosefn = $loosefn[0];
	  $looseln = explode(" ",$newstuds['Lastname'][$six]);
	  $looseln = $looseln[0];
	  $sexist = SA_loadquery("SELECT sid FROM student WHERE firstname LIKE '". $loosefn. "%' AND lastname LIKE '". $looseln. "%'");
	  if(isset($sexist['sid'][2]))
	  {
	    unset($sexist);
		echo("Meer dan 1 student gevonden met voornaam ". $loosefn. " en achternaam ". $looseln. "<BR>");
	  }
	}
	
	if(!isset($sexist['sid']))
	{
	  echo("Student ". $newstuds['Firstname'][$six]. " ". $newstuds['Lastname'][$six]. " bestaat niet! <BR>");
	  $failcount++;
	}
	else
	{
	  $sid=$sexist['sid'][1];
	  // Now add the data for the student
	  // Start with converting the specials
	  // Birth date
	  $newstuds['Bday'][$six] = $newstuds['Bday'][$six]. "-". $newstuds['Bmonth'][$six]. "-". $newstuds['Byear'][$six];
	  // Baptised
	  $newstuds['Baptised'][$six] = $newstuds['Baptised'][$six] == 1 ? "ja" : "nee";
	  // Birth country
	  $bc = 0;
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
      $newstuds['BirthCountry'][$six] = $bc;
	  // All other fields based on the array!
	  foreach($transtable AS $fieldname => $tablename)
	  {
	    if($newstuds[$fieldname][$six] != "")
	    {
	      //mysql_query("INSERT IGNORE INTO `". $tablename. "` (sid,data) VALUES(". $sid. ",\"". $newstuds[$fieldname][$six]. "\")", $userlink);
	      //echo(mysql_error($userlink));	
	    }
	  }
	}
	
	$studcount++;
  }
  echo($studcount. " studenten verwerkt, ". $failcount. " studenten niet gevonden!<BR>");
}
else
  echo("Geen nieuwe inschrijvingen gevonden");
?>

