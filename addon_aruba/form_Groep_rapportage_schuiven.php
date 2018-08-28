<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  //$login_qualify = 'C';
  include ("schooladminfunctions.php");
	require_once("inputlib/inputclasses.php");
  inputclassbase::dbconnect($userlink);
	if(isset($_POST) && sizeof($_POST) > 1)
	{
		foreach($_POST AS $pix => $dgid)
		{
			if(substr($pix,0,4) == "dest" && $dgid > 0)
			{
				$sgid = substr($pix,4);
				mysql_query("UPDATE reports SET sid=". $dgid. " WHERE sid=". $sgid. " AND (type='C' OR type='X')");
				echo(mysql_error());
				//echo($sgid. "=>". $dgid. "<BR>");
			}
		}
		echo("<a href=# onclick='window.close();'>Verschuiving is uitgevoerd. Sluit dit scherm</a>");
		exit;
	}
	
	//  Create the list of source groups
	$sgrpqr = inputclassbase::load_query("SELECT * FROM (SELECT gid,groupname FROM sgroup WHERE groupname LIKE '1_' OR groupname LIKE '2_' OR groupname LIKE '3_' OR groupname LIKE '4_' OR groupname LIKE '5_' OR groupname LIKE '6_' OR groupname LIKE 'KO__' ORDER BY groupname DESC) AS t1 UNION SELECT * FROM (SELECT gid,groupname FROM sgroup WHERE groupname LIKE 'K_' ORDER BY groupname DESC) AS t2");
	$dgrpqr = inputclassbase::load_query("SELECT gid,groupname FROM sgroup ORDER BY groupname DESC");
  echo("<html><head><title>Groep rapportage verschuiven</title></head><body link=blue vlink=blue>");
	echo("<H1>Groep rapportages verschuiven</H1>");
	echo("<FORM METHOD=POST>");
	foreach($sgrpqr['groupname'] AS $gix => $gname)
	{
		echo($gname. " gaat naar groep ");
		echo("<SELECT NAME=dest". $sgrpqr['gid'][$gix]. ">")	;
		echo("<OPTION VALUE=0> </OPTION>");
		foreach($dgrpqr['gid'] AS $dgix => $dgid)
		{
			echo("<OPTION VALUE=". $dgid. ">". $dgrpqr['groupname'][$dgix]. "</option>");
		}
		echo("</SELECT><BR>");
	}
	echo("<INPUT TYPE=SUBMIT VALUE='Uitvoeren'></FORM>");
    
  // close the page
  echo("</html>");
?>
