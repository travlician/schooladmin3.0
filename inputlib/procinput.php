<?
// Processing an Ajax type request to store a field in the database.
require_once("inputclasses.php");
if(isset($_POST['fieldid']))
{ // all requests need fieldid to be set to handle the data
  if(isset($_SESSION['inputobjects'][$_POST['fieldid']]))
  { // Now we do have an object to process
    $iobj = $_SESSION['inputobjects'][$_POST['fieldid']];
	$orgkey = $iobj->get_key();
	$iobj->handle_input();
	$newkey = $iobj->get_key();
	if($orgkey != $newkey)
	  foreach($_SESSION['inputobjects'] AS $eobj)
	  {
	    if($eobj->get_key() == $orgkey)
  	      $eobj->inserted_key($newkey);
	  }
  }
  else
  {
    echo("No linked object found for field ". $_POST['fieldid']);
  }
}