<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  require_once("schooladminfunctions.php");
  require_once("student.php");
//  require_once("vakpakGPK.php");
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  echo ('<LINK rel="stylesheet" type="text/css" href="style_SO_Kaart_AVO.css" title="style1">');
  // Tabellen opzetten
  $profilenames=array("HU"=>"Humaniora","MM"=>"Mens en Maatschappijwetenschappen","NW"=>"Natuurwetenschappen");
  $subjseq = array("HU01"=>array("ne","en","lo","sp","ckv","gs","em & o"),
                   "HU02"=>array("ne","en","lo","sp","ckv","gs","wi"),
                   "HU03"=>array("ne","en","lo","sp","ckv","gs","pa"),
                   "HU04"=>array("ne","en","lo","sp","ckv","ak","em & o"),
                   "HU05"=>array("ne","en","lo","sp","ckv","ak","wi"),
                   "HU06"=>array("ne","en","lo","sp","ckv","ak","pa"),
                   "HU07"=>array("ne","en","lo","sp","ak","gs","em & o"),
                   "HU08"=>array("ne","en","lo","sp","ak","gs","wi"),
                   "HU09"=>array("ne","en","lo","sp","ak","gs","ckv"),
                   "HU10"=>array("ne","en","lo","sp","ak","gs","pa"),
// Vakkenpakketten voor Mens en Maatschappijwetenschappen			   
                   "MM01"=>array("ne","en","lo","wi","gs","em & o","sp"),
                   "MM02"=>array("ne","en","lo","wi","gs","em & o","pa"),
                   "MM03"=>array("ne","en","lo","wi","gs","em & o","bi"),
                   "MM04"=>array("ne","en","lo","wi","ak","em & o","sp"),
                   "MM05"=>array("ne","en","lo","wi","ak","em & o","pa"),
                   "MM06"=>array("ne","en","lo","wi","ak","em & o","bi"),
                   "MM07"=>array("ne","en","lo","wi","ak","em & o","gs"),
                   "MM08"=>array("ne","en","lo","wi","ak","gs","sp"),
                   "MM09"=>array("ne","en","lo","wi","ak","gs","pa"),
                   "MM10"=>array("ne","en","lo","wi","ak","gs","bi"),
// Vakkenpakketten voor Natuurwetenschappen				   
                   "NW01"=>array("ne","en","lo","wi","na","sk","sp"),
                   "NW02"=>array("ne","en","lo","wi","na","sk","pa"),
                   "NW03"=>array("ne","en","lo","wi","na","sk","bi"),
                   "NW04"=>array("ne","en","lo","wi","bi","sk","sp"),
                   "NW05"=>array("ne","en","lo","wi","bi","sk","pa"));

  $subjmids = inputclassbase::load_query("SELECT shortname,mid FROM subject");
  if(isset($subjmids))
  {
    foreach($subjmids['shortname'] AS $six => $subjname)
	{
	  $subj2mid[strtolower($subjname)] = $subjmids['mid'][$six];
	  if($subjname == "CKV ex")
	    $subj2mid['ckv'] = $subjmids['mid'][$six];
	}
  }
  // Dummy for cases where no subject is given
  $subj2mid[' '] = 0;
  // Examen klassen
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
 
 // Voorkant SO-kaart:
    echo("<html><head><title>SO-kaart</title></head><body link=blue vlink=blue>");
	  
  IF(isset ($LLlijst))
  {
    foreach ($LLlijst AS $student)
	{
	//	De opmaak van de pagina:
		echo("<div align=center><img background=transparent src=schoollogo.png height=66px align=middle></div>");
		echo("<div class = koptxt>". $schoolname ." M.A.V.O.<BR>
				Schoolexamen / Centraal Schriftelijk Examen - kaart voor CA-2<br>
				Schooljaar ". $schoolyear ."
			  </div>");
															 
// 	Paragraaf over de gegevens van de leerling:
		echo("<pre>");
		echo("<div class = LLdata>
Naam  :  ". $student->get_lastname(). ", ". $student->get_firstname(). "
Ex.nr.:  ". $student->get_student_detail("s_exnr"). "
Id.nr :  ". $student->get_student_detail("*sid"). "
			
Klas  :  ". $_SESSION['CurrentGroup']. "</div><br>");

// Klas:&nbsp;". $student->get_student_detail("*sgroup.groupname"). "</div>");
// Profiel bepalen van de student:		
		echo("<div class = LLdata><b>&nbsp;&nbsp;&nbsp;Profiel:&nbsp;");	// hier moet het profiel komen:
		
// foutmelding opvangen als er niet gekozen is voor een examenklas:
		IF (substr($_SESSION['CurrentGroup'],0,1) < 4 && substr($_SESSION['CurrentGroup'],0,4) != "Exam")  // Eerst de examenklassen $eindklas[$sid] ??
												 // ophalen en dan vergelijken.
												 // Voor mavo is dat 4mavo, 4A, 4B, 4C, enz.
												 // voor do havo is dat 5havo, 5H1, 5H2, enz en
												 // voor vwo is dat 6V1, 6V2, enz.. nieuwe conventie ....
		{ echo("<span class = Examenklas>Kies een examenklas!</span>"); exit();}
		ELSE
		{ echo("<b>" . $profilenames[substr($student->get_student_detail("*package"),0,2)]."</b>");}
		echo("</b></div></pre>");

    // SO cijfers voor deze lln ophalen en in een handig array zetten
    unset($results); // Forget previous student results
	unset($averages); // Forget previous averages for the student
	unset($vrijst); // Forget previous vrijstellingen
	// Get the SO and exam results
    $resqry = inputclassbase::load_query("SELECT result,mid,short_desc FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN `class` USING(cid) WHERE year='". $schoolyear. "' AND type <> '' AND period>1 AND result IS NOT NULL AND sid=". $student->get_id());

    if(isset($resqry))
    {
      foreach($resqry['mid'] AS $rix => $mid)
      $results[$mid][$resqry['short_desc'][$rix]] = $resqry['result'][$rix];
    }
	// Get the calculated averages
    $resqry = inputclassbase::load_query("SELECT result,mid,period FROM gradestore WHERE year='". $schoolyear. "' AND sid=". $student->get_id());

    if(isset($resqry))
    {
      foreach($resqry['mid'] AS $rix => $mid)
      $averages[$mid][$resqry['period'][$rix]] = $resqry['result'][$rix];
    }
	
	// Get "vrijstellingen"
	$resqry = inputclassbase::load_query("SELECT xstatus,mid FROM ex45data WHERE xstatus > 4 AND year='". $schoolyear. "' AND sid=". $student->get_id());
    if(isset($resqry))
    {
      foreach($resqry['mid'] AS $rix => $mid)
        $vrijst[$mid] = $resqry['xstatus'][$rix] + 2;
    }
	
	// Get the name for the package - dus het vakkenpakket: b.v. MM07
	$package = substr($student->get_student_detail("*package"),0,4);
	// Get 7de vak
	$pparts = explode(" : ",$student->get_student_detail("*package"));
	if(isset($pparts[1]))
	  $zevendevak = strtolower($pparts[1]);
	else
	  $zevendevak=" ";
	if($zevendevak == "ckv ex")
	  $zevendevak = "ckv";
		
// 	Paragraaf SO uitslag:
	echo("<table class=OpmaakKaart><tr class=Dikte3LijnOnder><td class = breedte1>Examenonderdelen</td><td class = breedte2a>Vak</td>
			<td class=breedte2 colspan=2>S.E. 1<BR><SPAN class=HerSubText>her</SPAN></td><td class=breedte2 colspan=2>S.E. 2<BR><SPAN class=HerSubText>her</SPAN></td>
			<td class=breedte2 colspan=2>S.E. 3<BR><SPAN class=HerSubText>her</SPAN></td><td class = breedte2a>P.O.</td><td class=breedte2b class=Dikte3LijnTop>S.E. gem.</td>
			<td class=breedte2a class=Dikte3LijnTop colspan=2>CSE<BR><SPAN class=HerSubText>her</SPAN></td><td class = breedte2 class=Dikte3LijnTop>Eindcijfer</td></tr>

	<tr class=Tekstgrootte>
		<td rowspan=5 class=DunneLijnOnder class=SierTekst><b>Gemeenschapplijk deel</b></td>
		<td class=DikkeLijnen>");
		// Hier komt het verplichte vak Nederlands:
		echo($subjseq[$package][0]);
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE1TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][0]]]["SE1TV1"]))
			echo($results[$subj2mid[$subjseq[$package][0]]]["SE1TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE1TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][0]]]["SE1TV2"]))
			echo($results[$subj2mid[$subjseq[$package][0]]]["SE1TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE2TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][0]]]["SE2TV1"]))
			echo($results[$subj2mid[$subjseq[$package][0]]]["SE2TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE2TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][0]]]["SE2TV2"]))
			echo($results[$subj2mid[$subjseq[$package][0]]]["SE2TV2"]);
		else
			echo("&nbsp;");
		
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE3TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][0]]]["SE3TV1"]))
			echo($results[$subj2mid[$subjseq[$package][0]]]["SE3TV1"]);
		// Hier komt het SE3TV2-cijfer:
		echo("</td><td class = LijnenWegHerBG>");
		if(isset($results[$subj2mid[$subjseq[$package][0]]]["SE3TV2"]))
			echo($results[$subj2mid[$subjseq[$package][0]]]["SE3TV2"]);
		else
			echo("&nbsp;");
		// Hier komt het PO cijfer
		echo("</td><td class=DikkeLijnen>");
		if(isset($results[$subj2mid[$subjseq[$package][0]]]["PO"]))
			echo($results[$subj2mid[$subjseq[$package][0]]]["PO"]);
		else
			echo("&nbsp;");
		echo("</td>");

		// 	Paragraaf Examenuitslag: SO-gem - CSE(+HER) - Einduitslag:
		echo("<td class=Tekstgrootte>
		<center>". (isset($averages[$subj2mid[$subjseq[$package][0]]][2]) ? $averages[$subj2mid[$subjseq[$package][0]]][2] : (isset($vrijst[$subj2mid[$subjseq[$package][0]]]) ? "&nbsp" : "-&#120;-")). "</center></td><td class=DunneLijnen>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][0]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][0]]]["Ex"]) ? $results[$subj2mid[$subjseq[$package][0]]]["Ex"] : "-&#120;-")). "</center></td><td class=LijnenWegHerBG>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][0]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][0]]]["Hex"]) ? $results[$subj2mid[$subjseq[$package][0]]]["Hex"] : "-&#120;-")). "</center></td><td class=Dikte3LijnRechts>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][0]]]) ? "v(". $vrijst[$subj2mid[$subjseq[$package][0]]]. ")" : (isset($results[$subj2mid[$subjseq[$package][0]]]["Ex"]) || isset($results[$subj2mid[$subjseq[$package][0]]]["Hex"]) ? $averages[$subj2mid[$subjseq[$package][0]]][0] : "-&#120;-")). "</center></td>
	</tr>");

	echo("<tr><td class=DikkeLijnen>");
	// Hier komt het verplichte vak Engels:
		echo($subjseq[$package][1]);
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE1TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][1]]]["SE1TV1"]))
			echo($results[$subj2mid[$subjseq[$package][1]]]["SE1TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE1TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][1]]]["SE1TV2"]))
			echo($results[$subj2mid[$subjseq[$package][1]]]["SE1TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE2TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][1]]]["SE2TV1"]))
			echo($results[$subj2mid[$subjseq[$package][1]]]["SE2TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE2TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][1]]]["SE2TV2"]))
			echo($results[$subj2mid[$subjseq[$package][1]]]["SE2TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE3TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][1]]]["SE3TV1"]))
			echo($results[$subj2mid[$subjseq[$package][1]]]["SE3TV1"]);
		else
			echo("&nbsp;");
		// Hier komt het SE3TV2-cijfer:
		echo("</td><td class = LijnenWegHerBG>");
		if(isset($results[$subj2mid[$subjseq[$package][1]]]["SE3TV2"]))
			echo($results[$subj2mid[$subjseq[$package][1]]]["SE3TV2"]);
		else
			echo("&nbsp;");
		// Hier komt het PO cijfer
		echo("</td><td class=DikkeLijnen>");
		if(isset($results[$subj2mid[$subjseq[$package][1]]]["PO"]))
			echo($results[$subj2mid[$subjseq[$package][1]]]["PO"]);
		else
			echo("&nbsp;");
		echo("</td>");

		// 	Paragraaf Examenuitslag: SO-gem - CSE(+HER) - Einduitslag:
		echo("<td class=Tekstgrootte>
		<center>". (isset($averages[$subj2mid[$subjseq[$package][1]]][2]) ? $averages[$subj2mid[$subjseq[$package][1]]][2] : (isset($vrijst[$subj2mid[$subjseq[$package][1]]]) ? "&nbsp" : "-&#120;-")). "</center></td><td class=DunneLijnen>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][1]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][1]]]["Ex"]) ? $results[$subj2mid[$subjseq[$package][1]]]["Ex"] : "-&#120;-")). "</center></td><td class=LijnenWegHerBG>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][1]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][1]]]["Hex"]) ? $results[$subj2mid[$subjseq[$package][1]]]["Hex"] : "-&#120;-")). "</center></td><td class=Dikte3LijnRechts>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][1]]]) ? "v(". $vrijst[$subj2mid[$subjseq[$package][1]]]. ")" : (isset($results[$subj2mid[$subjseq[$package][1]]]["Ex"]) || isset($results[$subj2mid[$subjseq[$package][1]]]["Hex"]) ? $averages[$subj2mid[$subjseq[$package][1]]][0] : "-&#120;-")). "</center></td>
	</tr>");

	echo("<tr><td class=DikkeLijnen>");
	// Hier komt het verplichte vak Rekenen:
		echo('rek');
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE1TV1-cijfer:
		if(isset($results[$subj2mid['rek']]["SE1TV1"]))
			echo($results[$subj2mid['rek']]["SE1TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE1TV2-cijfer:
		if(isset($results[$subj2mid['rek']]["SE1TV2"]))
			echo($results[$subj2mid['rek']]["SE1TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE2TV1-cijfer:
		if(isset($results[$subj2mid['rek']]["SE2TV1"]))
			echo($results[$subj2mid['rek']]["SE2TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE2TV2-cijfer:
		if(isset($results[$subj2mid['rek']]["SE2TV2"]))
			echo($results[$subj2mid['rek']]["SE2TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE3TV1-cijfer:
		if(isset($results[$subj2mid['rek']]["SE3TV1"]))
			echo($results[$subj2mid['rek']]["SE3TV1"]);
		else
			echo("&nbsp;");
		// Hier komt het SE3TV2-cijfer:
		echo("</td><td class = LijnenWegHerBG>");
		if(isset($results[$subj2mid['rek']]["SE3TV2"]))
			echo($results[$subj2mid['rek']]["SE3TV2"]);
		else
			echo("&nbsp;");
		// Hier komt het PO cijfer
		echo("</td><td class=DikkeLijnen>");
		if(isset($results[$subj2mid['rek']]["PO"]))
			echo($results[$subj2mid['rek']]["PO"]);
		else
			echo("&nbsp;");
		echo("</td>");
		echo("</td>");

		// 	Paragraaf Examenuitslag: SO-gem - CSE(+HER) - Einduitslag:
		echo("<td class=Tekstgrootte>
		<center>". (isset($averages[$subj2mid['rek']][2]) ? $averages[$subj2mid['rek']][2] : (isset($vrijst[$subj2mid['rek']]) ? "&nbsp" : "-&#120;-")). "</center></td><td class=DunneLijnen>
		<center>". (isset($vrijst[$subj2mid['rek']]) ? "&nbsp" : (isset($results[$subj2mid['rek']]["Ex"]) ? $results[$subj2mid['rek']]["Ex"] : "-&#120;-")). "</center></td><td class=LijnenWegHerBG>
		<center>". (isset($vrijst[$subj2mid['rek']]) ? "&nbsp" : (isset($results[$subj2mid['rek']]["Hex"]) ? $results[$subj2mid['rek']]["Hex"] : "-&#120;-")). "</center></td><td class=Dikte3LijnRechts>
		<center>". (isset($vrijst[$subj2mid['rek']]) ? "v(". $vrijst[$subj2mid['rek']]. ")" : (isset($results[$subj2mid['rek']]["Ex"]) || isset($results[$subj2mid['rek']]["Hex"]) ? $averages[$subj2mid['rek']][0] : "-&#120;-")). "</center></td>
	</tr>");

	echo("<tr><td class=DikkeLijnen>");
	// Hier komt het verplichte vak LO:
		echo($subjseq[$package][2]);
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE1TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][2]]]["SE1TV1"]))
			echo($results[$subj2mid[$subjseq[$package][2]]]["SE1TV1"] < 5.5 ? "onvold." : "voldoende");
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE1TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][2]]]["SE1TV2"]))
			echo($results[$subj2mid[$subjseq[$package][2]]]["SE1TV2"] < 5.5 ? "onvold." : "voldoende");
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE2TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][2]]]["SE2TV1"]))
			echo($results[$subj2mid[$subjseq[$package][2]]]["SE2TV1"] < 5.5 ? "onvold." : "voldoende");
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE2TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][2]]]["SE2TV2"]))
			echo($results[$subj2mid[$subjseq[$package][2]]]["SE2TV2"] < 5.5 ? "onvold." : "voldoende");
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE3TV1-cijfer:
		// 3 lines commented out for LS
		//if(isset($results[$subj2mid[$subjseq[$package][2]]]["SE3TV1"]))
		//	echo($results[$subj2mid[$subjseq[$package][2]]]["SE3TV1"] < 5.5 ? "onvold." : "voldoende");
		//else
			echo("&nbsp;");
		// Hier komt het SE3TV2-cijfer:
		echo("</td><td class = LijnenWegHerBG>");
		// 3 lines commented out for LS
		//if(isset($results[$subj2mid[$subjseq[$package][2]]]["SE3TV2"]))
		//	echo($results[$subj2mid[$subjseq[$package][2]]]["SE3TV2"] < 5.5 ? "onvold." : "voldoende");
		//else
			echo("&nbsp;");
		// Hier komt het PO cijfer
		echo("</td><td class=DikkeLijnen> ");
		echo("</td>");
		echo("</td>");

		// 	Paragraaf Examenuitslag: SO-gem - CSE(+HER) - Einduitslag:
		// For LS: changed -x- in non blank space.
		echo("<td class=Tekstgrootte>
		<center>". (isset($averages[$subj2mid[$subjseq[$package][2]]][2]) ? $averages[$subj2mid[$subjseq[$package][2]]][2] : "&nbsp;"). "</center></td><td class=DunneLijnen>
		<center>&nbsp;</center></td><td class=LijnenWegHerBG>
		<center>&nbsp;</center></td><td class=Dikte3LijnRechts>
		<center>&nbsp;</center></td>
	</tr>");
	// Dit is de CKV paragraaf:
	$ckvres = inputclassbase::load_query("SELECT ckvres FROM examresult WHERE year='". $schoolyear. "' AND sid=". $student->get_id(). " ORDER BY lastmodifiedat DESC");
	if(isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1)
	  $ckvtxt = "voldoende";
	else
	  $ckvtxt = "onvold.";
	//$ckvtxt = "&nbsp;";
	$ckvresqr = inputclassbase::load_query("SELECT result FROM gradestore WHERE period=0 AND sid=". $student->get_id(). " AND mid=". $subj2mid['ckv']. " ORDER BY year DESC");
	if(isset($ckvresqr['result'][0]))
	  $ckvscore = $ckvresqr['result'][0];
	else
	  $ckvscore = "?";
	$ckvscore="&nbsp;";
	echo("<tr><td class=DikkeLijnenA>ckv</td>");
	echo("<td class=DunneLijnOnder>". $ckvtxt. "</td><td class = LijnenHerBG>&nbsp;</td>");
	echo("<td class=DunneLijnOnder>". $ckvtxt. "</td><td class = LijnenHerBG>&nbsp;</td>");
	echo("<td class=DunneLijnOnder>". $ckvtxt. "</td><td class = LijnenHerBG>&nbsp;</td>");
	echo("<td class=DunneLijnOnder> </td>"); // PO kolom
	echo("<td class=DikkeLijnenB>
		<center>&nbsp;</center></td><td class=DunneLijnOnderEnLinks>
		<center>&nbsp;</center></td><td class=LijnenHerBG>
		<center>&nbsp;</center></td><td class=Dikte3LijnRechtsDunOnder>
		<center>". $ckvscore. "</center></td>
	      </tr>");	

	// verplicht keuzvakken:
	echo("<tr><td class=LijnOnderWeg><b>Profieldeel:</b><span class=TigSpaties>verplicht </span></td>");
		echo("<td class=DikkeLijnen>");
		// Hier komt het eerste verplichte keuzevak:
		echo($subjseq[$package][3]);
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE1TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][3]]]["SE1TV1"]))
			echo($results[$subj2mid[$subjseq[$package][3]]]["SE1TV1"]);
		else
			echo("&nbsp");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE1TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][3]]]["SE1TV2"]))
			echo($results[$subj2mid[$subjseq[$package][3]]]["SE1TV2"]);
		else
			echo("&nbsp");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE2TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][3]]]["SE2TV1"]))
			echo($results[$subj2mid[$subjseq[$package][3]]]["SE2TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE2TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][3]]]["SE2TV2"]))
			echo($results[$subj2mid[$subjseq[$package][3]]]["SE2TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE3TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][3]]]["SE3TV1"]))
			echo($results[$subj2mid[$subjseq[$package][3]]]["SE3TV1"]);
		else
			echo("&nbsp;");
		// Hier komt het SE3TV2-cijfer:
		echo("</td><td class = LijnenWegHerBG>");
		if(isset($results[$subj2mid[$subjseq[$package][3]]]["SE3TV2"]))
			echo($results[$subj2mid[$subjseq[$package][3]]]["SE3TV2"]);
		else
			echo("&nbsp;");
		// Hier komt het PO cijfer
		echo("</td><td class=DikkeLijnen>");
		if(isset($results[$subj2mid[$subjseq[$package][3]]]["PO"]))
			echo($results[$subj2mid[$subjseq[$package][3]]]["PO"]);
		else
			echo("&nbsp;");
		echo("</td>");

		// 	Paragraaf Examenuitslag: SO-gem - CSE(+HER) - Einduitslag:
		echo("<td class=Tekstgrootte>
		<center>". (isset($averages[$subj2mid[$subjseq[$package][3]]][2]) ? $averages[$subj2mid[$subjseq[$package][3]]][2] : (isset($vrijst[$subj2mid[$subjseq[$package][3]]]) ? "&nbsp" : "-&#120;-")). "</center></td><td class=DunneLijnen>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][3]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][3]]]["Ex"]) ? $results[$subj2mid[$subjseq[$package][3]]]["Ex"] : "-&#120;-")). "</center></td><td class=LijnenWegHerBG>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][3]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][3]]]["Hex"]) ? $results[$subj2mid[$subjseq[$package][3]]]["Hex"] : "-&#120;-")). "</center></td><td class=Dikte3LijnRechts>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][3]]]) ? "v(". $vrijst[$subj2mid[$subjseq[$package][3]]]. ")" : (isset($results[$subj2mid[$subjseq[$package][3]]]["Ex"]) || isset($results[$subj2mid[$subjseq[$package][3]]]["Hex"]) ? $averages[$subj2mid[$subjseq[$package][3]]][0] : "-&#120;-")). "</center></td>
	</tr>");
	
	// 2 "echte" keuzevakken:
	echo("<tr><td rowspan=2 class=DunneLijnOnder><div align=right>keuze 2 vakken</div></td>");
		echo("<td class=DikkeLijnen>");
		// Hier komt het eerste verplichte keuzevak:
		echo($subjseq[$package][4]);
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE1TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][4]]]["SE1TV1"]))
			echo($results[$subj2mid[$subjseq[$package][4]]]["SE1TV1"]);
		else
			echo("&nbsp");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE1TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][4]]]["SE1TV2"]))
			echo($results[$subj2mid[$subjseq[$package][4]]]["SE1TV2"]);
		else
			echo("&nbsp");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE2TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][4]]]["SE2TV1"]))
			echo($results[$subj2mid[$subjseq[$package][4]]]["SE2TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE2TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][4]]]["SE2TV2"]))
			echo($results[$subj2mid[$subjseq[$package][4]]]["SE2TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE3TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][4]]]["SE3TV1"]))
			echo($results[$subj2mid[$subjseq[$package][4]]]["SE3TV1"]);
		else
			echo("&nbsp;");
		// Hier komt het SE3TV2-cijfer:
		echo("</td><td class = LijnenWegHerBG>");
		if(isset($results[$subj2mid[$subjseq[$package][4]]]["SE3TV2"]))
			echo($results[$subj2mid[$subjseq[$package][4]]]["SE3TV2"]);
		else
			echo("&nbsp;");
		// Hier komt het PO cijfer
		echo("</td><td class=DikkeLijnen>");
		if(isset($results[$subj2mid[$subjseq[$package][4]]]["PO"]))
			echo($results[$subj2mid[$subjseq[$package][4]]]["PO"]);
		else
			echo("&nbsp;");
		echo("</td>");

		// 	Paragraaf Examenuitslag: SO-gem - CSE(+HER) - Einduitslag:
		echo("<td class=Tekstgrootte>
		<center>". (isset($averages[$subj2mid[$subjseq[$package][4]]][2]) ? $averages[$subj2mid[$subjseq[$package][4]]][2] : (isset($vrijst[$subj2mid[$subjseq[$package][4]]]) ? "&nbsp" : "-&#120;-")). "</center></td><td class=DunneLijnen>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][4]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][4]]]["Ex"]) ? $results[$subj2mid[$subjseq[$package][4]]]["Ex"] : "-&#120;-")). "</center></td><td class=LijnenWegHerBG>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][4]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][4]]]["Hex"]) ? $results[$subj2mid[$subjseq[$package][4]]]["Hex"] : "-&#120;-")). "</center></td><td class=Dikte3LijnRechts>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][4]]]) ? "v(". $vrijst[$subj2mid[$subjseq[$package][4]]]. ")" : (isset($results[$subj2mid[$subjseq[$package][4]]]["Ex"]) || isset($results[$subj2mid[$subjseq[$package][4]]]["Hex"]) ? $averages[$subj2mid[$subjseq[$package][4]]][0] : "-&#120;-")). "</center></td>
	</tr>");

	echo("<tr><td class = DikkeLijnenA>");
		// Hier komt het tweede verplichte keuzevak:
		echo($subjseq[$package][5]);
		echo("</td><td class = DunneLijnOnder>");
		// Hier komt het SE1TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][5]]]["SE1TV1"]))
			echo($results[$subj2mid[$subjseq[$package][5]]]["SE1TV1"]);
		else
			echo("&nbsp");
		echo("</td><td class = LijnenHerBG>");
		// Hier komt het SE1TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][5]]]["SE1TV2"]))
			echo($results[$subj2mid[$subjseq[$package][5]]]["SE1TV2"]);
		else
			echo("&nbsp");
		echo("</td><td class = DunneLijnOnder>");
		// Hier komt het SE2TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][5]]]["SE2TV1"]))
			echo($results[$subj2mid[$subjseq[$package][5]]]["SE2TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenHerBG>");
		// Hier komt het SE2TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][5]]]["SE2TV2"]))
			echo($results[$subj2mid[$subjseq[$package][5]]]["SE2TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = DunneLijnOnder>");
		// Hier komt het SE3TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][5]]]["SE3TV1"]))
			echo($results[$subj2mid[$subjseq[$package][5]]]["SE3TV1"]);
		else
			echo("&nbsp;");
		// Hier komt het SE3TV2-cijfer:
		echo("</td><td class = LijnenHerBG>");
		if(isset($results[$subj2mid[$subjseq[$package][5]]]["SE3TV2"]))
			echo($results[$subj2mid[$subjseq[$package][5]]]["SE3TV2"]);
		else
			echo("&nbsp;");
		// Hier komt het PO cijfer
		echo("</td><td class=DunneLijnOnder>");
		if(isset($results[$subj2mid[$subjseq[$package][5]]]["PO"]))
			echo($results[$subj2mid[$subjseq[$package][5]]]["PO"]);
		else
			echo("&nbsp;");
		echo("</td>");

		// 	Paragraaf Examenuitslag: SO-gem - CSE(+HER) - Einduitslag:
		echo("<td class=DikkeLijnenB>
		<center>". (isset($averages[$subj2mid[$subjseq[$package][5]]][2]) ? $averages[$subj2mid[$subjseq[$package][5]]][2] : (isset($vrijst[$subj2mid[$subjseq[$package][5]]]) ? "&nbsp" : "-&#120;-")). "</center></td><td class=DunneLijnOnderEnLinks>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][5]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][5]]]["Ex"]) ? $results[$subj2mid[$subjseq[$package][5]]]["Ex"] : "-&#120;-")). "</center></td><td class=LijnenHerBG>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][5]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][5]]]["Hex"]) ? $results[$subj2mid[$subjseq[$package][5]]]["Hex"] : "-&#120;-")). "</center></td><td class=Dikte3LijnRechtsDunOnder>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][5]]]) ? "v(". $vrijst[$subj2mid[$subjseq[$package][5]]]. ")" : (isset($results[$subj2mid[$subjseq[$package][5]]]["Ex"]) || isset($results[$subj2mid[$subjseq[$package][5]]]["Hex"]) ? $averages[$subj2mid[$subjseq[$package][5]]][0] : "-&#120;-")). "</center></td>
	</tr>");

	// Het [6]de keuze vak vak het vakkenpakket:
	echo("<tr class=Dikte3LijnOnder><td><b>Keuze deel</b> het 6-de vak</td><td class = DikkeLijnen>");
		// Hier komt het tweede verplichte keuzevak:
		echo($subjseq[$package][6]);
		echo("</td><td class = DunneLijnOnder>");
		// Hier komt het SE1TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][6]]]["SE1TV1"]))
			echo($results[$subj2mid[$subjseq[$package][6]]]["SE1TV1"]);
		else
			echo("&nbsp");
		echo("</td><td class = LijnenHerBG>");
		// Hier komt het SE1TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][6]]]["SE1TV2"]))
			echo($results[$subj2mid[$subjseq[$package][6]]]["SE1TV2"]);
		else
			echo("&nbsp");
		echo("</td><td class = DunneLijnOnder>");
		// Hier komt het SE2TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][6]]]["SE2TV1"]))
			echo($results[$subj2mid[$subjseq[$package][6]]]["SE2TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenHerBG>");
		// Hier komt het SE2TV2-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][6]]]["SE2TV2"]))
			echo($results[$subj2mid[$subjseq[$package][6]]]["SE2TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = DunneLijnOnder>");
		// Hier komt het SE3TV1-cijfer:
		if(isset($results[$subj2mid[$subjseq[$package][6]]]["SE3TV1"]))
			echo($results[$subj2mid[$subjseq[$package][6]]]["SE3TV1"]);
		else
			echo("&nbsp;");
		// Hier komt het SE3TV2-cijfer:
		echo("</td><td class = LijnenHerBG>");
		if(isset($results[$subj2mid[$subjseq[$package][6]]]["SE3TV2"]))
			echo($results[$subj2mid[$subjseq[$package][6]]]["SE3TV2"]);
		else
			echo("&nbsp;");
		// Hier komt het PO cijfer
		echo("</td><td class=DikkeLijnen>");
		if(isset($results[$subj2mid[$subjseq[$package][6]]]["PO"]))
			echo($results[$subj2mid[$subjseq[$package][6]]]["PO"]);
		else
			echo("&nbsp;");
		echo("</td>");

		// 	Paragraaf Examenuitslag: SO-gem - CSE(+HER) - Einduitslag:
		echo("<td class=Tekstgrootte>
		<center>". (isset($averages[$subj2mid[$subjseq[$package][6]]][2]) ? $averages[$subj2mid[$subjseq[$package][6]]][2] : (isset($vrijst[$subj2mid[$subjseq[$package][6]]]) ? "&nbsp" : "-&#120;-")). "</center></td><td class=DunneLijnen>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][6]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][6]]]["Ex"]) ? $results[$subj2mid[$subjseq[$package][6]]]["Ex"] : "-&#120;-")). "</center></td><td class=LijnenHerBG>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][6]]]) ? "&nbsp" : (isset($results[$subj2mid[$subjseq[$package][6]]]["Hex"]) ? $results[$subj2mid[$subjseq[$package][6]]]["Hex"] : "-&#120;-")). "</center></td><td class=Dikte3LijnRechts>
		<center>". (isset($vrijst[$subj2mid[$subjseq[$package][6]]]) ? "v(". $vrijst[$subj2mid[$subjseq[$package][6]]]. ")" : (isset($results[$subj2mid[$subjseq[$package][6]]]["Ex"]) || isset($results[$subj2mid[$subjseq[$package][6]]]["Hex"]) ? $averages[$subj2mid[$subjseq[$package][6]]][0] : "-&#120;-")). "</center></td>
	</tr>");
	
	// Hier komt het 7de vak:
				echo("<tr><td ><div class=Tekstgrootte>&nbsp;&nbsp;&nbsp;<b>Extra</b> keuze 7-de vak</div></td>
					<td class = DikkeLijnen>");
		// Hier komt het 7de vak:
		echo($zevendevak);
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE1TV1-cijfer:
		if(isset($results[$subj2mid[$zevendevak]]["SE1TV1"]))
			echo($results[$subj2mid[$zevendevak]]["SE1TV1"]);
		else
			echo("&nbsp");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE1TV2-cijfer:
		if(isset($results[$subj2mid[$zevendevak]]["SE1TV2"]))
			echo($results[$subj2mid[$zevendevak]]["SE1TV2"]);
		else
			echo("&nbsp");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE2TV1-cijfer:
		if(isset($results[$subj2mid[$zevendevak]]["SE2TV1"]))
			echo($results[$subj2mid[$zevendevak]]["SE2TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het SE2TV2-cijfer:
		if(isset($results[$subj2mid[$zevendevak]]["SE2TV2"]))
			echo($results[$subj2mid[$zevendevak]]["SE2TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SE3TV1-cijfer:
		if(isset($results[$subj2mid[$zevendevak]]["SE3TV1"]))
			echo($results[$subj2mid[$zevendevak]]["SE3TV1"]);
		else
			echo("&nbsp;");
		// Hier komt het SE3TV2-cijfer:
		echo("</td><td class = LijnenWegHerBG>");
		if(isset($results[$subj2mid[$zevendevak]]["SE3TV2"]))
			echo($results[$subj2mid[$zevendevak]]["SE3TV2"]);
		else
			echo("&nbsp;");
		// Hier komt het PO cijfer
		echo("</td><td class=DikkeLijnen>");
		if(isset($results[$subj2mid[$zevendevak]]["PO"]))
			echo($results[$subj2mid[$zevendevak]]["PO"]);
		else
			echo("&nbsp;");
		echo("</td>");					
		echo("<td class=Tekstgrootte><center>". (isset($averages[$subj2mid[$zevendevak]][2]) ? $averages[$subj2mid[$zevendevak]][2] : (isset($vrijst[$subj2mid[$zevendevak]]) ? "&nbsp" : "-&#120;-")). "</center></td>
		<td class = DunneLijnen>". (isset($results[$subj2mid[$zevendevak]]["Ex"]) ? $results[$subj2mid[$zevendevak]]["Ex"] : "-&#120;-"). "</td>
		<td class=LijnenWegHerBG>". (isset($results[$subj2mid[$zevendevak]]["Hex"]) ? $results[$subj2mid[$zevendevak]]["Hex"] : "-&#120;-"). "</td>
					<td class=Dikte3LijnRechts><center>". (isset($vrijst[$subj2mid[$zevendevak]]) ? "v(". $vrijst[$subj2mid[$zevendevak]]. ")" : (isset($results[$subj2mid[$zevendevak]]["Ex"]) || isset($results[$subj2mid[$zevendevak]]["Hex"]) ? $averages[$subj2mid[$zevendevak]][0] : "-&#120;-")). "</center></td>
				</tr>");

	// Hier komt het 8de vak:
				echo("<tr class=Dikte3LijnOnder><td>&nbsp;&nbsp;&nbsp;<b>Extra</b> keuze 8-ste vak</td><td class = DikkeLijnen></td>
					<td class=DunneLijnOnder></td><td class=LijnenHerBG></td>
					<td class=DunneLijnOnder></td><td class=LijnenHerBG></td>
					<td class=DunneLijnOnder></td><td class=LijnenHerBG></td><td class = DikkeLijnen></td>
					<td></td><td class=DunneLijnen></td><td class=LijnenHerBG></td><td class=Dikte3LijnRechts></td>
				</tr>");
	// Bereken resultaten voor SE1TV1, SE2TV1, SE3TV1
	$SE1TV1minp = 0;
	$SE1TV1comp = 0;
	$SE1TV1valid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if($q != 2 && !isset($vrijst[$subj2mid[$subjseq[$package][$q]]])) // Exclude 2 and subjects that are excempted
	  {
	    $SE1TV1valid = (isset($results[$subj2mid[$subjseq[$package][$q]]]) && $SE1TV1valid);
	    $sres = round(max(isset($results[$subj2mid[$subjseq[$package][$q]]]["SE1TV1"]) ? $results[$subj2mid[$subjseq[$package][$q]]]["SE1TV1"] : 0,
		                  isset($results[$subj2mid[$subjseq[$package][$q]]]["SE1TV2"]) ? $results[$subj2mid[$subjseq[$package][$q]]]["SE1TV2"] : 0),0);
		if($sres < 6)
		  $SE1TV1minp += 6 - $sres;
		else
		  $SE1TV1comp += $sres - 6;
	  }
	}
	if(isset($results[$subj2mid[$zevendevak]]))
	{
	  $SE1TV1valid = (isset($results[$subj2mid[$zevendevak]]) && $SE1TV1valid);
	  $sres = round(max(isset($results[$subj2mid[$zevendevak]]["SE1TV1"]) ? $results[$subj2mid[$zevendevak]]["SE1TV1"] : 0,
	                    isset($results[$subj2mid[$zevendevak]]["SE1TV2"]) ? $results[$subj2mid[$zevendevak]]["SE1TV2"] : 0),0);
	  if($sres < 6)
	    $SE1TV1minp += 6 - $sres;
	  else
	    $SE1TV1comp += $sres - 6;
    }
	  
	$SE1TV1pass = (($SE1TV1minp < 2 || ($SE1TV1minp == 2 && $SE1TV1comp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1));

	$SE2TV1minp = 0;
	$SE2TV1comp = 0;
	$SE2TV1valid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if($q != 2 && !isset($vrijst[$subj2mid[$subjseq[$package][$q]]])) // Exclude 2
	  {
	    $SE2TV1valid = (isset($results[$subj2mid[$subjseq[$package][$q]]]) && $SE2TV1valid);
	    $sres = round(max(isset($results[$subj2mid[$subjseq[$package][$q]]]["SE2TV1"]) ? $results[$subj2mid[$subjseq[$package][$q]]]["SE2TV1"] : 0,
		                  isset($results[$subj2mid[$subjseq[$package][$q]]]["SE2TV2"]) ? $results[$subj2mid[$subjseq[$package][$q]]]["SE2TV2"] : 0),0);
		if($sres < 6)
		  $SE2TV1minp += 6 - $sres;
		else
		  $SE2TV1comp += $sres - 6;
	  }
	}
	if(isset($results[$subj2mid[$zevendevak]]))
	{
	  $SE2TV1valid = (isset($results[$subj2mid[$zevendevak]]) && $SE2TV1valid);
	  $sres = round(max(isset($results[$subj2mid[$zevendevak]]["SE2TV1"]) ? $results[$subj2mid[$zevendevak]]["SE2TV1"] : 0,
	                    isset($results[$subj2mid[$zevendevak]]["SE2TV2"]) ? $results[$subj2mid[$zevendevak]]["SE2TV2"] : 0),0);
	  if($sres < 6)
	    $SE2TV1minp += 6 - $sres;
	  else
	    $SE2TV1comp += $sres - 6;
    }
	  
	$SE2TV1pass = (($SE2TV1minp < 2 || ($SE2TV1minp == 2 && $SE2TV1comp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1));

	$SE3TV1minp = 0;
	$SE3TV1comp = 0;
	$SE3TV1valid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if($q != 2 && !isset($vrijst[$subj2mid[$subjseq[$package][$q]]])) // Exclude 2
	  {
	    $SE3TV1valid = (isset($results[$subj2mid[$subjseq[$package][$q]]]) && $SE3TV1valid);
	    $sres = round(max(isset($results[$subj2mid[$subjseq[$package][$q]]]["SE3TV1"]) ? $results[$subj2mid[$subjseq[$package][$q]]]["SE3TV1"] : 0,
		                  isset($results[$subj2mid[$subjseq[$package][$q]]]["SE3TV2"]) ? $results[$subj2mid[$subjseq[$package][$q]]]["SE3TV2"] : 0),0);
		if($sres < 6)
		{
		  $SE3TV1minp += 6 - $sres;
			//echo("sres for mid ". $subj2mid[$subjseq[$package][$q]]. " = ". $sres. "<BR>");
		}
		else
		  $SE3TV1comp += $sres - 6;
	  }
	}
	if(isset($results[$subj2mid[$zevendevak]]))
	{
	  $SE3TV1valid = (isset($results[$subj2mid[$zevendevak]]) && $SE3TV1valid);
	  $sres = round(max(isset($results[$subj2mid[$zevendevak]]["SE3TV1"]) ? $results[$subj2mid[$zevendevak]]["SE3TV1"] : 0,
	                    isset($results[$subj2mid[$zevendevak]]["SE3TV2"]) ? $results[$subj2mid[$zevendevak]]["SE3TV2"] : 0),0);
	  if($sres < 6)
	    $SE3TV1minp += 6 - $sres;
	  else
	    $SE3TV1comp += $sres - 6;
    }
	  
	$SE3TV1pass = (($SE3TV1minp < 2 || ($SE3TV1minp == 2 && $SE3TV1comp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1));
	
	$sogminp = 0;
	$sogcomp = 0;
	$sogvalid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if($q != 2 && !isset($vrijst[$subj2mid[$subjseq[$package][$q]]])) // Exclude 2
	  {
	    $sogvalid = ((isset($averages[$subj2mid[$subjseq[$package][$q]]][2]) || isset($averages[$subj2mid[$subjseq[$package][$q]]][2]))&& $sogvalid);
        $sres = round(isset($averages[$subj2mid[$subjseq[$package][$q]]][2]) ? $averages[$subj2mid[$subjseq[$package][$q]]][2] : 0,0);
		if($sres < 6)
		  $sogminp += 6 - $sres;
		else
		  $sogcomp += $sres - 6;
	  }
	}
	if(isset($averages[$subj2mid[$zevendevak]][2]))
	{
	  $sogvalid = (isset($averages[$subj2mid[$zevendevak]][2]) && $SE1TV1valid);
        $sres = round(isset($averages[$subj2mid[$zevendevak]][2]) ? $averages[$subj2mid[$zevendevak]][2] : 0,0);
	  if($sres < 6)
	    $SE1TV1minp += 6 - $sres;
	  else
	    $SE1TV1comp += $sres - 6;
	}
	// Line added for LS, made all average SO invalid so can be entered manually
	$sogvalid = false;
	$sogpass = (($sogminp < 2 || ($sogminp == 2 && $sogcomp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1));

	$egminp = 0;
	$egcomp = 0;
	$egvalid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if($q != 2 && !isset($vrijst[$subj2mid[$subjseq[$package][$q]]])) // Exclude 2
	  {
	    $egvalid = ((isset($averages[$subj2mid[$subjseq[$package][$q]]][3]))&& $sogvalid);
        $sres = round(isset($averages[$subj2mid[$subjseq[$package][$q]]][0]) ? $averages[$subj2mid[$subjseq[$package][$q]]][0] : 0,0);
		if($sres < 6)
		  $egminp += 6 - $sres;
		else
		  $egcomp += $sres - 6;
	  }
	}
	if(isset($averages[$subj2mid[$zevendevak]][2]))
	{
	  $egvalid = (isset($averages[$subj2mid[$zevendevak]][3]) && $SE1TV1valid);
        $sres = round(isset($averages[$subj2mid[$zevendevak]][0]) ? $averages[$subj2mid[$zevendevak]][0] : 0,0);
	  if($sres < 6)
	    $egminp += 6 - $sres;
	  else
	    $egcomp += $sres - 6;
	}
	$egpass = (($egminp < 2 || ($egminp == 2 && $egcomp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1));
	// Hier komt het resultaat
				echo("
				<tr><td colspan=2 class=LijnenWeg><div  align=right>Resultaat:</div></td>
					<td colspan=2 class=DikkeLijnen>". ($SE1TV1valid ? ($SE1TV1pass ? "voldoende" : "onvoldoende") : "&nbsp;"). "</td>
					<td colspan=2 class=DikkeLijnen>". ($SE2TV1valid ? ($SE2TV1pass ? "voldoende" : "onvoldoende") : "&nbsp;"). "</td>
					<td colspan=2 class=DikkeLijnen>". ($SE3TV1valid ? ($SE3TV1pass ? "voldoende" : "onvoldoende") : "&nbsp;"). "</td><td class=DikkeLijnen> </td>
					<td class=DikkeLijnen>". ($sogvalid ? ($sogpass ? "voldoende" : "onvoldoende") : "&nbsp;"). "</td>
					<td colspan=3 class=Dikte3LijnRechts> Uitslag: <B>". ($egvalid ? ($egpass ? "Geslaagd" : "Afgewezen") : "&nbsp;"). "</td>
				</tr>
				<tr><td colspan=2 class=LijnenWeg><div  align=right>&nbsp;</div></td>
					<td colspan=2 class=DikkeLijnenC></td>
					<td colspan=2 class=DikkeLijnenC></td>
					<td colspan=2 class=DikkeLijnenC></td>
					<td colspan=1 class=DikkeLijnenC></td><td colspan=1 class=DikkeLijnenC></td>
					<td colspan=3 class=Dikte3Lijn></td>
				</tr>
		</table>");		
	echo("<br>");
	echo("<P class=Remarks>Opmerking(en):</P>");
	echo("<BR>Handtekening Mentor: ________________________________");
	echo("<br><br><br>");
	echo("Om te kunnen slagen heeft uw zoon/dochter minimaal het volgende nodig:");
	echo("<table><tr><td class = breedte3>* alle vakken een voldoende</td><td></td></tr>
		 <tr><td class = breedte3>* &eacute;&eacute;n vijf en de rest 6 of hoger</td>
			<td class = Voorbeelden>B.v.: 5,&nbsp;6,&nbsp;6,&nbsp;6,&nbsp;6,&nbsp;6</td></tr>
		 <tr><td class = breedte3>* twee vijven met twee compensatiepunten</td>
			<td class = Voorbeelden>B.v.: 5,&nbsp;5,&nbsp;7,&nbsp;7,&nbsp;6,&nbsp;6 &oacute;f b.v.: 5,&nbsp;5,&nbsp;8,&nbsp;6,&nbsp;6,&nbsp;6</td></tr>
		 <tr><td class = breedte3>* &eacute;&eacute;n vier met twee compensatiepunten</td>
			<td class = Voorbeelden>B.v.: 4,&nbsp;7,&nbsp;7,&nbsp;6,&nbsp;6,&nbsp;6 &oacute;f b.v.: 4,&nbsp;8,&nbsp;6,&nbsp;6,&nbsp;6,&nbsp;6</td></tr>
		</table>");
		 echo("<br>");
	//echo("<P class=Legenda>Legenda<BR><span class=legenditem>v.</SPAN>: Voldaan<BR><span class=legenditem>n.v.</SPAN>: Niet Voldaan");
    // Line changed for LS
	//echo("<P class=dirsign>Handtekening Directeur<br><br><br><br><br>_________________________</P>");
	echo("<P class=dirsign>Handtekening Directeur<br><img width=200 src=dirfirm.jpg><br>_________________________</P>");
	} // einde foreach student
  } // Endif 1  
  // close the page
  echo("</html>");
?>
