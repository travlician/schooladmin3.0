<?
// MUST load the classes before session_start()!
require_once("inputlib/inputclasses.php");
session_start();
require_once("schooladminconstants.php");
// Reconnect with the database as we don't use persistent connections
$dbconn = mysql_connect($databaseserver,$datausername,$datapassword);
mysql_select_db($databasename,$dbconn);
// echo("ERR"); // Debuging
// Let the inputclasses be aware of this new connection
inputclassbase::dbconnect($dbconn);
// Let the library page handle the data
include("inputlib/procinput.php");
// Just for demo purposes: show the fields posted(note that the library only shows an alert with this data if something went wrong)
foreach($_POST AS $parm => $val)
{
  echo("\r\nPassed parameter: ". $parm. " = ". $val); 
}
?>