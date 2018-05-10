<?PHP
echo("<FORM ID=view_abs_stud NAME=view_abs_stud METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. "><input type=hidden name=student value=0>");
add_posts();
echo("</FORM>");
echo("<FORM ID=delete_abs NAME=delete_abs METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. "><input type=hidden name=delte value=0>");
add_posts();
echo("</FORM>");
echo("<FORM ID=delete_abs_stud NAME=delete_abs_stud METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. "><input type=hidden name=delte value=0><input type=hidden name=student value=0>");
add_posts();
echo("</FORM>");

// Function to add posted fields
function add_posts()
{
  if(isset($_POST) && count($_POST) > 0)
    foreach($_POST AS $pkey => $pval)
	  if($pkey != "student" && $pkey != "delte")
	    echo("<input type=hidden name='". $pkey. "' value='". $pval. "'>");
}
?>
<SCRIPT>
  function viewabs_stud(stud)
  {
	document.getElementById("view_abs_stud").student.value=stud;
	document.getElementById("view_abs_stud").submit();
  }
  function deleteabs(asid)
  {
	document.getElementById("delete_abs").delte.value=asid;
	document.getElementById("delete_abs").submit();
  }
  function deleteabsstud(asid,stud)
  {
    if(confirm("<? echo(isset($_SESSION['dtext']['confirm_delete']) ? $_SESSION['dtext']['confirm_delete'] : "Confirm"); ?>"))
		{
			document.getElementById("delete_abs_stud").delte.value=asid;
			document.getElementById("delete_abs_stud").student.value=stud;
			document.getElementById("delete_abs_stud").submit();
		}
  }
</SCRIPT>