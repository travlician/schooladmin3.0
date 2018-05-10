<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("displayelements/displayelement.php");
require_once("teacher.php");
require_once("student.php");

class StudentPlacement extends displayelement
{
  protected function add_contents()
  {
    // This function is based on tables that is created as needed. So now we create it if it does not exist.
	global $userlink;
    $sqlquery = "CREATE TABLE IF NOT EXISTS `stud_imgloc` (
      `sid` INTEGER(11) NOT NULL,
      `tid` INTEGER(11) UNSIGNED NOT NULL,
      `xoff` INTEGER(11) DEFAULT NULL,
      `yoff` INTEGER(11) DEFAULT NULL,
	  PRIMARY KEY (`sid`,`tid`)
      ) ENGINE=InnoDB;";
    mysql_query($sqlquery,$userlink);
    echo(mysql_error());
  }
  
  public function show_contents()
  {
    global $livepictures;
	  // Get the table name for the images
	  $pictn = inputclassbase::load_query("SELECT table_name FROM student_details WHERE type='picture' ORDER BY seq_no LIMIT 1"); 
	  // Get a list of all applicable students
	  $students = inputclassbase::load_query("SELECT CONCAT(lastname,', ',firstname) AS name,firstname, lastname, sid, data AS imgname 
								FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) 
								LEFT JOIN ". $pictn['table_name'][0]. " USING(sid)
								WHERE active=1 AND sgroup.groupname = '". $_SESSION['CurrentGroup']. "' ORDER BY name");
	  // Get the image locations applicable for this group, first we load the mentor's locations, then current teacher locations
	  $imglocs = SA_loadquery("SELECT sid,xoff,yoff FROM stud_imgloc LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND tid=tid_mentor AND groupname= '". $_SESSION['CurrentGroup']. "'");
	  if(isset($imglocs['sid']))
	  {
		foreach($imglocs['sid'] AS $six => $sid)
		{
		  $imgloc[$sid]['x'] = $imglocs['xoff'][$six];
		  $imgloc[$sid]['y'] = $imglocs['yoff'][$six];
		}
	  }
	  $imglocs = SA_loadquery("SELECT sid,xoff,yoff FROM stud_imgloc LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND tid=". $_SESSION['uid']. " AND groupname= '". $_SESSION['CurrentGroup']. "'");
	  if(isset($imglocs['sid']))
	  {
		foreach($imglocs['sid'] AS $six => $sid)
		{
		  $imgloc[$sid]['x'] = $imglocs['xoff'][$six];
		  $imgloc[$sid]['y'] = $imglocs['yoff'][$six];
		}
	  }
?>
	<style>
	.dragme
	{
	  position:relative;
	  margin: 30px;
	  width: 100px;
	}
	.caption
	{
		position: absolute;
		z-index: 30;
		margin-left: 0px;
		margin-top: 0px;
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
	  ev.target.style.left = (ev.target.orgL + ev.targetTouches[0].pageX - ev.target.orgX)+"px";
	  ev.target.style.top = (ev.target.orgT + ev.targetTouches[0].pageY - ev.target.orgY)+"px";
    ev.target.previousSibling.style.left=(window.pageXOffset + ev.target.getBoundingClientRect().left)+"px";
    ev.target.previousSibling.style.top=(window.pageYOffset + ev.target.getBoundingClientRect().bottom)+"px";
	}

	function dragmove(ev)
	{
	  if(ev.target.dragging)
	  {
		//ev.preventDefault();
		ev.target.style.left = (ev.target.orgL + ev.pageX - ev.target.orgX)+"px";
		ev.target.style.top = (ev.target.orgT + ev.pageY - ev.target.orgY)+"px";		
    ev.target.previousSibling.style.left=(window.pageXOffset + ev.target.getBoundingClientRect().left)+"px";
    ev.target.previousSibling.style.top=(window.pageYOffset + ev.target.getBoundingClientRect().bottom)+"px";
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
			 "' class=dragme TITLE='". $students['name'][$six]. "' ALT='test<BR>2ndline' ID=". $sid. " onmousedown='dragstart(event)' 
			 onmouseup='dragend(event)' onmouseout='dragend(event)' onmousemove='dragmove(event)' ontouchstart='touchstart(event)' ontouchmove='touchmove(event)' ontouchend='touchend(event)'>");
		//echo($students['name'][$six]);
		$poscnt++;
		if($poscnt % 5 == 0)
		  echo("</p><p style='width: 900px;'>");
	  }
	  echo '</p><BR><a href=# onClick="window.close();">';
	  echo("</div>");
	  echo $_SESSION['dtext']['back_teach_page'];
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
	  imgConn.connect("datahandler.php", "POST", "studentimagelocation="+imgobj.id+"&xoff="+parseInt(imgobj.style.left+0)+"&yoff="+parseInt(imgobj.style.top+0), imgconnDone);
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
				echo("document.getElementById('". $imgsid. "').style.left='". $imgxy['x']. "px'; ");
				echo("document.getElementById('". $imgsid. "').style.top='". $imgxy['y']. "px'; ");
				
			}
		}
		// Show a text box with the title
		/* echo("var stImgs = document.getElementsByClassName('dragme'); ");
		echo("for(i=0; i<stImgs.length; i++) { ");
		echo("var stName = document.createElement(\"DIV\"); stName.className='caption'; var title = stImgs[i].getAttribute('alt'); var divCaption_text = document.createTextNode(title); stName.appendChild(divCaption_text); stImgs[i].parentNode.insertBefore(stName,stImgs[i]); ");
		echo("stName.style.left=stImgs[i].getBoundingClientRect().left + 'px';  stName.style.top=stImgs[i].getBoundingClientRect().bottom + 'px'; }\r\n");
		*/
		echo("var stImgs = document.getElementsByClassName('dragme'); ");
		echo("var xoff=parseInt(window.pageXOffset); var yoff=parseInt(window.pageYOffset); ");
		echo("for(i=0; i<stImgs.length; i++) { ");
		echo("var stName = document.createElement(\"DIV\"); stName.className='caption'; var title = stImgs[i].getAttribute('title'); stName.innerHTML=title.replace(', ','<BR>');  stImgs[i].parentNode.insertBefore(stName,stImgs[i]); ");
		echo("stName.style.left=(xoff + stImgs[i].getBoundingClientRect().left) + 'px';  stName.style.top=(yoff + stImgs[i].getBoundingClientRect().bottom) + 'px'; }\r\n");

	?>
	}
	//alert("Scroll pos = " + window.pageXOffset + "," + window.pageYOffset);
	setTimeout('window.addEventListener("load",move_imgs());',500);
	</SCRIPT>
<?
  }
  
}
?>
