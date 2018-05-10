<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
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
session_start();

global $login_qualify;
$login_qualify = 'ACT';
require_once('./schooladminfunctions.php');


/**
 * Increase time limit for script execution and initializes some variables
 */
@set_time_limit(300);

$uid = $_SESSION['uid'];
$CurrentUID = $uid;
$CurrentGroup = $_SESSION['CurrentGroup'];
$ReportID = $_SESSION['rid'];

if($ReportID == "")
{
  echo($dtext['mising_rid']);
  SA_closeDB();
  exit;
}

// Get all the data from the report
$sql_query = "SELECT * FROM reports WHERE rid=$ReportID";
$sql_result = mysql_query($sql_query,$userlink);
//echo mysql_error($userlink);
$nrows = 0;
if (mysql_num_rows($sql_result)!=0)
{
  $nfields = mysql_num_fields($sql_result);
  for($r=0;$r<mysql_num_rows($sql_result);$r++)
  {
    $nrows++;
    for ($i=0;$i<$nfields;$i++)
    {
      $fieldname = mysql_field_name($sql_result,$i);
      $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
      $report_array[$fieldname][$nrows]=$fieldvalu;
    } // for $i
  } //for $r
  mysql_free_result($sql_result);
}//If numrows != 0
$report_n = $nrows;

if(!$report_array['type'][1] == "F" && !$report_array['type'][1] == "X")
{ // It's not a file!
  echo($dtext['no_rep_file']);
  SA_closeDB();
  exit;
}

$filename = $report_array['content'][1];

// Generate filename and mime type if needed
$SA_uri_parts = parse_url($livesite);

// Generate basic dump extension
$mime_type = (SA_USR_BROWSER_AGENT == 'IE' || SA_USR_BROWSER_AGENT == 'OPERA')
                   ? 'application/octetstream'
                   : 'application/octet-stream';
    
/**
 * Send headers depending on whether the user chose to download a dump file
 * or not
 */
// Download
header('Content-Type: "' . $mime_type. '; charset=UTF-8"');
header('Expires: ' . gmdate('D, d M Y H:i:s', time()-36000) . ' GMT');
// lem9 & loic1: IE need specific headers
if (SA_USR_BROWSER_AGENT == 'IE') 
{
   header('Content-Disposition: inline; filename="' . $filename . '"');
   header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
   header('Pragma: public');
} 
else 
{
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Pragma: no-cache');
}


/**
 * Send the file content
 */
$filename = $reportspath . $filename;
if (!($fp = fopen($filename,"rb")))
{
  echo($dtext['err_openfile'] . " \"". $filename . "\" " . $dtext['4read']);
  echo("<br><a href=reportsongroup.php>" . $dtext['back_repgrp'] . "</a>");
  SA_closeDB();
  exit;
}
// file is opened, now we start the copy to user
while ($lineoftext=fgets($fp,65536))
{   
  echo($lineoftext);
}
fclose($fp);

?>
