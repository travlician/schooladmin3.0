<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  
  require_once("inputlib/inputclasses.php");
  require_once("teacher.php");
  require_once("student.php");
  
  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  
  // Link the database connection with the input library
  inputclassbase::dbconnect($userlink);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  
  // Store reported image movements and exit if done so
  if(isset($_POST['sid']))
  { // Image position change reported
    mysql_query("REPLACE INTO stud_imgloc (sid,tid,xoff,yoff) VALUES(". $_POST['sid']. ",". $uid. ",". $_POST['xoff']. ",". $_POST['yoff']. ")", $userlink);
	if(mysql_error($userlink))
	  echo(mysql_error($userlink));
	else
	  echo("OK");
	exit;
  }
  
  $uid = intval($uid);
  
  
  // Get the table name for the images
  $pictn = inputclassbase::load_query("SELECT table_name FROM student_details WHERE type='picture' ORDER BY seq_no LIMIT 1"); 
  // Get a list of all applicable students
  $students = SA_loadquery("SELECT CONCAT(lastname,', ',firstname) AS name,firstname, lastname, sid, data AS imgname 
                            FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) 
							LEFT JOIN ". $pictn['table_name'][0]. " USING(sid)
							WHERE active=1 AND sgroup.groupname = '$CurrentGroup' ORDER BY name");
  // Get the image locations applicable for this group, first we load the mentor's locations, then current teacher locations
  $imglocs = SA_loadquery("SELECT sid,xoff,yoff FROM stud_imgloc LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND tid=tid_mentor AND groupname= '$CurrentGroup'");
  if(isset($imglocs['sid']))
  {
    foreach($imglocs['sid'] AS $six => $sid)
	{
	  $imgloc[$sid]['x'] = $imglocs['xoff'][$six];
	  $imgloc[$sid]['y'] = $imglocs['yoff'][$six];
	}
  }
  $imglocs = SA_loadquery("SELECT sid,xoff,yoff FROM stud_imgloc LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND tid=". $uid. " AND groupname= '$CurrentGroup'");
  if(isset($imglocs['sid']))
  {
    foreach($imglocs['sid'] AS $six => $sid)
	{
	  $imgloc[$sid]['x'] = $imglocs['xoff'][$six];
	  $imgloc[$sid]['y'] = $imglocs['yoff'][$six];
	}
  }

  // First part of the page
  echo("<html><head><title>Student images</title></head><body background=schooladminbg.jpg link=blue vlink=blue onload=move_imgs()>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
?>
<style>
.dragme
{
  position:relative;
  margin: 30px;
  width: 100px;
}
</style>
<script>
function touchstart(ev)
{
  ev.target.orgX = ev.targetTouches[0].pageX;
  ev.target.orgY = ev.targetTouches[0].pageY;
  ev.target.orgL = parseInt(ev.target.style.left+0);
  ev.target.orgT = parseInt(ev.target.style.top+0);
}

function dragstart(ev)
{
  ev.preventDefault();
  ev.target.style.zIndex=1000;
  ev.target.orgX = ev.pageX;
  ev.target.orgY = ev.pageY;
  ev.target.orgL = parseInt(ev.target.style.left+0);
  ev.target.orgT = parseInt(ev.target.style.top+0);
  ev.target.dragging = true;
}

function touchmove(ev)
{
  ev.preventDefault();
  //alert("Touchmove on "+ev.target.id+" ("+ev.touches[0].pageX+","+ev.touches[0].pageY+")");
  ev.target.style.left = ev.target.orgL + ev.targetTouches[0].pageX - ev.target.orgX;
  ev.target.style.top = ev.target.orgT + ev.targetTouches[0].pageY - ev.target.orgY;  
}

function dragmove(ev)
{
  if(ev.target.dragging)
  {
    ev.preventDefault();
    ev.target.style.left = ev.target.orgL + ev.pageX - ev.target.orgX;
    ev.target.style.top = ev.target.orgT + ev.pageY - ev.target.orgY;  
  }
}

function touchend(ev)
{
  send_imgpos(ev.target);
}

function dragend(ev)
{
  if(ev.target.dragging)
  {
    ev.target.style.zIndex=1;
    ev.target.dragging=false;
    send_imgpos(ev.target);
  }
}
</script>


<?


  // Create an image for each student
  $poscnt = 0;
  echo("<DIV id='div1'>");
  echo("<p style='width: 900px;'>");
  foreach($students['sid'] AS $six => $sid)
  {
    echo("<IMG SRC='".  ($students['imgname'][$six] != "" ? $livepictures. $students['imgname'][$six] : "PNG/user.png"). 
	     "' class=dragme TITLE='". $students['name'][$six]. "' ID=". $sid. " onmousedown='dragstart(event)' 
		 onmouseup='dragend(event)' onmouseout='dragend(event)' onmousemove='dragmove(event)' ontouchstart='touchstart(event)' ontouchmove='touchmove(event)' ontouchend='touchend(event)'>");
    $poscnt++;
	if($poscnt % 5 == 0)
	  echo("</p><p style='width: 900px;'>");
  }
  echo '</p><BR><a href=# onClick="window.close();">';
  echo("</div>");
  echo $dtext['back_teach_page'];
  echo '</a>';

// Ajax type scripting to send changed positions
?>
<SCRIPT>
<?
  require_once("inputlib/xhconn.js");
?>
var AjaxPending=0;
function send_imgpos(imgobj)
{
  imgConn = new XHConn(imgobj);
  if (!imgConn) alert("XMLHTTP not available. Try a newer/better browser.");
  imgConn.connect("<? echo($_SERVER['REQUEST_URI']) ?>", "POST", "sid="+imgobj.id+"&xoff="+parseInt(imgobj.style.left+0)+"&yoff="+parseInt(imgobj.style.top+0), imgconnDone);
}
function imgconnDone(oXML,imgobj)
{
  if(oXML.responseText.substring(0,2) != "OK" && typeof oXML.responseText != "undefined")
    alert(oXML.responseText);  
}
function move_imgs()
{
<?
  if(isset($imgloc))
  {
    foreach($imgloc AS $imgsid => $imgxy)
	{
	  echo("document.getElementById('". $imgsid. "').style.left=". $imgxy['x']. "; ");
	  echo("document.getElementById('". $imgsid. "').style.top=". $imgxy['y']. "; ");
	}
  }
?>
}
</SCRIPT>
<?
  include("touchfix.js");
  // close the page
  echo("</html>");
?>
