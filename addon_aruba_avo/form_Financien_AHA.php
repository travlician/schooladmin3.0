<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  //error_reporting(E_STRICT);
 

  include ("schooladminfunctions.php");
  require_once("inputlib/inputclasses.php");
  require_once("student.php");
  require_once("groupselector.php");
	if(!isset($pngsource))
		$pngsource = "PNG";
  
  // Link database resource with input library
  inputclassbase::dbconnect($userlink);
 
  // Subject translation tables
  $xxsubjects = array("I&S","Pfw");


  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;
  
  $ro = isset($_GET['RO']);
  
  $uid = intval($uid);
  
  // We need to get the year for entry!
  $curyearqr = inputclassbase::load_query("SELECT year FROM period WHERE id=3");
  $curyear = $curyearqr['year'][0];
  $yearsuffx = substr($curyear,0,4);

  // This function is based on tables that are created as needed. So now we create them if it does not exist.
  create_data_table("s_inschrijfgeld", $userlink);    
  create_data_table("s_betaling_student_". $yearsuffx, $userlink);    
  create_data_table("s_betaaldatum_student_". $yearsuffx, $userlink);    
  create_data_table("s_betaling2_student_". $yearsuffx, $userlink);    
  create_data_table("s_betaaldatum2_student_". $yearsuffx, $userlink);    
  create_data_table("s_terugbetaling_". $yearsuffx, $userlink);    
  create_data_table("s_cheque_terugbetaling_". $yearsuffx, $userlink);  
  create_data_table("s_fin_opmerkingen_". $yearsuffx, $userlink);  
  for($i=1; $i <= 3; $i++)
  {  
    create_data_table("s_beloning_". $i. "_". $yearsuffx, $userlink);
    create_data_table("s_beloning_". $i. "_cheque_". $yearsuffx, $userlink);
  }	
  
  if(isset($_GET['print']))
  {
    $student = new student($_GET['print']);
	$sid = $student->get_id();
    echo("<html><head><title>Factuur studenten</title></head><body>");
    echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
	echo("<H1>FACTUUR</H1>");
	echo("<IMG style='float: right' src=schoollogo.png>");
	echo("<H2>AVOND HAVO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AVOND VWO</H2>");
	echo("<H2>". $curyear. "</H2>");
	echo("<H2>Persoonsgegevens</H2>");
	echo("<P><SPAN style='width:200px;display: inline-block;'>Achternaam</SPAN>". $student->get_student_detail("*student.lastname"). "<BR>");
	echo("<SPAN style='width:200px;display: inline-block;'>Voornaam (voluit)</SPAN>". $student->get_student_detail("*student.firstname"). "<BR>");
	echo("<SPAN style='width:200px;display: inline-block;'>Geboortedatum</SPAN>". $student->get_student_detail("s_ASBirthDate"). "<BR>");
	echo("<SPAN style='width:200px;display: inline-block;'>Adres</SPAN>". $student->get_student_detail("s_ASAddress"). "<BR>");
	// Find out which location and year this student is in.
	$groupnames = $student->get_student_detail("*sgroup.groupname");
	$grpnms = explode("<BR>",$groupnames);
	$loc="?";
	$yr="?";
	if(in_array("OS1",$grpnms))
	{
	  $loc="OS"; $yr=1;
	}
	else if(in_array("OS2",$grpnms))
	{
	  $loc="OS"; $yr=2;
	}
	else if(in_array("OS3",$grpnms))
	{
	  $loc="OS"; $yr=3;
	}
	else if(in_array("SN1",$grpnms))
	{
	  $loc="SN"; $yr=1;
	}
	else if(in_array("SN2",$grpnms))
	{
	  $loc="SN"; $yr=2;
	}
	else if(in_array("SN3",$grpnms))
	{
	  $loc="SN"; $yr=3;
	}
	else if(in_array("NW". date("Y"). "1O",$grpnms))
	{
	  $loc="OS"; $yr=1;
	}
	else if(in_array("NW". date("Y"). "2O",$grpnms))
	{
	  $loc="OS"; $yr=2;
	}
	else if(in_array("NW". date("Y"). "3O",$grpnms))
	{
	  $loc="OS"; $yr=3;
	}
	else if(in_array("NW". date("Y"). "1S",$grpnms))
	{
	  $loc="SN"; $yr=1;
	}
	else if(in_array("NW". date("Y"). "2S",$grpnms))
	{
	  $loc="SN"; $yr=2;
	}
	else if(in_array("NW". date("Y"). "3S",$grpnms))
	{
	  $loc="SN"; $yr=3;
	}
	echo("<SPAN style='width:200px;display: inline-block;'>Leerjaar</SPAN>". $yr. "<BR>");
	echo("<SPAN style='width:200px;display: inline-block;'>Locatie</SPAN>". $loc. "<BR>");
	$subjcntq = "SELECT COUNT(DISTINCT mid) AS `subjcnt` FROM sgrouplink LEFT JOIN class USING(gid) LEFT JOIN subject USING(mid) WHERE sid=". $sid;
	foreach($xxsubjects AS $xsub)
	{
	  $subjcntq .= " AND shortname <> '". $xsub. "'";
	}
	$subjcntq .= " GROUP BY sid";
	$subjcnt = inputclassbase::load_query($subjcntq);
	$subjcnt = $subjcnt['subjcnt'][0];
	echo("<SPAN style='width:200px;display: inline-block;'>Vakkenpakket</SPAN>
	         <SPAN style='width:50px;display: inline-block;'>". $subjcnt. "</SPAN>
	         <SPAN style='width:50px;display: inline-block; text-align: center;'>50.00</SPAN>
	         <SPAN style='width:50px;display: inline-block; text-align: center;'>p/vak</SPAN><BR>");
	echo("<SPAN style='width:200px;display: inline-block;'>Adm. kosten</SPAN>
	         <SPAN style='width:50px;display: inline-block;'>1</SPAN>
	         <SPAN style='width:50px;display: inline-block; text-align: center;'>50.00</SPAN><BR>");
	echo("<SPAN style='width:200px;display: inline-block;'>Examengeld</SPAN>
	         <SPAN style='width:50px;display: inline-block;'>". ($yr == 3 ? "1" : "0"). "</SPAN>
	         <SPAN style='width:50px;display: inline-block; text-align: center;'>50.00</SPAN><BR>");
	echo("</P>");
	
	echo("<H2>Schoolgeld&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<SPAN style='display: inline-block; left margin: 350px; font-size:12px; font-weight:normal;'> </SPAN></H2>");
	echo("<P><SPAN style='width:100px;display: inline-block; text-align: center;'>Bedrag</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'>Betaald</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'>d.d.</SPAN>
			 <SPAN style='width:100px;display: inline-block; text-align: center;'>Balans</SPAN><BR>");
	$paidfld = new inputclass_amount("paid". $sid,6,NULL,"data","s_betaling_student_". $yearsuffx,$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");	
	$paidddfld = new inputclass_datefield("paiddd". $sid,NULL,NULL,"data","s_betaaldatum_student_". $yearsuffx,$sid,"sid",NULL,"datahandler.php");
	$paidfld2 = new inputclass_amount("paidx". $sid,6,NULL,"data","s_betaling2_student_". $yearsuffx,$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");	
	$paidddfld2 = new inputclass_datefield("paidddx". $sid,NULL,NULL,"data","s_betaaldatum2_student_". $yearsuffx,$sid,"sid",NULL,"datahandler.php");
	echo("<SPAN style='width:100px;display: inline-block; text-align: center;'>". $student->get_student_detail("s_inschrijfgeld"). "</SPAN>
	      <BR><SPAN style='width:100px;display: inline-block; text-align: center;'>&nbsp;</SPAN>
	      <SPAN style='width:100px;display: inline-block; text-align: center;'>". $paidfld->__toString(). "</SPAN>
	      <SPAN style='width:100px;display: inline-block; text-align: center;'>". $paidddfld->__toString(). "</SPAN>
	      <BR><SPAN style='width:100px;display: inline-block; text-align: center;'>&nbsp;</SPAN>
	      <SPAN style='width:100px;display: inline-block; text-align: center;'>". $paidfld2->__toString(). "</SPAN>
	      <SPAN style='width:100px;display: inline-block; text-align: center;'>". $paidddfld2->__toString(). "</SPAN>
	      <BR><SPAN style='width:100px;display: inline-block; text-align: center;'>&nbsp;</SPAN>
	      <SPAN style='width:100px;display: inline-block; text-align: center;'>&nbsp;</SPAN>
	      <SPAN style='width:100px;display: inline-block; text-align: center;'>&nbsp;</SPAN>
		  <SPAN style='width:100px;display: inline-block; text-align: center; border-top: 1px solid black;'>". number_format($paidfld->__toString() + $paidfld2->__toString() - $student->get_student_detail("s_inschrijfgeld"),2). "</SPAN><BR>");
	echo("</p><h2>Restitutie</h2>");
	echo("<H2>Afgeschreven / Teveel betaald</H2>");
	echo("<P><SPAN style='width:100px;display: inline-block; text-align: center;'>Bedrag</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'> </SPAN>
			 <SPAN style='width:100px;display: inline-block; text-align: center;'>Cheque #</SPAN><BR>");
	$paybackfld = new inputclass_amount("payback". $sid,6,NULL,"data","s_terugbetaling_". $yearsuffx,$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");
	if($paybackfld->__toString() != "")
	{
	  $cheqfld = new inputclass_textfield("cheq". $sid,6,NULL,"data","s_cheque_terugbetaling_". $yearsuffx,$sid,"sid","","datahandler.php");
	echo("<SPAN style='width:100px;display: inline-block; text-align: center;'>". $paybackfld->__toString(). "</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'>&nbsp;</SPAN>
			 <SPAN style='width:100px;display: inline-block; text-align: center;'>". $cheqfld->__toString() ."</SPAN><BR>");
	}
	echo("</p><h2>Restitutie</h2>");
	echo("<H2>Beloning</H2>");
	echo("<P><SPAN style='width:100px;display: inline-block; text-align: center;'>Rapport1 / Geslaagd</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'>Cheque #</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'>Rapport 2</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'>Cheque #</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'>Overgang</SPAN>
			 <SPAN style='width:100px;display: inline-block; text-align: center;'>Cheque #</SPAN><BR>");
	$rewardtot=0.0;
	for($i=1; $i<=3; $i++)
	{
	  $rewardfld = new inputclass_amount("reward". $i. "_". $sid,6,NULL,"data","s_beloning_". $i. "_". $yearsuffx,$sid,"sid","","datahandler.php");
	  echo("<SPAN style='width:100px;display: inline-block; text-align: center;'>". $rewardfld->__toString(). "</SPAN> ");
	  $rewardcfld = new inputclass_textfield("rewardc". $i. "_". $sid,6,NULL,"data","s_beloning_". $i. "_cheque_". $yearsuffx,$sid,"sid","","datahandler.php");
	  echo("<SPAN style='width:100px;display: inline-block; text-align: center;'>". $rewardcfld->__toString(). "</SPAN> ");
	  $rewardtot += $rewardfld->__toString();
	}
	echo("</p><h2>Resumerend</h2>");
	echo("<P><SPAN style='width:100px;display: inline-block; text-align: center;'>Schoolgeld</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'>Beloning</SPAN>
			 <SPAN style='width:100px;display: inline-block; text-align: center;'>Betaald</SPAN><BR>");
	echo("<SPAN style='width:100px;display: inline-block; text-align: center;'>". $student->get_student_detail("s_inschrijfgeld") ."</SPAN>
	         <SPAN style='width:100px;display: inline-block; text-align: center;'>". number_format($rewardtot,2). "</SPAN>
			 <SPAN style='width:100px;display: inline-block; text-align: center;'>". number_format($student->get_student_detail("s_inschrijfgeld") - $rewardtot,2)."</SPAN></p>");
	
	echo("</html>");
    exit;
  }
  
  if(isset($_GET['excel']))
	  { // Need to upload as Excel file
  // Get a list of all applicable students
      header("Content-Disposition: attachment; filename=". $_SESSION['CurrentGroup']. ".xls");
      header('Content-type: application/xls');
	  $students = student::student_list();
	  echo("#\tAchternaam\tVoornamen\t# vakken\tSchoolgeld\tBetaald\td.d.\t \tNabetaald\td.d.\tSaldo\tRestitutie\tCheque# restitutie");
	  for($i=1; $i<=3; $i++)
		echo("\tBeloning ". $i. "\tCheque# beloning ". $i);

	  echo("\tOpmerkingen\r\n");

	  // Create a row in the table for each student
	  $negix = 0;
	  // Reset totals
	  $totschoolgeld = 0.0;
      $totbetaald = 0.0;
      $totnabetaald = 0.0;
      $totsaldo = 0.0;
      $totrestitutie = 0.0;
      $totbeloning[1] = 0.0;
      $totbeloning[2] = 0.0;
      $totbeloning[3] = 0.0;
		  $snum = 1;


	  foreach($students AS $student)
	  { 
	   if(isset($student))
	   {
		$sid=$student->get_id();
		echo($snum++. "\t". $student->get_student_detail("*student.lastname"). "\t". $student->get_student_detail("*student.firstname"));
		// We need to show the amount of subjects, excluding I&S and Pfw.
		$subjcntq = "SELECT COUNT(DISTINCT mid) AS `subjcnt` FROM sgrouplink LEFT JOIN class USING(gid) LEFT JOIN subject USING(mid) WHERE sid=". $sid;
		foreach($xxsubjects AS $xsub)
		{
		  $subjcntq .= " AND shortname <> '". $xsub. "'";
		}
		$subjcntq .= " GROUP BY sid";
		$subjcnt = inputclassbase::load_query($subjcntq);
		$subjcnt = $subjcnt['subjcnt'][0];
		echo("\t". $student->get_student_detail("s_vakken"). "~". $subjcnt);
		
		$regfeefld = new inputclass_amount("regfee". $sid,6,NULL,"data","s_inschrijfgeld",$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");
		echo("\t". $regfeefld->__toString());
		$totschoolgeld += $regfeefld->__toString();
		
		$paidfld = new inputclass_amount("paid". $sid,6,NULL,"data","s_betaling_student_". $yearsuffx,$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");
		echo("\t". $paidfld->__toString());
		$totbetaald += $paidfld->__toString();
	    $paidddfld = new inputclass_datefield("paiddd". $sid,NULL,NULL,"data","s_betaaldatum_student_". $yearsuffx,$sid,"sid",NULL,"datahandler.php");
		echo("\t". inputclassbase::nldate2mysql($paidddfld->__toString()));

		$paidfld2 = new inputclass_amount("paidx". $sid,6,NULL,"data","s_betaling2_student_". $yearsuffx,$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");
		echo("\t \t". $paidfld2->__toString());
		$totnabetaald += $paidfld2->__toString();
	    $paidddfld2 = new inputclass_datefield("paidddx". $sid,NULL,NULL,"data","s_betaaldatum2_student_". $yearsuffx,$sid,"sid",NULL,"datahandler.php");
		echo("\t". inputclassbase::nldate2mysql($paidddfld2->__toString()));

		// The saldo, calculate and put in a RO field with unique ID so script can change it.
		$paybackfld = new inputclass_amount("payback". $sid,6,NULL,"data","s_terugbetaling_". $yearsuffx,$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");
		$saldo = number_format($paidfld->__toString() + $paidfld2->__toString() - $regfeefld->__toString() - $paybackfld->__toString(),2);
		echo("\t". $saldo);
		$totsaldo += $saldo;

		echo("\t". $paybackfld->__toString());
		$totrestitutie += $paybackfld->__toString();
		$cheqfld = new inputclass_textfield("cheq". $sid,6,NULL,"data","s_cheque_terugbetaling_". $yearsuffx,$sid,"sid","","datahandler.php");
		echo("\t". $cheqfld->__toString());
		
		for($i=1; $i<=3; $i++)
		{
		  $rewardfld = new inputclass_amount("reward". $i. "_". $sid,6,NULL,"data","s_beloning_". $i. "_". $yearsuffx,$sid,"sid","","datahandler.php");
		  echo("\t". $rewardfld->__toString());
		  $totbeloning[$i] += $rewardfld->__toString();
		  $rewardcfld = new inputclass_textfield("rewardc". $i. "_". $sid,6,NULL,"data","s_beloning_". $i. "_cheque_". $yearsuffx,$sid,"sid","","datahandler.php");
		  echo("\t". $rewardcfld->__toString());
		}
		$remarkfld = new inputclass_textfield("remarks". $sid,6,NULL,"data","s_fin_opmerkingen_". $yearsuffx,$sid,"sid","","datahandler.php");
		echo("\t". $remarkfld->__toString());
		echo("\r\n");
	   }
	  }
	  // Show totals
	  echo("Totalen\t \t \t \t". $totschoolgeld. "\t". $totbetaald. "\t \t \t". $totnabetaald. "\t \t". $totsaldo. "\t". $totrestitutie. "\t \t". $totbeloning[1]. "\t \t". $totbeloning[2]. "\t \t". $totbeloning[3]. "\r\n");
    exit;
  }
  // Grpupselector object creation must take place before using group info!  
  $grpselfld = new groupselector();
  // Get a list of all applicable students
  $students = student::student_list();
  

 
  // First part of the page
  echo("<html><head><title>Financiële administratie studenten</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Financiële administratie studenten<p></font>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");
  if($ro)
    echo("<a href='". $_SERVER['REQUEST_URI']. "&excel=1'>Download Excel bestand</a><br>");
  else
    echo("<a href='". $_SERVER['REQUEST_URI']. "?excel=1'>Download Excel bestand</a><br>");
  $grpselfld->show();
  // Javascript to remove all items in the group selection that need not be there
?>
  <SCRIPT>
  var selform = document.getElementById("gselectfld");
  for(var i=0; i<selform.length; i++)
  {
    if((selform.options[i].text.substr(0,2) != "OS" || selform.options[i].text.length > 3) &&
	   (selform.options[i].text.substr(0,2) != "SN" || selform.options[i].text.length > 3) &&
	   (selform.options[i].text.substr(0,2) != "wg") &&
	   (selform.options[i].text != "AHATotaal") &&
	   (selform.options[i].text != "VWO") &&
	   (selform.options[i].text.substr(0,6) != "NW<? echo date("Y"); ?>")
	  )
	{
	  selform.remove(i);
	  i--;
	}
  }
  </SCRIPT>

<?

  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><th>#</th><th>Achternaam</th><th>Voornamen</th><th><center># vakken</center></th><th>Schoolgeld</th><th>Betaald</th>
            <TH>d.d.</TH><TH>&nbsp;</th><th>Nabetaald</th><th>d.d.</th><th>Saldo</th><th>Restitutie</th><th>Cheque# restitutie</th>");
  for($i=1; $i<=3; $i++)
    echo("<th>Beloning ". $i. "</th><th>Cheque# beloning ". $i. "</th>");

  echo("<th>Opmerkingen</th>");
  echo("<th>Factuur</th>");
  echo("</tr>");
  
  // Reset totals
  $totschoolgeld = 0.0;
  $totbetaald = 0.0;
  $totnabetaald = 0.0;
  $totsaldo = 0.0;
  $totrestitutie = 0.0;
  $totbeloning[1] = 0.0;
  $totbeloning[2] = 0.0;
  $totbeloning[3] = 0.0;

  // Create a row in the table for each student
  $negix = 0;
	$snum=1;

  foreach($students AS $student)
  { 
   if(isset($student))
   {
    $sid=$student->get_id();
    echo("<tr><td>". $snum++. "</td><td>". $student->get_student_detail("*student.lastname"). "</td><td>". $student->get_student_detail("*student.firstname"). "</td>");
    // We need to show the amount of subjects, excluding I&S and Pfw.
	$subjcntq = "SELECT COUNT(DISTINCT mid) AS `subjcnt` FROM sgrouplink LEFT JOIN class USING(gid) LEFT JOIN subject USING(mid) WHERE sid=". $sid;
	foreach($xxsubjects AS $xsub)
	{
	  $subjcntq .= " AND shortname <> '". $xsub. "'";
	}
	$subjcntq .= " GROUP BY sid";
	$subjcnt = inputclassbase::load_query($subjcntq);
	$subjcnt = $subjcnt['subjcnt'][0];
	echo("<td><center>". $student->get_student_detail("s_vakken"). "~". $subjcnt. "</center></td>");
	
	$regfeefld = new inputclass_amount("regfee". $sid,6,NULL,"data","s_inschrijfgeld",$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");
	echo("<td>");
	if($ro)
	  echo($regfeefld->__toString());
	else
	  $regfeefld->echo_html();
	echo("</td>");
	$totschoolgeld += $regfeefld->__toString();
	$paidfld = new inputclass_amount("paid". $sid,6,NULL,"data","s_betaling_student_". $yearsuffx,$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");
	echo("<td>");
	if($ro)
	  echo($paidfld->__toString());
	else
	  $paidfld->echo_html();
	echo("</td>");
	$totbetaald += $paidfld->__toString();
	$paidddfld = new inputclass_datefield("paiddd". $sid,NULL,NULL,"data","s_betaaldatum_student_". $yearsuffx,$sid,"sid",NULL,"datahandler.php");
	echo("<td>");
	if($ro)
	  echo($paidddfld->__toString());
	else
	  $paidddfld->echo_html();
	echo("</td><TD>&nbsp;</td>");

	$paidfld2 = new inputclass_amount("paidx". $sid,6,NULL,"data","s_betaling2_student_". $yearsuffx,$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");
	echo("<td>");
	if($ro)
	  echo($paidfld2->__toString());
	else
	  $paidfld2->echo_html();
	echo("</td>");
	$totnabetaald += $paidfld2->__toString();
	$paidddfld2 = new inputclass_datefield("paidddx". $sid,NULL,NULL,"data","s_betaaldatum2_student_". $yearsuffx,$sid,"sid",NULL,"datahandler.php");
	echo("<td>");
	if($ro)
	  echo($paidddfld2->__toString());
	else
	  $paidddfld2->echo_html();
	echo("</td>");

	// The saldo, calculate and put in a RO field with unique ID so script can change it.
	$paybackfld = new inputclass_amount("payback". $sid,6,NULL,"data","s_terugbetaling_". $yearsuffx,$sid,"sid","\" onBlur=\"calcsaldo(". $sid. ")","datahandler.php");
	$saldo = number_format($paidfld->__toString() + $paidfld2->__toString() - $regfeefld->__toString() - $paybackfld->__toString(),2);
	if($ro)
	  echo("<td>". $saldo. "</td>");
	else
	  echo("<td><INPUT TYPE=TEXT SIZE=6 READONLY ID='saldo". $sid. "' VALUE='". $saldo. "'></td>");
	$totsaldo += $saldo;
	echo("<td>");
	if($ro)
	  echo($paybackfld->__toString());
	else
	  $paybackfld->echo_html();
	echo("</td>");
	$totrestitutie += $paybackfld->__toString();
	$cheqfld = new inputclass_textfield("cheq". $sid,6,NULL,"data","s_cheque_terugbetaling_". $yearsuffx,$sid,"sid","","datahandler.php");
	echo("<td>");
	if($ro)
	  echo($cheqfld->__toString());
	else
	  $cheqfld->echo_html();
	echo("</td>");
	
	for($i=1; $i<=3; $i++)
	{
	  $rewardfld = new inputclass_amount("reward". $i. "_". $sid,6,NULL,"data","s_beloning_". $i. "_". $yearsuffx,$sid,"sid","","datahandler.php");
	  echo("<td>");
	  if($ro)
	    echo($rewardfld->__toString());
	  else
	    $rewardfld->echo_html();
	  echo("</td>");
	  $totbeloning[$i] += $rewardfld->__toString();
	  $rewardcfld = new inputclass_textfield("rewardc". $i. "_". $sid,6,NULL,"data","s_beloning_". $i. "_cheque_". $yearsuffx,$sid,"sid","","datahandler.php");
	  echo("<td>");
	  if($ro)
	    echo($rewardcfld->__toString());
	  else
	    $rewardcfld->echo_html();
	  echo("</td>");
	}
	$remarkfld = new inputclass_textfield("finremark". $sid,20,NULL,"data","s_fin_opmerkingen_". $yearsuffx,$sid,"sid",NULL,"datahandler.php");
	echo("<td>");
	if($ro)
	  echo($remarkfld->__toString());
	else
	  $remarkfld->echo_html();
	echo("</td>");

	
	
	// Show the download letter icon with link
	echo("<td><a href='". $_SERVER['REQUEST_URI']. "?print=". $sid. "'><img src='". $pngsource. "/download.png' BORDER=0></a></td>");
	
	echo("</tr>");
   }
  }
  echo("<tr><th colspan=4>Totalen</th><th>". number_format($totschoolgeld,2). "</th><th>". number_format($totbetaald,2). "</th>
            <TH>&nbsp;</TH><TH>&nbsp;</TH><th>". number_format($totnabetaald,2). "</th><th>&nbsp;</th><th>". number_format($totsaldo,2). "</th><th>". number_format($totrestitutie,2). "</th><th>&nbsp;</th>");
  for($i=1; $i<=3; $i++)
    echo("<th>". number_format($totbeloning[$i],2). "</th><th>&nbsp;</th>");

  echo("<th>&nbsp;</th>");
  echo("<th>&nbsp;</th>");
  echo("</tr>");
  echo("</table>");
  echo '<a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  // Add script to deal with change in values (recalculate onscreen)
?>
  <SCRIPT>
  function calcsaldo(sid)
  {
    fee = document.getElementsByName("regfee" + sid)[0].value;
    paid = document.getElementsByName("paid" + sid)[0].value;
    paid2 = document.getElementsByName("paidx" + sid)[0].value;
    payback = document.getElementsByName("payback" + sid)[0].value;
    saldofld = document.getElementById("saldo" + sid);
	saldofld.value = ((paid - -1.0 * paid2) - fee - payback).toFixed(2);
  }
  </SCRIPT>
<?
  // close the page
  echo("</html>");
  
  // Function to create a table if it does not exist
  function create_data_table($tablename,$userlink)
  {
    $sqlquery = "CREATE TABLE IF NOT EXISTS `". $tablename. "` (
      `sid` INTEGER(11) NOT NULL,
      `data` TEXT,
	  PRIMARY KEY (`sid`)
      ) ENGINE=InnoDB;";
    mysql_query($sqlquery,$userlink);
    echo(mysql_error());
  }
?>
