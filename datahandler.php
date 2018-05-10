<?
// MUST load the classes before session_start()!
require_once("inputlib/inputclasses.php");
require_once("schooladminconstants.php");
require_once("schooladmingradecalc.php");
require_once("testdef.php");
session_start();
// echo("ERR"); // Force debug info
/* foreach($_POST AS $pkey => $pval)
   echo($pkey. "=". $pval. "<BR>"); 
	 echo($_SESSION['ClassBookDate']); */
	 

// Define functions for conversions
function week2date($aweek)
{
  $perdata = inputclassbase::load_query("SELECT * FROM period WHERE status='open' ORDER BY startdate");
  if(isset($perdata['startdate']))
    foreach($perdata['id'] AS $pdix => $pid)
	{
	  $fweek = date("W",mktime(0,0,0,substr($perdata['startdate'][$pdix],5,2),substr($perdata['startdate'][$pdix],8,2),substr($perdata['startdate'][$pdix],0,4)));
	  $lweek = date("W",mktime(0,0,0,substr($perdata['enddate'][$pdix],5,2),substr($perdata['enddate'][$pdix],8,2),substr($perdata['enddate'][$pdix],0,4)));
	  if(($aweek >= $fweek && $aweek <= $lweek) || ($aweek <= $lweek && $fweek > $lweek) || ($aweek >= $fweek && $fweek > $lweek))
	  { // Week given is within this period.
	    if($aweek >= $fweek)
	      $year = substr($perdata['startdate'][$pdix],0,4);
		else
	      $year = substr($perdata['startdate'][$pdix],0,4);
	    //$d = strptime("WED ". $aweek. " ". $year, '%a %W %Y');
		if(date("W",mktime(0,0,0,1,1,$year)) != 1)
		  $retdate = date("d-m-Y",mktime(0,0,0,1,$aweek * 7,$year));
		else
		  $retdate = date("d-m-Y",mktime(0,0,0,1,($aweek * 7)-7,$year));
	  }
	}
	if(isset($retdate))
	  return($retdate);
	else
	{
	  echo("\r\nINVALID WEEK NUMBER ". $aweek. "\r\n");
	  return(NULL);
	}
}
function date2period($idate)
{
  $adate = substr($idate,6,4). "-". substr($idate,3,2). "-". substr($idate,0,2);
  $perdata = inputclassbase::load_query("SELECT * FROM period WHERE status='open' ORDER BY startdate");
  if(isset($perdata['startdate']))
    foreach($perdata['id'] AS $pdix => $pid)
	{
	  if($adate >= $perdata['startdate'][$pdix] && $adate <= $perdata['enddate'][$pdix])
	    $retper = $pid;
	}
  if(isset($retper))
    return($retper);
  else
  {
    return(NULL);
  }
}
// Reconnect with the database as we don't use persistent connections
inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);
$userlink = inputclassbase::$dbconnection;
  // Store reported image movements (from student location page) and exit if done so
  if(isset($_POST['studentimagelocation']))
  { // Image position change reported
    mysql_query("REPLACE INTO stud_imgloc (sid,tid,xoff,yoff) VALUES(". $_POST['studentimagelocation']. ",". $_SESSION['uid']. ",". $_POST['xoff']. ",". $_POST['yoff']. ")", $userlink);
		if(mysql_error($userlink))
		{
			echo(mysql_error($userlink));
			echo("REPLACE INTO stud_imgloc (sid,tid,xoff,yoff) VALUES(". $_POST['studentimagelocation']. ",". $_SESSION['uid']. ",". $_POST['xoff']. ",". $_POST['yoff']. ")");
		}
		else
			echo("OK");
		exit;
  }
  // Store reported image movements (from students gui) and exit if done so
  if(isset($_POST['studentguilocation']))
  { // Image position change reported
		// get the current year
		$yearqr = mysql_query("SELECT year FROM period");
		$myyear = mysql_result($yearqr,0);
		// get current orientation
		$stoqr = inputclassbase::load_query("SELECT sid,tid,xoff,yoff,orientation FROM stud_guiloc WHERE sid=". $_POST['studentguilocation']. " AND tid=". $_SESSION['uid']. " AND year='". $myyear. "' UNION SELECT sid,tid,xoff,yoff,orientation FROM stud_guiloc LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE sid=". $_POST['studentguilocation']. " AND active=1 AND tid=tid_mentor AND groupname= '". $_SESSION['CurrentGroup']. "' AND year='". $myyear. "'");
		if(isset($stoqr['sid']))
		{ // A record already existed, update if owned, add a record copying exiting data if from mentor
			if($stoqr['tid'][0] == $_SESSION['uid'])
			{ // Owned record so update
				mysql_query("UPDATE stud_guiloc SET xoff=". $_POST['xoff']. ",yoff=". $_POST['yoff']. " WHERE sid=". $_POST['studentguilocation']. " AND year='". $myyear. "' AND tid=". $_SESSION['uid'], $userlink);				
			}
			else
			{ // Mentor record so insert new
				mysql_query("INSERT INTO stud_guiloc (sid,tid,xoff,yoff,year,orientation) VALUES(". $_POST['studentguilocation']. ",". $_SESSION['uid']. ",". $_POST['xoff']. ",". $_POST['yoff']. ",'". $myyear. "','". $stoqr['orientation'][0]. "')",$userlink);				
			}
		}
		else // Now record existed, create a new one
			mysql_query("INSERT INTO stud_guiloc (sid,tid,xoff,yoff,year) VALUES(". $_POST['studentguilocation']. ",". $_SESSION['uid']. ",". $_POST['xoff']. ",". $_POST['yoff']. ",'". $myyear. "')", $userlink);
		if(mysql_error($userlink))
			echo(mysql_error($userlink). " Error in database command.");
		else
			echo("OK");
		exit;
  }
  if(isset($_POST['studentguiorientation']))
  { // Image layout change reported
		// get the current year
		$yearqr = mysql_query("SELECT year FROM period");
		$myyear = mysql_result($yearqr,0);
		
		// get current orientation
		$stoqr = inputclassbase::load_query("SELECT sid,tid,xoff,yoff,orientation FROM stud_guiloc WHERE sid=". $_POST['studentguiorientation']. " AND tid=". $_SESSION['uid']. " AND year='". $myyear. "' UNION SELECT sid,tid,xoff,yoff,orientation FROM stud_guiloc LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE sid=". $_POST['studentguiorientation']. " AND active=1 AND tid=tid_mentor AND groupname= '". $_SESSION['CurrentGroup']. "' AND year='". $myyear. "'");
		if(isset($stoqr['orientation']))
		{
			$ornext = array("b" => "t","t" => "l","l"=>"r","r"=>"b");
			if($stoqr['tid'][0] == $_SESSION['uid']) // For own record just update the orientation
				mysql_query("UPDATE stud_guiloc SET orientation='". $ornext[$stoqr['orientation'][0]]. "' WHERE sid=". $_POST['studentguiorientation']. " AND year='". $myyear. "' AND tid=". $_SESSION['uid'], $userlink);
			else // For record from mentor, insert a new record copying mentor data
				mysql_query("INSERT INTO stud_guiloc (sid,tid,xoff,yoff,year,orientation) VALUES(". $_POST['studentguiorientation']. ",". $_SESSION['uid']. ",". $stoqr['xoff'][0]. ",". $stoqr['yoff']. ",'". $myyear. "','". $ornext[$stoqr['orientation'][0]]. "')",$userlink);
		}
		else
		 mysql_query("INSERT INTO stud_guiloc (sid,tid,xoff,yoff,year,orientation) VALUES(". $_POST['studentguiorientation']. ",". $_SESSION['uid']. ",0,0,'". $myyear. "','t')",$userlink);
		if(mysql_error($userlink))
		{
			echo(mysql_error($userlink));
		}
		else
			echo("OK REFRESH");
		exit;
  }
	if(isset($_POST['addguibadge']))
	{ // New badge added
		$xpd = explode(",",$_POST['addguibadge']);
		mysql_query("REPLACE INTO guibadges (sid,tid,libid) VALUES(". $xpd[0]. ",". $xpd[1]. ",". $xpd[2]. ")", $userlink);
		echo(mysql_error($userlink). "OK");
		exit;
	}
	if(isset($_POST['delguibadge']))
	{ // New badge added
		$xpd = explode(",",$_POST['delguibadge']);
		mysql_query("DELETE FROM guibadges WHERE sid=". $xpd[0]. " AND tid=". $xpd[1]. " AND libid=". $xpd[2], $userlink);
		echo(mysql_error($userlink). "OK");
		exit;
	}

// Convert non numeric values to make sure its alpha
if(substr($_POST['fieldid'],0,4) == "tres")
{
	if($_POST[$_POST['fieldid']] > 0.0)
	{
		$val = str_replace($dtext['dec_sep'],'.',$_POST[$_POST['fieldid']]);
		$nval = 1.0 * $val;
		$sval = ' '. $val;
		$ssval = ' '. $nval;
		if($sval != $ssval)
			$_POST[$_POST['fieldid']] = ' '. $_POST[$_POST['fieldid']];
	}
}
// Catch client time value transfer
if($_POST['fieldid'] == "ClientTime")
{
  $_SESSION['ClientTimeOffset'] = $_POST['ClientTime'] - mktime();
  echo("OK Time offset value: ". $_SESSION['ClientTimeOffset']. ", passed value = ". $_POST['ClientTime']. ", My time = ". mktime(). "(". date("G:i:s"). ")");
  exit;
}
if($_POST['fieldid'] == "ClassBookDate")
{
  $newdate = $_POST[$_POST['fieldid']];
  $_SESSION['CurrentClassBookDate'] = mktime(0,0,0,substr($newdate,3,2),substr($newdate,0,2),substr($newdate,6,4));
  echo("OK ". $newdate. " REFRESH");
  exit;
}
// Catch client Touch screen state
if($_POST['fieldid'] == "TouchScreen")
{
  $_SESSION['TouchScreen'] = 1;
  echo("OK");
  exit;
}
// Catch direct touch absense setting
if($_POST['fieldid'] == "sidabsset")
{ // Now $_POST['fieldid'] contains the sid and name of the absence category. We need to find the rest and set the absence record
  $sdata = explode(";",$_POST['sidabsset'],2);
  // Get the date
  if(isset($_SESSION['CurrentClassBookDate']))
    $absdat = date("Y-m-d", $_SESSION['CurrentClassBookDate']);
  else
		$absdat = date("Y-m-d");
  // Get the time
  $abstim = date("H:i:s",mktime() + (isset($_SESSION['ClientTimeOffset']) ? $_SESSION['ClientTimeOffset'] : 0));
  // Get an absence reason
  $absrqr = inputclassbase::load_query("SELECT aid FROM absencereasons LEFT JOIN absencecats USING(acid) WHERE name='". $sdata[1]. "' ORDER BY description");
  if(isset($absrqr['aid'][0]))
  {
		mysql_query("DELETE FROM absence WHERE sid=". $sdata[0]. " AND date='". $absdat. "' AND aid=". $absrqr['aid'][0]. " AND class=". $_SESSION['CurrentSubject'], $userlink);
		echo(mysql_error($userlink));
		if(mysql_affected_rows($userlink) == 0)
		{
			mysql_query("INSERT INTO absence (sid,aid,date,time,authorization,class,lastmodifiedby) VALUES(". $sdata[0]. ",". $absrqr['aid'][0]. ",'". $absdat. "','". $abstim. "','Pending',". $_SESSION['CurrentSubject']. ",". $_SESSION['uid']. ")", $userlink);
			echo(mysql_error($userlink));
		}
		//echo("ABS rec with sid=". $sdata[0]. ", aid=". $absrqr['aid'][0]. ", date=". $absdat. ", time=". $abstim. ", class=". $_SESSION['CurrentSubject']);
    echo("OK");
  }
  else  
    echo("ERR unlinked absence with data: ". $_POST['sidabsset']);
  exit;
}
// Catches for test definition changes
// Need to set period and date too if week entered and week and period if date changed
if(substr($_POST['fieldid'],0,6) == 'tddate' && $lessonplan == 1)
{
  $weekfld = "tdweek". substr($_POST['fieldid'],6);
  $date = $_POST[$_POST['fieldid']];
  $week = date("W",mktime(0,0,0,substr($date,3,2),substr($date,0,2),substr($date,6,4)));
  $periodfld = "tdperiod". substr($_POST['fieldid'],6);
  $period = date2period($date);
}
if(substr($_POST['fieldid'],0,6) == 'tdweek')
{
  $datefld = "tddate". substr($_POST['fieldid'],6);
  // Convert week to a date
  $date = week2date($_POST[$_POST['fieldid']]);
  $periodfld = "tdperiod". substr($_POST['fieldid'],6);
  $period = date2period($date);
}
// Need to recalc original and previous period if period was changed so now we retrieve the original period
if(substr($_POST['fieldid'],0,8) == 'tdperiod' || substr($_POST['fieldid'],0,6) == 'tddate' || substr($_POST['fieldid'],0,6) == 'tdweek')
{
  if(substr($_POST['fieldid'],0,8) == 'tdperiod')
    $tdid = substr($_POST['fieldid'],8);
  else
    $tdid = substr($_POST['fieldid'],6);
  $orgperiod = $_SESSION['inputobjects']["tdperiod". $tdid]->__toString();
}
// Need to replace , in testresult with .
if(substr($_POST['fieldid'],0,4) == 'tres')
{
  $_POST[$_POST['fieldid']] = str_replace(',','.',$_POST[$_POST['fieldid']]);
}
// Catch calendar data
if(substr($_POST['fieldid'],0,14) == "calweekendday_")
{ // A day of the week is set or reset as weekend day
  // First get the dates set in the periods table
  $daterange = inputclassbase::load_query("SELECT MIN(startdate) AS sdat, MAX(enddate) AS edat FROM period");
  if(isset($daterange))
  {
    $startdate = mktime(0,0,0,substr($daterange['sdat'][0],5,2),substr($daterange['sdat'][0],8,2),substr($daterange['sdat'][0],0,4));
	// change the startdate to the first occurence after the day of the specified day
	$afday = substr($_POST['fieldid'],14);
	$startdate = mktime(0,0,0,date("n",$startdate),date("j",$startdate)+$afday-date("w",$startdate),date("Y",$startdate));
    $enddate = mktime(0,0,0,substr($daterange['edat'][0],5,2),substr($daterange['edat'][0],8,2),substr($daterange['edat'][0],0,4));
	$curdat = $startdate;
	while($curdat <= $enddate)
	{
	  if($_POST[$_POST['fieldid']] == 1)
	  { // Setting the dates
	    mysql_query("INSERT INTO calendaritem (caldate,calclass,caldata) VALUES('". date("Y-m-d",$curdat). "','DisabledDays',' ')", $userlink);
	  }
	  else
	  { // Removing the dates
	    mysql_query("DELETE FROM calendaritem WHERE caldate='". date("Y-m-d",$curdat). "' AND calclass='DisabledDays'", $userlink);	  
	  }
	  $curdat = mktime(0,0,0,date("n", $curdat), date("j",$curdat) + 7, date("Y", $curdat));
	}
	echo("OK");
  }
  else
    echo("ERR: no daterange extracted from periods");
  exit;
}
// Catch new timetable creation
if($_POST['fieldid'] == "calnewtimetabletimesname")
{
  mysql_query("INSERT INTO timetabletimes (tablename,timeslot) VALUES('". $_POST[$_POST['fieldid']]. "',1)", $userlink);
  // We made some strange use of the extra key to get the date where the item must be inserted so we get it back here
  $fldobj = $_SESSION['inputobjects'][$_POST['fieldid']];
  $objdate = $fldobj->get_extrakey();
  mysql_query("DELETE FROM calendaritem WHERE caldate='". $objdate. "' AND calclass='TimetableTimes'", $userlink);
  mysql_query("INSERT INTO calendaritem (caldate,calclass,caldata) VALUES('". $objdate. "','TimetableTimes','". $_POST[$_POST['fieldid']]. "')", $userlink);
  $_SESSION['currenttimetabletimesname'] = $_POST[$_POST['fieldid']];
  echo("OK");
  exit;
}
if($_POST['fieldid'] == "calnewtimetableactivitiesname")
{
  // We made some strange use of the extra key to get the date where the item must be inserted so we get it back here
  $fldobj = $_SESSION['inputobjects'][$_POST['fieldid']];
  $objdate = $fldobj->get_extrakey();
  mysql_query("INSERT INTO calendaritem (caldate,calclass,caldata) VALUES('". $objdate. "','TimetableActivities','". $_POST[$_POST['fieldid']]. "')", $userlink);
  $_SESSION['currenttimetableactivitiesname'] = $_POST[$_POST['fieldid']];
  echo(mysql_error($userlink));
  echo("OK");
  exit;
}

// Generic filtering: fields starting with "flt_" are set (or cleared!) as session var, no database action!
if(substr($_POST['fieldid'],0,4) == "flt_")
{
  if($_POST[$_POST['fieldid']] == "")
    unset($_SESSION[$_POST['fieldid']]);
  else
    $_SESSION[$_POST['fieldid']] = $_POST[$_POST['fieldid']];
  echo("OK");
  //echo("Set session varible ". $_POST['fieldid']. " to ". $_POST[$_POST['fieldid']]);
  echo("REFRESH");
  exit;
}

// Let the library page handle the data
include("inputlib/procinput.php");
// Refresh if new student overview or item is added
if($_POST['fieldid'] == "newov" || $_POST['fieldid'] == "stovni")
{
	if($_POST['fieldid'] == "newov")
	{
		$iobj = $_SESSION['inputobjects'][$_POST['fieldid']];	
		$_SESSION['newviewediting'] = $iobj->get_key();
	}
	echo("REFRESH");
	exit;
}
//  Refresh if student view sorting has been changed
if(substr($_POST['fieldid'],0,6) == "stovss")
{
	echo("REFRESH");
	exit;
}
// Handle date, week and/or period additional fields
if(isset($weekfld) && $week != NULL)
{
  $_POST['fieldid'] = $weekfld;
  $_POST[$weekfld] = $week;
  include("inputlib/procinput.php");
}
if(isset($datefld) && $date != NULL)
{
  $_POST['fieldid'] = $datefld;
  $_POST[$datefld] = $date;
  include("inputlib/procinput.php");
}
if(isset($periodfld) && $period != NULL)
{
  $_POST['fieldid'] = $periodfld;
  $_POST[$periodfld] = $period;
  include("inputlib/procinput.php");
}
// Need to recalculate if period(s) or type changed
if(substr($_POST['fieldid'],0,8) == 'tdperiod' || substr($_POST['fieldid'],0,6) == 'tdtype')
{ // Need to recalc 
  // Get cid and period
  if(substr($_POST['fieldid'],0,8) == 'tdperiod')
    $tdid = substr($_POST['fieldid'],8);
  else
    $tdid = substr($_POST['fieldid'],6);
  $tdinfo = inputclassbase::load_query("SELECT cid,period FROM testdef WHERE tdid=". $tdid);
  if(substr($_POST['fieldid'],0,6) == "tdtype" || (isset($tdinfo['period'][0]) && $tdinfo['period'][0] != $orgperiod))
  { // period or type has really changed
	  if(isset($tdinfo['cid'][0]) && isset($tdinfo['period'][0]) && $tdinfo['cid'][0] > 0 && $tdinfo['period'][0] > 0)
	  {
			SA_calcGradeGroup($tdinfo['cid'][0],$tdinfo['period'][0]);
	  }
	  if(isset($tdinfo['cid'][0]) && isset($orgperiod) && $tdinfo['cid'][0] > 0 && $orgperiod > 0)
	  {
			SA_calcGradeGroup($tdinfo['cid'][0],$orgperiod);
	  }
  }
}
// Need to recalculate if result entered
if(substr($_POST['fieldid'],0,4) == "tres")
{
  $idinfo = substr($_POST['fieldid'],4);
  $idinfos = explode("-", $idinfo);
  $td = new testdef($idinfos[0]);
  SA_calcGrades($idinfos[1],$td->get_cid(),$td->get_period()); 
//  echo("Recalculated for student : ". $idinfos[1]. " cid: ". $td->get_cid(). " period: ". $td->get_period());
}
// Catch testresult being entered, to inform via hook if defined
if(substr($_POST['fieldid'],0,4) == "tres" && function_exists("result_hook"))
{
  $idinfo = substr($_POST['fieldid'],4);
  $idinfos = explode("-", $idinfo);
	result_hook($idinfos[0],$idinfos[1]); // Calling hook with tdid and sid
}
// Catch report being created, this might result in sending SMS
if(substr($_POST['fieldid'],0,7) == "repcont" && function_exists("report_hook"))
{ // We got a report summery creation so might send SMS here...
  // Get the key first
  $iobj = $_SESSION['inputobjects'][$_POST['fieldid']];
  report_hook($iobj->get_key());
}

// Catch absence being created, this might result in sending SMS
if((substr($_POST['fieldid'],0,6) == "absid-" || substr($_POST['fieldid'],0,5) == "abaid") && function_exists("absence_hook"))
{ // We got a new absence send SMS here...
  // Get the key first
  $iobj = $_SESSION['inputobjects'][$_POST['fieldid']];
  absence_hook($iobj->get_key());
}

// Set the default protection if a report is added and no protection setting has been done
if(substr($_POST['fieldid'],0,7) == "repdate" || 
   substr($_POST['fieldid'],0,6) == "repcat" ||
   substr($_POST['fieldid'],0,6) == "repsum" ||
   substr($_POST['fieldid'],0,6) == "repcont")
{
	$xfld = $_SESSION['inputobjects'][$_POST['fieldid']];
	$defprotvalqr = inputclassbase::load_query("SELECT defrepaccess FROM teacher WHERE tid=". $_SESSION['uid']);
	mysql_query("UPDATE reports SET protect='". $defprotvalqr['defrepaccess'][0]. "' WHERE rid=". $xfld->get_key(). " AND protect IS NULL", $userlink);
}

// Just for demo purposes: show the fields posted(note that the library only shows an alert with this data if something went wrong)
foreach($_POST AS $parm => $val)
{
  echo("\r\nPassed parameter: ". $parm. " = ". $val); 
}

?>
