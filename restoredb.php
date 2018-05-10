<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4Me N.V  (http://www.aim4me.info)         |
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
// | Authors: Wilfred van Weert   -  traclician@bigfoot.com               |
// +----------------------------------------------------------------------+
//
session_start();
$login_qualify = 'A';
require_once("schooladminfunctions.php");

?>
<html>
<head>
<title><?php echo($dtext['restore_title2']); ?></title>
</head>
<LINK rel="stylesheet" type="text/css" href="style.css" name="style1">
<body background=schooladminbg.jpg>
<h3><?php echo($dtext['restore_title2']); ?></h3>

<form method=post action=restore.php ENCTYPE="multipart/form-data">
<?php echo($dtext['restore_expl_1']); ?>
<br> <input type=file name="userfile" value="<?php echo($dtext['locate_file']); ?>">
<p>
<?php echo($dtext['restore_expl_2']); ?>
<br>
<input type=submit value="<?php echo($dtext['restore_from_file']); ?>">
<p>

</form>
</body>
</html>


