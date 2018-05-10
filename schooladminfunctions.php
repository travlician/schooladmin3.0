<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014  Aim4me N.V.   (http://www.aim4me.info)	  |
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
//Common Schooladmin Functions, starts a connection /w the sql server!

include ("schooladminconstants.php");
/**
 * Misc stuff and functions used by almost all the scripts.
 */
/**
 * Gets constants that defines the PHP version number.
 */
require_once('./schooladmindefines.php');


/**
 * Add slashes before "'" and "\" characters so a value containing them can
 * be used in a sql comparison.
 *
 * param   string   the string to slash
 * param   boolean  whether the string will be used in a 'LIKE' clause
 *                   (it then requires two more escaped sequences) or not
 * param   boolean  whether to treat cr/lfs as escape-worthy entities
 *                   (converts \n to \\n, \r to \\r)
 *
 * return  string   the slashed string
 */
function SA_sqlAddslashes($a_string = '', $is_like = FALSE, $crlf = FALSE)
{
    if ($is_like)
        $a_string = str_replace('\\', '\\\\\\\\', $a_string);
    else
        $a_string = str_replace('\\', '\\\\', $a_string);

    if ($crlf)
    {
        $a_string = str_replace("\n", '\n', $a_string);
        $a_string = str_replace("\r", '\r', $a_string);
        $a_string = str_replace("\t", '\t', $a_string);
    }

    $a_string = str_replace('\'', '\\\'', $a_string);

    return $a_string;
} // end of the 'SA_sqlAddslashes()' function



$bkp_track_err = @ini_set('track_errors', 1);

// Connect to the server (validates user's login)
$userlink = mysql_connect($databaseserver,$datausername,$datapassword);
// Connect UTF8 style
mysql_set_charset("utf8", $userlink);
mysql_query("SET NAMES UTF8", $userlink);

if ($userlink == FALSE) 
{
  echo("Unable to connect to the MySQL server, contact the administrator to check the schooladminconstants.php file");
  exit;
} // end if
ini_set('track_errors', $bkp_track_err);

$dbh = $userlink;

// Setup to use the current dastabase defined in schooladminconstants.php
if (! mysql_select_db($databasename,$userlink))
  echo("Unable to select the database specified in schooladminconstants.php, contect the administrator");


function checkupdates()
{
  if ($handle = opendir('dbupgrades'))
  {
    while (false !== ($file = readdir($handle)))
		{
			if(substr($file,0,7) == "version")
			{
				$ver = substr(substr($file,7),0,-4);
				$vernum=0;
				$vers = explode(".",$ver,3);
				foreach($vers AS $versub)
					$vernum = 1000 * $vernum + $versub;
				$verfiles[$vernum] = $file;
			}
    }
    closedir($handle);
		$curverqr = SA_loadquery("SHOW TABLE STATUS WHERE Name='config'");
		if(isset($curverqr['Comment'][1]))
		{
			$cver = substr($curverqr['Comment'][1],8);
			$curver=0;
			$cvers = explode(".",$cver,3);
			foreach($cvers AS $cversub)
			  $curver = $curver * 1000 + $cversub;
		}
		else
			$curver=0;

		foreach($verfiles AS $vnum => $vfile)
		{
			if($vnum <= $curver)
				unset($verfiles[$vnum]);
		}
		if(isset($verfiles))
		{
			ksort($verfiles);
			foreach($verfiles as $updfile)
				do_update($updfile);
		}
  }
	
}

function do_update($versionfile)
{
	global $userlink;
	global $dtext;
	//echo("Should update wih file ". $versionfile. "<BR>");
	if (!($fp = fopen("dbupgrades/". $versionfile,"r")))
		return;

	//Read the file line-by-line (up to 64k per line) and parse the data
	$data="";
	$total_queries = 0;
	$failed_queries = 0;
	while ($lineoftext=fgets($fp,65536))
	{
		if(($lineoftext != strstr($lineoftext,'#')) && ($lineoftext != strstr($lineoftext,'--')))
			$data = $data . $lineoftext;
		if(strstr($data,';') && (strlen(strstr($data,';')) > 0))
		{
			$total_queries++;
			// removing everything after the ; mark!
			$data = strrev(strstr(strrev($data),';'));
			$sql_result = mysql_query($data,$userlink);
			if(! $sql_result)
			{
				$failed_queries++;
			}
			$data = "";
		}
	}
	fclose($fp);
}
checkupdates();
// Get the texts to be displayed on the pages (for internationalisation)
global $dtext;
if(isset($_SESSION['dtext']))
{
  $dtext = $_SESSION['dtext'];
}
else
{ // Text was not loaded, do it now!
  SA_loadLanguage($defaultlanguage);
}

// Verify if the user may access the page
if(isset($login_qualify))
{
  $loginType="F";
  $LoginType = $_SESSION['LoginType'];
  SA_verifyQualification();
}

// Need to do the stuff below since PHP 4
$HTTP_GET_VARS = $_GET;
$HTTP_POST_VARS = $_POST;
$HTTP_POST_FILES = $_FILES;


// Gets the mysql release number
/**
 * DEFINES MYSQL RELATED VARIABLES & CONSTANTS
 * Overview:
 *    SA_MYSQL_INT_VERSION    (int)    - eg: 32339 instead of 3.23.39
 */

if (!defined('SA_MYSQL_INT_VERSION') && isset($userlink))
{
        $result = mysql_query('SELECT VERSION() AS version', $userlink);
        if ($result != FALSE && @mysql_num_rows($result) > 0) {
            $row   = mysql_fetch_array($result);
            $match = explode('.', $row['version']);
            mysql_free_result($result);
    } // end server id is defined case

    if (!isset($match) || !isset($match[0])) {
        $match[0] = 3;
    }
    if (!isset($match[1])) {
        $match[1] = 23;
    }
    if (!isset($match[2])) {
        $match[2] = 32;
    }

    if(!isset($row)) {
        $row['version'] = '3.23.32';
    }

    define('SA_MYSQL_INT_VERSION', (int)sprintf('%d%02d%02d', $match[0], $match[1], intval($match[2])));
    define('SA_MYSQL_STR_VERSION', $row['version']);
    unset($result, $row, $match);
}


/* ----------------------- Set of misc functions ----------------------- */


/**
 * Adds backquotes on both sides of a database, table or field name.
 * Since MySQL 3.23.6 this allows to use non-alphanumeric characters in
 * these names.
 *
 * param   mixed    the database, table or field name to "backquote" or
 *                   array of it
 * param   boolean  a flag to bypass this function (used by dump
 *                   functions)
 *
 * return  mixed    the "backquoted" database, table or field name if the
 *                   current MySQL release is >= 3.23.6, the original one
 *                   else
 *
 * access  public
 */
function SA_backquote($a_name, $do_it = TRUE)
{
    if ($do_it && !empty($a_name) && $a_name != '*')
    {
        if (is_array($a_name))
        {
            $result = array();
            foreach($a_name AS $key => $val)
                $result[$key] = '`' . $val . '`';
            return $result;
        }
        else 
            return '`' . $a_name . '`';
    }
    else
        return $a_name;
} // end of the 'SA_backquote()' function


/**
 * Defines the <CR><LF> value depending on the user OS.
 *
 * @return  string   the <CR><LF> value to use
 *
 * @access  public
 */
function SA_whichCrlf()
{
    $the_crlf = "\n";

    // Win case
    if (SA_USR_OS == 'Win')
        $the_crlf = "\r\n";
    // Mac case
    else if (SA_USR_OS == 'Mac')
            $the_crlf = "\r";
    // Others
    else
        $the_crlf = "\n";

    return $the_crlf;
} // end of the 'SA_whichCrlf()' function


/////////////////////////////////
////fn_writelog//////////////////
/////////////////////////////////
function SA_writeLog($logtype,$userid)
{
  global $userlink,$logevents;
  include("schooladminconstants.php");
  if ($logevents == 1)
  { //Open the database and write the event...
    $ts = date("Y-m-d H:i:s", time());
    $ip = getenv("REMOTE_ADDR");
    $sql = "INSERT INTO eventlog (eventid,user,ipaddr) VALUES"."('$logtype','$userid','$ip');";
    mysql_query($sql,$userlink);
  }
}

function SA_closeDB()
{
  global $userlink;
  mysql_close($userlink);
}

function SA_verifyQualification()
{
  global $login_qualify, $LoginType, $dtext;

	//echo("Login qualify = ". $login_qualify. ", Logintype = ". $LoginType);
  if(empty($LoginType))
    $is_qualified = FALSE;
  else
    $is_qualified = strstr($login_qualify, $LoginType);
  if(!$is_qualified)
  {
    echo($dtext['not_qualified'] . "<br>");
    exit;
  }
}

// This function checks if a teacher has access to the specified group.
function SA_verifyGroupAccess($uid,$newgroup)
{
  global $userlink, $LoginType;
  if($LoginType == "A" || $LoginType == "C")
  { // See if it's with in range of existing groups
    $sql_query = "SELECT groupname FROM sgroup WHERE active=1 AND groupname ='" . $newgroup . "'";
    $sql_result = mysql_query($sql_query, $userlink);
    if($sql_result && (mysql_num_rows($sql_result) > 0))
      return TRUE;	// Is an existing group
    else
      return FALSE;   // Is NOT and existing group!
  } 
  // See if the teacher is mentor of the group selected
  $sql_query = "SELECT groupname FROM sgroup WHERE active=1 AND tid_mentor = $uid AND groupname ='" . $newgroup ."'";
  $sql_result = mysql_query($sql_query,$userlink);
  if($sql_result && (mysql_num_rows($sql_result) > 0))
    return TRUE;	// Is mentor of the new group
  // So, it's not the mentor, let's see if it's any of the classes
  $sql_query = "SELECT sgroup.groupname FROM class,sgroup WHERE active=1 AND class.tid = $uid AND class.gid = sgroup.gid AND sgroup.groupname='" . $newgroup."'";
  $sql_result = mysql_query($sql_query);
  if($sql_result && (mysql_num_rows($sql_result) > 0))
    return TRUE;	// Is teacher of the new group
  // None of the conditions met so we return false!
  return FALSE;
}

function SA_addLimitScript()
{
  // Adds a javascript to the page to be able to limit the number of characters in a text field.
  echo("<SCRIPT LANGUAGE=\"JavaScript\">");
  echo("function textLimit(field, maxchars) { ");
  echo("if (field.value.length > maxchars) ");
  echo("field.value = field.value.substring(0, maxchars); }");
  echo("</SCRIPT>");
}

function SA_loadLanguage($lang)
{
  global $dtext, $userlink;
  $tt_name = "tt_" . $lang;
  $sql_query = "SELECT * FROM " . $tt_name;
  $sql_result = mysql_query($sql_query,$userlink);
  if($sql_result)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $dtext[mysql_result($sql_result,$r,'short')] = mysql_result($sql_result,$r,'full');
    }
    $_SESSION['dtext'] = $dtext;
    $_SESSION['currentlanguage'] = $lang;
  }
  else
   echo("Failed to load texts to display");
}

function SA_loadquery($query)
{
  global $userlink;
  $sql_result = mysql_query($query,$userlink);
  if(mysql_error($userlink))
    echo("<br>". mysql_error($userlink). " {". $query. "}");
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
       $results[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
	return($results);
  }//If numrows != 0
  return null;
}

function SA_mysqldate2nl($mysqldate)
{
  return(substr($mysqldate,8,2). "-". substr($mysqldate,5,2). "-". substr($mysqldate,0,4));
}

?>