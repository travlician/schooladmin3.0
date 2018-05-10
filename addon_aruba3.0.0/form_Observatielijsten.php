<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+

 // $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("schooladminconstants.php");
	require_once("OLclasses.php");
  session_start();
  // Connect to the database
  inputclassbase::dbconnect($userlink);
  
  if(isset($_POST['fieldid']))
  {
    // DEBUG: show field change
		//foreach($_POST AS $pkey => $pval)
		//  echo($pkey. "=". $pval. "<BR>");
		OLlist::handle_input();
    // Let the library page handle the data
    include("inputlib/procinput.php");  
		// Upon adding category or change subject or category name, we must refresh in order for underlying items to be properly processed, also on entry of new item
		if($_POST['fieldid'] == "newolcatname" || $_POST['fieldid'] == "olsubjectname" || substr($_POST['fieldid'],0,11) == "newitemdesc" || substr($_POST['fieldid'],0,9) == "olcatname")
			echo("REFRESH");
    exit;
  }
	if(isset($_POST['delcat']))
	{
		$oitem = new OLitem($_POST['delcat']);
		$oitem->delete_category();
		$_POST['delcat'] = 0;
	}
	if(isset($_POST['upcat']))
	{
		$oitem = new OLitem($_POST['upcat']);
		$oitem->up_category();
		$_POST['upcat'] = 0;
	}
	if(isset($_POST['delitem']))
	{
		$oitem = new OLitem($_POST['delitem']);
		$oitem->delete_item();
		$_POST['delitem'] = 0;
	}
	if(isset($_POST['upitem']))
	{
		$oitem = new OLitem($_POST['upitem']);
		$oitem->up_item();
		$_POST['upitem'] = 0;
	}
  // Create a dummy field (invisible) to force a handler (myself)
  $trfld = new inputclass_listfield("dummy","SELECT '' AS id,'' AS tekst",NULL,"tdid","SCTtestref",0,"sctid","display:none",$_SERVER['PHP_SELF']);
  $trfld->echo_html();
  
  // Link with stylesheet
  //echo ('<HTML><BODY><LINK rel="stylesheet" type="text/css" href="style.css" title="style1"><LINK rel="stylesheet" type="text/css" href="style_OL.css" title="style2">');
  echo ('<HTML><BODY><LINK rel="stylesheet" type="text/css" href="style_OL.css" title="style2">');
  
  // Get the year
  $schoolyearqr = inputclassbase::load_query("SELECT year FROM period");
	if(isset($schoolyearqr['year']))
		$schoolyear = $schoolyearqr['year'][0];
  $ollist = new OLlist($schoolyear);
	$ollist->show();
	// javascript and forms to delete categories and items
  echo("<FORM METHOD=POST ID=catdelform ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=hidden NAME=delcat VALUE='' ID=delcat></FORM>");
	echo("<SCRIPT> function delete_cat(cat) { document.getElementById('delcat').value=cat; document.getElementById('catdelform').submit(); } </SCRIPT>");
  echo("<FORM METHOD=POST ID=itemdelform ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=hidden NAME=delitem VALUE='' ID=delitem></FORM>");
	echo("<SCRIPT> function delete_item(item) { document.getElementById('delitem').value=item; document.getElementById('itemdelform').submit(); } </SCRIPT>");
  echo("<FORM METHOD=POST ID=upcatform ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=hidden NAME=upcat VALUE='' ID=upcat></FORM>");
	echo("<SCRIPT> function up_cat(upcat) { document.getElementById('upcat').value=upcat; document.getElementById('upcatform').submit(); } </SCRIPT>");
  echo("<FORM METHOD=POST ID=itemupform ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=hidden NAME=upitem VALUE='' ID=upitem></FORM>");
	echo("<SCRIPT> function up_item(item) { document.getElementById('upitem').value=item; document.getElementById('itemupform').submit(); } </SCRIPT>");
  // close the page
  echo("</BODY></html>");
  
?>
