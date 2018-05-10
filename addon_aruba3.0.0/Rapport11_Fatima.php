<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | This program is free software.  You can redistribute in and/or       |
// | modify it under the terms of the GNU General Public License Version  |
// | 2 as published by the Free Software Foundation.                      |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY, without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program;  If not, write to the Free Software         |
// | Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.            |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  
  // Configurable part
  $graders = array('B1tab' => '1. Tellend aantal bepalen', 'B1hkb' => '2. Hoeveelheden koppelen aan getalbeeld', 'B1hkg' => '3. Hoeveelheden koppelen aan getal',
                   'B1gkg' => '1. Getalbeeld koppelen aan getal', 'B1gm' => 'Groepjes maken', 'B1ssg' => 'Samenstellen en splitsen van getallen',
				   'B2gpg' => '1. Getallen plaatsen op getallenlijn', 'B2gig' => '2.Getallen invullen op getalkaartjes', 'B2tab' => '1. Tellend aantal bepalen',
				   'B2gkb' => '2. Getallen koppelen aan getalbeeld', 'B2ast' => '1. Aanvullen en splitsen tot 10', 'asy' => '1. Auditieve synthese',
				   'wdk' => '2. Woordkennis', 'ltk' => '3. Letterkennis', 'bgl' => '4. Begrijpend lezen', 'wsch' => '1. Woordenschat', 'begr' => '2. Begrijpen',
				   'uitsp' => '3. Uitspraak', 'neth' => '1. Netheid', 'lttk' => '2. Letterkennis', 'lttb' => '3. Letterbeheersing', 'mtrk' => '4. Motoriek');
  $aspects = array('Gedr' => 'Gedrag', 'Conc' => 'Concentratie', 'Wrkt' => 'Werktempo', 'Nwk' => 'Nauwkeurigheid', 'Zelfv' => 'Zelfvertrouwen', 'Dzv' => 'Doorzettingsvermogen', 'Zelfs' => 'Zelfstandigheid', 'Clkr' => 'Contact met leerkracht', 'Cklg' => 'Contact met klasgenoten', 'Wrkvz' => 'Werkverzorging', 'Motv' => 'Motivatie', 'Ijvr' => 'IJver');
  
  // Table with month texts
  $months = array(1 => "januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
  // Translate the current group to a group id (gid)
  $sql_result = mysql_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='$CurrentGroup'",$userlink);
  $gid = mysql_result($sql_result,0,'gid');

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

  // Get the list of periods with their details
  $periods = SA_loadquery("SELECT * FROM period WHERE status='open' ORDER BY id");
  $curperiod = $periods['id'][1];
  $curyear = $periods['year'][1];

  foreach($student_array['sid'] AS $sidx)
  {
    // Get a list of results 
    $stres = SA_loadquery("SELECT result,short_desc FROM testresult LEFT JOIN testdef USING(tdid) WHERE sid=". $sidx. " AND year='". $curyear. "' AND period=1");
    // Translate to handy array
	if(isset($stres))
	  foreach($stres['short_desc'] AS $mix => $sd)
	  {
	    $result[$sidx][$sd] = $stres['result'][$mix];
	  }
  }
    
  // Get the mentor for this group
  $teacher = SA_loadquery("SELECT teacher.* FROM sgroup LEFT JOIN teacher ON(teacher.tid=sgroup.tid_mentor) WHERE active=1 AND groupname='". $CurrentGroup. "'");
  
  // Get the remarks for the report
  $rems = SA_loadquery("SELECT sid,opmtext,period FROM bo_opmrap_data LEFT JOIN sgroplink USING(sid) WHERE gid=". $gid. " AND year='". $curyear. "'");
  if(isset($rems)) // convert it to easier array
    foreach($rems['sid'] AS $rix => $rsid)
	  $rapopms[$rsid][$rems['period'][$rix]] = $rems['opmtext'][$rix];
	  
  // Get behaviour data
  $behaviour = SA_loadquery("SELECT sid,period,aspect,xstatus FROM bo_houding_data LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " AND year='". $curyear. "' AND period=1");
  if(isset($behaviour))
    foreach($behaviour['sid'] AS $bix => $bsid)
	  $behave[$bsid][$behaviour['aspect'][$bix]] = $behaviour['xstatus'][$bix];

  SA_closeDB();
  

  // First part of the page
  echo("<html><head><title>Rapporten</title></head><body link=blue vlink=blue");
  //echo(" onload=\"window.print();setTimeout(window.close(),10000);\"");
  echo(">");
  echo '<LINK rel="stylesheet" type="text/css" href="fatimarapport11.css" title="style1">';
  
  foreach($student_array['sid'] AS $si => $sidx)
  {
    echo("<DIV class=leftpage>");
	// Funny how we start with that last page of the rapport
	// Start with behavioural aspects
	foreach($aspects AS $aspab => $asptext)
	{
      echo("<SPAN class=leftclean>". $asptext. "</SPAN>");
	  if(isset($behave[$sidx][$aspab]))
	    show_result1($behave[$sidx][$aspab]);
	  else
	    echo("<SPAN class=resultblock>&nbsp;</SPAN>");
	  echo("<BR>");
	}
	echo("<BR><BR><BR><BR><SPAN class=lefthalf3>Handtekening Hoofd:<BR><BR><BR>");
	echo("<P class=signline>");
    for($i=0;$i<30;$i++) echo("&nbsp;");
	  echo("</P>");
    echo("</SPAN><SPAN class=righthalf>Handtekening leerkracht:<BR><BR><BR>");	
	echo("<P class=signline>");
    for($i=0;$i<35;$i++) echo("&nbsp;");
	  echo("</P>");
    echo("</SPAN>");

	echo("</DIV>");
	// Then the front page
	echo("<DIV class=rightpage>");
	echo("<center><img src=schoollogo.png border=0 width=244 height=244 align=center></center>");
	echo("<BR><center><img src=textlogo.gif border=0 align=center></center>");
	echo("<P class=rapporttext>RAPPORT VAN:</P><BR>");
	echo("<P class=frontlabel><SPAN class=frontlabelspan>Naam:</SPAN><SPAN class=frontfield>". $student_array['firstname'][$si]. " ". $student_array['lastname'][$si]. "</SPAN></P>");
	echo("<P class=frontlabel><SPAN class=frontlabelspan>Klas:</SPAN><SPAN class=frontfield>". $CurrentGroup. "</SPAN></P>");
	echo("<P class=frontlabel><SPAN class=frontlabelspan>Jaar:</SPAN><SPAN class=frontfield>". $curyear. "</SPAN></P>");
	echo("</DIV><P class=pagebreak>&nbsp;</P>");

    echo("<DIV class=leftpage>");
	// second page
	echo("<SPAN class=leftbordered>Rekenen</SPAN><BR><BR>");
	echo("<SPAN class=leftclean><B>Getalbegrip</B></SPAN><BR><BR>");
	echo("<SPAN class=leftclean><U>Blok 1</U></SPAN><BR>");
	echo("<SPAN class=leftclean>Hoeveelheden vergelijken</SPAN><BR>");
    show_subject1('B1tab');
    show_subject1('B1hkb');
    show_subject1('B1hkg');
	echo("<BR><SPAN class=leftclean>Structureren van getallen</SPAN><BR>");
    show_subject1('B1gkg');
    show_subject1('B1gm');
    show_subject1('B1ssg');
	echo("<BR><SPAN class=leftclean><U>Blok 2</U></SPAN><BR>");
	echo("<SPAN class=leftclean>Plaats/volgorde bepalen van getallen t/m 10</SPAN><BR>");
    show_subject1('B2gpg');
    show_subject1('B2gig');
	echo("<BR><SPAN class=leftclean>Hoeveelheden vergelijken</SPAN><BR>");
    show_subject1('B2tab');
    show_subject1('B2gkb');
	echo("<BR><SPAN class=leftclean>Structureren van getallen</SPAN><BR>");
    show_subject1('B2ast');
	if(isset($rapopms[$sidx][1]))
	  echo("<BR><BR><SPAN class=fullbordered>Opmerking: <SPAN class=opmtext>". $rapopms[$sidx][1]. "</SPAN></SPAN>");
	else
	  echo("<BR><BR><SPAN class=fullbordered>Opmerking: <SPAN class=opmtext>Geen opmerkingen.</SPAN></SPAN>");
	echo("</DIV>");
	// Then the third page
	echo("<DIV class=rightpage>");
	echo("<SPAN class=leftbordered>Lezen</SPAN><BR><BR>");
	show_subject1('asy');
	show_subject1('wdk');
	show_subject1('ltk');
	show_subject1('bgl');
	echo("<BR><BR><BR><SPAN class=leftbordered>Taal</SPAN><BR><BR>");
	show_subject1('wsch');
	show_subject1('begr');
	show_subject1('uitsp');
	echo("<BR><BR><BR><SPAN class=leftbordered>Schrijven</SPAN><BR><BR>");
	show_subject1('neth');
	show_subject1('lttk');
	show_subject1('lttb');
	show_subject1('mtrk');

	echo("<BR><BR><SPAN class=leftbordered><SPAN class=smallfont>A: uitstekend<BR>B: goed<BR>C: voldoende<BR>D: onvoldoende<BR>E: slecht</SPAN></SPAN>");

	echo("</DIV><P class=pagebreak>&nbsp;</P>");
  }
  // close the page
  echo("</html>");

  function show_subject1($sbix)
  {
    global $graders, $result, $sidx;
	if(isset($graders[$sbix]))
	{
      echo("<SPAN class=leftclean>". $graders[$sbix]. "</SPAN>");
	  if(isset($result[$sidx][$sbix]))
	  { // There is a result
	    show_result1($result[$sidx][$sbix]);
	  }
	  else
	    echo("<SPAN class=resultblock>&nbsp;</SPAN>");
	}
	echo("<BR>");
  }
  
  function show_result1($res)
  {
    echo("<SPAN class=resultblock>". $res. "</SPAN>");
  }
?>
