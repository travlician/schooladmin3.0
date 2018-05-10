<?
require_once("menulistelement.php");

class vanishingmenulistelement extends menulistelement
{
  protected $orgdivid;
  public function __construct($divid = NULL, $style = NULL, $menuheading = "", $submenudiv = NULL, $submenustyle = NULL, $itemstyle = NULL, $itemactivestyle = NULL, $paramname = NULL)
  {
    if($divid == NULL)
	  $divid = "vanmenudiv";
	$this->orgdivid = $divid;
	$divid = $divid. " onMouseOver='enter_". $this->orgdivid. "(this,event);' onMouseOut='leave_". $this->orgdivid. "(this,event);'";
    parent::__construct($divid, $style, $menuheading, $submenudiv, $submenustyle, $itemstyle, $itemactivestyle, $paramname);
  }
  public function pre_show()
  {
    echo("<SCRIPT> \r\n");
	echo("function enter_". $this->orgdivid. "(elem,e) { clearTimeout(starttimeout". $this->orgdivid. "); if(slider". $this->orgdivid. " != null) clearInterval(slider". $this->orgdivid. "); elem.style.marginLeft=-1; }  ");
	echo("function leave_". $this->orgdivid. "(elem,e) {  elem.style.marginLeft=0; slider". $this->orgdivid. "=setInterval('slide". $this->orgdivid. "();',50); } ");
	echo(" function slide". $this->orgdivid. "() { elem=document.getElementById('". $this->orgdivid. "'); elem.style.marginLeft=parseInt(elem.style.marginLeft) - 10; if(parseInt(elem.style.marginLeft) < (8 - elem.offsetWidth)) { clearInterval(slider". $this->orgdivid. "); elem.style.marginLeft=4-elem.offsetWidth; } } "); 
	echo(" var starttimeout". $this->orgdivid. "=setTimeout(\"leave_". $this->orgdivid. "(document.getElementById('". $this->orgdivid. "',null))\",2000); ");
	echo(" var slider". $this->orgdivid. " = null; ");
    echo("</SCRIPT>");
	parent::pre_show();
  }
}
?>
