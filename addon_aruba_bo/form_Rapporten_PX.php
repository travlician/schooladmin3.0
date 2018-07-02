<?php
  session_start();
  
  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;

  // Configurable part
  $mainsubjects = array( 1 => "VERKEER", "ENGELS", "SPAANS", (substr($CurrentGroup,0,1) != 3 ? "LICHAMELIJKE OPVOEDING" : "ZWEMNIVEAU"), "HANDVAARDIGHEID", "TEKENEN", "MUZIKALE VORMING",
                              "GODSDIENST", "NEDERLANDSE TAAL", "LEZEN", "REKENEN", "SCHRIJVEN", "AARDRIJKSKUNDE", "GESCHIEDENIS", "KENNIS DER NATUUR", "MAATSCHAPPIJLEER","GEZONDHEIDSEDUCATIE");
  $mainsubjectabbrevs = array( 1 => "vk","en","sp",substr($CurrentGroup,0,1) != 3 ? "lo" : "zw","hv","te","mu","go","ne","le","re","sc","ak","gs","kdn","Maats.L","gze");
  /* $subsubjects["Ned"] = array(1 => "&nbsp;&nbsp;&nbsp;<i>Dictee</i>", "&nbsp;&nbsp;&nbsp;<i>Woordenschat</i>","&nbsp;&nbsp;&nbsp;<i>Taal verkennen</i>","&nbsp;&nbsp;&nbsp;<i>Tekst</i>");
  $subsubjectabbrevs["Ned"] = array(1 => "Dictee","Woord s","Taal ve","Tekst"); */
	if(substr($CurrentGroup,0,1) == 1)
	{
		$subsubjects["ne"] = array(1 => "&nbsp;&nbsp;&nbsp;<i>Begrijpend lezen</i>", "&nbsp;&nbsp;&nbsp;<i>Taaloefeningen</i>","&nbsp;&nbsp;&nbsp;<i>Dictee</i>");
		$subsubjectabbrevs["ne"] = array(1 => "bl","to","dc");		
	}
	else
	{ // 16 mrt 2018: No sub-subject on report for class 2-6
		// $subsubjects["ne"] = array(1 => "&nbsp;&nbsp;&nbsp;<i>Woordenschat</i>", "&nbsp;&nbsp;&nbsp;<i>Taalverkennen</i>","&nbsp;&nbsp;&nbsp;<i>Spelling</i>");
		// $subsubjectabbrevs["ne"] = array(1 => "ws","tv","spe");	
	}
	// 16 mrt 2018: AVI niveau below LEZEN
	//$subsubjects['le'] = array(1 => "AVI Niveau");
	//$subsubjectabbrevs['le'] = array(1 => "an");
  //$subsubjects["re"] = array(1 => "getalbegrip", "hoofdbewerking", "meten/grafieken/meetkunde","verhouding/statistiek","inzicht");
  //$subsubjectabbrevs["re"] = array(1 => "gb","hb","mm","vs","iz");
  $subjects4period = array(1 => 0,0,0,0,0,0,0,0,1,0,1,0,1,1,1,0,0);
  $aspects = array('Gedr' => 'Gedrag', 'Conc' => 'Concentratie', 'Wrkvz' => 'Werkverzorging', 'Zelfs' => 'Zelfstandigheid', 'Motv' => 'Motivatie');
  if(date("n") < 5) 
		$repper = 2;
	else if(date("n") > 8)
		$repper = 1;
	else
		$repper = 3;

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

  $uid = intval($uid);

  // Table with month texts
  $months = array(1 => "januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
  // Translate the current group to a group id (gid)
  $sql_result = mysql_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='$CurrentGroup'",$userlink);
  $gid = mysql_result($sql_result,0,'gid');

  // Get the list of periods with their details
  $periods = SA_loadquery("SELECT * FROM period WHERE status='open' ORDER BY id");
  if(!isset($periods['year']))
    $periods = SA_loadquery("SELECT * FROM period ORDER BY id DESC");
  $curperiod = $periods['id'][1];
  $curyear = $periods['year'][1];
	// Curperiod redone based on month in year...
	$curperiod = $repper;
  /*
  if($curperiod == 1 && substr($CurrentGroup,0,1) == '1')
  {
    include("Rapport11_Fatima.php");
	exit();
  }
  */
  // First we get the data from the students in an array.
  $sql_query = "SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid='$gid' ORDER BY lastname,firstname";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $student_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $row_n = $nrows;


  // Get a list of subjects 
  $subsqr = SA_loadquery("SELECT * FROM subject");
  // Translate to handy array
  foreach($subsqr['mid'] AS $mix => $cmid)
    $submid[$subsqr['shortname'][$mix]] = $cmid;

  foreach($student_array['sid'] AS $sidx)
  {
    // Get a list of calculated results 
    $stres = SA_loadquery("SELECT result,mid,period FROM gradestore WHERE sid=". $sidx. " AND year='". $curyear. "'");
    // Translate to handy array
		if(isset($stres))
			foreach($stres['mid'] AS $mix => $mid)
			{
				$result[$sidx][$mid][$stres['period'][$mix]] = $stres['result'][$mix];
			}
  }
    
  // Get the mentor for this group
  $teacher = SA_loadquery("SELECT teacher.* FROM sgroup LEFT JOIN teacher ON(teacher.tid=sgroup.tid_mentor) WHERE active=1 AND groupname='". $CurrentGroup. "'");
  
  // Get the remarks for the report
  $rems = SA_loadquery("SELECT sid,opmtext,period FROM bo_opmrap_data LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " AND year='". $curyear. "'");
  if(isset($rems)) // convert it to easier array
    foreach($rems['sid'] AS $rix => $rsid)
	  $rapopms[$rsid][$rems['period'][$rix]] = $rems['opmtext'][$rix];
	  
  // Get the year result
  $yres = SA_loadquery("SELECT sid,result,advice FROM bo_jaarresult_data LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " AND year='". $curyear. "'");
  if(isset($yres)) // convert it to easier array
    foreach($yres['sid'] AS $rix => $rsid)
	{
	  $yrresult[$rsid] = $yres['result'][$rix];
	  $yradvice[$rsid] = $yres['advice'][$rix];
	}

  // Get referrals to other school
  $rres = SA_loadquery("SELECT sid,data FROM s_ASRefferedTo LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid);
  if(isset($rres)) // convert it to easier array
    foreach($rres['sid'] AS $rix => $rsid)
	{
	  $ref[$rsid] = $rres['data'][$rix];
	}

  // Get behaviour data
  $behaviour = SA_loadquery("SELECT sid,period,aspect,xstatus FROM bo_houding_data LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " AND year='". $curyear. "'");
  if(isset($behaviour))
    foreach($behaviour['sid'] AS $bix => $bsid)
	  $behave[$bsid][$behaviour['aspect'][$bix]][$behaviour['period'][$bix]] = $behaviour['xstatus'][$bix];
	  
	  
  // Get data for absence/late/homework
  // First find out till what date
  $abqr = SA_loadquery("SELECT * FROM period");
  if(isset($abqr['id']))
    foreach($abqr['id'] AS $perix => $perid)
	{
	  $perst[$perid] = $abqr['startdate'][$perix];
	  $perend[$perid] = $abqr['enddate'][$perix];
	}

  $absq1 = "SELECT sid,SUM(IF(acid=1,1,0)) AS absent, SUM(IF(acid=2,1,0)) AS late, SUM(IF(acid=3,1,0)) AS homework FROM absence 
            LEFT JOIN absencereasons USING(aid) WHERE date <='". $perend[1]. "' AND date >= '". $perst[1]. "' GROUP BY sid";
  $absqr1 = SA_loadquery($absq1);
  if(isset($absqr1['sid']))
    foreach($absqr1['sid'] AS $abix => $sid)
	{
	  $absent[$sid][1] = $absqr1['absent'][$abix];
	  $late[$sid][1] = $absqr1['late'][$abix];
	  $homework[$sid][1] = $absqr1['homework'][$abix];
	}

  $absq2 = "SELECT sid,SUM(IF(acid=1,1,0)) AS absent, SUM(IF(acid=2,1,0)) AS late, SUM(IF(acid=3,1,0)) AS homework FROM absence 
            LEFT JOIN absencereasons USING(aid) WHERE date <='". $perend[2]. "' AND date >= '". $perst[2]. "' GROUP BY sid";
  $absqr2 = SA_loadquery($absq2);
  if(isset($absqr2['sid']))
    foreach($absqr2['sid'] AS $abix => $sid)
	{
	  $absent[$sid][2] = $absqr2['absent'][$abix];
	  $late[$sid][2] = $absqr2['late'][$abix];
	  $homework[$sid][2] = $absqr2['homework'][$abix];
	}

  $absq3 = "SELECT sid,SUM(IF(acid=1,1,0)) AS absent, SUM(IF(acid=2,1,0)) AS late, SUM(IF(acid=3,1,0)) AS homework FROM absence 
            LEFT JOIN absencereasons USING(aid) WHERE date <='". $perend[3]. "' AND date >= '". $perst[3]. "' GROUP BY sid";
  $absqr3 = SA_loadquery($absq3);
  if(isset($absqr3['sid']))
    foreach($absqr3['sid'] AS $abix => $sid)
	{
	  $absent[$sid][3] = $absqr3['absent'][$abix];
	  $late[$sid][3] = $absqr3['late'][$abix];
	  $homework[$sid][3] = $absqr3['homework'][$abix];
	}
	
/*  $absq1 = "SELECT sid,SUM(IF(authorization='Yes',1,0)) AS absentt, SUM(IF(authorization<>'Yes',1,0)) AS absentn FROM absence 
            LEFT JOIN absencereasons USING(aid) WHERE date <='". $perend[1]. "' AND date >= '". $perst[1]. "' GROUP BY sid";
  $absqr1 = SA_loadquery($absq1);
  if(isset($absqr1['sid']))
    foreach($absqr1['sid'] AS $abix => $sid)
	{
	  $absentt[$sid][1] = $absqr1['absentt'][$abix];
	  $absentn[$sid][1] = $absqr1['absentn'][$abix];
	}
  $absq2 = "SELECT sid,SUM(IF(authorization='Yes',1,0)) AS absentt, SUM(IF(authorization<>'Yes',1,0)) AS absentn FROM absence 
            LEFT JOIN absencereasons USING(aid) WHERE date <='". $perend[2]. "' AND date >= '". $perst[2]. "' GROUP BY sid";
  $absqr2 = SA_loadquery($absq2);
  if(isset($absqr2['sid']))
    foreach($absqr2['sid'] AS $abix => $sid)
	{
	  $absentt[$sid][2] = $absqr2['absentt'][$abix];
	  $absentn[$sid][2] = $absqr2['absentn'][$abix];
	}
  $absq3 = "SELECT sid,SUM(IF(authorization='Yes',1,0)) AS absentt, SUM(IF(authorization<>'Yes',1,0)) AS absentn FROM absence 
            LEFT JOIN absencereasons USING(aid) WHERE date <='". $perend[3]. "' AND date >= '". $perst[3]. "' GROUP BY sid";
  $absqr3 = SA_loadquery($absq3);
  if(isset($absqr3['sid']))
    foreach($absqr3['sid'] AS $abix => $sid)
	{
	  $absentt[$sid][3] = $absqr3['absentt'][$abix];
	  $absentn[$sid][3] = $absqr3['absentn'][$abix];
	}
*/

  SA_closeDB();
  

  // First part of the page
  echo("<html><head><title>Rapporten</title></head><body link=blue vlink=blue");
  //echo(" onload=\"window.print();setTimeout(window.close(),10000);\"");
  echo(">");
  echo '<LINK rel="stylesheet" type="text/css" href="style_pxrapport.css" title="style1">';
  
  foreach($student_array['sid'] AS $si => $sidx)
  {
    // Header: Logo left and school info text right
		echo("<DIV class=schoollogo><IMG src=schoollogo.png border=0 width=80 height=80></DIV>");
		echo("<DIV class=schoolinfo>Pius X School<BR>Mispelstraat 8-B<BR>Tel.: 5821692<BR>e-mail: piusaruba@gmail.com<BR>&nbsp;</DIV>");
		
		// Student, group and year info
		echo("<P class=frontlabel><SPAN class=frontlabelspan>Naam:</SPAN><SPAN class=frontfield>". $student_array['firstname'][$si]. " ". $student_array['lastname'][$si]. "</SPAN></P>");
		echo("<P class=frontlabel><SPAN class=frontlabelspan>Klas:</SPAN><SPAN class=frontfield>". $CurrentGroup. "</SPAN></P>");
		echo("<P class=frontlabel><SPAN class=frontlabelspan>Datum:</SPAN><SPAN class=frontfield>". date("d-m-Y"). "</SPAN></P>");
		echo("<P class=frontlabel><SPAN class=frontlabelspan>Leerjaar:</SPAN><SPAN class=frontfield>". $curyear. "</SPAN></P><P><BR>&nbsp;</P>");

			echo("<TABLE class=subjtable><TR><TH>&nbsp;</TH><TH>R1</TH><TH>R2</TH><TH>R3</TH>". (substr($CurrentGroup,0,1) > 3 ? "<TH>Eind</TH>" : ""). "</TR>");
		show_subject(9);
		echo("<TR><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD></TR>");
		show_subject(10);
		echo("<TR><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD></TR>");
		show_subject(11);
		echo("<TR><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD></TR>");
		//show_subject(12); // Changed from A-F to digits per request on oct 16th 2017 and unchanged back on nov 9 2017 wrong interpreted email.
		show_subject(12,true);
		echo("<TR><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD></TR>");
		show_subject(13);
		show_subject(14);
		show_subject(15);
		echo("<TR><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD></TR>");
		show_subject(17);
		show_subject(1);
		show_subject(2);
		show_subject(3);
		echo("<TR><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD></TR>");
		//show_subject(16);
		show_subject(4);
		show_subject(5,true);
		show_subject(6,true);
		show_subject(7,true);
		echo("<TR><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD></TR>");
		show_subject(8,true);
		
			echo("<TR class=subjmainrow><TD class=mainsubjname>HOUDING:</TD><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>");
		foreach($aspects AS $aspab => $asptext)
		{
				echo("<TR><TD class=subsubjname>&nbsp;&nbsp;&nbsp;<i>". $asptext. "</i></TD>");
			if(isset($behave[$sidx][$aspab][1]))
				show_result($behave[$sidx][$aspab][1]);
			else
				echo("<TD class=emptyresult>&nbsp;</TD>");
			if(isset($behave[$sidx][$aspab][2]) && $curperiod >= 2)
				show_result($behave[$sidx][$aspab][2]);
			else
				echo("<TD class=emptyresult>&nbsp;</TD>");
			if(isset($behave[$sidx][$aspab][3]) && $curperiod >= 3)
				show_result($behave[$sidx][$aspab][3]);
			else
				echo("<TD class=emptyresult>&nbsp;</TD>");
			echo("</TR>");
		}
		echo("<TR><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD><TD class=spaceline>&nbsp;</TD></TR>");
		
		// Table with numeric behavioural aspects
		echo("<TR><TD class=mainsubjname>Afwezig</TD>");
		for($i=1; $i<4;$i++)
		{
			//if(isset($absentt[$sidx][$i]) && $absentt[$sidx][$i] > 0 && $curperiod >= $i)
				//show_result($absentt[$sidx][$i]);
			if(isset($absent[$sidx][$i]) && $absent[$sidx][$i] > 0 && $curperiod >= $i)
				show_result($absent[$sidx][$i]);
			else if($curperiod >= $i)
				show_result("0");
			else
				show_result("&nbsp;");
		}
		echo("</TR>");
		echo("<TR><TD class=mainsubjname>Te laat</TD>");
		for($i=1; $i<4;$i++)
		{
			//if(isset($absentn[$sidx][$i]) && $absentn[$sidx][$i] > 0 && $curperiod >= $i)
				//show_result($absentn[$sidx][$i]);
			if(isset($late[$sidx][$i]) && $late[$sidx][$i] > 0 && $curperiod >= $i)
				show_result($late[$sidx][$i]);
			else if($curperiod >= $i)
				show_result("0");
			else
				show_result("&nbsp;");
		}
	/*	echo("</TR>");
		echo("<TR><TD class=mainsubjname>Huiswerk</TD>");
		for($i=1; $i<4;$i++)
		{
			if(isset($homework[$sidx][$i]) && $homework[$sidx][$i] > 0 && $curperiod >= $i)
				show_result($homework[$sidx][$i]);
				else
				show_result("&nbsp;");
		} */
		echo("</TR></TABLE>");
		echo("<BR>Betekenis van de letters:<BR>");
			echo("<SPAN class=lettermean>A=Uitstekend&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B=Goed&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;C=Voldoende&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;D=Onvoldoende&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;E=Slecht</SPAN><BR>");
		
		// Table for signatures
		/* echo("<BR><TABLE class=signtable><TR><TD width=20%>&nbsp;</TD><TD width=40%>Naam:</TD><TD>Handtekening:</TD></TR>"); */
		echo("<BR><TABLE class=signtable>");
		echo("<TR><TD>Klassenleerkracht:</TD><TD>". $teacher['firstname'][1]. " ". $teacher['lastname'][1]. "</TD><TD></TD></TR>");
		echo("<TR><TD>Hoofd:</TD><TD>Sheila Lazo-Tromp</TD><TD style='background-image: url(dirfirm.jpg); background-size: auto 100%; background-repeat: no-repeat; height: 60px;'></TD></TR>");
		echo("<TR><TD>Ouder:</TD><TD>&nbsp;</TD><TD></TD></TR>");
		
		// Final row
		echo("<TR><TD>Overgang</TD><TD colspan=2>");
		if(isset($yrresult[$sidx]))
		{
			if($yrresult[$sidx] == "OVER")
				echo("BEVORDERD");
			else if($yrresult[$sidx] == "NIET OVER")
				echo("NIET BEVORDERD");
			else if($yrresult[$sidx] == "O.W.L.")
				echo("NIET BEVORDERD, Gaat wegens leeftijd naar ". (substr($CurrentGroup,0,1) < 6 ? "klas ". (substr($CurrentGroup,0,1) + 1) : "EPB"));
			else if($yrresult[$sidx] == "S.V.")
				echo("NIET BEVORDERD; verwezen naar ". (isset($ref[$sidx]) ? $ref[$sidx] : " een andere school"). ".");

			if(isset($yradvice[$sidx]) && $yradvice[$sidx] != "")
			{
				echo(" Advies: ". $yradvice[$sidx]);
			}
		}

		echo("</TD></TR>");
		echo("</TABLE>");
	}
  // close the page
  echo("</html>");

  function show_subject($sbix,$asletter = false)
  {
    global $mainsubjects, $mainsubjectabbrevs, $subsubjects, $subsubjectabbrevs, $subjects4period, $CurrentGroup, $curperiod, $result, $submid, $sidx;
	if(isset($submid[$mainsubjectabbrevs[$sbix]]))
	  $mid = $submid[$mainsubjectabbrevs[$sbix]];
	echo("<TR class=subjmainrow><TD class=mainsubjname>". $mainsubjects[$sbix]. "</TD>");
	if(isset($mid) && isset($result[$sidx][$mid][1]) && (substr($CurrentGroup,0,1) != 1 && !$asletter))
	{ // There is a period 1 result
	  show_result($result[$sidx][$mid][1]);
	}
	else if(isset($mid) && isset($result[$sidx][$mid][1]))
	  show_letterresult($result[$sidx][$mid][1]);
	else
	  echo("<TD class=emptyresult>&nbsp;</TD>");
	if(isset($mid) && isset($result[$sidx][$mid][2]) && $curperiod >= 2)
	{ // There is a period 2 result
		if($asletter)
			show_letterresult($result[$sidx][$mid][2]);
		else
			show_result($result[$sidx][$mid][2]);
	}
	else
	  echo("<TD class=emptyresult>&nbsp;</TD>");
	if(isset($mid) && isset($result[$sidx][$mid][3]) && $curperiod >= 3)
	{ // There is a period 3 result
		if($asletter)
			show_letterresult($result[$sidx][$mid][3]);
		else
			show_result($result[$sidx][$mid][3]);
	}
	else
	  echo("<TD class=emptyresult>&nbsp;</TD>");
	if(isset($mid) && isset($result[$sidx][$mid][0]) && isset($result[$sidx][$mid][3]) && $curperiod >= 3 && $subjects4period[$sbix] == 1 && substr($CurrentGroup,0,1) > 3)
	{ // There is a period 3 result
	  show_result($result[$sidx][$mid][0]);
	}
	else if($subjects4period[$sbix] == 1 && substr($CurrentGroup,0,1) > 3)
	  echo("<TD class=per4result>&nbsp;</TD>");
	
    // Here must check for and list sub-subjects if present!
	if(isset($subsubjects[$mainsubjectabbrevs[$sbix]]))
	{
	  foreach($subsubjects[$mainsubjectabbrevs[$sbix]] AS $ssbix => $ssbdlabel)
	  {
        echo("</TR><TR><TD class=subsubjname>". $ssbdlabel. "</TD>");
	    if(isset($submid[$subsubjectabbrevs[$mainsubjectabbrevs[$sbix]][$ssbix]]))
	      $smid = $submid[$subsubjectabbrevs[$mainsubjectabbrevs[$sbix]][$ssbix]];
		else
		  unset($smid);
	    if(isset($smid) && isset($result[$sidx][$smid][1]) && substr($CurrentGroup,0,1) != 1)
	    { // There is a period 1 result
	      show_result($result[$sidx][$smid][1]);
	    }
	    else
	      echo("<TD class=emptyresult>&nbsp;</TD>");
	    if(isset($smid) && isset($result[$sidx][$smid][2]) && $curperiod >= 2)
	    { // There is a period 2 result
	      show_result($result[$sidx][$smid][2]);
	    }
	    else
	      echo("<TD class=emptyresult>&nbsp;</TD>");
	    if(isset($smid) && isset($result[$sidx][$smid][3]) && $curperiod >= 3)
	    { // There is a period 3 result
	      show_result($result[$sidx][$smid][3]);
	    }
	    else
	      echo("<TD class=emptyresult>&nbsp;</TD>");	    
	  }
	}
	echo("</TR>");
	//echo("</TABLE>");
  }
  
  function show_result($res)
  {
    echo("<TD class=resok>". $res. "</TD>");
  }
  function show_letterresult($res)
  {
		if($res>=9.0)
			show_result("A");
		else if($res >= 7.7)
			show_result("B");
		else if($res >= 5.5)
			show_result("C");
		else if($res >= 4.1)
			show_result("D");
		else if($res >0)
			show_result("E");
		else
			show_result($res);
  }
?>
