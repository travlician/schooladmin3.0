<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.  (http://www.aim4me.info)        |
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
// | Authors: Wilfred van Weert - travlcian@bigfoot.com                   |
// +----------------------------------------------------------------------+
//

  session_start();
  require_once("schooladminfunctions.php");
  require_once("teacher.php");
  inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);
	
	// See if we got this ip address suspect of being used for hacking
	$myip = getenv("REMOTE_ADDR");
  $secqr = SA_loadquery("SELECT id FROM eventlog WHERE ipaddr='". $myip. "' AND LastUpdate > TIMESTAMPADD(HOUR,-1,NOW()) AND eventid LIKE 'DEN%'");
  if(isset($secqr['id']))
  { //A login from the current ip address has failed during the last hour! If no captcha is sent with this request or no capta session item is set, we just wait and exit
    if(!isset($_POST['captcha']) || !isset($_SESSION['captcha']))
		{ // Wait and exit...
			sleep(5);
			exit;
		}
		else // The captcha data is there, if it doesn't match, go back to the login page after 5 seconds
		if($_POST['captcha'] != $_SESSION['captcha']['code'])
		{
			sleep(5);
      header("Location: " . $livesite ."login.php");
		}
  }
	
	// See if this login originates from an email message with parameters
	if(isset($_GET['sid']))
	{ // So it is...
		// Generate allowed keys
		$yr = date("Y");
		$month = date("n");
		$day = date("j");
		$keyok = false;
		for($d=0; $d<3; $d++) // Checking 3 days
		{
			$ckdate = mktime(10,10,10,$month,$day - $d,$yr);
			$chkdate = date("Y-m-d",$ckdate);
			if($_GET['key'] == md5($chkdate. $_GET['page']. $_GET['sid']))
				$keyok = true;
			//else
				//echo("Key invalid (checked ". $_GET['key']. " against ". md5($chkdate. $_GET['page']. $_GET['sid']). " on ". $chkdate. ")<BR>");
		}
		if($keyok)
		{ // Key is ok, set vars and refer to page requested
			$_SESSION['LoginType'] = "S";
			$_SESSION['usertype'] = "S";
			$_SESSION['uid'] = $_GET['sid'];
			$_SESSION['CurrentUID'] = $_GET['sid'];
			$_SESSION['Schoolkey'] = $databasename;
			SA_writeLog("IN-STU",$_GET['sid']);
      header("Location: " . $livesite . $_GET['page']);
 			exit;
		}
	}


  $HTTP_POST_VARS = $_POST;
  $uid = $HTTP_POST_VARS['uid'];
  $radio1 = $HTTP_POST_VARS['radio1'];
  if(!isset($_POST['pword']))
  {
    // No password field present, for google chrome bug, check which fields are present
	foreach($_POST AS $pfld => $pval)
	  echo("<BR>". $pfld. "=". $pval);
  }
  $pword = $HTTP_POST_VARS['pword'];
  //echo("Radio1 is $radio1 !! ");
  if($encryptedpasswords == 1)
    $pword = MD5($pword);

  if (trim($uid) == "")
  {
    echo($dtext['missing_uid']);
    echo("<br><a href=login.php>" . $dtext['back_login'] . "</a>");
    exit;
  }
  if ($radio1 == ""){
    echo($dtext['missing_utype']);
    echo("<br><a href=login.php>" . $dtext['back_login'] . "</a>");
    exit;
  }
  $uid = intval($uid);

  if($radio1 == "teacher")
  {
    if(isset($teachercode))
      $sql_query = "SELECT teacher.* FROM teacher LEFT JOIN ". $teachercode. " USING(tid) WHERE (teacher.tid = "  . $uid. " OR data=\"" .$HTTP_POST_VARS['uid']. "\") AND password = '$pword' AND is_gone <> 'Y'";
	else
      $sql_query = "SELECT teacher.* FROM teacher WHERE tid = "  . $uid. " AND password = '$pword' AND is_gone <> 'Y'";
  }
  else if($radio1 == "student")
  {
    if($altsids == 1)
      $sql_query = "SELECT * FROM student WHERE altsid = \""  . $HTTP_POST_VARS['uid']. "\" AND password = '$pword';";
    else
      $sql_query = "SELECT * FROM student WHERE sid = "  . $uid. " AND password = '$pword';";
  }
  else
  {
    if($altsids == 1)
      $sql_query = "SELECT * FROM student WHERE altsid = \""  . $HTTP_POST_VARS['uid']. "\" AND ppassword = '$pword';";
    else
      $sql_query = "SELECT * FROM student WHERE sid = "  . $uid. " AND ppassword = '$pword';";
  }
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
       $grade_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  SA_closeDB();
  $row_n = $nrows;
  
 if ($row_n != 0)
 {

   if($radio1 == "student" || $radio1 == "parent")
     $LoginType = "S";
   else
   {
     $I = new teacher($uid);
	 if($I->has_role("admin"))
	   $LoginType = "A";
	 else if($I->has_role("counsel"))
	   $LoginType = "C";
	 else
       $LoginType = "T";
   }
   $usertype = $radio1;
   if($LoginType == "S")
     $uid = $grade_array['sid'][1];
   else
     $uid = $grade_array['tid'][1];
   $_SESSION['LoginType'] = $LoginType;
   $_SESSION['usertype'] = $radio1;
   $_SESSION['uid'] = $uid;
   $_SESSION['CurrentUID'] = $uid;
   $_SESSION['Schoolkey'] = $databasename;


//Log the login to a file...
   if($usertype == "student")
     SA_writeLog("IN-STU",$uid);
   else if($usertype == "parent")
     {
       SA_writeLog("IN-PAR",$uid);
	   mysql_query("UPDATE student SET plogin=NOW() WHERE sid=". $uid, $userlink);
	 }
   else
     SA_writeLog("IN-TEA",$uid);
   // Now we go to another page depending on the logon type.
   if($usertype == "student" || $usertype == "parent")
     header("Location: " . $livesite ."showreportcard.php");
   else
     header("Location: " . $livesite ."teacherpage.php");
  } 
 else 
 {
   SA_writeLog("DEN-". $pword,$uid);
   echo ($dtext['INV_LOGIN_CAP']);
   echo("<br><a href=login.php>" . $dtext['back_login'] . "</a>");
 }
 exit;


?>


