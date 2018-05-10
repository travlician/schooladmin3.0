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
  
  // Configurable part
  $mainsubjects = array( 1 => "VERKEER", "ENGELS", "SPAANS", "LICHAMELIJKE OPVOEDING", "HANDVAARDIGHEID", "TEKENEN", "MUZIKALE VORMING",
                              "GODSDIENST", "NEDERLANDSE TAAL", "LEZEN", "REKENEN", "SCHRIJVEN", "AARDRIJKSKUNDE", "GESCHIEDENIS", "KENNIS DER NATUUR");
  $mainsubjectabbrevs = array( 1 => "vk","en","sp","lo","hv","te","mu","go","ne","le","re","sc","ak","gs","kdn");
  $subsubjects["ne"] = array(1 => "dictee", "taaloefening","tekst","opstel","luisteren","spreken");
  $subsubjectabbrevs["ne"] = array(1 => "dc","to","tk","op","lst","spr");
  //$subsubjects["re"] = array(1 => "getalbegrip", "hoofdbewerking", "meten/grafieken/meetkunde","verhouding/statistiek","inzicht");
  //$subsubjectabbrevs["re"] = array(1 => "gb","hb","mm","vs","iz");
  $subjects4period = array(1 => 0,0,0,0,0,0,0,0,1,1,1,1,1,1,1);
  $aspects = array('Gedr' => 'Gedrag', 'Conc' => 'Concentratie', 'Wrkt' => 'Werktempo', 'Nwk' => 'Nauwkeurigheid', 'Zelfv' => 'Zelfvertrouwen', 'Dzv' => 'Doorzettingsvermogen', 'Zelfs' => 'Zelfstandigheid', 'Clkr' => 'Contact met leerkracht', 'Cklg' => 'Contact met klasgenoten', 'Wrkvz' => 'Werkverzorging', 'Motv' => 'Motivatie', 'Ijvr' => 'IJver');
  

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;

  $uid = intval($uid);

  // Table with month texts
  $months = array(1 => "januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
  // Translate the current group to a group id (gid)
  $sql_result = mysql_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='$CurrentGroup'",$userlink);
  $gid = mysql_result($sql_result,0,'gid');

  // Get the list of periods with their details
  $periods = SA_loadquery("SELECT * FROM period WHERE status='open' ORDER BY id");
  $curperiod = $periods['id'][1];
  $curyear = $periods['year'][1];
  
  if($curperiod == 1 && substr($CurrentGroup,0,1) == '1')
  {
    //include("Rapport11_Fatima.php");
	echo("Gebruik formulier Rapport11_Fatima!");
	exit();
  }
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
  $rems = SA_loadquery("SELECT sid,opmtext,period FROM bo_opmrap_data LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " AND year='". $curyear. "'");
  if(isset($rems)) // convert it to easier array
    foreach($rems['sid'] AS $rix => $rsid)
	  $rapopms[$rsid][$rems['period'][$rix]] = $rems['opmtext'][$rix];

  // Get the year results
  $yresqr = SA_loadquery("SELECT sid,result FROM bo_jaarresult_data LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " AND year='". $curyear. "'");
  if(isset($yresqr)) // convert it to easier array
    foreach($yresqr['sid'] AS $rix => $rsid)
	  $yrresult[$rsid] = $yresqr['result'][$rix];
  
	  
  // Get behaviour data
  $behaviour = SA_loadquery("SELECT sid,period,aspect,xstatus FROM bo_houding_data LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " AND year='". $curyear. "'");
  if(isset($behaviour))
    foreach($behaviour['sid'] AS $bix => $bsid)
	  $behave[$bsid][$behaviour['aspect'][$bix]][$behaviour['period'][$bix]] = $behaviour['xstatus'][$bix];
	  
	  
  // Get data for absence/late/homework
  // First find out till what date
  $abdq = "SELECT * FROM period";
  $abqr = SA_loadquery($abdq);
  if(isset($abqr))
    foreach($abqr['id'] AS $perix => $perid)
	{
	  $perstart[$perid] = $abqr['startdate'][$perix];
	  $perend[$perid] = $abqr['enddate'][$perix];
	}

  $absq1 = "SELECT sid,SUM(IF(acid=1,1,0)) AS absent, SUM(IF(acid=2,1,0)) AS late, SUM(IF(acid=3,1,0)) AS homework FROM absence 
            LEFT JOIN absencereasons USING(aid) WHERE date <= '". $perend[1]. "' AND date >= '". $perstart[1]. "' GROUP BY sid";
  $absqr1 = SA_loadquery($absq1);
  if(isset($absqr1['sid']))
    foreach($absqr1['sid'] AS $abix => $sid)
	{
	  $absent[$sid][1] = $absqr1['absent'][$abix];
	  $late[$sid][1] = $absqr1['late'][$abix];
	  $homework[$sid][1] = $absqr1['homework'][$abix];
	}

  $absq2 = "SELECT sid,SUM(IF(acid=1,1,0)) AS absent, SUM(IF(acid=2,1,0)) AS late, SUM(IF(acid=3,1,0)) AS homework FROM absence 
            LEFT JOIN absencereasons USING(aid) WHERE date <= '". $perend[2]. "' AND date >= '". $perstart[2]. "' GROUP BY sid";
  $absqr2 = SA_loadquery($absq2);
  if(isset($absqr2['sid']))
    foreach($absqr2['sid'] AS $abix => $sid)
	{
	  $absent[$sid][2] = $absqr2['absent'][$abix];
	  $late[$sid][2] = $absqr2['late'][$abix];
	  $homework[$sid][2] = $absqr2['homework'][$abix];
	}

  $absq3 = "SELECT sid,SUM(IF(acid=1,1,0)) AS absent, SUM(IF(acid=2,1,0)) AS late, SUM(IF(acid=3,1,0)) AS homework FROM absence 
            LEFT JOIN absencereasons USING(aid) WHERE date <= '". $perend[3]. "' AND date >= '". $perstart[3]. "' GROUP BY sid";
  $absqr3 = SA_loadquery($absq3);
  if(isset($absqr3['sid']))
    foreach($absqr3['sid'] AS $abix => $sid)
	{
	  $absent[$sid][3] = $absqr3['absent'][$abix];
	  $late[$sid][3] = $absqr3['late'][$abix];
	  $homework[$sid][3] = $absqr3['homework'][$abix];
	}

  SA_closeDB();
  

  // First part of the page
  echo("<html><head><title>Rapporten</title></head><body link=blue vlink=blue");
  //echo(" onload=\"window.print();setTimeout(window.close(),10000);\"");
  echo(">");
  echo '<LINK rel="stylesheet" type="text/css" href="fatimarapport.css" title="style1">';
  
  foreach($student_array['sid'] AS $si => $sidx)
  {
    echo("<DIV class=leftpage>");
	// Funny how we start with that last page of the rapport
	// Start with behavioural aspects
    echo("<TABLE class=subjtable><TR class=subjmainrow><TD class=mainsubjname>HOUDING</TD><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>");
	foreach($aspects AS $aspab => $asptext)
	{
      echo("<TR><TD class=subsubjname>". $asptext. "</TD>");
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
	echo("</TABLE><BR>");
	
	show_subject(8);
	
	// Table with numeric behavioural aspects
	echo("<BR><TABLE class=numasptable><TR><TD class=numberaspects>Te laat</TD>");
	for($i=1; $i<4;$i++)
	{
	  if(isset($late[$sidx][$i]) && $late[$sidx][$i] > 0)
	    show_result($late[$sidx][$i]);
      else
	    show_result("&nbsp;");
	}
	echo("</TR>");
	echo("<TR><TD class=numberaspects>Afwezig</TD>");
	for($i=1; $i<4;$i++)
	{
	  if(isset($absent[$sidx][$i]) && $absent[$sidx][$i] > 0)
	    show_result($absent[$sidx][$i]);
      else
	    show_result("&nbsp;");
	}
	echo("</TR>");
	echo("<TR><TD class=numberaspects>Huiswerk</TD>");
	for($i=1; $i<4;$i++)
	{
	  if(isset($homework[$sidx][$i]) && $homework[$sidx][$i] > 0)
	    show_result($homework[$sidx][$i]);
      else
	    show_result("&nbsp;");
	}
	echo("</TR></TABLE>");
	echo("<BR><BR><BR><BR><B>Betekenis van de</B><BR><SPAN class=lefthalf2><b>letters:</B><BR><BR>");
    echo("A=Uitstekend<BR>B=Goed<BR>C=Voldoende<BR>D=Onvoldoende<BR>E=Slecht</SPAN>");
    echo("<SPAN class=righthalf><B>cijfers:</B><BR><BR>10=uitmuntend<BR>9=zeer goed<BR>8=goed<BR>7=ruim voldoende<BR>6=voldoende<BR>");
	echo("5=onvoldoende<BR>4=slecht<BR>4-=zeer slecht</SPAN>");

	echo("</DIV>");
	// Then the front page
	echo("<DIV class=rightpage>");
	echo("<center><img src=schoollogo.png border=0 width=244 height=244 align=center></center><BR>");
	echo("<BR><BR>");
	echo("<DIV class=rapporttext>Rapport</DIV><BR>");
	echo("<P class=frontlabel><SPAN class=frontlabelspan>Over:</SPAN><SPAN class=frontfield>". $student_array['firstname'][$si]. " ". $student_array['lastname'][$si]. "</SPAN></P>");
	echo("<P class=frontlabel><SPAN class=frontlabelspan>Klas:</SPAN><SPAN class=frontfield>". $CurrentGroup. "</SPAN></P>");
	echo("<P class=frontlabel><SPAN class=frontlabelspan>Leerjaar:</SPAN><SPAN class=frontfield>". $curyear. "</SPAN></P>");
	echo("</DIV><P class=pagebreak>&nbsp;</P>");

    echo("<DIV class=leftpage>");
	// second page
    show_subject(9);
    show_subject(10);
    show_subject(11);
    show_subject(12);
    show_subject(13);
    show_subject(14);
    show_subject(15);

	echo("<BR><BR><B>Handtekening Ouder/Voogd:</B><BR>");
    for($p=1; $p<=3; $p++)
	{
	  echo("<DIV class=signline> ". $p. ". ");
      for($i=0;$i<45;$i++) echo("&nbsp;");
	  echo("</DIV>");
	}
	echo("</DIV>");
	// Then the third page
	echo("<DIV class=rightpage>");
	show_subject(1);
	show_subject(2);
	show_subject(3);
	show_subject(4);
	show_subject(5);
	show_subject(6);
	show_subject(7);
	echo("<BR><B>Opmerkingen:</B><BR>");
	
    for($p=1; $p<=3; $p++)
	{
	  echo("<BR><U> ". $p. "e: ");
	  if(isset($rapopms[$sidx][$p]))
	    echo("<SPAN class=remarktext>". $rapopms[$sidx][$p]). "</SPAN>";
	  else
	  {
	    for($i=0;$i<80;$i++) 
		  echo("&nbsp;");
		echo("<BR>");
	    for($i=0;$i<86;$i++) 
		  echo("&nbsp;");
	  }
	  echo("</U>");
	}
	
	echo("<BR><BR><BR><BR><SPAN class=lefthalf3><B>Handtekening leerkracht:</B><BR>");
    for($p=1; $p<=3; $p++)
	{
	  echo("<P class=signline> ". $p. ". ");
      for($i=0;$i<30;$i++) echo("&nbsp;");
	  echo("</P>");
	}
    echo("</SPAN><SPAN class=righthalf><B>Hoofd der school:</B><BR>");	
    for($p=1; $p<=3; $p++)
	{
	  echo("<P class=signline> ". $p. ". ");
      for($i=0;$i<25;$i++) echo("&nbsp;");
	  echo("</P>");
	}
    echo("</SPAN>");
	if(isset($yrresult[$sidx]))
	{
	  echo("<font size=+2><center>");
	  if($yrresult[$sidx] == "OVER")
	    echo("Bevorderd");
	  else if($yrresult[$sidx] == "NIET OVER")
	    echo("Niet bevorderd");
	  else if($yrresult[$sidx] == "O.W.L.")
	    echo("Over wegens leeftijd");
	  else if($yrresult[$sidx] == "S.V.")
	    echo("Niet bevorderd");
	  echo("</center></font>");
    }

	echo("</DIV><P class=pagebreak>&nbsp;</P>");
  }
  // close the page
  echo("</html>");

  function show_subject($sbix)
  {
    global $mainsubjects, $mainsubjectabbrevs, $subsubjects, $subsubjectabbrevs, $subjects4period, $CurrentGroup, $curperiod, $result, $submid, $sidx;
	if(isset($submid[$mainsubjectabbrevs[$sbix]]))
	  $mid = $submid[$mainsubjectabbrevs[$sbix]];
    //echo("<TABLE class=subjtable><TR class=subjmainrow><TD class=mainsubjname>". $mainsubjects[$sbix]. "(". $mid. ")". "</TD>");
    echo("<TABLE class=subjtable><TR class=subjmainrow><TD class=mainsubjname>". $mainsubjects[$sbix]. "</TD>");
	if(isset($mid) && isset($result[$sidx][$mid][1]) && substr($CurrentGroup,0,1) != 1)
	{ // There is a period 1 result
	  show_result($result[$sidx][$mid][1]);
	}
	else
	  echo("<TD class=emptyresult>&nbsp;</TD>");
	if(isset($mid) && isset($result[$sidx][$mid][2]) && $curperiod >= 2)
	{ // There is a period 2 result
	  show_result($result[$sidx][$mid][2]);
	}
	else
	  echo("<TD class=emptyresult>&nbsp;</TD>");
	if(isset($mid) && isset($result[$sidx][$mid][3]) && $curperiod >= 3)
	{ // There is a period 3 result
	  show_result($result[$sidx][$mid][3]);
	}
	else
	  echo("<TD class=emptyresult>&nbsp;</TD>");
	if(isset($mid) && isset($result[$sidx][$mid][0]) && $curperiod >= 3 && $subjects4period[$sbix] == 1 && substr($CurrentGroup,0,1) > 3)
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
	echo("</TR></TABLE>");
	echo("<BR>");	
  }
  
  function show_result($res)
  {
    echo("<TD class=resok>". $res. "</TD>");
  }
?>
