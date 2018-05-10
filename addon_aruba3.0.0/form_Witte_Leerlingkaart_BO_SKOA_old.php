<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("student.php");
  
  // Translation of subjects to subject short names, also giving sequence for subjects. A * in front of despription indicates a bolded subject, @ indicates bold and fat topline, - indicates ignore
  $subjlinks = array("@NEDERLANDSE TAAL" => "ne", "Dictee" => "dc", "Taaloefeningen" => "to", "Tekst" => "tk","-endnl" => "",
                     "*LEZEN (technisch)" => "le", "AVI-niveau" => "avi", "Begrijpend" => "ble", "-endle" => "",
					 "*REKENEN" => "re", "Getallen/bewerking" => "gb", "Verh/Breuken/Proc." => "br", "Meten/Meetk" => "mm", "Tijd" => "td", "Geld" => "gld", 
					 "Tabellen/Grafieken" => "tg", "-endre" => "",
					 "*AARDRIJKSKUNDE" => "ak", "*GESCHIEDENIS" => "gs", "*KENNIS DER NATUUR" => "kdn", "-endmain" => "",
					 "@Godsdient" => "go", "Schrijven" => "sc", "Verkeer" => "vk", "Engels" => "en", "Spaans" => "sp", "Papiamento" => "pa", "Maatschappijleer" => "ml",
					 "Bewegingsonderwijs" => "lo", "Handvaardigheid" => "hv", "Tekenen" => "te", "Muziek" => "mu", "-endall" => "");
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
    echo("<html><head><title>Witte Leerlingkaart</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_LLOverzicht.css" title="style1">';
	
    foreach ($LLlijst AS $student)
	{
//	De opmaak van de "minitabel rechtsboven:
		echo("<div class = rechtsboven> <table class = minitabel>
											<tr><td>Lln. nr. school: ". $student->get_student_detail("*sid"). "</td></tr>
											<tr><td>S.K.O.A. nr.: ". $student->get_student_detail("skoanr"). "</td></tr>
											<tr class = zonderlijnen><td>&nbsp;</td></tr>
											<tr class = pasfoto><td>&nbsp;</td></tr>
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
				Geb. Dat. <span class = kleineltrs>(j/m/d): </span><span class = GebDatumLL>". $student->get_student_detail("s_Geboortedatum"). "</span>
				Geb. Plaats: <span class = GebPlaatsLL>". $student->get_student_detail("s_geboorteplaats"). "</span><br></P>");

/* 				voertaal staat op een aparte regel, los van de andere tekst	*/
		echo("<P class = LLdata><span class = LinkerMarge1>Voertaal:</span><br>
				Sexe:&nbsp;&nbsp;<span class = SexeLL>". $student->get_student_detail("s_Geslacht"). "</span>
				<span class = VoertaalLL>". $student->get_student_detail("s_voertaal"). "</span>
				Nat.: ". $student->get_student_detail("s_nationaliteit"). "<br>
				Kerkgezindte: ". $student->get_student_detail("s_kerkgezindte"). "<br></P>");

/* 				Tel./ mob. staat op een aparte regel, los van de andere tekst	*/
		echo("<div class = LLdata><span class = LinkerMarge2>Tel.</span> / mob.:<br>
				Adres: <SPAN class = AdresLL>". $student->get_student_detail("s_Adres"). "</SPAN>
					". $student->get_student_detail("s_telefoon_thuis"). "<br>
				AZV-nr: <SPAN class = AZVnrLL>". $student->get_student_detail("s_telefoon_thuis"). "</SPAN>
						Huisarts: <SPAN class = HuisartsLL>". $student->get_student_detail("s_Huisarts"). "</SPAN>
						Tel.: ". $student->get_student_detail("s_Tel_Huisarts"). "<br>
				Medische Info:<span class = tal1spaties>". $student->get_student_detail("s_Medische_info"). "</SPAN> Tandarts: <SPAN class = TandartsLL>". $student->get_student_detail("s_Tandarts"). "</SPAN>
						Tel.: ". $student->get_student_detail("s_Tel_Tandarts"). "</div>
				<span class = kleineltrs>(ziekte, allergie, gebruik van medicamenten, etc.)</span>");
		echo("<br><br><br>");	
	
/* 	Paragraaf over de gegevens van de ouders:	*/
		echo("<div class = koptxt>Gegevens van de ouders</div>");
		echo("<table class = GegTblOuders>
				<tr><td colspan=2>Burgelijke staat: ");
	// Hier komt echt een zooitje, voor burgerlijke staat wordo ingevuld : D,G,O,S,W,Gehuwd,Gescheiden,ongehuwd,samenwonend of Weduwe.
	    $bstaat = $student->get_student_detail("s_burgerlijke_staat");
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
		if($bstaat == "O" || $bstaat == "ongehuwd" || $bstaat == "W" || $bstaat == "Weduwe")
		  echo("<SPAN class=omcirkel>alleenstaande (m/v)</SPAN>");
		else
		  echo("alleenstaande (m/v)");
        echo(" - <i>omcirkel</i></td></tr>
				<tr><td>Naam vader / pleegvader: ". $student->get_student_detail("s_naam_vader"). "</td>
				<td>Naam moeder / pleegmoeder: ". $student->get_student_detail("s_naam_moeder"). "</td></tr>
				<tr><td>Adres: ". $student->get_student_detail("s_adres_vader"). "</td>
				<td>Adres: ". $student->get_student_detail("s_adres_moeder"). "</td></tr>
				<tr><td>Beroep: ". $student->get_student_detail("s_Beroep_vader"). "</td>
				<td>Beroep: ". $student->get_student_detail("s_Beroep_moeder"). "</td></tr>
				<tr><td>Werkzaam bij: ". $student->get_student_detail("s_werkzaam_vader"). "</td>
				<td>Werkzaam bij: ". $student->get_student_detail("s_werkzaam_moeder"). "</td></tr>
				<tr><td>Tel.: ". $student->get_student_detail("s_telefoon_werk_vader"). "</td>
				<td>Tel.: ". $student->get_student_detail("s_telefoon_werk_moeder"). "</td></tr>
			</table>");

		echo("<br>");	
		echo("Verzorger / voogd: <SPAN class = VoogdLL>". $student->get_student_detail("s_Aansprakelijkpers"). "</SPAN>
				Adres: <SPAN class = AdresvoogdLL>". $student->get_student_detail("s_adres_voogd"). "</SPAN>
				Tel.: ". $student->get_student_detail("s_telefoon_voogd"));
// hier snap ik nieets van!!!!!
				echo("<br><br><br>");	

		echo("<table class = GegDatInschrijving>
				<tr><td>Dat. Inschr. : <SPAN class = DatumInschrLL>". $student->get_student_detail("s_datum_inschrijving"). "</SPAN>
					Klas: <SPAN class = KlasLL>". $student->get_student_detail("s_inschrijving_klas"). "</SPAN>
					Afkomstig van: ". $student->get_student_detail("s_Afkomstige_school"). "<br>
				Dat. Uitschr.: <SPAN class = DatumUitschrLL>". $student->get_student_detail("s_datum_afschrijving"). "</SPAN>
					Klas: <SPAN class = KlasLL>". (isset($eindklas[$student->get_id()]) ? $eindklas[$student->get_id()] : ' '). "</SPAN>
				Reden:<SPAN class = DatumUitschrLL>". $student->get_student_detail("s_reden_afschrijving"). "</SPAN>
					Naar: ". $student->get_student_detail("s_doorstroming_vo"). "<br>
				&nbsp;</tr>
			</table>");

		echo("<br>");	
		echo("Advies hoofd: <SPAN class=advieshoofd>". $student->get_student_detail("s_advies_hoofd"). "</SPAN><SPAN class=wensspacer>&nbsp;</SPAN>");
		echo("Wens ouders: <SPAN class=wensouders>". $student->get_student_detail("s_wens_ouders"). "</SPAN>");
		echo("<br><br>");	
		echo("Schoolcarri&egrave;re K.O.: <u>&nbsp;". $student->get_student_detail("s_jaren_KO"). " </u> jr.<SPAN class=jarenspacer>&nbsp;</SPAN>B.O.: ");
		// Laat elk leerjaar zien , onderstreept met spaties voor en achter en gevolgd door puntcomma
		// Welke jaren zijn er cijfers:
		$yearresults = inputclassbase::load_query("SELECT * FROM bo_jaarresult_data LEFT JOIN `". $teachercode. "` ON (mentor=tid) WHERE sid=". $student->get_id(). " ORDER BY year");
		$gradesqr = inputclassbase::load_query("SELECT * FROM gradestore WHERE sid=". $student->get_id(). " ORDER BY year");
		unset($grades);
		// Convert grades query result ta a handy array
		foreach($gradesqr['result'] AS $rix => $result)
		{
		  $grades[$gradesqr['year'][$rix]][$gradesqr['period'][$rix]][$gradesqr['mid'][$rix]] = $result;
		}
		unset($houding);
		$houdingqr = inputclassbase::load_query("SELECT * FROM bo_houding_data WHERE sid=". $student->get_id());
		if(isset($houdingqr))
		  foreach($houdingqr['xstatus'] AS $hix => $hres)
		  {
		    $houding[$houdingqr['year'][$hix]][$houdingqr['period'][$hix]][$houdingqr['aspect'][$hix]] = $hres;
		  }
		else
		  $houding=0;
		
		unset($yeardata);
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
		  if(!isset($yeardata[$year]) && $year == $schoolyear)
		  { // No data found for current year in year results, so get it from current setting
		    $mygroup = new group();
			$mygroup->load_current();
		    $yeardata[$year]['klas'] = $mygroup->get_groupname();
			$yeardata[$year]['teacher'] = $mygroup->get_mentor()->get_teacher_detail($teachercode);
		  }
		}
		$yearcount = 0;
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
	
		echo("<div class = SchoolStempel>Schoolstempel:</div>");

		// Now the results table has 8 positions and we need to set each year to a certain position. 
		// 1: first time first year
		// 2: second time first year or second year if not repeated first year
		// 3: second year if repeated first year or send year else third year
		// 4: third year of repeated first, second or third year.
		// 5: forth year
		// 6: fourth year if repeated, else fifth year
		// 7: fifth year if repeated fourth or fifth year, else sixth year
		// 8: sixth year if repeated fourth, fifth or sixth year.
		// Problem is, the first entry can be unkknown!
		unset($tablepos);
		unset($firstposa);
		unset($firstsyear);
		unset($secondsyear);
		// First resolve what we know
		foreach($grades AS $jaar => $dummy)
		{
		  if(isset($yeardata[$jaar]['klas']))
		  { // It's known which klas
		    $syear = substr($yeardata[$jaar]['klas'],0,1);
			$tpos = $syear;
			if(isset($tablepos[$tpos]))
			  $tpos++;
            $tablepos[$tpos] = $jaar;			  
		  }
		}
		// Now resolve what we don't know
		foreach($grades AS $jaar => $dummy)
		{
		  if(!isset($yeardata[$jaar]['klas']))
		  { // It's NOT known which klas
		    echo("<BR>klas not known for year ". $jaar. "<BR>");
		    // Get the first available table position
			foreach($tablepos AS $tposu => $tposy)
			{
			  if(!isset($firstposa))
			  {
			    $firstposa = $tposu;
				$firstsyear = substr($yeardata[$tposy]['klas'],0,1);
			  }
			  else
			    if(!isset($secondsyear))
				  $secondsyear = substr($yeardata[$tposy]['klas'],0,1);
			}
			// Now we know: what's the first used position, which learning year is has and what is next as learning year in the table.
			// If the first position is 1 or the two learning years are not equal, we must shift 1-7 => 2-8
			echo("<BR>years1and2: ". $firstsyear. "--". $secondsyear. "<BR>");
			if($firstposa == 1 || ($firstsyear != $secondsyear))
			{ // We need to shift the positions up!
			  for($spos = 7; $spos >= 1;$spos--)
			  {
			    if(isset($tablepos[$spos]))
				  $tablepos[$spos + 1] = $tablepos[$spos];
			  }
			}
			else
			  $firstposa--;
			// Now fill the created or first available position
			$tablepos[$firstposa] = $jaar;
		  }
		} 
		
		// Fix for prisma
		if(isset($tablepos['p']))
		  $tablepos[1] = $tablepos['p'];
		
		echo("<table class = TblPropRap>
				<tr><td class = RapOverzKol1 colspan=30>Lln.: ". $student->get_firstname(). " ". $student->get_lastname(). "</td></tr>
				<tr class = Dikte4LijnTop><td class = TxtLinks>Leerkracht</td>
				<td class = DikkelijnLinks colspan=3>". (isset($tablepos[1]) ? (isset($yeardata[$tablepos[1]]['teacher']) ? $yeardata[$tablepos[1]]['teacher'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=3>". (isset($tablepos[2]) ? (isset($yeardata[$tablepos[2]]['teacher']) ? $yeardata[$tablepos[2]]['teacher'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=3>". (isset($tablepos[3]) ? (isset($yeardata[$tablepos[3]]['teacher']) ? $yeardata[$tablepos[3]]['teacher'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[4]) ? (isset($yeardata[$tablepos[4]]['teacher']) ? $yeardata[$tablepos[4]]['teacher'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[5]) ? (isset($yeardata[$tablepos[5]]['teacher']) ? $yeardata[$tablepos[5]]['teacher'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[6]) ? (isset($yeardata[$tablepos[6]]['teacher']) ? $yeardata[$tablepos[6]]['teacher'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[7]) ? (isset($yeardata[$tablepos[7]]['teacher']) ? $yeardata[$tablepos[7]]['teacher'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLR colspan=4>". (isset($tablepos[8]) ? (isset($yeardata[$tablepos[8]]['teacher']) ? $yeardata[$tablepos[8]]['teacher'] : "&nbsp;") : "&nbsp;"). "</td>
				</tr>
				<tr><td>Schooljaar</td>
				<td class = Schooljaartabel colspan=3>". (isset($tablepos[1]) ? $tablepos[1] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=3>". (isset($tablepos[2]) ? $tablepos[2] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=3>". (isset($tablepos[3]) ? $tablepos[3] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[4]) ? $tablepos[4] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[5]) ? $tablepos[5] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[6]) ? $tablepos[6] : "&nbsp;"). "</td>
				<td class = Schooljaartabel colspan=4>". (isset($tablepos[7]) ? $tablepos[7] : "&nbsp;"). "</td>
				<td class = SchooljaartabelLR colspan=4>". (isset($tablepos[8]) ? $tablepos[8] : "&nbsp;"). "</td>
				</tr>
				<tr><td>Klas</td>
				<td class = DikkelijnLinks colspan=3>". (isset($tablepos[1]) ? (isset($yeardata[$tablepos[1]]['klas']) ? $yeardata[$tablepos[1]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=3>". (isset($tablepos[2]) ? (isset($yeardata[$tablepos[2]]['klas']) ? $yeardata[$tablepos[2]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=3>". (isset($tablepos[3]) ? (isset($yeardata[$tablepos[3]]['klas']) ? $yeardata[$tablepos[3]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[4]) ? (isset($yeardata[$tablepos[4]]['klas']) ? $yeardata[$tablepos[4]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[5]) ? (isset($yeardata[$tablepos[5]]['klas']) ? $yeardata[$tablepos[5]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[6]) ? (isset($yeardata[$tablepos[6]]['klas']) ? $yeardata[$tablepos[6]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLinks colspan=4>". (isset($tablepos[7]) ? (isset($yeardata[$tablepos[7]]['klas']) ? $yeardata[$tablepos[7]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				<td class = DikkelijnLR colspan=4>". (isset($tablepos[8]) ? (isset($yeardata[$tablepos[8]]['klas']) ? $yeardata[$tablepos[8]]['klas'] : "&nbsp;") : "&nbsp;"). "</td>
				</tr>
				<tr class = KleurRij><td>Rapport</td><td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td><td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td><td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td><td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td>E</td><td class = DikkelijnLinks>1</td><td>2</td><td>3</td><td class = DikkelijnRechts>E</td></tr>

				<tr class = Dikte4LijnTop><td class = vet>HOUDING</td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnRechts></td></tr>");
				print_houding("Gedrag","Gedr",$tablepos,$houding);
				print_houding("Concentratie","Conc",$tablepos,$houding);
				print_houding("Werktempo","Wrkt",$tablepos,$houding);
				print_houding("Zelfstandigheid","Zelfs",$tablepos,$houding);
				print_houding("Sociale vaardigheden","Cklg",$tablepos,$houding,"Clkr");
				print_houding("Werkverzorging","Wrkvz",$tablepos,$houding);
				print_houding("Motivatie / ijver","Motv",$tablepos,$houding,"Ijvr");
				echo("
				<tr class = Dikte3LijnTop><td>Afwezig</td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnRechts></td></tr>
				<tr><td>Te laat</td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnRechts></td></tr>
				<tr><td>&nbsp;</td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td></td><td class = DikkelijnLinks></td><td></td><td></td><td class = DikkelijnRechts></td></tr>");
				foreach($subjlinks AS $naam => $code)
				  print_grades($naam,$code,$tablepos,$grades);
                echo("
				<tr class = Dikte3aLijnTop><td>OVER</td>
				<td class = DikkelijnLinks colspan=3><center>". ((isset($tablepos[1]) && isset($yeardata[$tablepos[1]]['result']) && $yeardata[$tablepos[1]]['result'] == "OVER") ? "OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=3><center>". ((isset($tablepos[2]) && isset($yeardata[$tablepos[2]]['result']) && $yeardata[$tablepos[2]]['result'] == "OVER") ? "OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=3><center>". ((isset($tablepos[3]) && isset($yeardata[$tablepos[3]]['result']) && $yeardata[$tablepos[3]]['result'] == "OVER") ? "OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[4]) && isset($yeardata[$tablepos[4]]['result']) && $yeardata[$tablepos[4]]['result'] == "OVER") ? "OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[5]) && isset($yeardata[$tablepos[5]]['result']) && $yeardata[$tablepos[5]]['result'] == "OVER") ? "OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[6]) && isset($yeardata[$tablepos[6]]['result']) && $yeardata[$tablepos[6]]['result'] == "OVER") ? "OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[7]) && isset($yeardata[$tablepos[7]]['result']) && $yeardata[$tablepos[7]]['result'] == "OVER") ? "OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLR colspan=4><center>". ((isset($tablepos[8]) && isset($yeardata[$tablepos[8]]['result']) && $yeardata[$tablepos[8]]['result'] == "OVER") ? "OVER" : "&nbsp;"). "</center></td></tr>
				<tr><td>NIET OVER</td>
				<td class = DikkelijnLinks colspan=3><center>". ((isset($tablepos[1]) && isset($yeardata[$tablepos[1]]['result']) && $yeardata[$tablepos[1]]['result'] == "NIET OVER") ? "NIET OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=3><center>". ((isset($tablepos[2]) && isset($yeardata[$tablepos[2]]['result']) && $yeardata[$tablepos[2]]['result'] == "NIET OVER") ? "NIET OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=3><center>". ((isset($tablepos[3]) && isset($yeardata[$tablepos[3]]['result']) && $yeardata[$tablepos[3]]['result'] == "NIET OVER") ? "NIET OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[4]) && isset($yeardata[$tablepos[4]]['result']) && $yeardata[$tablepos[4]]['result'] == "NIET OVER") ? "NIET OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[5]) && isset($yeardata[$tablepos[5]]['result']) && $yeardata[$tablepos[5]]['result'] == "NIET OVER") ? "NIET OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[6]) && isset($yeardata[$tablepos[6]]['result']) && $yeardata[$tablepos[6]]['result'] == "NIET OVER") ? "NIET OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[7]) && isset($yeardata[$tablepos[7]]['result']) && $yeardata[$tablepos[7]]['result'] == "NIET OVER") ? "NIET OVER" : "&nbsp;"). "</center></td>
				<td class = DikkelijnLR colspan=4><center>". ((isset($tablepos[8]) && isset($yeardata[$tablepos[8]]['result']) && $yeardata[$tablepos[8]]['result'] == "NIET OVER") ? "NIET OVER" : "&nbsp;"). "</center></td></tr>
				<tr class = DikteLijnOnder><td>O.W.L.</td>
				<td class = DikkelijnLinks colspan=3><center>". ((isset($tablepos[1]) && isset($yeardata[$tablepos[1]]['result']) && $yeardata[$tablepos[1]]['result'] == "O.W.L.") ? "O.W.L." : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=3><center>". ((isset($tablepos[2]) && isset($yeardata[$tablepos[2]]['result']) && $yeardata[$tablepos[2]]['result'] == "O.W.L.") ? "O.W.L." : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=3><center>". ((isset($tablepos[3]) && isset($yeardata[$tablepos[3]]['result']) && $yeardata[$tablepos[3]]['result'] == "O.W.L.") ? "O.W.L." : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[4]) && isset($yeardata[$tablepos[4]]['result']) && $yeardata[$tablepos[4]]['result'] == "O.W.L.") ? "O.W.L." : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[5]) && isset($yeardata[$tablepos[5]]['result']) && $yeardata[$tablepos[5]]['result'] == "O.W.L.") ? "O.W.L." : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[6]) && isset($yeardata[$tablepos[6]]['result']) && $yeardata[$tablepos[6]]['result'] == "O.W.L.") ? "O.W.L." : "&nbsp;"). "</center></td>
				<td class = DikkelijnLinks colspan=4><center>". ((isset($tablepos[7]) && isset($yeardata[$tablepos[7]]['result']) && $yeardata[$tablepos[7]]['result'] == "O.W.L.") ? "O.W.L." : "&nbsp;"). "</center></td>
				<td class = DikkelijnLR colspan=4><center>". ((isset($tablepos[8]) && isset($yeardata[$tablepos[8]]['result']) && $yeardata[$tablepos[8]]['result'] == "O.W.L.") ? "O.W.L." : "&nbsp;"). "</center></td></tr>

				</table>");
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
	for($tp=1;$tp<=8;$tp++)
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
	  if($tp > 3)
	  { // has 4 columns in stead of 3
	    echo("<td class=");
	    if($tp == 8)
		{ // has fat right line
		  echo("DikkelijnRechtsRC>&nbsp;</td></tr>");
		}
		else
		  echo("RCell>&nbsp;</td>");
	  }
	}
  }
  function print_grades($naam,$code,$tablepos,$grades)
  {
    global $ssn2mid;
    if(substr($naam,0,1) == "*")
	  echo("<tr class=KleurRij><td class=vet>". substr($naam,1). "</td>");
	else if(substr($naam,0,1) == "@")
	  echo("<tr class=Dikte3aLijnTop><td class=vet>". substr($naam,1). "</td>");
    else if(substr($naam,0,1) == "-")
      echo("<tr><td>&nbsp;</td>");
    else
      echo("<tr><td>". $naam. "</td>");	
	for($tp=1;$tp<=8;$tp++)
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
	  if($tp > 3)
	  { // has 4 columns in stead of 3
	    echo("<td class=");
	    if($tp == 8)
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
		if($tp == 8)
		  echo("</tr>");
	  }
	}
  }
?>
