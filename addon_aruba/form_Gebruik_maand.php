<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  require_once("inputlib/inputclasses.php");
  require_once("group.php");
  require_once("student.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  inputclassbase::dbconnect($userlink);
  // Setup table for monitoring actions
   if(!isset($_SESSION['showgroup']))
  {
    $mygroup = new group();
		$mygroup->load_current();
		$_SESSION['showgroup'] = $mygroup->get_id();
  }
  if(isset($_POST['showgr']))
    $_SESSION['showgroup'] = $_POST['showgr'];
	
  
  echo("<html><head><title>Lijst inlog tellers laaste maand</title>");
  echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
  echo("</head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
   
  echo '<p class=txtmidden><a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  echo '</a></p>';
	echo("<H1>Inlog tellers voor ouders en leerlingen over de afgelopen maand</h1>");
  
	$groups = group::group_list();
  echo("<form name=showgroupedit id=showgroupedit method=POST action=". $_SERVER['PHP_SELF']. "><SELECT name=showgr onChange=showgroupedit.submit()><OPTION value=0>-</OPTION>");
  foreach($groups AS $rgrp)
  {
    echo("<OPTION value=". $rgrp->get_id(). ($rgrp->get_id() == $_SESSION['showgroup'] ? " selected" : ""). ">". $rgrp->get_groupname(). "</OPTION>");
  }
  echo("</SELECT></FORM>");

	// Get a list of student
	$studs = student::student_list(new group($_SESSION['showgroup']));
	if(isset($studs))
	{
		echo("<table border=1><tr>");
		$labels = student::get_list_headers();
		foreach($labels AS $alabel)
		  echo("<th>". $alabel. "</th>");
		echo("<th>Ouders</th><th>Leerling</th></tr>")	;
		foreach($studs AS $stud)
		{
			if(isset($stud))
			{
				echo("<tr>");
				$stdata = $stud->get_list_data();
				foreach($stdata AS $sdat)
					echo("<td>". $sdat. "</td>");
				// Get the counters for parents and student logon counts
				$logcntqr = inputclassbase::load_query("SELECT SUM(IF(eventid='IN-PAR',1,0)) AS parcnt, SUM(IF(eventid='IN-STU',1,0)) AS stucnt FROM eventlog WHERE user=". $stud->get_id(). " AND LastUpdate > NOW() - INTERVAL 1 MONTH");
				if(isset($logcntqr['parcnt'][0]))
					echo("<td>". $logcntqr['parcnt'][0]. "</td><td>". $logcntqr['stucnt'][0]. "</td>");
				else
					echo("<td>0</td><td>0</td>");
				echo("</tr>");
			}
		}
			
		echo("</table>");
	}
  // Scripts for functions
  echo("</html>");
?>
