<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+

 // $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("schooladminconstants.php");
  require_once("inputlib/inputclasses.php");
  require_once("SCTclasses.php");
  require_once("group.php");
  require_once("teacher.php");
  require_once("student.php");
  session_start();
  // Connect to the database
  inputclassbase::dbconnect($userlink);
  
  // If a global SCTprefix is defined, we use that to prefix tables SCTtestand SCTtestitem, else global is emulated as empty
  if(!isset($SCTprefix))
    $SCTprefix="";
  
  if(isset($_POST['fieldid']))
  {
    // DEBUG: show field change
		//echo("RESULT: ". $_POST['fieldid']. " = ". $_POST[$_POST['fieldid']]);
    // Let the library page handle the data
    include("inputlib/procinput.php");  
		// If testreference has changed, refresh!
		if(substr($_POST['fieldid'],0,5) == "scttr")
			echo(" REFRESH");
		// Testing javascript parse
		if(substr($_POST['fieldid'],0,8) == "resentry")
		{ // A result was entered, calculate further depending on data encoded in fieldid 
			//echo("Resentry detect");
			$resdata = explode(":",$_POST['fieldid']);
			// resdata 0=resentry, 1=sid, 2=sctid, 3=phase (s,c,t), 4 is test number (0 for total points / errors)
			// Calculation is done through the SCTtest class so get the object
			$testobj = new SCTtest($resdata[2]);
			// We need the current schoolyear for this!
			$syear = inputclassbase::load_query("SELECT year FROM period ORDER BY id");
			$syear = $syear['year'][0];
			if($testobj->get_catweight() && $resdata[3] != "t")
			{
				if($resdata[3] != "t" && $resdata[4] != 0)
				{ // Need to put the reteaching/remediating data for this phase
					echo("[pr:". $resdata[1]. ":". $resdata[3]. "]{". $testobj->get_phase_remediate($resdata[1],$resdata[3],$syear). "}");
				}
				// Need to put the result for this phase and for the test as a total
				echo("[pc:". $resdata[1]. ":". $resdata[3]. "]{". $testobj->get_phase_result($resdata[1],$resdata[3],$syear). "}");
				echo("[tc:". $resdata[1]. "]{". $testobj->get_result($resdata[1],$syear,true). "}");
				// Need to put the result for the sub-subjects since indiviual items have changed
				$subsbs = $testobj->get_subjectsused();
				if(count($subsbs) > 1)
				{
					foreach($subsbs AS $amid => $dummy)
						if($amid != "")
							echo("[sc:". $resdata[1]. ":". $amid. "]{". $testobj->get_subject_result($resdata[1],$amid,$syear,true). "}");
				}
				// Need to put the result for this test item
				$tstitemobj = new SCTtestitem($resdata[2],$resdata[4]);
				echo("[tir:". $resdata[1]. ":". $resdata[2]. ":". $resdata[3]. ":". $resdata[4]. "]{". $tstitemobj->get_result_rnd($resdata[1],$resdata[3],$syear). "}");
			}
			else
			{
				if($resdata[3] != "t")
				{ // need to put the calculated other points for this phase
					echo("[po:". $resdata[1]. ":". $resdata[3]. "]{". $testobj->get_phase_other($resdata[1],$resdata[3],$syear). "}");
				}
				if($resdata[3] != "t" && $resdata[4] != 0)
				{ // Need to put the reteaching/remediating data for this phase
					echo("[pr:". $resdata[1]. ":". $resdata[3]. "]{". $testobj->get_phase_remediate($resdata[1],$resdata[3],$syear). "}");
				}
				if($resdata[4] == 0)
				{ // Need to put the result for this phase and for the test as a total
					echo("[pc:". $resdata[1]. ":". $resdata[3]. "]{". $testobj->get_phase_result($resdata[1],$resdata[3],$syear). "}");
					echo("[tc:". $resdata[1]. "]{". $testobj->get_result($resdata[1],$syear,true). "}");
				}
				if($resdata[4] != 0)
				{
					// Need to put the result for the sub-subjects since indiviual items have changed
					$subsbs = $testobj->get_subjectsused();
					if(count($subsbs) > 1)
					{
						foreach($subsbs AS $amid => $dummy)
							echo("[sc:". $resdata[1]. ":". $amid. "]{". $testobj->get_subject_result($resdata[1],$amid,$syear,true). "}");
					}
				}
			}
		}
    exit;
  }
	// See if the testcolor exists, if not, we need to add that column to the database
	$tcolcheck = inputclassbase::load_query("SHOW COLUMNS FROM ". $SCTprefix. "SCTtest LIKE 'testcolor'");
	if(!isset($tcolcheck))
	{ 
		// add the testcolor column
		mysql_query("ALTER TABLE ". $SCTprefix. "SCTtest ADD COLUMN testcolor TEXT", $userlink);
		echo(mysql_error());
	}
 
  // Link with stylesheet
  echo ('<HTML><BODY><LINK rel="stylesheet" type="text/css" href="style_SCT.css" title="style1">');
  
  // Get the year
  $periodqr = inputclassbase::load_query("SELECT * FROM period WHERE status != 'final' UNION SELECT * FROM period");
  $schoolyear = $periodqr['year'][0];
	if(isset($_GET['print']))
		echo("<H1>". date("d-m-Y"). "</h1>");
	else
		echo("<DIV ID=topsupressable><H1>SCT (Signaal, Controle, Toepassing) Toets resultaten</H1>");
  if(!isset($_GET['sctid']))
  { // Select an SCT test 
		echo("<FORM METHOD=POST ID=sctsearchbox>Toets filter: <INPUT TYPE=TEXT NAME=SCTsearch onChange='document.getElementById(\"sctsearchbox\").submit();' VALUE='". (isset($_POST['SCTsearch']) ? $_POST['SCTsearch'] : (isset($sctsearch) ? $sctsearch : "")). "'><IMG SRC='PNG/search.png'></FORM>");
   $sctlist = SCTtest::listObjects(isset($_POST['SCTsearch']) ? $_POST['SCTsearch'] : (isset($sctsearch) ? $sctsearch : NULL));
		if(isset($sctlist))
		{
			echo("<table><tr><th>Omschrijving</th><th>&nbsp</th></tr>");
			foreach($sctlist AS $scttestobj)
			{
				echo("<TR><TD style='". $scttestobj->get_style(). "'>". $scttestobj->get_description(). "</td><td><a href=". $_SERVER['REQUEST_URI']. "?sctid=". $scttestobj->get_id(). "><img src='PNG/reply.png'></a>");
			}
			echo("</table>");
		}
		echo("</DIV>");
  }
  else
  { // An SCT test is selected, first see if test definitions are filled
    // First get my group
		$skipphasefinal = 0; // indocator default value to supress phase and final result
		$mygroup = new group();
		$mygroup->load_current();
		// Get a list of subject(s) for which we need test defs
		$scttestobj = new SCTtest($_GET['sctid']);
		// Display the test name
		echo("<H3>". $scttestobj->get_description(). "</H3>");
		$mysubjects = $scttestobj->get_subjectsused();
		$emptysubs = 0;
		foreach($mysubjects AS $amid => $sbname)
			if($amid == "")
				$emptysubs++;
		
		//if(!isset($mysubjects) || $emptysubs > 0)
		if(!isset($mysubjects))
			echo("SCT toets niet compleet gedefinieerd.</DIV>");
		else
		{ // Now we first see if test definitions are filled already and what options are available
			foreach($mysubjects AS $amid => $sbname)
			{
				if($amid != "")
				{
					//echo("<BR>Testdef voor ". $sbname);
					$tdqr = inputclassbase::load_query("SELECT tdid FROM SCTtestref LEFT JOIN class USING(cid) LEFT JOIN testdef USING(tdid) WHERE sctid=". $scttestobj->get_id(). " AND mid=". $amid. " AND gid=". $mygroup->get_id(). " AND year='". $schoolyear. "'");
					if(isset($tdqr['tdid']))
						$tdid4[$amid] = $tdqr['tdid'][0];
					$tdoqr = inputclassbase::load_query("SELECT GROUP_CONCAT(tdid) AS tdopts FROM testdef LEFT JOIN class USING(cid) LEFT JOIN period ON(period.id=testdef.period) LEFT JOIN SCTtestref USING(tdid) WHERE sctid IS NULL AND mid=". $amid. " AND testdef.year='". $schoolyear. "' AND gid=". $mygroup->get_id(). " AND period.status='open' GROUP BY mid");
					if(isset($tdoqr['tdopts']) || isset($tdid4[$amid]))
					{
						if(isset($tdid4[$amid]))
						{
							$tdos4[$amid] = $tdid4[$amid];
							if(isset($tdoqr['tdopts']))
								$tdos4[$amid] .= ",". $tdoqr['tdopts'][0];

						}
						else
							$tdos4[$amid] = $tdoqr['tdopts'][0];
						// echo(" options: ". $tdoqr['tdopts'][0]);
					}
					else
					{ // No options are available, we create a testdef and set that!
						// We need to get a test type, get the first one for our subject
						$ttqr = inputclassbase::load_query("SELECT testtype FROM reportcalc WHERE (mid=0 OR mid=". $amid. ") AND weight > 0 ORDER BY mid DESC");
						$tt = $ttqr['testtype'][0];
						// We need to get a cid for the testdef.
						$cidqr = inputclassbase::load_query("SELECT cid FROM class WHERE gid=". $mygroup->get_id(). " AND mid=". $amid);
						if(!isset($cidqr['cid']))
						{
							echo("Deze klas krijgt niet de vakken die nodig zijn voor deze toets!");
						exit;
							}
						$cid=$cidqr['cid'][0];
						//echo("cid set to ". $cid);
						// We need a period id for the new testdef
						$per=$periodqr['id'][0];
						// Create the new testdef
						mysql_query("INSERT INTO testdef (short_desc,description,date,type,period,cid,year,week) VALUES('SCT". $scttestobj->get_id(). "','". $scttestobj->get_description(). "',CURDATE(),'". $tt. "',". $per. ",". $cid. ",'". $schoolyear. "',WEEK(CURDATE()))", $userlink);
						$tdid = mysql_insert_id($userlink);
						// Set the testdef
						$tdid4[$amid] = $tdid;
						$tdos4[$amid] = $tdid;
						mysql_query("INSERT INTO SCTtestref (sctid,cid,tdid) VALUES(". $scttestobj->get_id(). ",". $cid. ",". $tdid. ")", $userlink);
						//echo(" option ". $tdid. " created");
					}
				}
			}
			// Now see if we may change the testdefs. If results are entered, this is a no go (except for Administrator)
			$I = new teacher();
			$I->load_current();
			$mayedit = true;
			if(!$I->has_role("admin"))
			{ // Now if results are set for this tests, we can not change the testdefs unless...
				foreach($mysubjects AS $amid => $sbname)
				{
					$trcheckqr = inputclassbase::load_query("SELECT result FROM SCTresult LEFT JOIN sgrouplink USING(sid) WHERE sctid=". $scttestobj->get_id(). " AND gid=". $mygroup->get_id(). " AND year='". $schoolyear. "'");
					if(isset($trcheck['result']))
						$mayedit = false;
				}	    
			}
			if(!isset($_GET['print']))
			{
				// Now show the dialogue part to edit testdefs
				foreach($mysubjects AS $amid => $sbname)
				{
					if($amid != "")
					{
						// We need to get a cid for the testdef.
						$cidqr = inputclassbase::load_query("SELECT cid FROM class WHERE gid=". $mygroup->get_id(). " AND mid=". $amid);
						$cid=$cidqr['cid'][0];
							echo("<BR><LABEL class=testreflabel>Toetsdefinitie voor ". $sbname. ":</LABEL>");
						$trfld = new inputclass_listfield("scttr". $amid,"SELECT '' AS id,'' AS tekst UNION SELECT tdid,description FROM testdef WHERE tdid IN(". $tdos4[$amid]. ")",NULL,"tdid","SCTtestref",$scttestobj->get_id(),"sctid",NULL,$_SERVER['PHP_SELF']);
						$trfld->set_extrakey("cid", $cid);
						if($mayedit)
							$trfld->echo_html();
						else
							echo($trfld->__toString());
					}
				}
			}
			// Now see if all testrefs needed are present, and if so, show the test entry form.
			$alldef = true;
			foreach($mysubjects AS $amid => $sbname)
			{
				if($amid != "")
					if(!isset($tdid4[$amid]) || $tdid4[$amid] == '')
						$alldef = false;
			}
			if($alldef)
			{ // It's ok to show the test result entry form
				// Close the top div, and hide if not admin
				echo("</DIV>");
				if(!$I->has_role("admin"))
				{
					echo("<SCRIPT> document.getElementById('topsupressable').style.display='none'; </SCRIPT>");
				}
				if(!isset($_GET['print']))
				{
					// Prepare the date fields
					$sdatefld = new inputclass_datefield("sdate",NULL,NULL,"sdate","SCTtestdates",$scttestobj->get_id(),"sctid");
					$sdatefld->set_extrakey("gid",$mygroup->get_id());
					$cdatefld = new inputclass_datefield("cdate",NULL,NULL,"cdate","SCTtestdates",$scttestobj->get_id(),"sctid");
					$cdatefld->set_extrakey("gid",$mygroup->get_id());
					$tdatefld = new inputclass_datefield("tdate",NULL,NULL,"tdate","SCTtestdates",$scttestobj->get_id(),"sctid");
					$tdatefld->set_extrakey("gid",$mygroup->get_id());
				}
				echo("<TABLE class=resulttable>");
				if(!isset($_GET['print']))
				{
					if(count($scttestobj->get_subjectsused()) > 1 && $scttestobj->get_catweight() == 1)
						$skipphasefinal = 1;
					else
						$skipphasefinal = 0;
					echo("<TR><th class=toprow1 rowspan=5>&nbsp;</th><th class=toprow1>&nbsp;</th><th class=toprow2 colspan=". ($scttestobj->get_testcount() + ($scttestobj->get_catweight() ? ($skipphasefinal == 1 ? 1 : 2) : 4)). ">Signaaltoets");
					$sdatefld->echo_html();
					if($scttestobj->get_item("controlweight") != "")
					{
						echo("</th><th class=toprow2 colspan=". ($scttestobj->get_testcount() + ($scttestobj->get_catweight() ? ($skipphasefinal == 1 ? 1 : 2) : 4)). ">Controletoets");
						$cdatefld->echo_html();
					}
					if($scttestobj->get_item("terminalweight") != "")
					{
						echo("</th><th class=toprow2 colspan=3>Toepassing");
						$tdatefld->echo_html();
					}
					echo("</th></tr>");
				}
				$testitems = $scttestobj->get_testitems();
				if(!isset($_GET['print']))
				{
					echo("<tr><td class=frontrow1>Max aantal ". $scttestobj->get_type());
					if(!$scttestobj->get_catweight())
						echo("</td><td class=rescol>&nbsp;</td>");
					foreach($testitems AS $testitemobj)
						echo("<td class=rescol>". $testitemobj->get_maxpoints(). "</td>");
					echo("<td class=rescolsep colspan=". ($scttestobj->get_catweight() ? ($skipphasefinal == 1 ? 1 : 2) : 3). ">&nbsp;</td>");
					if($scttestobj->get_item("controlweight") != "")
					{
						if(!$scttestobj->get_catweight())
							echo("<td class=rescol1>&nbsp;</td>");
						foreach($testitems AS $testitemobj)
							echo("<td class=rescol>". $testitemobj->get_maxpoints(). "</td>");
						echo("<td class=rescolsep colspan=". ($scttestobj->get_catweight() ? ($skipphasefinal == 1 ? 1 : 2) : 3). ">&nbsp;</td>");
					}
					if($scttestobj->get_item("terminalweight") != "")
					{
						echo("<td class=rescolendtext colspan=3 rowspan=2>&nbsp;</td>");
					}
					// If there are multiple sub-subjects, these results need also be displayed.
					$subsbs = $scttestobj->get_subjectsused();
					if(count($subsbs) > 1)
					{
						foreach($subsbs AS $asn)
							if($asn != "")
								echo("<td class=rescoltext rowspan=4>Cijfer ". $asn. "</td>");		    
					}
					if($skipphasefinal == 0)
						echo("<td class=rescolendtext rowspan=4>Eindcijfer toets</td></tr>");
					echo("<tr><td class=frontrow1>Remediëring</td>");
					if(!$scttestobj->get_catweight())
						echo("<td class=rescol1>&nbsp;</td>");
					foreach($testitems AS $testitemobj)
						echo("<td class=rescol>". $testitemobj->get_category(). "</td>");
					echo("<td class=rescolsep colspan=". ($scttestobj->get_catweight() ? ($skipphasefinal == 1 ? 1 : 2) : 3). ">&nbsp;</td>");
					if($scttestobj->get_item("controlweight") != "")
					{
						if(!$scttestobj->get_catweight())
							echo("<td class=rescol1>&nbsp;</td>");
						foreach($testitems AS $testitemobj)
							echo("<td class=rescol>". $testitemobj->get_category(). "</td>");
						echo("<td class=rescolsep colspan=". ($scttestobj->get_catweight() ? ($skipphasefinal == 1 ? 1 : 2) : 3). ">&nbsp;</td>");
					}
					echo("</tr>");
					echo("<tr><th class=frontrow2>". $scttestobj->get_description(). "</th>");
					if(!$scttestobj->get_catweight())
						echo("<td class=rescol1text>Totaal aantal ". $scttestobj->get_type(). "</td>");
					foreach($testitems AS $testitemobj)
						echo("<td class=rescoltext>". $testitemobj->get_description(). "</td>");
					if(!$scttestobj->get_catweight())
						echo("<td class=rescoltext>Andere ". $scttestobj->get_type(). "</td>");
					if($skipphasefinal == 0)
					{
						echo("<td class=rescol><A href='". $_SERVER['REQUEST_URI']. "&print' target=new class=remlink>Remediëring</a></td>");
						echo("<td class=rescolseptext>Cijfer</td>");
					}
					else
						echo("<td class=rescolsep><A href='". $_SERVER['REQUEST_URI']. "&print' target=new class=remlink>Remediëring</a></td>");
						
					if($scttestobj->get_item("controlweight") != "")
					{
						if(!$scttestobj->get_catweight())
							echo("<td class=rescol1text>Totaal aantal ". $scttestobj->get_type(). "</td>");
						foreach($testitems AS $testitemobj)
							echo("<td class=rescoltext>". $testitemobj->get_description(). "</td>");
						if(!$scttestobj->get_catweight())
							echo("<td class=rescoltext>Andere ". $scttestobj->get_type(). "</td>");
					if($skipphasefinal == 0)
					{
						echo("<td class=rescol><A href='". $_SERVER['REQUEST_URI']. "&print' target=new class=remlink>Remediëring</a></td>");
						echo("<td class=rescolseptext>Cijfer</td>");
					}
					else
						echo("<td class=rescolsep><A href='". $_SERVER['REQUEST_URI']. "&print' target=new class=remlink>Remediëring</a></td>");
					}

					if($scttestobj->get_item("terminalweight") != "")
					{
						echo("<td class=rescoltext>Aantal ". $scttestobj->get_type(). "</td>");
						echo("<td class=rescoltext>Cijfer</td>");
						echo("<td class=rescolend><A href='". $_SERVER['REQUEST_URI']. "&print' target=new class=remlink>Remediëring</a><BR>zelf in te vullen</td>");
					}
					echo("</tr>");
					echo("<tr><td class=frontrow1>Signaleringsgrens</td>");
					if(!$scttestobj->get_catweight())
						echo("<td class=rescol1>&nbsp;</td>");
					foreach($testitems AS $testitemobj)
						echo("<td class=rescol>". $testitemobj->get_treshold(). "</td>");
					echo("<td class=rescolsep colspan=". ($scttestobj->get_catweight() ? ($skipphasefinal == 1 ? 1 : 2) : 3). ">&nbsp;</td>");
					if($scttestobj->get_item("controlweight") != "")
					{
						if(!$scttestobj->get_catweight())
							echo("<td class=rescol1>&nbsp;</td>");
						foreach($testitems AS $testitemobj)
							echo("<td class=rescol>". $testitemobj->get_treshold(). "</td>");
						echo("<td class=rescolsep colspan=". ($scttestobj->get_catweight() ? ($skipphasefinal == 1 ? 1 : 2) : 3). ">&nbsp;</td>");
					}
					if($scttestobj->get_item("terminalweight") != "")
						echo("<td class=rescolend colspan=3>&nbsp;</td>");
					echo("</tr>");	
				}
				// Now show the rows with results for each student
				$students = student::student_list($mygroup);
				$sseq = 0;
				$fieldseq = 0;
				foreach($students AS $student)
				{
					if(isset($student))
					{
						// Get the students results so far and put them in a handy array (for later field production)
						$sresqr = inputclassbase::load_query("SELECT * FROM SCTresult WHERE sid=". $student->get_id(). " AND sctid=". $scttestobj->get_id(). " AND year='". $schoolyear. "'");
						unset($sctres);
						if(isset($sresqr['result']))
						{
							foreach($sresqr['sctresid'] AS $srix => $sctresid)
							{
								$sctres[$sresqr['phase'][$srix]][$sresqr['seqno'][$srix]] = $sctresid;
							}
						}
						echo("<tr><td class=rescol". ($sseq % 5 == 0 ? "top" : ""). ">". ($sseq+1). "</td>
										<td class=frontrow3". ($sseq % 5 == 0 ? "top" : ""). ">". $student->get_name(). "</td>");
						if(!isset($_GET['print']))
						{
							if(!$scttestobj->get_catweight())
							{
								echo("<td class=rescol1". ($sseq % 5 == 0 ? "top" : ""). ">");
								$rfld = new inputclass_integerBlank("resentry:". $student->get_id(). ":". $scttestobj->get_id(). ":s:0",2,NULL,"result","SCTresult",isset($sctres['s'][0]) ? $sctres['s'][0] : $fieldseq--,"sctresid");
								if(!isset($sctres['s'][0]))
								{
									$rfld->set_extrafield("sid",$student->get_id());
									$rfld->set_extrafield("sctid",$scttestobj->get_id());
									$rfld->set_extrafield("seqno",0);
									$rfld->set_extrafield("year",$schoolyear);
									$rfld->set_extrafield("phase","s");
									$rfld->set_initial_value("");
								}
								$rfld->echo_html();
								echo("</td>");
							}
						
							foreach($testitems AS $testitemobj)
							{
								echo("<td class=rescol". ($sseq % 5 == 0 ? "top" : ""). ">");
										$rfld = new inputclass_integerBlank("resentry:". $student->get_id(). ":". $scttestobj->get_id(). ":s:". $testitemobj->get_seqno(),2,NULL,"result","SCTresult",isset($sctres['s'][$testitemobj->get_seqno()]) ? $sctres['s'][$testitemobj->get_seqno()] : $fieldseq--,"sctresid");
								if(!isset($sctres['s'][$testitemobj->get_seqno()]))
								{
									$rfld->set_extrafield("sid",$student->get_id());
									$rfld->set_extrafield("sctid",$scttestobj->get_id());
									$rfld->set_extrafield("seqno",$testitemobj->get_seqno());
									$rfld->set_extrafield("year",$schoolyear);
									$rfld->set_extrafield("phase","s");
								}
								$rfld->echo_html();
								if($scttestobj->get_catweight())
								{
									echo("<SPAN ID='tir:". $student->get_id(). ":". $scttestobj->get_id(). ":s:". $testitemobj->get_seqno(). "'>". $testitemobj->get_result_rnd($student->get_id(),"s",$schoolyear). "</span>");
								}
								echo("</td>");
							}
							if(!$scttestobj->get_catweight())
								echo("<td class=rescol". ($sseq % 5 == 0 ? "top" : ""). " ID='po:". $student->get_id(). ":s'>". $scttestobj->get_phase_other($student->get_id(),"s",$schoolyear). "</td>");
						}
						echo("<td class=rescol". ($skipphasefinal == 1 ? "sep" : ""). ($sseq % 5 == 0 ? "top" : ""). " ID='pr:". $student->get_id(). ":s'>". $scttestobj->get_phase_remediate($student->get_id(),"s",$schoolyear). "</td>");
						if(!isset($_GET['print']))
						{
							if($skipphasefinal == 0)
								echo("<td class=rescolsep". ($sseq % 5 == 0 ? "top" : ""). " ID='pc:". $student->get_id(). ":s'>". $scttestobj->get_phase_result($student->get_id(),"s",$schoolyear). "</td>");
							else
								echo("<SPAN style='display:none' ID='pc:". $student->get_id(). ":s'>". $scttestobj->get_phase_result($student->get_id(),"s",$schoolyear). "</span>");

						}
						if($scttestobj->get_item("controlweight") != "")
						{
							if(!isset($_GET['print']))
							{
								if(!$scttestobj->get_catweight())
								{
									echo("<td class=rescol1". ($sseq % 5 == 0 ? "top" : ""). ">");
											$rfld = new inputclass_integer("resentry:". $student->get_id(). ":". $scttestobj->get_id(). ":c:0",2,NULL,"result","SCTresult",isset($sctres['c'][0]) ? $sctres['c'][0] : $fieldseq--,"sctresid");
									if(!isset($sctres['c'][0]))
									{
										$rfld->set_extrafield("sid",$student->get_id());
										$rfld->set_extrafield("sctid",$scttestobj->get_id());
										$rfld->set_extrafield("seqno",0);
										$rfld->set_extrafield("year",$schoolyear);
										$rfld->set_extrafield("phase","c");
										$rfld->set_initial_value("");
									}
									$rfld->echo_html();
								}
								foreach($testitems AS $testitemobj)
								{
									echo("<td class=rescol". ($sseq % 5 == 0 ? "top" : ""). ">");
											$rfld = new inputclass_integerBlank("resentry:". $student->get_id(). ":". $scttestobj->get_id(). ":c:". $testitemobj->get_seqno(),2,NULL,"result","SCTresult",isset($sctres['c'][$testitemobj->get_seqno()]) ? $sctres['c'][$testitemobj->get_seqno()] : $fieldseq--,"sctresid");
									if(!isset($sctres['c'][$testitemobj->get_seqno()]))
									{
										$rfld->set_extrafield("sid",$student->get_id());
										$rfld->set_extrafield("sctid",$scttestobj->get_id());
										$rfld->set_extrafield("seqno",$testitemobj->get_seqno());
										$rfld->set_extrafield("year",$schoolyear);
										$rfld->set_extrafield("phase","c");
									}
									$rfld->echo_html();
									if($scttestobj->get_catweight())
									{
										echo("<SPAN ID='tir:". $student->get_id(). ":". $scttestobj->get_id(). ":c:". $testitemobj->get_seqno(). "'>". $testitemobj->get_result_rnd($student->get_id(),"c",$schoolyear). "</span>");
									}
									echo("</td>");
								}
								if(!$scttestobj->get_catweight())
									echo("<td class=rescol". ($sseq % 5 == 0 ? "top" : ""). " ID='po:". $student->get_id(). ":c'>". $scttestobj->get_phase_other($student->get_id(),"c",$schoolyear). "</td>");
							}
							echo("<td class=rescol". ($skipphasefinal == 1 ? "sep" : ""). ($sseq % 5 == 0 ? "top" : ""). " ID='pr:". $student->get_id(). ":c'>". $scttestobj->get_phase_remediate($student->get_id(),"c",$schoolyear). "</td>");
							if(!isset($_GET['print']))
							{
								if($skipphasefinal == 0)
									echo("<td class=rescolsep". ($sseq % 5 == 0 ? "top" : ""). " ID='pc:". $student->get_id(). ":c'>". $scttestobj->get_phase_result($student->get_id(),"c",$schoolyear). "</td>");
								else
									echo("<SPAN style='display:none' ID='pc:". $student->get_id(). ":c'>". $scttestobj->get_phase_result($student->get_id(),"c",$schoolyear). "</span>");
							}
						}
						if($scttestobj->get_item("terminalweight") != "")
						{
							if(!isset($_GET['print']))
							{
								echo("<td class=rescol". ($sseq % 5 == 0 ? "top" : ""). ">");
										$rfld = new inputclass_integer("resentry:". $student->get_id(). ":". $scttestobj->get_id(). ":t:0",2,NULL,"result","SCTresult",isset($sctres['t'][0]) ? $sctres['t'][0] : $fieldseq,"sctresid");
								if(!isset($sctres['t'][0]))
								{
									$rfld->set_extrafield("sid",$student->get_id());
									$rfld->set_extrafield("sctid",$scttestobj->get_id());
									$rfld->set_extrafield("seqno",0);
									$rfld->set_extrafield("year",$schoolyear);
									$rfld->set_extrafield("phase","t");
									$rfld->set_initial_value("");
								}
								$rfld->echo_html();
								echo("</td>");
								echo("<td class=rescolsep". ($sseq % 5 == 0 ? "top" : ""). " ID='pc:". $student->get_id(). ":t'>". $scttestobj->get_phase_result($student->get_id(),"t",$schoolyear). "</td>");
								echo("<td class=rescolend". ($sseq % 5 == 0 ? "top" : ""). ">");
							
								$rfld = new inputclass_textfield("rementry:". $student->get_id(),15,NULL,"remediate","SCTresult",isset($sctres['t'][0]) ? $sctres['t'][0] : $fieldseq--,"sctresid");
								$rfld->echo_html();
								echo("</td>");
							}
							else
							{
								$rfld = new inputclass_textfield("rementry:". $student->get_id(),15,NULL,"remediate","SCTresult",isset($sctres['t'][0]) ? $sctres['t'][0] : $fieldseq--,"sctresid");
								echo("<td>". $rfld->__toString(). "</td>");
							}
						}
						if(!isset($_GET['print']))
						{
							// If there are multiple sub-subjects, these results need also be displayed.
							$subsbs = $scttestobj->get_subjectsused();
							if(count($subsbs) > 1)
							{
								foreach($subsbs AS $amid => $asn)
									if($asn != "")
										echo("<td class=rescol". ($sseq % 5 == 0 ? "top" : ""). " ID='sc:". $student->get_id(). ":". $amid. "'>". $scttestobj->get_subject_result($student->get_id(),$amid,$schoolyear). "</td>");
							}
							if($skipphasefinal == 0)
								echo("<td class=rescolend". ($sseq % 5 == 0 ? "top" : ""). " ID='tc:". $student->get_id(). "'>". $scttestobj->get_result($student->get_id(),$schoolyear). "</td>");
							else
								echo("<SPAN style='display: none' ID='tc:". $student->get_id(). "'>". $scttestobj->get_result($student->get_id(),$schoolyear). "</span>");
						}
					
						echo("</tr>");				
						$sseq++;
					}
				}
							
				echo("</table>");
			}
			echo("</DIV>");		
		}	
  }
  // Script to update content in the page
?>
<SCRIPT>
function xmlconnDone(oXML,fieldobj)
{
  fieldobj.style.backgroundColor='white';
  if(oXML.responseText.substring(0,2) != "OK" && typeof oXML.responseText != "undefined")
    alert(oXML.responseText);
  if(oXML.responseText.substr(oXML.responseText.length - 7) == "REFRESH")
    document.location.reload(true);
  if(oXML.responseText.indexOf("[") > 0)
  { // parse "[...]{..}[...]{..} etc
    startPos = oXML.responseText.indexOf("[");
	endPos = oXML.responseText.lastIndexOf("}");
	parseUpdate(oXML.responseText.slice(startPos,endPos+1));
  }  
}
function parseUpdate(parseTxt)
{
  while(parseTxt.indexOf("[") != -1)
  {
    epP = parseTxt.indexOf("]");
    evP = parseTxt.indexOf("}");
		updateObject(parseTxt.substring(1,epP),parseTxt.slice(epP+2,evP));
		parseTxt = parseTxt.substring(evP+1);
  }
}
function updateObject(id,content)
{
	//alert(id);
  document.getElementById(id).innerHTML=content;
}
</SCRIPT>
<?
  // close the page
  echo("</BODY></html>");
?>
