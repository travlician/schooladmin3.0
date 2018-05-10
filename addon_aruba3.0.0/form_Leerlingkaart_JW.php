<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("student.php");
  require_once("inputlib/inputclasses.php");
  inputclassbase::dbconnect($userlink);
  // create table formLLlistSEQ which contains subject sequences if does not exit already
  $sqlquery = "CREATE TABLE IF NOT EXISTS `formLLlistSEQ` (
    `sseqid` INTEGER(11) NOT NULL AUTO_INCREMENT,
	`mid` INTEGER(11),
	`highlight` INTEGER(1) DEFAULT 0,
	`separate` INTEGER(1) DEFAULT 0,
	`heading` TEXT DEFAULT NULL,
	PRIMARY KEY (`sseqid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  // Array with possible behaviour aspects
  $aspects = array('Inz/Mot' => 'Inzet/motivatie','Conc' => 'Concentratie', 'Werkverz' => 'Werkverzorging', 'HWerk' => 'Huiswerkattitude','Omgaan ARP' => 'Omgaan ARP', 'Tempo' => 'Tempo', 'SocGedr' => 'Sociaal gedrag', 'Vlijt' => 'Vlijt');

  // If no items in table formLLListSEQ or GET['editseq'] is set, we edit the list of sequenced subject!
  $slinkq = "SELECT sseqid,CONCAT(IF(separate=1,'@',''),IF(highlight=1 AND separate=0,'*',''),IF(heading IS NOT NULL,CONCAT('{',heading,'}'),''),IF(fullname IS NOT NULL,fullname,'-')) AS subjname,shortname
			 FROM formLLlistSEQ LEFT JOIN subject USING(mid) ORDER BY sseqid";
  $slinkqr = inputclassbase::load_query($slinkq);
  if(!isset($slinkqr) || isset($_GET['editseq']))
  { // Edit sequence of subjects for student schoolcard in stead of showing the schoolcards.
    echo("<html><head><title>Leerlingkaart</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_LLOverzicht.css" title="style1">';
	echo("<h1>Instelling volgorde en layout vakken Leerlingkaart</H1>");
	echo("<table><TR><TH>#</TH><TH>Vak</TH><TH>Afscheiden</TH><TH>Vet</TH><TH>Heading</TH></TR>");
	$subjqry = "SELECT 0 AS id, '' AS tekst UNION SELECT mid,shortname FROM subject ORDER BY tekst";
	if(isset($slinkqr))
	{ // Show existing entries for edit
	  foreach($slinkqr['sseqid'] AS $seqid)
	  {
	    echo("<TR><TD>". $seqid. "</td><TD>");
        $subjfield = new inputclass_listfield("subjfld". $seqid,$subjqry,$userlink,"mid","formLLlistSEQ",$seqid,"sseqid","","datahandler.php");
	    $subjfield->echo_html();
	    echo("</td><TD>");
        $sepfield = new inputclass_checkbox("sepfld". $seqid,0,$userlink,"separate","formLLlistSEQ",$seqid,"sseqid","","datahandler.php");
	    $sepfield->echo_html();
	    echo("</td><TD>");
        $hlfield = new inputclass_checkbox("hlfld". $seqid,0,$userlink,"highlight","formLLlistSEQ",$seqid,"sseqid","","datahandler.php");
	    $hlfield->echo_html();
	    echo("</td><TD>");
        $hdfield = new inputclass_textfield("hdfld". $seqid,0,$userlink,"heading","formLLlistSEQ",$seqid,"sseqid","","datahandler.php");
	    $hdfield->echo_html();
	    echo("</td></tr>");	
	  }
	}
	// Show new entry
	echo("<TR><TD><A href='form_Leerlingkaart_AVO.php?editseq=1'><IMG SRC='PNG/action_add.png'></A></td><TD>");
    $subjfield = new inputclass_listfield("subjfld0",$subjqry,$userlink,"mid","formLLlistSEQ",0,"sseqid","","datahandler.php");
	$subjfield->echo_html();
	echo("</td><TD>");
    $sepfield = new inputclass_checkbox("sepfld0",0,$userlink,"separate","formLLlistSEQ",0,"sseqid","","datahandler.php");
	$sepfield->echo_html();
	echo("</td><TD>");
    $hlfield = new inputclass_checkbox("hlfld0",0,$userlink,"highlight","formLLlistSEQ",0,"sseqid","","datahandler.php");
	$hlfield->echo_html();
	echo("</td><TD>");
    $hdfield = new inputclass_textfield("hdfld0",0,$userlink,"heading","formLLlistSEQ",0,"sseqid","","datahandler.php");
	$hdfield->echo_html();
	echo("</td></tr>");	
	echo("</table>");
	echo("</body></html>");
    exit;
  }
  
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  
  foreach($slinkqr['subjname'] AS $sbix => $sname)
  {
    $subjlinks[$sname] = $slinkqr['shortname'][$sbix];
  }
  // Translation of subjects to subject short names, also giving sequence for subjects. A * in front of despription indicates a bolded subject, @ indicates bold and fat topline, - indicates ignore
//  $subjlinks = array("@NEDERLANDSE TAAL" => "ne", "Dictee" => "dc", "Taaloefeningen" => "to", "Tekst" => "tk","-endnl" => "",
//                     "*LEZEN (technisch)" => "le", "AVI-niveau" => "avi", "Begrijpend" => "ble", "-endle" => "",
//					 "*REKENEN" => "re", "Getallen/bewerking" => "gb", "Verh/Breuken/Proc." => "br", "Meten/Meetk" => "mm", "Tijd" => "td", "Geld" => "gld", 
//					 "Tabellen/Grafieken" => "tg", "-endre" => "",
//					 "*AARDRIJKSKUNDE" => "ak", "*GESCHIEDENIS" => "gs", "*KENNIS DER NATUUR" => "kdn", "-endmain" => "",
//					 "@Godsdient" => "go", "Schrijven" => "sc", "Verkeer" => "vk", "Engels" => "en", "Spaans" => "sp", "Papiamento" => "pa", "Maatschappijleer" => "ml",
//					 "Bewegingsonderwijs" => "lo", "Handvaardigheid" => "hv", "Tekenen" => "te", "Muziek" => "mu", "-endall" => "");
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  // Create a translation from subject shortname to mid
  $ssn2midqr = inputclassbase::load_query("SELECT shortname,mid FROM subject");
  foreach($ssn2midqr['mid'] AS $suix => $smid)
    $ssn2mid[$ssn2midqr['shortname'][$suix]] = $smid;
  echo ('<LINK rel="stylesheet" type="text/css" href="style_WLLKaart_BO_SKOA.css" title="style1">');
  
  // Schoolverlaters klassen
  $rshv = SA_loadquery("SELECT sid,klas FROM bo_jaarresult_data WHERE result='S.V.'");
  if(isset($rshv))
    foreach($rshv['sid'] AS $six => $sid)
    {
      $eindklas[$sid] = $rshv['klas'][$six];
    }

  // $rshv['sid'] = array(1 => 609, 712);	OF $rshv[$sid][1] = 609; $rshv['sid'][2] = 712;
  // $rshv['klas'] = array(1 => '1','6B', 78=> '3C');
  // Functions

  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  $LLlijst = student::student_list();
  
  IF(isset ($LLlijst))
 {
 // Voorkant leerlingenkaart:
    echo("<html><head><title>Leerlingkaart</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_LLOverzicht.css" title="style1">';
	
    foreach ($LLlijst AS $student)
	{
//	De opmaak van de "minitabel rechtsboven:
	  echo("<div class = rechtsboven> <table class = minitabel>
											<tr><td>Lln. nr. school: ". $student->get_student_detail("*sid"). "</td></tr>");
	  if($student->get_student_detail("s_skoanr") != "")									
	    echo("<tr><td>S.K.O.A. nr.: ". $student->get_student_detail("s_skoanr"). "</td></tr>");
	  else if($student->get_student_detail("s_spcoanr") != "")									
	    echo("<tr><td>S.P.C.O.A. nr.: ". $student->get_student_detail("s_spcoanr"). "</td></tr>");
	  echo("<tr class = zonderlijnen><td>&nbsp;</td></tr>
											<tr class = zonderlijnen><td>ID-nr: ". $student->get_student_detail("s_ASIdNr"). "</td></tr>
											<tr class = pasfoto><td>". $student->get_student_detail("s_foto"). "</td></tr>
										</table>
			  </div>");
/*																 
	Een veld opvragen van een lln (b.v. Adres)
	$student->get_student_detail("s_Adres");
	Huidige klas als tekst: $student->get_group()->get_groupname();
	Huidige mentor naam: $student->get_group()->get_mentor()->get_teacher_detail("*teacher.firstname"). " ".$student->get_group()->get_mentor()->get_teacher_detail("*teacher.lastname");
	Aantal keer te laat in 2010-2011:
	$aw = SA_loadquery("SELECT COUNT(asid) AS afwezig FROM absence LEFT JOIN absencereasons USING(aid) WHERE date > '2010-08-01' AND date < '2011-07-15' AND acid=1 AND sid=". $student->get_id());
	$afwezig = $aw['afwezig'][1];
	
*/																 
/* 	Paragraaf over de gegevens van de leerling:	*/
		echo("<P class = koptxt>Gegevens van de leerling</P>");
		echo("<P class = LLdata>
				Achternaam: ". $student->get_lastname(). "<br>
				Voornamen:&nbsp;&nbsp;". $student->get_firstname(). "<br>
				Geb. Dat. <span class = kleineltrs>(j/m/d): </span><span class = GebDatumLL>". $student->get_student_detail("s_ASBirthDate"). "</span>
				Geb. Plaats: <span class = GebPlaatsLL>". ($student->get_student_detail("s_ASBirthPlace") != "" ? $student->get_student_detail("s_ASBirthPlace") : $student->get_student_detail("s_ASBirthCountry")). "</span><br></P>");

/* 				voertaal staat op een aparte regel, los van de andere tekst, sinds 22-10-2013 niet meer!*/
		echo("<P class = LLdata>
				Sexe:&nbsp;&nbsp;<span class = SexeLL>". $student->get_student_detail("s_ASGender"). "</span>
				<span class = VoertaalLL>Voertaal: ". $student->get_student_detail("s_ASHomeLanguage"). "</span>
				Nat.: ". $student->get_student_detail("s_ASNationality"). "<br>
				Kerkgezindte: ". $student->get_student_detail("s_ASReligion"). "<br></P>");

/* 				Tel./ mob. staat op een aparte regel, los van de andere tekst, sinds 22-10-2013 niet meer!	*/
		echo("<div class = LLdata>
				<SPAN class = AdresLL>Adres: ". $student->get_student_detail("s_ASAddress"). "</SPAN>Tel. / Mob.:". 
				$student->get_student_detail("s_ASPhoneHomeStudent"). "<br>
				<span class = LinkerMarge2>Tel. / Mob.: ". $student->get_student_detail("s_ASPhoneMobileStudent"). "<br>
				<SPAN class = AZVnrLL>AZV-nr: ". $student->get_student_detail("s_ASMedicalInsuranceNumber"). "</SPAN>
						<SPAN class = HuisartsLL>Huisarts: ". $student->get_student_detail("s_ASHomeMedic"). "</SPAN>
						Tel.: ". $student->get_student_detail("s_Tel_Huisarts"). "<br>
				<span class = AZVnrLL>Medische Info: </SPAN> <SPAN class = TandartsLL>Tandarts: ". $student->get_student_detail("s_Tandarts"). "</SPAN>
						Tel.: ". $student->get_student_detail("s_Tel_Tandarts"). "</div>
				<span class = kleineltrs>(ziekte, allergie, gebruik van medicamenten, etc.)</span><BR>". $student->get_student_detail("s_ASMedicalProblems"));
		echo("<br><br><br>");	
	
/* 	Paragraaf over de gegevens van de ouders:	*/
		echo("<div class = koptxt>Gegevens van de ouders</div>");
		echo("<table class = GegTblOuders>
				<tr><td colspan=2>Burgelijke staat: ");
	// Hier komt echt een zooitje, voor burgerlijke staat wordo ingevuld : D,G,O,S,W,Gehuwd,Gescheiden,ongehuwd,samenwonend of Weduwe.
	    $bstaat = $student->get_student_detail("s_ASCivilStateFamily");
		if($bstaat == "G" || $bstaat == "Gehuwd")
		  echo("<SPAN class=omcirkel>gehuwd</SPAN>");
		else
		  echo("gehuwd");
		echo(" - ");
		if($bstaat == "S" || $bstaat == "samenwonend")
		  echo("<SPAN class=omcirkel>samenwonend</SPAN>");
		else
		  echo("samenwonend");
		echo(" - ");
		if($bstaat == "D" || $bstaat == "Gescheiden")
		  echo("<SPAN class=omcirkel>gescheiden</SPAN>");
		else
		  echo("gescheiden");
		echo(" - ");
		if($bstaat == "O" || $bstaat == "ongehuwd")
		  echo("<SPAN class=omcirkel>alleenstaande (m/v)</SPAN>");
		else
		  echo("alleenstaande (m/v)");
		echo(" - ");
		if($bstaat == "W" || $bstaat == "Weduwe" || $bstaat == "Weduwnaar")
		  echo("<SPAN class=omcirkel>weduw(e)</SPAN>");
		else
		  echo("weduw(e)");
        echo("</td></tr>
				<tr><td>Naam (pleeg)vader: ". $student->get_student_detail("s_ASLastNameParent1"). ", ". $student->get_student_detail("s_ASFirstNameParent1"). "</td>
				<td>Naam (pleeg)moeder: ". $student->get_student_detail("s_ASLastNameParent2"). ", ". $student->get_student_detail("s_ASFirstNameParent2"). "</td></tr>
				<tr><td>Adres: ". $student->get_student_detail("s_ASAddressParent1"). "</td>
				<td>Adres: ". $student->get_student_detail("s_ASAddressParent2"). "</td></tr>
				<tr><td>Beroep: ". $student->get_student_detail("s_Beroep_vader"). "</td>
				<td>Beroep: ". $student->get_student_detail("s_Beroep_moeder"). "</td></tr>
				<tr><td>Werkzaam bij: ". $student->get_student_detail("s_ASEmployerParent1"). "</td>
				<td>Werkzaam bij: ". $student->get_student_detail("s_ASEmployerParent2"). "</td></tr>
				<tr><td>Tel.: ". $student->get_student_detail("s_ASPhoneWorkParent1"). "/". $student->get_student_detail("s_ASPhoneHomeParent1"). "/". $student->get_student_detail("s_ASPhoneMobileParent1"). "</td>
				<td>Tel.: ". $student->get_student_detail("s_ASPhoneWorkParent2"). "/". $student->get_student_detail("s_ASPhoneHomeParent2"). "/". $student->get_student_detail("s_ASPhoneMobileParent2"). "</td></tr>");
		echo("<tr><td colspan=2>Verzorger / voogd: <SPAN class = VoogdLL>". $student->get_student_detail("s_ASResponsablePerson"). "</SPAN>
				Adres: <SPAN class = AdresvoogdLL>". $student->get_student_detail("s_ASAddressResponsablePerson"). "</SPAN>
				Tel.: ". $student->get_student_detail("s_ASMobilePhoneResponsablePerson"). "</td></tr>");
		echo("</table>");

		echo("<br>");	

		echo("<table class = GegDatInschrijving>
				<tr><td>Dat. Inschr. : <SPAN class = DatumInschrLL>". $student->get_student_detail("s_datum_inschrijving"). "</SPAN>
					Klas: <SPAN class = KlasLL>". $student->get_student_detail("s_inschrijving_klas"). "</SPAN>
					Afkomstig van: ". $student->get_student_detail("s_ASPrimarySchool"). "<br>
				Dat. Uitschr.: <SPAN class = DatumUitschrLL>". $student->get_student_detail("s_datum_uitschrijving"). "</SPAN>
					Klas: <SPAN class = KlasLL>". $student->get_student_detail("s_uitschrijving_klas"). "</SPAN>
				Reden:<SPAN class = DatumUitschrLL>". $student->get_student_detail("s_reden_uitschrijving"). "</SPAN>
					Naar: ". $student->get_student_detail("s_doorstroming_vo"). "<br>
				&nbsp;</tr>
			</table>");

		echo("<br>");	
		echo("Advies hoofd: <SPAN class=advieshoofd>". $student->get_student_detail("s_advies_hoofd"). "</SPAN><SPAN class=wensspacer>&nbsp;</SPAN>");
		echo("Wens ouders: <SPAN class=wensouders>". $student->get_student_detail("s_wens_ouders"). "</SPAN>");
		echo("<br><br>");	
		echo("Schoolcarri&egrave;re B.O.: <u>&nbsp;". $student->get_student_detail("s_jaren_BO"). " </u> jr.<SPAN class=jarenspacer>&nbsp;</SPAN>AVO: ");
		// Laat elk leerjaar zien , onderstreept met spaties voor en achter en gevolgd door puntcomma
		// Welke jaren zijn er cijfers:
		$yearresults = inputclassbase::load_query("SELECT year,klas,data,result FROM bo_jaarresult_data LEFT JOIN `". $teachercode. "` ON (mentor=tid) 
		                                           WHERE sid=". $student->get_id(). " ORDER BY year");
		$klasyears = inputclassbase::load_query("SELECT year,groupname FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid)
		                                         LEFT JOIN sgroup USING(gid) WHERE active=1 AND sid=". $student->get_id(). " AND groupname LIKE '__' GROUP BY year,groupname DESC ORDER BY year");
		//foreach($klasyears['year'] AS $kyix => $yr)
		//  echo("<BR>". $yr. " - ". $klasyears['groupname'][$kyix]);
		$gradesqr = inputclassbase::load_query("SELECT year,period,mid,result FROM gradestore WHERE sid=". $student->get_id(). " UNION SELECT '2013-2014',0,0,NULL ORDER BY year");
		unset($grades);
		// Convert grades query result ta a handy array
		if(isset($gradesqr))
		foreach($gradesqr['result'] AS $rix => $result)
		{
			if($gradesqr['year'][$rix] != '') // Skip empty years
				$grades[$gradesqr['year'][$rix]][$gradesqr['period'][$rix]][$gradesqr['mid'][$rix]] = $result;
		}
		// Fix for JW: since an entry was added with dummy values for 2013-2014 (using ScolPaNos), remove it if no data is present for 2012-2013
		if(!isset($grades['2012-2013']))
			unset($grades['2013-2014']);
		
		unset($houding);
		$houdingqr = inputclassbase::load_query("SELECT * FROM avo_pk_data WHERE sid=". $student->get_id());
		if(isset($houdingqr))
		  foreach($houdingqr['xstatus'] AS $hix => $hres)
		  {
		    $houding[$houdingqr['year'][$hix]][$houdingqr['period'][$hix]][$houdingqr['aspect'][$hix]] = $hres;
		  }
		else
		  $houding=0;
		
		unset($yeardata);
		if(isset($grades))
			foreach($grades AS $year => $dummy)
			{
				if(isset($yearresults['year']))
				foreach($yearresults['year'] AS $yrix => $ryear)
				{
					if($year == $ryear)
					{
						$yeardata[$year]['klas'] = $yearresults['klas'][$yrix];
						$yeardata[$year]['teacher'] = $yearresults['data'][$yrix];
						$yeardata[$year]['result'] = $yearresults['result'][$yrix];
					}
				}
				if(!isset($yeardata[$year]['klas']))
				{
					if(isset($klasyears['groupname']))
					{
						foreach($klasyears['year'] AS $kyix => $kyear)
						{
							if($kyear == $year && $klasyears['groupname'][$kyix] != "" && $year != '2009-2010')
								$yeardata[$year]['klas'] = $klasyears['groupname'][$kyix];
						}
					}
				}
				if(!isset($yeardata[$year]) && $year == $schoolyear)
				{ // No data found for current year in year results, so get it from current setting
					$mygroup = new group();
				  $mygroup->load_current();
					$yeardata[$year]['klas'] = $mygroup->get_groupname();
				  $yeardata[$year]['teacher'] = $mygroup->get_mentor()->get_teacher_detail($teachercode);
				}
			}
		$yearcount = 0;
		if(isset($grades))
		foreach($grades AS $jaar => $dummy)
		{
		  if(isset($yeardata[$jaar]['klas']))
		    echo("<SPAN class=bojaar>". substr($yeardata[$jaar]['klas'],0,1). "</SPAN>;");
		  else
		    echo("<SPAN class=bojaar>?</SPAN>;");
		  $yearcount++;
		}
		echo("<br><br>");	

		echo("<table class = KopTblKlas>
				<tr><td class = breedte1>KLAS</td><td class = breedte2>&nbsp;SCHOOLJAAR&nbsp;</td><td class = breedte3>OPMERKINGEN / BIJZONDERHEDEN</td></tr>");
		if(isset($grades))
		foreach($grades AS $jaar => $dummy)
		{
		  echo("<tr><td><center>");
		  echo(isset($yeardata[$jaar]['klas']) ? $yeardata[$jaar]['klas'] : "&nbsp;");
		  echo("</center></td><td><center>". $jaar. "</center></td><td>");
		  echo(isset($yeardata[$jaar]['result']) ? $yeardata[$jaar]['result'] : "&nbsp;");
		  echo("</td></tr>");
		}
		for($filrow = 0; $filrow < (8-$yearcount); $filrow++)
		  echo("<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>");
		echo("</table>");
	
		//echo("<div class = SchoolStempel>Schoolstempel:</div>");
		echo("<div class = SchoolStempel>&nbsp;</div>");

		// Now the results table has 7 positions and we need to set each year to a certain position. 
		// 1: first time first year
		// 2: second time first year or second year if not repeated first year
		// 3: second year if repeated first year or send year else third year
		// 4: third year if repeated first, second or third year.
		// 5: forth year
		// 6: fourth year if repeated, else fifth year
		// 7: fifth year if repeated fourth or fifth year, else sixth year
		// 8: sixth year if repeated fourth, fifth or sixth year.
		// Problem is, the entries can be unkknown!
		unset($tablepos);
		unset($firstposa);
		unset($firstsyear);
		unset($secondsyear);
		// First resolve what we know
		if(isset($grades))
			foreach($grades AS $jaar => $dummy)
			{
				if(isset($yeardata[$jaar]['klas']))
				{ // It's known which klas
					$syear = substr($yeardata[$jaar]['klas'],0,1);
					$tpos = $syear;
					if(isset($tablepos[$tpos]))
						$tpos++;
					$tablepos[$tpos] = $jaar;	
					//echo("Placed year ". $jaar. " at position ". $tpos. "<BR>");
				}
			}
		// Now resolve what we don't know
		if(isset($grades))
			foreach($grades AS $jaar => $dummy)
			{
				//echo("Processing year ". $jaar. "<BR>");
				if(!isset($yeardata[$jaar]['klas']))
				{ // It's NOT known which klas
			    //echo("Year is not placed so far<BR>");
					// Get the first available table position
					unset($placebefore);
					unset($placeafter);
					foreach($tablepos AS $tposu => $tposy)
					{
						//echo("Checking table position ". $tposu. " now holding year ". $tposy. ", to place ". $jaar. "<BR>");
						if($tposy < $jaar)
						{
							if(!isset($placeafter) || $tposu > $placeafter)
							{
								$placeafter=$tposu;
								//echo("Placeafter set to ". $placeafter. "<BR>");
							}
						}
						else
						{
							if(!isset($placebefore) || $tposu < $placebefore)
							{
								$placebefore= $tposu;
								//echo("Placebefore set to ". $placebefore. "<BR>");
							}
						}
					}
					//echo("Place before ". (isset($placebefore) ? $placebefore : 'undefined'). " and after ". (isset($placeafter) ? $placeafter : 'undefined'). "<BR>");
					if(isset($placeafter))
					{ // We know more or less where to place it, namely directly after the one we got as place after
						$placeat = $placeafter+1;
					}
					else if(isset($placebefore))
					{ // Put it right before the one that is set to be placed before
						$placeat = $placebefore-1;
						if($placeat == 0)
							$placeat = 1;
					}
					else // This is like a "put it wherever you want" so we put it in the middle
					  $placeat=4;
					//echo("Decided to place at ". $placeat. "<BR>");
					// If the position to place at is used, we shift the ones occupied up
					if(isset($tablepos[$placeat]))
					{ // We need to shift the positions up!
						//echo("Shifting up<BR>");
						for($spos = 7; $spos >= $placeat;$spos--)
						{
							if(isset($tablepos[$spos]))
								$tablepos[$spos + 1] = $tablepos[$spos];
							else
								unset($tablepos[$spos+1]);
						}
					}
					// Now fill put out entry in
					$tablepos[$placeat] = $jaar;
				}
			} 
				
		echo("<table class = TblPropRap>
				<tr><td ID=RapOverzKol1 colspan=7>Leerl: ". $student->get_firstname(). " ". $student->get_lastname(). "</td></tr>
				<tr class = Dikte4LijnTop><td>Schooljaar :</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[1]) ? $tablepos[1] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[2]) ? $tablepos[2] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[3]) ? $tablepos[3] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[4]) ? $tablepos[4] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[5]) ? $tablepos[5] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[6]) ? $tablepos[6] : "&nbsp;"). "</td>
				<td class = SchooljaartabelLR colspan=4>". (isset($tablepos[7]) ? $tablepos[8] : "&nbsp;"). "</td>
				</tr>
				<tr><td class = TxtLinks>Klas :</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[1]) ? (isset($yeardata[$tablepos[1]]['klas']) ? $yeardata[$tablepos[1]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[2]) ? (isset($yeardata[$tablepos[2]]['klas']) ? $yeardata[$tablepos[2]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[3]) ? (isset($yeardata[$tablepos[3]]['klas']) ? $yeardata[$tablepos[3]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[4]) ? (isset($yeardata[$tablepos[4]]['klas']) ? $yeardata[$tablepos[4]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[5]) ? (isset($yeardata[$tablepos[5]]['klas']) ? $yeardata[$tablepos[5]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[6]) ? (isset($yeardata[$tablepos[6]]['klas']) ? $yeardata[$tablepos[6]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLR colspan=4>". (isset($tablepos[7]) ? (isset($yeardata[$tablepos[8]]['klas']) ? $yeardata[$tablepos[8]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				</tr>
				<tr class = KleurRij><td>Rapport</td><td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td>
				  <td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td>
				  <td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td>
				  <td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td>
				  <td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td>
				  <td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td>
				  <td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td class = DikkelijnRechts>E</td></tr>");

				foreach($subjlinks AS $naam => $code)
				  print_grades($naam,$code,$tablepos,$grades);
				echo("
				<tr class = Dikte4LijnTop><td class = vet>PERSOONLIJKE KWALITEITEN</td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnRechts></td></tr>");
				foreach($aspects AS $asix => $astxt)
				  print_houding($astxt,$asix,$tablepos,$houding);
				echo("
				<tr class = Dikte3LijnTop><td>Afwezig</td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnRechts></td></tr>
				<tr><td>Te laat</td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnRechts></td></tr>
				<tr><td>&nbsp;</td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td></td>
				  <td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnRechts></td></tr>");

				echo("</table>");
				echo("<SPAN class=pagebreak>&nbsp;</SPAN>");


			
	} // einde foreach student

echo("<br><br><br>");


echo("<br><br><br>");
	
  } // Endif 1
    
  // close the page
  echo("</html>");
  function print_houding($naam,$code,$tablepos,$houding,$seccode = NULL)
  {
    echo("<tr><td>". $naam. "</td>");
	for($tp=1;$tp<=7;$tp++)
	{
	  echo("<td class = DikkelijnLinksRC>");
	  if(isset($tablepos[$tp]) && isset($houding[$tablepos[$tp]][1][$code]) && ($seccode == NULL || isset($houding[$tablepos[$tp]][1][$seccode])))
	    echo($houding[$tablepos[$tp]][1][$code] .(isset($seccode) ? $houding[$tablepos[$tp]][1][$seccode] : ""));
	  else
	    echo("&nbsp;");
	  echo("</td><td class=RCell>");
	  if(isset($tablepos[$tp]) && isset($houding[$tablepos[$tp]][2][$code]) && ($seccode == NULL || isset($houding[$tablepos[$tp]][2][$seccode])))
	    echo($houding[$tablepos[$tp]][2][$code] .(isset($seccode) ? $houding[$tablepos[$tp]][2][$seccode] : ""));
	  else
	    echo("&nbsp;");
	  echo("</td><td class=RCell>");
	  if(isset($tablepos[$tp]) && isset($houding[$tablepos[$tp]][3][$code]) && ($seccode == NULL || isset($houding[$tablepos[$tp]][3][$seccode])))
	    echo($houding[$tablepos[$tp]][3][$code] .(isset($seccode) ? $houding[$tablepos[$tp]][3][$seccode] : ""));
	  else
	    echo("&nbsp;");
	  echo("</td>");
	    echo("<td class=");
	    if($tp == 7)
		{ // has fat right line
		  echo("DikkelijnRechtsRC>&nbsp;</td></tr>");
		}
		else
		  echo("RCell>&nbsp;</td>");
	}
  }
  function print_grades($naam,$code,$tablepos,$grades)
  {
    global $ssn2mid;
	if(strpos($naam,'{') !== FALSE)
	{
	  $hstart = strpos($naam,'{');
	  $hend = strpos($naam,'}');
	  $header = substr($naam,$hstart+1,$hend-$hstart-1);
	  $naam = substr($naam,0,$hstart). substr($naam,$hend+1);
	  echo("<tr><td colspan=29 class=DikkelijnRechts><B><U>". $header. "</U></B></td></tr>"); 
	}
    if(substr($naam,0,1) == "*")
	  echo("<tr class=KleurRij><td class=vet>". substr($naam,1). "</td>");
	else if(substr($naam,0,1) == "@")
	  echo("<tr class=Dikte3aLijnTop><td class=vet>". substr($naam,1). "</td>");
    else if(substr($naam,0,1) == "-")
      echo("<tr><td>&nbsp;</td>");
    else
      echo("<tr><td>". $naam. "</td>");	
	for($tp=1;$tp<=7;$tp++)
	{
	  echo("<td class = DikkelijnLinksRC>");
	  if(isset($tablepos[$tp]) && isset($ssn2mid[$code]) && isset($grades[$tablepos[$tp]][1][$ssn2mid[$code]]))
	    echo($grades[$tablepos[$tp]][1][$ssn2mid[$code]]);
	  else
	    echo("&nbsp;");
	  echo("</td><td class=RCell>");
	  if(isset($tablepos[$tp]) && isset($ssn2mid[$code]) && isset($grades[$tablepos[$tp]][2][$ssn2mid[$code]]))
	    echo($grades[$tablepos[$tp]][2][$ssn2mid[$code]]);
	  else
	    echo("&nbsp;");
	  echo("</td><td class=RCell>");
	  if(isset($tablepos[$tp]) && isset($ssn2mid[$code]) && isset($grades[$tablepos[$tp]][3][$ssn2mid[$code]]))
	    echo($grades[$tablepos[$tp]][3][$ssn2mid[$code]]);
	  else
	    echo("&nbsp;");
	  echo("</td>");
	    echo("<td class=");
	    if($tp == 7)
		{ // has fat right line
		  echo("DikkelijnRechtsRC>");
		}
		else
		  echo("RCell>");
	    if(isset($tablepos[$tp]) && isset($ssn2mid[$code]) && isset($grades[$tablepos[$tp]][0][$ssn2mid[$code]]))
	      echo($grades[$tablepos[$tp]][0][$ssn2mid[$code]]);
	    else
	      echo("&nbsp;");
	    echo("</td>");
		if($tp == 7)
		  echo("</tr>");
	}
  }
?>
