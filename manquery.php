<html><head>
<!-- ------------------------------------------------------------------ -->
<!--  Schooladmin -- Version 2.1                                        -->
<!-- ------------------------------------------------------------------ -->
<!-- Copyright (C) 2004-2011  Aim4me N.V.  (http://www.aim4me.info)     -->
<!-- ------------------------------------------------------------------ -->
<!-- This program is free software.  You can redistribute in and/or     -->
<!-- modify it under the terms of the GNU General Public License        -->
<!-- Version 2 as published by the Free Software Foundation.            -->
<!-- ------------------------------------------------------------------ -->
<!--  Authors: Wilfred van Weert - travlician@bigfoot.com               -->
<!-- ------------------------------------------------------------------ -->
<title>Submit a SQL Query</title>
</head>
<body background=schooladminbg.jpg>
<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">
<font size=+1>
<h2>Submit SQL Query</h2>
<form method=post action=submitquery.php>
<textarea cols=50 rows=10 name=sqltext></textarea><br>
<input type=submit value="Submit Query"><input type=reset value="Cancel">
<p>
<br>
Return results in...
<br>
<input type=radio name=returnstyle value=returnhtml checked>HTML Table<br>
<input type=radio name=returnstyle value=returntabtext >Tab Delimited Text<br>
<input type=radio name=returnstyle value=returncommatext >Comma Delimited Text<br>
<input type=radio name=returnstyle value=returnothertext >Text with ASCII <input type=text size=4 name=asciidelim> <br>


</form>
<a href="admin.php">Back to the administration page</a>
</body>
</html>
