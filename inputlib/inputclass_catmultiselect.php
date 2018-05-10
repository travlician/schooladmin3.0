<?
require_once("inputclass_multiselect.php");
class inputclass_catmultiselect extends inputclass_multiselect
{
  // As inputclass_multiselect but items are shown in a table with columns for each catagory (cat field in the given query)
  public function __toString()
  {
    if($this->dbkey > 0)
	{
	  // Since we have a query to get all the possibilities, now we crosslink in php with it.
      $catlist = $this->load_query("SELECT DISTINCT cat FROM (". $this->listquery. ") AS t1");
	  $choicelist = $this->load_query($this->listquery);
          if(isset($this->dbtable))
  	    $sellist = $this->load_query("SELECT * FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
	  if(isset($sellist))
	  {
	    // Create the table heading
        $retval="<table><tr>";
		foreach($catlist['cat'] AS $cat)
		  $retval .= "<th>". $cat. "</th>";
		$retval .= "</tr>";
		// Create a second row with each select item in the appropriate (category related) column
		$retval .= "<tr>";
		foreach($catlist['cat'] AS $cat)
		{
		  $retval .= "<td>";
		  $hasvals = false;
	      foreach($sellist[$this->dbfield] AS $did)
		  {
		    foreach($choicelist['id'] AS $cix => $cid)
			{
			  if($choicelist['cat'][$cix] == $cat)
			  {
		        if($cid == $did)
		        { 
			      if($hasvals)
			        $retval .= "<BR>";
			      $retval .= $choicelist['tekst'][$cix];
				  $hasvals = true;
			    }
			  }
			}
		  }
		  $retval .= "</td>";
		}
		// Close row and table
		$retval .= "</tr></table>";
		return $retval;
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
    // Put a checkbox for each choice with the textual choice behind it. First the once selected, then the ones not selected.
    $catlist = $this->load_query("SELECT DISTINCT cat FROM (". $this->listquery. ") AS t1");
    $choicelist = $this->load_query($this->listquery);
    if(isset($this->dbtable))
      $sellist = $this->load_query("SELECT `". $this->dbfield. "` AS sel FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
    else if(isset($this->initial_sellist))
      $sellist = $this->initial_sellist;
    // Create the table and heading with categories
    echo("<table". $this->styledata(). "><tr>");
    foreach($catlist['cat'] AS $cat)
    {
      echo("<th>". $cat. "</th>");
    }
    echo("</tr>");
    // Add a cell on the second row for each category with the relevant choices
    echo("<tr>");
    foreach($catlist['cat'] AS $cat)
    {
      echo("<td>");
      // Already selected items
      foreach($choicelist['id'] AS $cix => $cid)
      {
        if($choicelist['cat'][$cix] == $cat)
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
              echo(" onClick='return(send_xmlcb(\"". $this->fieldid. "\",this));'");
            echo("><SPAN ID=cb". $this->fieldid. $cid. "> ". $choicelist['tekst'][$cix]. "<BR></SPAN>");
	  }
	}
      }
      // Unselected items
      foreach($choicelist['id'] AS $cix => $cid)
      {
        if($choicelist['cat'][$cix] == $cat)
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
              echo(" onClick='return(send_xmlcb(\"". $this->fieldid. "\",this));'");
	    echo("><SPAN ID=cb". $this->fieldid. $cid. "> ". $choicelist['tekst'][$cix]. "<BR></SPAN>");
          }
        }
      }
      echo("</td>");
    } // End for each cat
    echo("</tr></table>");
  } // End function
} // End class def
?>