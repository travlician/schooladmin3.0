<?
require_once("displayelement.php");

class menuelement extends displayelement
{
  protected $menutext;
  protected $activestyle;
  protected $paramname;
  protected $menuvalue;
  public function __construct($divid = NULL, $style = NULL, $menutext = "", $menuvalue = NULL, $paramname = "menuitem", $activestyle = NULL)
  {
    $this->menutext = $menutext;
    $this->paramname = $paramname;
	if(isset($menuvalue))
	  $this->menuvalue = $menuvalue;
	else
	  $this->menuvalue = $menutext;
	if(isset($activestyle))
      $this->activestyle = $activestyle;
	parent::__construct($divid, $style);
  }
  protected function show_contents()
  {
    //echo("<a href=\"". $_SERVER['PHP_SELF']. "?". $this->paramname. "=". $this->menuvalue. "\">". $this->menutext. "</a>");
    //echo("<a href='javascript:setTimeout(window.location.href=\"". $_SERVER['PHP_SELF']. "?". $this->paramname. "=". $this->menuvalue. "\",5000);'>". $this->menutext. "</a>");
    echo("<a href='javascript:menuSwitch(\"". $_SERVER['PHP_SELF']. "?". $this->paramname. "=". $this->menuvalue. "\")'>". $this->menutext. "</a>");
	
  }
  protected function add_contents()
  { // change style if this item was selected
    if(isset($_GET[$this->paramname]) && $_GET[$this->paramname] == $this->menuvalue && isset($this->activestyle))
	  $this->set_style($this->activestyle);
  }
  public function set_menu_text($menutext)
  {
    $this->menutext = $menutext;
  }
  public function set_paramenter_name($paramname)
  {
    $this->paramname = $paramname;
  }
  public function set_active_style($activestyle)
  {
    $this->activestyle = $activestyle;
  }
  public function set_menu_value($menuvalue)
  {
    $this->menuvalue = $menuvalue;
  }
}
?>
