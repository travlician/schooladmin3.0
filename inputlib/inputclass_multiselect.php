<?
require_once("inputclassbase.php");
class inputclass_multiselect extends inputclassbase
{
  protected $listquery;
  protected $initial_sellist;
  public function __construct($fieldid,$listquery,$dbconnection = NULL,$dbfield = NULL, $dbtable = NULL, $dbkey = NULL, $dbkeyfield = NULL, $style = NULL, $handler = NULL, $extrafield = NULL, $extravalue = NULL)
  {
    parent::__construct($fieldid,$dbconnection,$dbfield,$dbtable,$dbkey,$dbkeyfield,$style,$handler,$extrafield,$extravalue);
	$this->listquery = $listquery;
  }
  public function set_initial_value($sels)
  {
    foreach($sels AS $six => $sval)
      $this->initial_sellist["sel"][$six] = $six;
  }
  public function handle_input()
  {
    foreach($_POST AS $fname => $postval)
    {
      if(substr($fname,0,strlen("cb".$this->fieldid)) == "cb".$this->fieldid)
      {
        $selkey = substr($fname,strlen("cb".$this->fieldid));
        if($postval == 1)
	{
	  $query = "INSERT INTO ". $this->dbtable. " (". ($this->dbkey > 0 ? "`". $this->dbkeyfield. "`," : ""). "`". $this->dbfield. "`";
	  if(isset($this->extrafield))
	    foreach($this->extrafield AS $fnm => $fvl)
	      $query .= ",`". $fnm. "`";
	  if(isset($this->extrakeyfield))
	    $query .= ",`". $this->extrakeyfield. "`";
          $query .= ") VALUES(". ($this->dbkey > 0 ? $this->dbkey. "," : "") .$selkey;
	  if(isset($this->extrafield))
	    foreach($this->extrafield AS $fnm => $fvl)
	      $query .= ",\"". $fvl. "\"";
	  if(isset($this->extrakeyfield))
	    $query .= ",\"". $this->extrakey. "\"";
	  $query .= ")";
	}
	else
	{
          $query = "DELETE FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. " AND `". $this->dbfield. "`=\"". $selkey. "\"";
	  if(isset($this->extrakeyfield))
	    $query .= " AND `". $this->extrakeyfield. "`=\"". $this->extrakey. "\"";
	}
	mysql_query($query,parent::$dbconnection);
	if(mysql_error(inputclassbase::$dbconnection))
	  echo(mysql_error(parent::$dbconnection));
	else
	  echo("OK\r\n");
	echo("\r\n". $query. "\r\n");
	if($this->dbkey <= 0)
  	  $this->dbkey = mysql_insert_id(parent::$dbconnection);
      }
    }
  }

  public function __toString()
  {
    if($this->dbkey > 0)
    {
      // Since we have a query to get all the possibilities, now we crosslink in php with it.
      $choicelist = $this->load_query($this->listquery);
      $sellist = $this->load_query("SELECT * FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
      if(isset($sellist))
      {
        $retval="";
        foreach($sellist[$this->dbfield] AS $did)
	{
	  foreach($choicelist['id'] AS $cix => $cid)
	    if($cid == $did)
	    { 
              if($retval != "")
              $retval .= "<BR>";
	      $retval .= $choicelist['tekst'][$cix];
            }
	}
      }
      else
        $retval = "Geen";
      return $retval;
    }
    else
      return "";
  }

  public function echo_html()
  {
    parent::echo_html();
    echo("<DIV". $this->styledata(). ">");
    // Put a checkbox for each choice with the textual choice behind it. First the once selected, then the ones not selected.
    $choicelist = $this->load_query($this->listquery);
    if(isset($this->dbtable))
      $sellist = $this->load_query("SELECT `". $this->dbfield. "` AS sel FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
    else
      if(isset($this->initial_sellist))
        $sellist = $this->initial_sellist;
    // Already selected items
    foreach($choicelist['id'] AS $cix => $cid)
    {
      $issel = false;
      if(isset($sellist))
      {
        foreach($sellist["sel"] AS $sid)
 	  if($sid == $cid)
	    $issel = true;
      }
      if($issel)
      {
        echo("<INPUT TYPE=CHECKBOX NAME=cb". $this->fieldid. $cid. " CHECKED");
        if($this->dbfield != NULL || $this->handlerpage != NULL)
          echo(" onChange='return(send_xmlcb(\"". $this->fieldid. "\",this));'");
        echo("> ". $choicelist['tekst'][$cix]. "<BR>");
      }
    }
    // Unselected items
    foreach($choicelist['id'] AS $cix => $cid)
    {
      $issel = false;
      if(isset($sellist))
      {
        foreach($sellist["sel"] AS $sid)
	  if($sid == $cid)
	    $issel = true;
      }
      if(!$issel)
      {
        echo("<INPUT TYPE=CHECKBOX NAME=cb". $this->fieldid. $cid);
        if($this->dbfield != NULL || $this->handlerpage != NULL)
          echo(" onChange='return(send_xmlcb(\"". $this->fieldid. "\",this));'");
	echo("> ". $choicelist['tekst'][$cix]. "<BR>");
      }
    }
    echo("</DIV>");
  }
}
?>