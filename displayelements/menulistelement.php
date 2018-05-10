<?
require_once("extendableelement.php");
require_once("menuelement.php");

class menulistelement extends extendableelement
{
  protected $submenustyle;
  protected $submenudiv;
  protected $itemstyle;
  protected $itemactivestyle;
  protected $paramname;
  public function __construct($divid = NULL, $style = NULL, $menuheading = "", $submenudiv = NULL, $submenustyle = NULL, $itemstyle = NULL, $itemactivestyle = NULL, $paramname = NULL)
  {
    parent::__construct($divid, $style, $menuheading);
	if(isset($submenustyle))
      $this->submenustyle = $submenustyle;
	if(isset($submenudiv))
      $this->submenudiv = $submenudiv;
	if(isset($itemstyle))
      $this->itemstyle = $itemstyle;
	if(isset($itemactivestyle))
      $this->itemactivestyle = $itemactivestyle;
	if(isset($paramname))
      $this->paramname = $paramname;
  }
  public function show_contents()
  {
    echo("<SCRIPT> function menuSwitch(newUrl) { setTimeout('document.location=\"'+newUrl+'\"',500); } </SCRIPT>");
    parent::show_contents();
  }
  public function set_menu_header($menuheader)
  {
    $this->set_contents($menuheader);
  }
  public function set_submenu_style($submenustyle)
  {
    $this->submenustyle = $submenustyle;
  }
  public function set_submenu_div_id($submenuddiv)
  {
    $this->submenuddiv = $submenuddiv;
  }
  public function set_item_style($itemstyle)
  {
    $this->itemstyle = $itemstyle;
  }
  public function set_item_active_style($itemactivestyle)
  {
    $this->itemactivestyle = $itemactivestyle;
  }
  public function set_paramanter_name($paramname)
  {
    $this->paramname = $paramname;
  }
  public function add_submenu($submenuname)
  {
    $this->add_element(new extendableelement($this->submenudiv,$this->submenustyle,$submenuname));
  }
  public function add_item($itemname, $itemvalue = NULL)
  {
    if(isset($itemvalue))
      $this->add_element(new menuelement(NULL, $this->itemstyle,$itemname,$itemvalue,$this->paramname,$this->itemactivestyle));
		else
      $this->add_element(new menuelement(NULL, $this->itemstyle,$itemname,$itemname,$this->paramname,$this->itemactivestyle));
  }
}
?>
