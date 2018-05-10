<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  require_once("inputlib/inputclasses.php");
	session_start();
  $login_qualify = 'ACT';
	require_once("schooladminfunctions.php");
  inputclassbase::dbconnect($userlink);
	
	if(isset($_POST['fieldid']))
	{
		echo("OK");
		if($_POST['fieldid'] == "absdate")
		{
			$_SESSION['absdate'] = inputclassbase::nldate2mysql($_POST['absdate']);
			echo("REFRESH");
		}
		if($_POST['fieldid'] == "absdate2")
		{
			$_SESSION['absdate2'] = inputclassbase::nldate2mysql($_POST['absdate2']);
			echo("REFRESH");
		}

		exit;
	}
	
	if(!isset($_SESSION['absdate']))
	{
		$_SESSION['absdate'] = date("Y-m-d");
		$_SESSION['absdate2'] = date("Y-m-d");
	}
	
  require_once("student.php");
	// Which fields to show
	$fieldlist = array("lastname","firstname","groupname","description","date","time","explanation","authorized","shortname","timeslot");
	$collabels = array("lastname" => $_SESSION['dtext']['Lastname'],
											"firstname" => $_SESSION['dtext']['Firstname'],
											"groupname" => $_SESSION['dtext']['Group_Cap'],
											"description" => $_SESSION['dtext']['Reason'],
											"date" => $_SESSION['dtext']['Date'],
											"time" => $_SESSION['dtext']['Time'],
											"explanation" => $_SESSION['dtext']['Remarks'],
											"authorized" => $_SESSION['dtext']['Authorization'],
											"shortname" => $_SESSION['dtext']['Subject'],
											"timeslot" => $_SESSION['dtext']['Timeslot']);
  // Link input library with database
	// See which date we need absence for
	//$absdate = date("Y-m-d");
	$absdate = $_SESSION['absdate'];
	$absdate2 = $_SESSION['absdate2'];
  // And then we start giving out content
  echo ('<LINK rel="stylesheet" type="text/css" href="style_zorglln.css" title="style1">');
  echo('<link rel="stylesheet" type="text/css" media="all" href="inputlib/datechooser.css">');

  // Get a list of groups
  if(isset($PrimaryGroupFilter))
    $groepfilter = $PrimaryGroupFilter;
  else 
    $groepfilter = "__";
  // Get a list of the absence
	$absence = inputclassbase::load_query("SELECT * FROM absence LEFT JOIN absencereasons USING(aid) LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) LEFT JOIN subject ON(class=mid) WHERE date>='". $absdate. "' AND date<='". $absdate2. "' AND (groupname LIKE '". $groepfilter. "') GROUP BY asid");
	echo("<html><head><title>Absentie dagoverzicht</title></head><body link=blue vlink=blue>");
	echo("<BR><BR><BR><H1>Absentie dagoverzicht ");
	$datsel = new inputclass_datefield("absdate",inputclassbase::mysqldate2nl($_SESSION['absdate']));
	$datsel->set_parameter("dc-offset-x","100px"); // Why doesn't this work???
	$datsel->echo_html("absdate",date("d-m-Y"));
	echo(" t/m ");
	$datsel2 = new inputclass_datefield("absdate2",inputclassbase::mysqldate2nl($_SESSION['absdate2']));
	$datsel2->set_parameter("dc-offset-y","100px"); // Why doesn't this work?
	$datsel2->echo_html("absdate2",date("d-m-Y"));
	echo("</h1>");
  if(isset($absence['asid']))
  {
		echo("<table class = TblPropKaart><tr>");
		// Prep sort items
		if(isset($_GET['sortseq']))
		{
			$sortdir = "forward";
			if(isset($_SESSION['ccsortseq']) && isset($_SESSION['ccsortdir']))
			{
				if($_GET['sortseq'] == $_SESSION['ccsortseq'] && $_SESSION['ccsortdir'] != "reverse")
				$sortdir = "reverse";
			}
			$_SESSION['ccsortseq'] = $_GET['sortseq'];
			$_SESSION['ccsortdir'] = $sortdir;
		}
		if(isset($_SESSION['ccsortseq']))
			$sortseq = $_SESSION['ccsortseq'];
		foreach($fieldlist AS $alabel)
		{
			//if($fldqr['table_name'][$lix] == " primegroup")
			//	$alabel="Klas";
			echo("<TH><A href='". $_SERVER['PHP_SELF']. "?sortseq=". $alabel. "'>". $collabels[$alabel]. "</a></th>");
		}
		echo("</tr>");
		foreach ($absence['asid'] AS $asix => $asid)
		{
			foreach($fieldlist AS $fldref)
			{
				if($fldref == "authorized")
						$stdata[$asid][$fldref] = $_SESSION['dtext'][$absence['authorization'][$asix]];
				else
						$stdata[$asid][$fldref] = $absence[$fldref][$asix];
			}
		} // einde foreach absence record
		// Sorting 
		if(isset($stdata))
		{
			foreach($stdata AS $asid => $stdat)
			{
				$sar[$asid] = "";
				if(isset($sortseq))
					$sar[$asid] = $stdat[$sortseq];
				if(!isset($sortseq) || $sortseq != "lastname")
					$sar[$asid] .= " ". $stdat["lastname"];
				if(!isset($sortseq) || $sortseq != "firstname")
					$sar[$asid] .= " ". $stdat["firstname"];
				if(!isset($sortseq) || $sortseq != "timeslot")
					$sar[$asid] .= " ". $stdat["timeslot"];
			}
			if(isset($sortdir) && $sortdir == "reverse")
				arsort($sar);
			else
				asort($sar);
			foreach($sar AS $asid => $dummy)
			{
				$stdat = $stdata[$asid];
				$prefix = "";
				echo("<tr>");
				foreach($fieldlist AS $fldref)
				{
					echo("<TD>". $stdat[$fldref]. "</td>");
				}
				echo("</tr>") ;
			}
		}
		echo("</table>");
  } // Endif we have absence records
    
  // close the page
  echo("</html>");
?>
