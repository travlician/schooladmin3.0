<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011  Aim4Me N.V.  (http://www.aim4me.info)       |
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
// | Authors: Wilfred van Weert  -  travlician@bigfoot.com                |
// +----------------------------------------------------------------------+
//
session_start();

$login_qualify = 'A';
require_once("schooladminfunctions.php");

?>

<html>
<head>
<title><?php echo($dtext['restore_title']); ?></title>
<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">
</head>
<body background = schooladminbg.jpg>
<h3><?php echo($dtext['restore_title']); ?></h3>

<?php
  $userfile = $HTTP_POST_FILES;
  $file = $userfile['userfile']['tmp_name'];

//Open the file the user just sent
if ($file=="none" || $file == "")
{
  echo($dtext['inv_file']);
  echo("<br><a href=admin.php>" . $dtext['back_admin'] . "</a>");
  SA_closeDB();
  exit;
}

if (!($fp = fopen($file,"r"))){
  echo($dtext['err_openfile'] . " " . $dtext['4read']);
  echo("<br><a href=admin.php>" . $dtext['back_admin'] . "</a>");
  SA_closeDB();
  exit;
}


//Read the file line-by-line (up to 64k per line) and parse the data
echo($dtext['restore_start'] . "<br>");
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
      echo($dtext['query_fail'] . "<br>");
      echo(@mysql_error($userlink). "<br>");
    }
    $data = "";
  }
}
fclose($fp);

//Close database
SA_closeDB();

// Show end-result:
echo($dtext['query_total'] . ": ". $total_queries . ", failed queries: " . $failed_queries);
echo("<br>" . $dtext['restore_complete'] . "<br>");

// Show a link back to the original page
echo("<a href=admin.php>" . $dtext['back_admin'] . "</a>");
echo("</body></html>");

?>

