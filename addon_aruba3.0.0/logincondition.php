<?
  if(!isset($_SESSION['passedmain']) || $_SESSION['passedmain'] != 1)
  {
		if($_SERVER['SERVER_NAME'] != "server1.myschoolresults.com" && $_SERVER['SERVER_NAME'] != "server2.myschoolresults.com")
		{
			header("Location: http://myschoolresults.com/index.php");
			exit;
		}
  }
?>
