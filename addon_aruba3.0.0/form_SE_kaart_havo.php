<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  require_once("schooladminfunctions.php");
  require_once("student.php");
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  echo ('<LINK rel="stylesheet" type="text/css" href="style_SO_Kaart_havo.css" title="style1">');
  // Tabellen opzetten
  $profilenames=array("HU"=>"Humaniora","MM"=>"Mens en Maatschappijwetenschappen","NW"=>"Natuurwetenschappen");
  // Subjectsequences HAVO
  $subjseq = array("ne","en","sp","wia","wib","na","sk","bio","ec","m&o","ak","gs");
/* Subjectsequences have changed by EBA (MAVO)for 2013-2014:
  $subjseq = array("HU01"=>array("ne","en","lo","sp","gs","ckv","pa"),
                   "HU02"=>array("ne","en","lo","sp","gs","ckv","wi"),
                   "HU03"=>array("ne","en","lo","sp","gs","ckv","ecmo"),
                   "HU04"=>array("ne","en","lo","sp","ak","ckv","pa"),
                   "HU05"=>array("ne","en","lo","sp","ak","ckv","wi"),
                   "HU06"=>array("ne","en","lo","sp","ak","ckv","ecmo"),
                   "HU07"=>array("ne","en","lo","sp","ak","gs","pa"),
                   "HU08"=>array("ne","en","lo","sp","ak","gs","wi"),
                   "HU09"=>array("ne","en","lo","sp","ak","gs","ecmo"),
                   "HU10"=>array("ne","en","lo","sp","ak","gs","ckv"),
// Vakkenpakketten voor Mens en Maatschappijwetenschappen			   
                   "MM01"=>array("ne","en","lo","wi","ecmo","gs","sp"),
                   "MM02"=>array("ne","en","lo","wi","ecmo","gs","pa"),
                   "MM03"=>array("ne","en","lo","wi","ecmo","gs","bio"),
                   "MM04"=>array("ne","en","lo","wi","ecmo","ak","sp"),
                   "MM05"=>array("ne","en","lo","wi","ecmo","ak","pa"),
                   "MM06"=>array("ne","en","lo","wi","ecmo","ak","bio"),
                   "MM07"=>array("ne","en","lo","wi","ecmo","ak","gs"),
                   "MM08"=>array("ne","en","lo","wi","ak","gs","sp"),
                   "MM09"=>array("ne","en","lo","wi","ak","gs","pa"),
                   "MM10"=>array("ne","en","lo","wi","ak","gs","bio"),
// Vakkenpakketten voor Natuurwetenschappen				   
                   "NW01"=>array("ne","en","lo","wi","nask 1","nask 2","sp"),
                   "NW02"=>array("ne","en","lo","wi","nask 1","nask 2","pa"),
                   "NW03"=>array("ne","en","lo","wi","nask 1","nask 2","bio"),
                   "NW04"=>array("ne","en","lo","wi","nask 2","bio","sp"),
                   "NW05"=>array("ne","en","lo","wi","nask 2","bio","pa"));
/*  $subjseq = array("HU01"=>array("ne","en","lo","sp","ckv","gs","ecmo"),
                   "HU02"=>array("ne","en","lo","sp","ckv","gs","wi"),
                   "HU03"=>array("ne","en","lo","sp","ckv","gs","pa"),
                   "HU04"=>array("ne","en","lo","sp","ckv","ak","ecmo"),
                   "HU05"=>array("ne","en","lo","sp","ckv","ak","wi"),
                   "HU06"=>array("ne","en","lo","sp","ckv","ak","pa"),
                   "HU07"=>array("ne","en","lo","sp","ak","gs","ecmo"),
                   "HU08"=>array("ne","en","lo","sp","ak","gs","wi"),
                   "HU09"=>array("ne","en","lo","sp","ak","gs","ckv"),
                   "HU10"=>array("ne","en","lo","sp","ak","gs","pa"),
// Vakkenpakketten voor Mens en Maatschappijwetenschappen			   
                   "MM01"=>array("ne","en","lo","wi","gs","ecmo","sp"),
                   "MM02"=>array("ne","en","lo","wi","gs","ecmo","pa"),
                   "MM03"=>array("ne","en","lo","wi","gs","ecmo","bio"),
                   "MM04"=>array("ne","en","lo","wi","ak","ecmo","sp"),
                   "MM05"=>array("ne","en","lo","wi","ak","ecmo","pa"),
                   "MM06"=>array("ne","en","lo","wi","ak","ecmo","bio"),
                   "MM07"=>array("ne","en","lo","wi","ak","ecmo","gs"),
                   "MM08"=>array("ne","en","lo","wi","ak","gs","sp"),
                   "MM09"=>array("ne","en","lo","wi","ak","gs","pa"),
                   "MM10"=>array("ne","en","lo","wi","ak","gs","bio"),
// Vakkenpakketten voor Natuurwetenschappen				   
                   "NW01"=>array("ne","en","lo","wi","nask 1","nask 2","sp"),
                   "NW02"=>array("ne","en","lo","wi","nask 1","nask 2","pa"),
                   "NW03"=>array("ne","en","lo","wi","nask 1","nask 2","bio"),
                   "NW04"=>array("ne","en","lo","wi","bio","nask 2","sp"),
                   "NW05"=>array("ne","en","lo","wi","bio","nask 2","pa"));
*/

  $subjmids = inputclassbase::load_query("SELECT shortname,mid FROM subject");
  if(isset($subjmids))
  {
    foreach($subjmids['shortname'] AS $six => $subjname)
	  $subj2mid[strtolower($subjname)] = $subjmids['mid'][$six];
  }
  // Dummy for cases where no subject is given
  $subj2mid[' '] = 0;
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
    echo("<html><head><title>SE-kaart</title></head><body link=blue vlink=blue>");
	  
  IF(isset ($LLlijst))
  {
    foreach ($LLlijst AS $student)
	{
	//	De opmaak van de pagina:
		echo("<div align=center><img background=transparent src=schoollogo.png height=66px align=middle></div>");
		echo("<div class = koptxt>". $schoolname ." H.A.V.O.<BR>
				Schoolexamen / Centraal Schriftelijk Examen - kaart<br>
				Schooljaar ". $schoolyear ."
			  </div>");
															 
// 	Paragraaf over de gegevens van de leerling:
		echo("<pre>");
		echo("<div class = LLdata>
Naam  :  ". $student->get_lastname(). ", ". $student->get_firstname(). "
Ex.nr.:  ". $student->get_student_detail("s_exnr"). "
Id.nr :  ". $student->get_student_detail("s_IDcenso"). "
			
Klas  :  ". $_SESSION['CurrentGroup']. "</div><br>");

// Klas:&nbsp;". $student->get_student_detail("*sgroup.groupname"). "</div>");
// Profiel bepalen van de student:		
		echo("<div class = LLdata><b>&nbsp;&nbsp;&nbsp;Profiel:&nbsp;");	// hier moet het profiel komen:
		
// foutmelding opvangen als er niet gekozen is voor een examenklas:
		IF (substr($_SESSION['CurrentGroup'],0,2) != "H5" && substr($_SESSION['CurrentGroup'],0,4) != "Exam")  // Eerst de examenklassen $eindklas[$sid] ??
												 // ophalen en dan vergelijken.
												 // Voor mavo is dat 4mavo, 4A, 4B, 4C, enz.
												 // voor do havo is dat 5havo, 5H1, 5H2, enz en
												 // voor vwo is dat 6V1, 6V2, enz.. nieuwe conventie ....
		{ echo("<span class = Examenklas>Kies een examenklas!</span>"); exit();}
		ELSE
		{ echo("<b>" . (isset($profilenames[substr($student->get_student_detail("*package"),0,2)]) ? $profilenames[substr($student->get_student_detail("*package"),0,2)] : "")."</b>");}
		echo("</b></div></pre>");

    // SO cijfers voor deze lln ophalen en in een handig array zetten
    unset($results); // Forget previous student results
	unset($averages); // Forget previous averages for the student
	unset($vrijst); // Forget previous vrijstellingen
	// Get the SO and exam results
    $resqry = inputclassbase::load_query("SELECT result,mid,short_desc FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN `class` USING(cid) WHERE year='". $schoolyear. "' AND type <> '' AND period>1 AND sid=". $student->get_id());

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
		
// 	Paragraaf SO uitslag:
	echo("<table class=OpmaakKaart><tr class=Dikte3LijnOnder><td class = breedte2a>Vak</td>
			<td class=breedte2 colspan=2>S.E. 1<BR><SPAN class=HerSubText>her</SPAN></td><td class=breedte2 colspan=2>S.E. 2<BR><SPAN class=HerSubText>her</SPAN></td>
			<td class=breedte2 colspan=2>S.E. 3<BR><SPAN class=HerSubText>her</SPAN></td><td class=breedte2 colspan=2>S.E. 4<BR><SPAN class=HerSubText>her</SPAN></td><td class=breedte2b class=Dikte3LijnTop>S.E. gem.</td>
			<td class=breedte2a class=Dikte3LijnTop colspan=2>CSE<BR><SPAN class=HerSubText>her</SPAN></td><td class = breedte2 class=Dikte3LijnTop>Eindcijfer</td></tr>

	<tr class=Tekstgrootte>");
	// echo("<td rowspan=4 class=DunneLijnOnder class=SierTekst>");
	// echo("<b>Gemeenschappelijk deel</b></td>
	// Show all subjects in the package or as extra subject with the results
	$pksubs = $student->get_student_detail("*package");
	foreach($subjseq AS $subj)
	{
	  if(strpos(strtolower($pksubs),strtolower($subj)) !== false && 
	      substr($pksubs,strpos(strtolower($pksubs),strtolower($subj))+2,1) != ' ')
	  {
		echo("<td class=DikkeLijnen>");
		// Hier komt het verplichte vak Nederlands:
		echo($subj);
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SO1-cijfer:
		if(isset($results[$subj2mid[$subj]]["SE1TV1"]))
			echo($results[$subj2mid[$subj]]["SE1TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het HER1-cijfer:
		if(isset($results[$subj2mid[$subj]]["SE1TV2"]))
			echo($results[$subj2mid[$subj]]["SE1TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SO2-cijfer:
		if(isset($results[$subj2mid[$subj]]["SE2TV1"]))
			echo($results[$subj2mid[$subj]]["SE2TV1"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnenWegHerBG>");
		// Hier komt het HER2-cijfer:
		if(isset($results[$subj2mid[$subj]]["SE2TV2"]))
			echo($results[$subj2mid[$subj]]["SE2TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SO3-cijfer:
		if(isset($results[$subj2mid[$subj]]["SE3TV1"]))
			echo($results[$subj2mid[$subj]]["SE3TV1"]);
		// Hier komt het HER3-cijfer:
		echo("</td><td class = LijnenWegHerBG>");
		if(isset($results[$subj2mid[$subj]]["SE3TV2"]))
			echo($results[$subj2mid[$subj]]["SE3TV2"]);
		else
			echo("&nbsp;");
		echo("</td><td class = LijnOnderWeg>");
		// Hier komt het SO4-cijfer:
		if(isset($results[$subj2mid[$subj]]["SE4TV1"]))
			echo($results[$subj2mid[$subj]]["SE4TV1"]);
		// Hier komt het HER4-cijfer:
		echo("</td><td class = LijnenWegHerBG>");
		if(isset($results[$subj2mid[$subj]]["SE4TV2"]))
			echo($results[$subj2mid[$subj]]["SE4TV2"]);
		else
			echo("&nbsp;");
		echo("</td>");

		// 	Paragraaf Examenuitslag: SO-gem - CSE(+HER) - Einduitslag:
		if($subj == "gs" || $subj == "ak")
		{ // These subject don't have a central exam, so show average of SEs as final result
		  echo("<td class=Tekstgrootte>
		  <center>n.v.t.</center></td><td class=DunneLijnen>
		  <center>n.v.t.</center></td><td class=LijnenWegHerBG>
		  <center>n.v.t.</center></td><td class=Dikte3LijnRechts>
		  <center>". (isset($vrijst[$subj2mid[$subj]]) ? "v(". $vrijst[$subj2mid[$subj]]. ")" : (isset($averages[$subj2mid[$subj]][0]) ? $averages[$subj2mid[$subj]][0] : "-&#120;-")). "</center></td>
	      </tr>");
		}
		else
		{
		  echo("<td class=Tekstgrootte>
		  <center>". (isset($averages[$subj2mid[$subj]][2]) ? number_format($averages[$subj2mid[$subj]][2],1) : (isset($vrijst[$subj2mid[$subj]]) ? "&nbsp" : "-&#120;-")). "</center></td><td class=DunneLijnen>
		  <center>". (isset($vrijst[$subj2mid[$subj]]) ? "&nbsp" : (isset($results[$subj2mid[$subj]]["Ex"]) ? $results[$subj2mid[$subj]]["Ex"] : "-&#120;-")). "</center></td><td class=LijnenWegHerBG>
		  <center>". (isset($vrijst[$subj2mid[$subj]]) ? "&nbsp" : (isset($results[$subj2mid[$subj]]["Hex"]) ? $results[$subj2mid[$subj]]["Hex"] : "-&#120;-")). "</center></td><td class=Dikte3LijnRechts>
		  <center>". (isset($vrijst[$subj2mid[$subj]]) ? "v(". $vrijst[$subj2mid[$subj]]. ")" : (isset($results[$subj2mid[$subj]]["Ex"]) || isset($results[$subj2mid[$subj]]["Hex"]) ? $averages[$subj2mid[$subj]][0] : "-&#120;-")). "</center></td>
	      </tr>");
		}
	  }
    }
	
	// Show results for I&S LO CKV and PWS
	echo("<tr><td colspan=13 class=Legetabelregel>&nbsp;</td>"); // Empty line
	// LO end result display if present
	if(isset($averages[$subj2mid['lo']][0]))
	  echo("<TR><td class=DikkeLijnen>lo</td><td colspan=11 class=DikkeLijnen>&nbsp;</td><td class=Dikte3LijnRechts><center>". $averages[$subj2mid['lo']][0]. "</center></td></tr>");
	// CKV must come from previous year or filled in in EX1-5 entry
	unset($ckvres);
	$ckvmid = $subj2mid["ckv"];
	if(isset($vrijst[$ckvmid]))
	  $ckvres = $vrijst[$ckvmid];
	else
	{ // Since CKV is not entered in EX1-5 entry, it may be retrievable from previous year(s)
	  $ckvresq = inputclassbase::load_query("SELECT result FROM gradestore WHERE period=0 AND mid=". $ckvmid. " AND sid=". $student->get_id(). " ORDER BY year DESC");
	  if(isset($ckvresq['result'][0]))
	    $ckvres = $ckvresq['result'][0];	   
	}
	if(isset($ckvres))
	  echo("<TR><td class=DikkeLijnen>ckv</td><td colspan=11 class=DikkeLijnen>&nbsp;</td><td class=Dikte3LijnRechts><center>". $ckvres. "</center></td></tr>");

	// I&S must come from previous year or filled in in EX1-5 entry
	unset($isres);
	$ismid = $subj2mid["i&s"];
	$isresq = inputclassbase::load_query("SELECT xstatus FROM ahxdata WHERE year='". $schoolyear. "' AND mid=". $ismid. " AND sid=". $student->get_id());
	if(isset($isresq['xstatus'][0]))
	  $isres = $isresq['xstatus'][0];
	else
	{ // Since I&S is not entered in EX1-5 entry, it may be retrievable from previous year(s)
	  $isresq = inputclassbase::load_query("SELECT result FROM gradestore WHERE period=0 AND mid=". $ismid. " AND sid=". $student->get_id(). " ORDER BY year DESC");
	  if(isset($isresq['result'][0]))
	    $isres = $isresq['result'][0];	   
	}
	if(isset($isres))
	  echo("<TR><td class=DikkeLijnenD>i&s</td><td colspan=11 class=DikkeLijnenD>&nbsp;</td><td class=Dikte3LijnRechtsD><center>". $isres. "</center></td></tr>");
	// PWS must come from this year or filled in in EX1-5 entry
	unset($pwsres);
	$pwsmid = $subj2mid["pws"];
	$pwsresq = inputclassbase::load_query("SELECT xstatus FROM ahxdata WHERE year='". $schoolyear. "' AND mid=". $pwsmid. " AND sid=". $student->get_id());
	if(isset($pwsresq['xstatus'][0]))
	  $pwsres = $pwsresq['xstatus'][0];
	else
	{ // Since PWS is not entered in EX1-5 entry, it may be retrievable from this year(s)
	  if(isset($averages[$pwsmid][0]))
	    $pwsres = $averages[$pwsmid][0];	
      else
		$pwsres = "-x-";
	}
	echo("<TR><td class=DikkeLijnenA>pws</td><td colspan=11 class=DikkeLijnenA>&nbsp;</td><td class=Dikte3LijnRechtsA><center>". $pwsres. "</center></td></tr>");
	// Combiresult, only valid if i&s result present and pws result is not -x-
	if(isset($isres) && $pwsres != '-x-')
	{
	  $combires = round(($isres + $pwsres) / 2.0);
	}
	else
	  $combires = "-x-";
	echo("<TR><td class=DikkeLijnenA>Combivak</td><td colspan=11 class=DikkeLijnenA>&nbsp;</td><td class=Dikte3LijnRechtsA><center>". $combires. "</center></td></tr>");
	

	  // Bereken resultaten voor SO1, SO2, SO3
	$so1minp = 0;
	$so1comp = 0;
	$so1valid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if(!isset($vrijst[$subj2mid[$subjseq[$q]]])) // Exclude subjects that are excempted
	  {
	    $so1valid = (isset($results[$subj2mid[$subjseq[$q]]]) && $so1valid);
	    $sres = round(max(isset($results[$subj2mid[$subjseq[$q]]]["SE1TV1"]) ? $results[$subj2mid[$subjseq[$q]]]["SE1TV1"] : 0,
		                  isset($results[$subj2mid[$subjseq[$q]]]["SE1TV2"]) ? $results[$subj2mid[$subjseq[$q]]]["SE1TV2"] : 0),0);
		if($sres < 6)
		  $so1minp += 6 - $sres;
		else
		  $so1comp += $sres - 6;
	  }
	}
/*	if(isset($results[$subj2mid[$zevendevak]]))
	{
	  $so1valid = (isset($results[$subj2mid[$zevendevak]]) && $so1valid);
	  $sres = round(max(isset($results[$subj2mid[$zevendevak]]["SO1"]) ? $results[$subj2mid[$zevendevak]]["SO1"] : 0,
	                    isset($results[$subj2mid[$zevendevak]]["HER1"]) ? $results[$subj2mid[$zevendevak]]["HER1"] : 0),0);
	  if($sres < 6)
	    $so1minp += 6 - $sres;
	  else
	    $so1comp += $sres - 6;
    }
*/	  
	$so1pass = (($so1minp < 2 || ($so1minp == 2 && $so1comp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1));

	$so2minp = 0;
	$so2comp = 0;
	$so2valid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if(!isset($vrijst[$subj2mid[$subjseq[$q]]])) 
	  {
	    $so2valid = (isset($results[$subj2mid[$subjseq[$q]]]) && $so2valid);
	    $sres = round(max(isset($results[$subj2mid[$subjseq[$q]]]["SE2TV1"]) ? $results[$subj2mid[$subjseq[$q]]]["SE2TV1"] : 0,
		                  isset($results[$subj2mid[$subjseq[$q]]]["SE2TV2"]) ? $results[$subj2mid[$subjseq[$q]]]["SE2TV2"] : 0),0);
		if($sres < 6)
		  $so2minp += 6 - $sres;
		else
		  $so2comp += $sres - 6;
	  }
	}
/*	if(isset($results[$subj2mid[$zevendevak]]))
	{
	  $so2valid = (isset($results[$subj2mid[$zevendevak]]) && $so2valid);
	  $sres = round(max(isset($results[$subj2mid[$zevendevak]]["SO2"]) ? $results[$subj2mid[$zevendevak]]["SO2"] : 0,
	                    isset($results[$subj2mid[$zevendevak]]["HER2"]) ? $results[$subj2mid[$zevendevak]]["HER2"] : 0),0);
	  if($sres < 6)
	    $so2minp += 6 - $sres;
	  else
	    $so2comp += $sres - 6;
    }
*/	  
	$so2pass = (($so2minp < 2 || ($so2minp == 2 && $so2comp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1));

	$so3minp = 0;
	$so3comp = 0;
	$so3valid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if(!isset($vrijst[$subj2mid[$subjseq[$q]]]))
	  {
	    $so3valid = (isset($results[$subj2mid[$subjseq[$q]]]) && $so3valid);
	    $sres = round(max(isset($results[$subj2mid[$subjseq[$q]]]["SE3TV1"]) ? $results[$subj2mid[$subjseq[$q]]]["SE3TV1"] : 0,
		                  isset($results[$subj2mid[$subjseq[$q]]]["SE3TV2"]) ? $results[$subj2mid[$subjseq[$q]]]["SE3TV2"] : 0),0);
		if($sres < 6)
		  $so3minp += 6 - $sres;
		else
		  $so3comp += $sres - 6;
	  }
	}
/*	if(isset($results[$subj2mid[$zevendevak]]))
	{
	  $so3valid = (isset($results[$subj2mid[$zevendevak]]) && $so3valid);
	  $sres = round(max(isset($results[$subj2mid[$zevendevak]]["SO3"]) ? $results[$subj2mid[$zevendevak]]["SO3"] : 0,
	                    isset($results[$subj2mid[$zevendevak]]["HER3"]) ? $results[$subj2mid[$zevendevak]]["HER3"] : 0),0);
	  if($sres < 6)
	    $so3minp += 6 - $sres;
	  else
	    $so3comp += $sres - 6;
    }
*/	  
	$so3pass = (($so3minp < 2 || ($so3minp == 2 && $so3comp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1));

	$so4minp = 0;
	$so4comp = 0;
	$so4valid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if(!isset($vrijst[$subj2mid[$subjseq[$q]]]))
	  {
	    $so4valid = (isset($results[$subj2mid[$subjseq[$q]]]) && $so4valid);
	    $sres = round(max(isset($results[$subj2mid[$subjseq[$q]]]["SE4TV1"]) ? $results[$subj2mid[$subjseq[$q]]]["SE4TV1"] : 0,
		                  isset($results[$subj2mid[$subjseq[$q]]]["SE4TV2"]) ? $results[$subj2mid[$subjseq[$q]]]["SE4TV2"] : 0),0);
		if($sres < 6)
		  $so4minp += 6 - $sres;
		else
		  $so4comp += $sres - 6;
	  }
	}
	$so4pass = (($so4minp < 2 || ($so4minp == 2 && $so4comp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1));


	$sogminp = 0;
	$sogcomp = 0;
	$sogvalid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if(!isset($vrijst[$subj2mid[$subjseq[$q]]]))
	  {
	    $sogvalid = ((isset($averages[$subj2mid[$subjseq[$q]]][2]) || isset($averages[$subj2mid[$subjseq[$q]]][2]))&& $sogvalid);
        $sres = round(isset($averages[$subj2mid[$subjseq[$q]]][2]) ? $averages[$subj2mid[$subjseq[$q]]][2] : 0,0);
		if($sres < 6)
		  $sogminp += 6 - $sres;
		else
		  $sogcomp += $sres - 6;
	  }
	}
/*	if(isset($averages[$subj2mid[$zevendevak]][2]))
	{
	  $sogvalid = (isset($averages[$subj2mid[$zevendevak]][2]) && $so1valid);
        $sres = round(isset($averages[$subj2mid[$zevendevak]][2]) ? $averages[$subj2mid[$zevendevak]][2] : 0,0);
	  if($sres < 6)
	    $sogminp += 6 - $sres;
	  else
	    $sogcomp += $sres - 6;
	}
*/
	$sogpass = (($sogminp < 2 || ($sogminp == 2 && $sogcomp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1) && (isset($averages[$subj2mid[$subjseq[$package][2]]][2]) && $averages[$subj2mid[$subjseq[$package][2]]][2] > 5.4));

	$egminp = 0;
	$egcomp = 0;
	$egvalid = TRUE;
	for($q=0; $q<7; $q++)
	{
	  if(!isset($vrijst[$subj2mid[$subjseq[$q]]]))
	  {
	    $egvalid = ((isset($averages[$subj2mid[$subjseq[$q]]][3]))&& $sogvalid);
        $sres = round(isset($averages[$subj2mid[$subjseq[$q]]][0]) ? $averages[$subj2mid[$subjseq[$q]]][0] : 0,0);
		if($sres < 6)
		  $egminp += 6 - $sres;
		else
		  $egcomp += $sres - 6;
	  }
	}
/*	if(isset($averages[$subj2mid[$zevendevak]][2]))
	{
	  $egvalid = (isset($averages[$subj2mid[$zevendevak]][3]) && $so1valid);
        $sres = round(isset($averages[$subj2mid[$zevendevak]][0]) ? $averages[$subj2mid[$zevendevak]][0] : 0,0);
	  if($sres < 6)
	    $egminp += 6 - $sres;
	  else
	    $egcomp += $sres - 6;
	}
*/
	$egpass = (($egminp < 2 || ($egminp == 2 && $egcomp >= 2)) && (isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1) && (isset($averages[$subj2mid[$subjseq[$package][2]]][0]) && $averages[$subj2mid[$subjseq[$package][2]]][0] > 5.4));

	// Hier komt het resultaat
				/*echo("
				<tr><td colspan=1 class=LijnenWeg><div  align=right>Resultaat:</div></td>
					<td colspan=2 class=DikkeLijnen>". ($so1valid ? ($so1pass ? "voldoende" : "onvoldoende") : "&nbsp;"). "</td>
					<td colspan=2 class=DikkeLijnen>". ($so2valid ? ($so2pass ? "voldoende" : "onvoldoende") : "&nbsp;"). "</td>
					<td colspan=2 class=DikkeLijnen>". ($so3valid ? ($so3pass ? "voldoende" : "onvoldoende") : "&nbsp;"). "</td>
					<td colspan=2 class=DikkeLijnen>". ($so4valid ? ($so4pass ? "voldoende" : "onvoldoende") : "&nbsp;"). "</td>
					<td class=DikkeLijnen>". ($sogvalid ? ($sogpass ? "voldoende" : "onvoldoende") : "&nbsp;"). "</td>
					<td colspan=3 class=Dikte3LijnRechts> Uitslag: <B>". ($egvalid ? ($egpass ? "Geslaagd" : "Afgewezen") : "&nbsp;"). "</b></td>
				</tr>
				<tr><td colspan=1 class=LijnenWeg><div  align=right>&nbsp;</div></td>
					<td colspan=2 class=DikkeLijnenC></td>
					<td colspan=2 class=DikkeLijnenC></td>
					<td colspan=2 class=DikkeLijnenC></td>
					<td colspan=2 class=DikkeLijnenC></td>
					<td colspan=1 class=DikkeLijnenC></td>
					<td colspan=3 class=Dikte3Lijn></td>
				</tr>"); */
	echo("</table>");		
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
	echo("<P class=dirsign>Handtekening Rector<br><br><br><br><br>_________________________</P>");
	echo("<P class=pagebreak>&nbsp</p>");
	} // einde foreach student
  } // Endif 1  
  // close the page
  echo("</html>");
?>
