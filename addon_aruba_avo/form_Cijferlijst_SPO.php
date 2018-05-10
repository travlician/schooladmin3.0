<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2017 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
	
	if(substr($_SESSION['CurrentGroup'],0,3) != "SC3" && substr($_SESSION['CurrentGroup'],0,3) != "SV3")
	{ // Illegal group!
		echo("Voor deze groep wordt geen cijferlijst gemaakt!");
		exit;
	}
	
	$num2txt = array("1"=>"uno","2"=>"dos","3"=>"tres","4"=>"cuater","5"=>"cinco","6"=>"seis","7"=>"shete","8"=>"ocho","9"=>"nuebe","10"=>"dies","V"=>"suficiente","O"=>"no suficiente");
	$mn2txt = array(1=>"januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
  
	if(substr($_SESSION['CurrentGroup'],0,3) == "SC3")
		$location = "Santa Cruz";
	else
		$location = "Savaneta";
	
	$class = substr($_SESSION['CurrentGroup'],3,2);
  
	// Operational definitions
  $vakcats = array("Dominio di Idioma","Materia exacto","Formacion general","Arte y educacion fisico","Materia practico","Pasantia");
 
  $vakhead["Dominio di Idioma"] = array("Pa","Ne","En");
	$vakhead["Materia exacto"] = array("Rek","If");
	$vakhead["Formacion general"] = array("Prf","Prt","Pvl");
	$vakhead["Arte y educacion fisico"] = array("Ckv","Lo","Exp");
	$vakhead["Materia practico"] = array("Pca","Ppd");
	$vakhead["Pasantia"] = array("St");
  $vakdesc = array("kgl"=>"Conocemento Spiritual y Religioso",
              "pv"=>"Formacion Personal",
						  "lo"=>"Educacion Fisico",
						  "asw"=>"Ciencia Social General",
						  "pa"=>"Papiamento",
						  "ne"=>"Hulandes",
						  "en"=>"Ingles",
						  "sp"=>"Spaño",
						  "ckv"=>"Formacion Cultural y Artistico",
						  "n&t"=>"Naturalesa y Tecnologia",
						  "rek"=>"Aritmetica",
						  "ik"=>"Informatica",
							"pca"=>"Practica Comercio y Administracion",
							"ppd"=>"Practica Cuido di Mata y Bestia",
							"prf"=>"Cuido General",
							"pvl"=>"Formacion Personal y Social",
							"prt"=>"Habilidad Tecnico",
							"st"=>"Stage");
  $afwezigreden = array(1,2,3,4,5,11,12,17,18,21);
  $telaatreden = array(6,7,8,9,10,19);
  $groepfilter = $_SESSION['CurrentGroup'];
  $llnperpage = 1;
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
  
  
  $uid = $_SESSION['uid'];
  $uid = intval($uid);

  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get a list of groups
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN teacher ON(tid_mentor=tid) WHERE active=1 AND groupname LIKE '". $groepfilter. "' ORDER BY groupname");
	
	// Get the remarks
	$remarksqr = SA_loadquery("SELECT sid,opmtext,period FROM bo_opmrap_data WHERE year='". $schoolyear. "'");
	if(isset($remarksqr))
		foreach($remarksqr['sid'] AS $rmix => $rmsid)
			$remarks[$rmsid][$remarksqr['period'][$rmix]] = $remarksqr['opmtext'][$rmix];
			
  // Get a list of last test dates for periods
  //$perends = SA_loadquery("SELECT period,CEIL(date) AS edate FROM testdef GROUP BY period ORDER BY period");

	// Get the behaviuor ascpects
	$behaveqr = SA_loadquery("SELECT sid,aspect,xstatus,period FROM bo_houding_data WHERE year='". $schoolyear. "'");
	if(isset($behaveqr['sid']))
		foreach($behaveqr['sid'] AS $bix => $bsid)
			if($behaveqr['xstatus'][$bix] == 1)
				$behave[$bsid][$behaveqr['aspect'][$bix]][$behaveqr['period'][$bix]] = "O";
			else if($behaveqr['xstatus'][$bix] == 2)
				$behave[$bsid][$behaveqr['aspect'][$bix]][$behaveqr['period'][$bix]] = "V";
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Cijferlijst</title>");
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		echo("</head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Cijferlijst_SPO.css" title="style1">';

    foreach($groups['gid'] AS $gix => $gid)
		{
			// Create a list of subject details
			$sdquery = "SELECT type,fullname,shortname,'' AS data FROM subject UNION SELECT type, fullname, shortname, data FROM class LEFT JOIN subject USING(mid) LEFT JOIN ". $teachercode. " USING(tid) WHERE gid=". $gid;
			$sdquery .= " UNION SELECT type, fullname, shortname, '' FROM subject WHERE type='meta'";
			$subjectdata = SA_loadquery($sdquery);
			foreach($subjectdata['shortname'] AS $cix => $subjab)
			{
				$subjdata[$subjab]["teacher"] = $subjectdata["data"][$cix];
				$subjdata[$subjab]["fullname"] = $subjectdata["fullname"][$cix];
				$subjdata[$subjab]["type"] = $subjectdata["type"][$cix];
			}

				// Get a list of students
				$students = SA_loadquery("SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " ORDER BY lastname,firstname");


			if(isset($students))
			{
				$llnoffset = 0;
				while ($llnoffset < sizeof($students['sid']))
				{
					$scnt = $llnperpage;
					if(sizeof($students['sid']) - $llnoffset < $scnt)
						$scnt = sizeof($students['sid']) - $llnoffset;
					// Show frontpage
					echo("<img src=schoollogo.png class=frontlogo><DIV class=imageright><P class=titlehdr>LISTA DI CIFRA</p><P class=schoolnamefront>Scol Practico pa Ofishi</p><P class=location>". (substr($_SESSION['CurrentGroup'],1,1) == "C" ? "Santa Cruz" : "Savaneta"). ", Aruba</p></div><P class=classdir><SPAN class=leaderlabel>Seccion:</SPAN>");
					if($class!="AF")
						echo("Tecnica / Cuido di Mata y Bestia");
					else
						echo("Administracion y Comercio / Facilitair");
					echo("</p><p class=studname><span class=leaderlabel>Otorga na:</span>");
					echo($students['lastname'][1+$llnoffset]. ", ". $students['firstname'][1+$llnoffset]. "</p>");
					echo("<p><span class=stamboeklabel>Stamboek no : ". $students['altsid'][1+$llnoffset]. "</span></p>");
					for($sx = 1; $sx <= $scnt; $sx++)
					{
					//echo("<SPAN class=rapdata>". $students['firstname'][1+$llnoffset]. " ". $students['lastname'][1+$llnoffset]. "</SPAN>");
					echo("<TABLE class=cijferlijst>");
					}
					
					// Get the student results for students in set
					for($sx = 1; $sx <= $scnt; $sx++)
					{
						$failed = false;
						$sres = SA_loadquery("SELECT period, result, shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $students['sid'][$llnoffset+$sx]. " AND year=\"". $schoolyear. "\" ");
						if(isset($sres))
							foreach($sres['period'] AS $rix => $perid)
								$stres[$llnoffset+$sx][$sres['shortname'][$rix]][$perid] = $sres['result'][$rix];
						unset($sres);
					}
					foreach($vakcats AS $vk)
					{
						foreach($vakhead[$vk] AS $vkn)
						{
							for($sx = 1; $sx <= $scnt; $sx++)
							{
								if(isset($stres[$llnoffset+$sx][$vkn][0]))
								{
									echo("<TR><TD class=subjectcol>". (isset($vakdesc[strtolower($vkn)]) ? $vakdesc[strtolower($vkn)] : $vkn). "</TD>");
									echo("<TD class=resultcol>");
									echo(colored($stres[$llnoffset+$sx][$vkn][0]));
									if($stres[$llnoffset+$sx][$vkn][0] > 0 && $stres[$llnoffset+$sx][$vkn][0] < 4)
										$failed=true;
									echo("</TD><TD class=txtrescol>");
									if(isset($num2txt[$stres[$llnoffset+$sx][$vkn][0]]))
										echo($num2txt[$stres[$llnoffset+$sx][$vkn][0]]);
									else
										echo("&nbsp;");
									echo("</td></tr>");
								}
							}
						} // End for each subject
					} // End subject categories
					echo("</TABLE>");
					echo("<P><BR><SPAN class=resultlabel>Resultado  :  </span><SPAN class=resultline>");
					if($failed)
						echo("no ");
					echo(" a slaag</span><span class=dateline>Aruba, 1 juli ". date("Y"). "</span></p>");
					
					echo("<BR><P class=legend>Significacion di cifra<BR><BR>");
					echo("<span class=legdig>10</span>excelente<BR><BR>");
					echo("<span class=legdig>9</span>hopi bon<BR><BR>");
					echo("<span class=legdig>8</span>bon<BR><BR>");
					echo("<span class=legdig>7</span>mas cu suficiente<BR><BR>");
					echo("<span class=legdig>6</span>suficiente<BR><BR>");
					echo("<span class=legdig>5</span>casi bon<BR><BR>");
					echo("<span class=legdig>4</span>no sificiente</p>");
					
					echo("<P class=signer>Director". ($location=="Savaneta" ? "" : "a"). " adhunto <span class=signline>&nbsp</span></p>");

					echo("</DIV><P class=pagebreak>&nbsp;</P>");
					$llnoffset += $llnperpage;
				} // End while for subgroups of students
			} // End if student for the group
			
				unset($stres);
			} // End for each group
		} // End if groups defined
      
		echo("</html>");
  
  function colored($res)
  {
    $res2 = str_replace(',','.',$res);
		if($res2 < 3.0)
			$res="3,0";
		if($res2 < 5.5)
			return("<SPAN class=redcolor>". $res. "</SPAN>");
		else
			return($res);
  }	
?>
