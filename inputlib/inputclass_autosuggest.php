<?
require_once("inputclass_textfield.php");
class inputclass_autosuggest extends inputclass_textfield
{
  public function echo_html()
  {
    inputclass_textfield::echo_html();
    //echo('<script language="Javascript" src="../inputlib/autosuggest.js"></script>');
    echo('<script language="Javascript">');
	require_once("autosuggest.js");
	// Get a list of existing values
	$optionsqr = inputclassbase::load_query("SELECT DISTINCT `". $this->dbfield. "` AS options FROM ". $this->dbtable. " ORDER BY `". $this->dbfield. "`");
	if(isset($optionsqr['options']))
	{
	  $first = true;
	  foreach($optionsqr['options'] AS $option)
	  {
	    if($first)
		{
		  $first = false;
		  echo(' var options = new Array(');
		}
		else
		  echo(",");
		echo('"'. $option. '"');
	  }
	  echo(");
	         ");
	}
	else
	  echo(' var options = new Array("<>");');
	echo(' new AutoSuggest(document.getElementById("'. $this->fieldid. '"),options);
          </script>');
  }
}
?>