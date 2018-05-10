<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("student.php");
  require_once("group.php");
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  echo("<html><head><title>Afsluiten/ontsluiten SE resultaten</title></head><body>");
  // Array with test names to be able to lock/unlock
  $tests = array("SE1TV1","SE1TV2","SE2TV1","SE2TV2","SE3TV1","SE3TV2","Ex","Hex");
// See if need to lock or unlock items
  if(isset($_POST['lockid']))
  {
    mysql_query("UPDATE testdef SET locked=". $_POST['lockfunc']. " WHERE short_desc='". $_POST['lockid']. "' AND (type LIKE 'SO%' OR type LIKE 'Exam' OR type LIKE 'SE%')", $userlink);
	echo(mysql_error($userlink));
  }
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];

  echo("<table border=1><tr><th>Schoolexamen</th><th><img src=PNG/unlock.png><img src=PNG/login.png></th></tr>");
  foreach($tests AS $desc)
  {
    $tlockcnt = SA_loadquery("SELECT IF(locked = 0 OR locked IS NULL,1,0) AS ulocked, IF(locked=1,1,0) AS ilocked FROM testdef WHERE short_desc = '". $desc. "' AND (type LIKE 'SO%' OR type LIKE 'Exam' OR type LIKE 'SE%') AND year='". $schoolyear. "' GROUP BY year");
	if(isset($tlockcnt['ulocked']))
	{
      echo("<tr><td>". $desc. "</td><td>");
	  if($tlockcnt['ilocked'][1] > 0)
	    echo("<a href=# onClick=unlock('". $desc. "');><img src=PNG/unlock.png title='Ontsluiten'></a>");
	  if($tlockcnt['ulocked'][1] > 0)
	    echo("<a href=# onClick=lock('". $desc. "');><img src=PNG/login.png title='Afsluiten'></a>");
	  echo("</td></tr>");
	}
  }
  echo("</table></FORM>");
?>
  <FORM METHOD="POST" ACTION="form_EX_Afsluiten_SOs.php" ID="lform" name="lform" enctype="text">
    <INPUT type='hidden' NAME=lockid ID='lockid' VALUE=0>
	<INPUT type='hidden' NAME=lockfunc ID='lockfunc' VALUE=1>
  </FORM>
  <SCRIPT>
  function lock(lockid)
  {
    document.getElementById('lockid').value=lockid;
	document.getElementById('lockfunc').value=1;
	document.getElementById('lform').submit();
  }
  function unlock(lockid)
  {
    document.getElementById('lockid').value=lockid;
	document.getElementById('lockfunc').value=0;
	document.getElementById('lform').submit();
  }
  </SCRIPT> 
  </body></html>
