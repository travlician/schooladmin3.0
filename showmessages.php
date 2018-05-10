<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 3.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2018 Aim4me N.V.   (http://www.aim4me.info)       |
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

  include ("schooladminfunctions.php");
	require_once("message.php");
	// Connect to database
	require_once("inputlib/inputclasses.php");
	inputclassbase::dbconnect($userlink);
	
	if(isset($_POST['readmsg']))
	{ // mark this message as read
		$readmsg = new message($_POST['readmsg']);
		$readmsg->set_read();
	}
	if(isset($_POST['skipmsg']))
		$skipmsg = $_POST['skipmsg'];
	else
		$skipmsg = 0;

	echo("<html>");
	if($_SESSION['usertype'] == "teacher")
		$desttype="t";
	else if($_SESSION['usertype'] == "student")
		$desttype="s";
	else if($_SESSION['usertype'] == "parent")
		$desttype="p";
	$msgs = message::list_messages($desttype,$_SESSION['uid']);
	echo("<form method=post action='". ($desttype == 't' ? "teacherpage.php" : ""). "' id=readmsg style='float: right;'><input type=button value=\"". $_SESSION['dtext']['Close']. "\" onClick='frameElement.parentNode.removeChild(frameElement);'></FORM>");
	if(!isset($msgs))
	{ // Show that there are no more message and allow close
		echo($dtext['NoMoreMessages']);
	}
	else
	{
		$keys = array_keys($msgs);
		$firstmsg = $msgs[$keys[$skipmsg]];
		echo($firstmsg->get_sender(). " @". $firstmsg->get_timestamp());
		
		// We now show some controls: mark read and next message
		if(isset($keys[$skipmsg+1]))
			echo("<form method=post id=skipmsg style='float: right;'><input type=hidden name=skipmsg value=". ($skipmsg+1). "><input type=submit value=\"". $_SESSION['dtext']['NextMessage']. "\"></FORM>");
		echo("<form method=post id=readmsg style='float: right;'><input type=hidden name=readmsg value=". $firstmsg->get_id(). "><input type=submit value=\"". $_SESSION['dtext']['MarkRead']. "\"></FORM>");
		// Next message is only displayed if there are more message
		echo("<BR><BR>". $firstmsg->get_message());
	}
	echo("</html>");

?>
