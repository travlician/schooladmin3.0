<?
  require_once("inputlib/inputclasses.php");
  require_once("schooladmingradecalc.php");
  class SCTtest
  {
    protected $sctid;
    public function __construct($sctid = NULL)
    {
      if(isset($sctid))
				$this->sctid = $sctid;
			else
				$this->sctid = 0;
    }
    public static function listObjects($filter = NULL)
		{
			global $SCTprefix,$yearpositioningroup;
			if(isset($filter))
			{
				$filter = "%". str_replace("{sy}", substr($_SESSION['CurrentGroup'], (isset($yearpositioningroup) ? $yearpositioningroup - 1 : 0), 1), $filter). "%";
			}
			else
				$filter="%";
			$olqr = inputclassbase::load_query("SELECT sctid FROM ". $SCTprefix. "SCTtest WHERE description LIKE '". $filter. "' ORDER BY description");
			if(isset($olqr['sctid']))
			{
				foreach($olqr['sctid'] AS $sctid)
				{
					$retlst[$sctid] = new SCTtest($sctid);
				}
				return($retlst);
			}
			else // No items found, return NULL
				return NULL;
		}
    public function get_id()
    {
      return $this->sctid;
    }

    public function get_description()
    {
      if($this->sctid == 0)
	    return NULL;
			$this->get_descriptionfld();
			return($this->descriptionfld->__toString());
    }
  
    private function get_descriptionfld()
    {
			global $SCTprefix;
      if(!isset($this->descriptionfld))
			{
				$this->descriptionfld = new inputclass_textfield("sctdesc". $this->sctid,40,NULL,"description",$SCTprefix. "SCTtest",$this->sctid,"sctid");
			}
		}

    public function edit_description()
    {
      $this->get_descriptionfld();
			$this->descriptionfld->echo_html();
    }

    public function get_style()
    {
      if($this->sctid == 0)
	    return NULL;
			$this->get_stylefld();
			return($this->stylefld->__toString());
    }
  
    private function get_stylefld()
    {
			global $SCTprefix;
      if(!isset($this->stylefld))
			{
				$this->stylefld = new inputclass_textfield("sctstyle". $this->sctid,40,NULL,"testcolor",$SCTprefix. "SCTtest",$this->sctid,"sctid");
			}
		}
		
		public function set_style($stylecode)
		{
			mysql_query("UPDATE ". $SCTprefix. "SCTtest SET testcolor='". $stylecode. "' WHERE sctid=". $this->sctid);
		}

    public function get_subject()
    {
      if($this->sctid == 0)
	    return NULL;
			$this->get_subjectfld();
			return($this->subjectfld->__toString());
    }
  
    private function get_subjectfld()
    {
			global $SCTprefix;
      if(!isset($this->subjectfld))
			{
				$this->subjectfld = new inputclass_listfield("sctsub". $this->sctid,"SELECT '' AS id, '' AS tekst UNION SELECT mid AS id, fullname AS tekst FROM subject",NULL,"mid","". $SCTprefix. "SCTtest",$this->sctid,"sctid");
			}
    }

    public function edit_subject()
    {
      $this->get_subjectfld();
			$this->subjectfld->echo_html();
    }
	
    public function get_testcount()
    {
      if($this->sctid == 0)
				return NULL;
			$this->get_testcountfld();
			return($this->testcountfld->__toString());
    }
  
    private function get_testcountfld()
    {
			global $SCTprefix;
      if(!isset($this->testcountfld))
			{
				$this->testcountfld = new inputclass_integer("sctcount". $this->sctid,2,NULL,"testcount","". $SCTprefix. "SCTtest",$this->sctid,"sctid");
			}
    }

    public function edit_testcount()
    {
      $this->get_testcountfld();
			$this->testcountfld->echo_html();
    }
	
    public function get_type()
    {
      if($this->sctid == 0)
				return NULL;
			$this->get_typefld();
			return($this->typefld->__toString());
    }
  
    private function get_typefld()
    {
			global $SCTprefix;
      if(!isset($this->typefld))
			{
				$this->typefld = new inputclass_listfield("scttype". $this->sctid,"SELECT '' AS id, '' AS tekst UNION SELECT 'p', 'Punten' UNION SELECT 'e','Fouten'",NULL,"pointsorerrors","". $SCTprefix. "SCTtest",$this->sctid,"sctid");
			}
    }

    public function edit_type()
    {
      $this->get_typefld();
			$this->typefld->echo_html();
    }

    public function get_catweight($mid=0)
    {
			global $SCTprefix;
      if($this->sctid == 0)
				return NULL;
			//$this->get_catweightfld();
			//return($this->catweightfld->__toString());
			$wqr = inputclassbase::load_query("SELECT ignorecatweights FROM ". $SCTprefix. "SCTtestsub WHERE sctid=". $this->sctid. " AND mid=". $mid. " AND ignorecatweights IS NOT NULL UNION SELECT ignorecatweights FROM ". $SCTprefix. "SCTtest WHERE sctid=". $this->sctid);
			if(isset($wqr['ignorecatweights'][0]))
				return $wqr['ignorecatweights'][0];
			else
				return NULL;
    }
  
    private function get_catweightfld($mid = 0)
    {
			global $SCTprefix;
			$this->catweightfld = new inputclass_checkbox("sctcatweight". $this->sctid. ($mid>0 ? "m".$mid : ""),$this->get_catweight($mid),NULL,"ignorecatweights","". $SCTprefix. "SCTtest". ($mid>0 ? "sub" : ""),$this->sctid,"sctid");
			if($mid > 0)
				$this->catweightfld->set_extrakey('mid',$mid);
    }

    public function edit_catweight($mid=0)
    {
      $this->get_catweightfld($mid);
			$this->catweightfld->echo_html();
    }

    public function get_conversiontype($mid=0)
    {
			global $SCTprefix;
      if($this->sctid == 0)
				return NULL;
			//$this->get_conversiontypefld();
			//return($this->conversiontypefld->__toString());
			$wqr = inputclassbase::load_query("SELECT conversiontype FROM ". $SCTprefix. "SCTtestsub WHERE sctid=". $this->sctid. " AND mid=". $mid. " AND conversiontype IS NOT NULL UNION SELECT conversiontype FROM ".$SCTprefix. "SCTtest WHERE sctid=". $this->sctid);
			if(isset($wqr['conversiontype'][0]))
				return $wqr['conversiontype'][0];
			else
				return NULL;
    }
  
    private function get_conversiontypefld($mid=0)
    {
			global $SCTprefix;
			$this->conversiontypefld = new inputclass_listfield("sctconversiontype". $this->sctid. ($mid>0 ? "m".$mid : ""),"SELECT '' AS id,'' AS tekst UNION SELECT '*K5.5','*K5.5' UNION SELECT '*K6.0','*K6.0' UNION SELECT DISTINCT conversiontype, conversiontype FROM SCTconversion",NULL,"conversiontype","". $SCTprefix. "SCTtest". ($mid>0 ? "sub" : ""),$this->sctid,"sctid");
			if($mid > 0)
				$this->conversiontypefld->set_extrakey('mid',$mid);
    }

    public function edit_conversiontype($mid=0)
    {
      $this->get_conversiontypefld($mid);
			$this->conversiontypefld->echo_html();
    }

    public function get_item($itemname,$mid=0)
    {
			global $SCTprefix;
      if($this->sctid == 0)
				return NULL;
			$wqr = inputclassbase::load_query("SELECT ". $itemname. " FROM ". $SCTprefix. "SCTtestsub WHERE sctid=". $this->sctid. " AND mid=". $mid. " AND ". $itemname. " IS NOT NULL UNION SELECT ". $itemname. " FROM ".$SCTprefix. "SCTtest WHERE sctid=". $this->sctid);
			if(isset($wqr[$itemname][0]))
				return $wqr[$itemname][0];
			else
				return NULL;
    }
  
    private function get_itemfld($itemname,$mid=0)
    {
			global $SCTprefix;
			$this->itemfld = new inputclass_amount($itemname. $this->sctid. ($mid>0 ? "m".$mid : ""),4,NULL,$itemname,"". $SCTprefix. "SCTtest". ($mid>0 ? "sub" : ""),$this->sctid,"sctid");
			if($mid>0)
				$this->itemfld->set_extrakey('mid',$mid);
    }

    public function edit_item($itemname,$mid=0)
    {
      $this->get_itemfld($itemname,$mid);
			$this->itemfld->echo_html();
    }	
	
		public function get_testitems()
		{
			if($this->get_testcount() > 0)
			{
				for($ti = 1; $ti <= $this->get_testcount(); $ti++)
				{
					$rettis[$ti] = new SCTtestitem($this->sctid, $ti);
				}
				return($rettis);
			}
			else
				return(NULL);
		}
		
		public function get_subjectoptions()
		{ // Return an array with [mid]=subjectname, if the subject is a meta subject return all sub subjects, else return the subject only.
			global $SCTprefix;
			$mymidq = inputclassbase::load_query("SELECT mid FROM ". $SCTprefix. "SCTtest WHERE sctid=". $this->sctid);
			$mymid = $mymidq['mid'][0];
			if(!isset($mymid))
				return NULL;
			$sublstqr = inputclassbase::load_query("SELECT mid,fullname FROM subject WHERE mid=". $mymid. " AND type <> 'meta' UNION SELECT mid,fullname FROM subject WHERE meta_subject=". $mymid);
			if(isset($sublstqr['mid']))
			{
				foreach($sublstqr['mid'] AS $sbix => $mid)
					$sblst[$mid] = $sublstqr['fullname'][$sbix];
				return($sblst);
			}
			else
				return NULL;
		}
		
		public function get_subjectsused()
		{
			global $SCTprefix;
			$suboptions = $this->get_subjectoptions();
			if(count($suboptions) > 1)
			{ // Multiple subjects possible, query to get only those used in ". $SCTprefix. "SCTtestitem entries
				$subsusedqr = inputclassbase::load_query("SELECT mid,fullname FROM ". $SCTprefix. "SCTtestitem LEFT JOIN subject USING(mid) WHERE sctid=". $this->sctid);
				if(isset($subsusedqr['mid']))
				{
					unset($suboptions);
					foreach($subsusedqr['mid'] AS $sbix => $amid)
						$suboptions[$amid] = $subsusedqr['fullname'][$sbix];
				}
				else
					return NULL;
			}
			return $suboptions; // Return NULL or the main subject (is not a meta subject)
		}
		
		public function get_phase_other($sid,$phase,$year)
		{
			$resqry = "SELECT SUM(IF(seqno=0,result,0)) AS tp, SUM(IF(seqno=0,0,result)) AS rp FROM SCTresult WHERE sctid=". $this->get_id(). " AND phase='". $phase. "' AND year='". $year. "' AND sid=". $sid. " GROUP BY sid";
			$resqr = inputclassbase::load_query($resqry);
			if($resqr['tp'][0] != "")
			{
				if($resqr['rp'][0] == "")
					return($resqr['tp'][0]);
				else
				{
					if($resqr['tp'][0] - $resqr['rp'][0] >= 0)
						return("<span style='". ($resqr['tp'][0] - $resqr['rp'][0] != 0 ? "font-weight: bold; " : ""). "'>". ($resqr['tp'][0] - $resqr['rp'][0]). "</span>");
					else
						return("<span style='". ($resqr['tp'][0] - $resqr['rp'][0] != 0 ? "font-weight: bold; padding: 3px 5px 4px; " : ""). "background-color:red'>". ($resqr['tp'][0] - $resqr['rp'][0]). "</span>");
				}
			}
			else
				return("-");
		}
		public function get_phase_remediate($sid,$phase,$year)
		{
			global $SCTprefix;
			$resqry = "SELECT GROUP_CONCAT(DISTINCT `category`) AS rem FROM SCTresult LEFT JOIN ". $SCTprefix. "SCTtestitem USING(sctid,seqno) WHERE sctid=". $this->get_id(). " AND phase='". $phase. "' AND year='". $year. "' AND sid=". $sid. " AND result ". ($this->get_type() == "Fouten" ? ">" : "<"). " treshold GROUP BY sid";
			$resqr = inputclassbase::load_query($resqry);
			if(isset($resqr['rem']))
				return("<SPAN class=res6>". $resqr['rem'][0]. "</span>");
			else
				return("");
		}
		public function get_phase_result($sid,$phase,$year)
		{
			if($this->get_catweight() && $phase != "t")
			{ // Get the average result for each subject in this phase weight by weigth factor for the phase and subject
				if(substr($this->get_conversiontype(),0,1)=="*")
				{
					$res = $this->get_phase_result_catweight($sid,$phase,$year);
					if(!isset($res))
						return("-");									
				}
				else
				{
					$totres = 0.0;
					$totw = 0.0;
					$sublist = $this->get_subjectsused();
					foreach($sublist AS $amid => $subtxt)
					{
						if($amid != "")
						{
							$r = $this->get_phase_result_subject($sid,$phase,$year,$amid);
							if(isset($r))
							{
								$weight = $this->get_item($phase=='c' ? 'controlweight' : ($phase=='s' ? 'signalweight' : 'terminalweight'),$amid);
								$totres += $weight * $r;
								$totw += $weight;
							}
						}
					}
					if($totw > 0.1)
					{
						$converter = new SCTconversion($this->get_conversiontype());
						return($converter->convert_result(round($totres / $totw),$this->get_item($phase=='c' ? 'controlborder' : ($phase=='s' ? 'signalborder' : 'terminalborder')),$this->get_item($phase=='c' ? 'controlmin' : ($phase=='s' ? 'signalmin' : 'terminalmin'))));
					}
					else
						return("-");
				}
			}
			else if($this->get_type() == "Punten")
			{
				$res = $this->get_phase_result_points($sid,$phase,$year);
				if(!isset($res))
					return("-");
			}
			else
			{
				$res = $this->get_phase_result_errors($sid,$phase,$year);
				if(!isset($res))
					return("-");
			}
			$res = round($res,0);
			return("<span class=res". $res. ">". $res. "</span>");
		}
		
		protected function get_phase_result_subject($sid,$phase,$year,$mid)
		{
			$totres = 0.0;
			$totcnt = 0.0;
			$tstobjs = $this->get_testitems();
			$catw = $this->get_catweight($mid);
			foreach($tstobjs AS $tstobj)
			{
				if($tstobj->get_subject_mid() != "")
				{
					$tres = $tstobj->get_result_percentage($sid,$phase,$year);
					if(isset($tres))
					{
						if($catw)
						{ // All testresult equal weight
							$totres += $tres;
							$totcnt += 1.0;
						}
						else
						{ // maxppoits is weight factor
							$w = $tstobj->get_maxpoints();
							$totres += $tres * $w;
							$totcnt += $w;
						}
					}
				}
			}
			if($totcnt > 0.5)
				return round($totres / $totcnt,1);
			else
				return null;
			
		}
		public function get_subject_result($sid,$mid,$year,$writeplt=false)
		{
			//echo("Call to get_subject_result()");
			// Get raw result and format.
			$rawres = $this->get_subject_result_raw($sid,$mid,$year);
			if(!isset($rawres))
				return("-");
			else
			{
				if($this->get_catweight() && substr($this->get_conversiontype($mid),0,1) != "*")
				{ // We need a different approach, convert the percentage to a result
					$converter = new SCTconversion($this->get_conversiontype($mid));
					$percres = $rawres;
					$rawres = $converter->convert_result($rawres,0,0,true);					
				}
				$resi = round($rawres,0);
				if($resi != "")
					$resd = number_format($rawres,1,",",".");
				else
					$resd = "";
				// This is where we need to store the result in result tables! 
				// First find the tdid for this
				if($writeplt && $mid != "")
				{
					$mytdidqry = "SELECT tdid FROM SCTtestref LEFT JOIN `class` USING(cid) LEFT JOIN sgrouplink USING(gid) WHERE sctid=". $this->get_id(). " AND sid=". $sid. " AND mid=". $mid;
					$mytdidqr = inputclassbase::load_query($mytdidqry);
					if(isset($mytdidqr['tdid'][0]))
					{
						$tdid = $mytdidqr['tdid'][0];
						// Now find all tests that refer to this tdid
						$tlqr = inputclassbase::load_query("SELECT DISTINCT sctid FROM SCTtestref WHERE tdid=". $tdid);
						$totw = 0.0;
						$totr = 0.0;
						foreach($tlqr['sctid'] AS $asctid)
						{
							if($asctid == $this->get_id())
							{
								$totw += $this->get_total_weight();
								$totr += round($rawres,1) * $this->get_total_weight();
							}
							else
							{
								$etobj = new SCTtest($asctid);
								$eraw = $etobj->get_subject_result_raw($sid,$mid,$year);
								if(isset($eraw))
								{
									$totw += $etobj->get_total_weight();
									$totr += round($eraw,1) * $etobj->get_total_weight();
								}
							}
						}
						$pltres = round($totr / $totw,1);
						mysql_query("REPLACE INTO testresult (tdid,sid,result,tid) VALUES(". $tdid. ",". $sid. ",". $pltres. ",". $_SESSION['uid']. ")");
						//echo("Should have written a test result!");
						echo(mysql_error());
						// Now we should recalc but for what cid and period? Do query based on tdid
						$cidperqr = inputclassbase::load_query("SELECT cid,period FROM testdef WHERE tdid=". $tdid);
						SA_calcGrades($sid,$cidperqr['cid'][0],$cidperqr['period'][0]);
					}
				}
				return("<span class=res". $resi. ">". $resd. (substr($this->get_conversiontype($mid),0,1) != "*" ? (" <font size=-4>(". round($percres). "%)</font>") : ""). "</span>");
			}

		}
		protected function get_subject_result_raw($sid,$mid,$year)
		{
			// Get the result using weight factors defined for this test, ommiting empty results.
			$totwres = 0.0;
			$totw = 0.0;
			if($this->get_catweight())
			{
				$ress = $this->get_phase_result_catweight_subject($sid,"s",$year,$mid);
				$resc = $this->get_phase_result_catweight_subject($sid,"c",$year,$mid);
				if($this->get_type() == "Punten")
					$rest = $this->get_phase_result_points_subject($sid,"t",$year,$mid);
				else
					$rest = $this->get_phase_result_errors_subject($sid,"t",$year,$mid);					
			}
			else if($this->get_type() == "Punten")
			{
				$ress = $this->get_phase_result_points_subject($sid,"s",$year,$mid);
				$resc = $this->get_phase_result_points_subject($sid,"c",$year,$mid);
				$rest = $this->get_phase_result_points_subject($sid,"t",$year,$mid);
			}
			else
			{
				$ress = $this->get_phase_result_errors_subject($sid,"s",$year,$mid);
				$resc = $this->get_phase_result_errors_subject($sid,"c",$year,$mid);
				$rest = $this->get_phase_result_errors_subject($sid,"t",$year,$mid);
			}
			if(isset($ress))
			{
				$totwres += 1.0 * $this->get_item("signalweight") * $ress;
				$totw += 1.0 * $this->get_item("signalweight");
			}
			if(isset($resc))
			{
				$totwres += 1.0 * $this->get_item("controlweight") * $resc;
				$totw += 1.0 * $this->get_item("controlweight");
			}
			if(isset($rest))
			{
				$totwres += 1.0 * $this->get_item("terminalweight") * $rest;
				$totw += 1.0 * $this->get_item("terminalweight");
			}
			if($totw == 0.0)
				return(NULL);
			else
				return($totwres/$totw);
		}
		public function get_result($sid,$year,$writeplt=false)
		{
			global $SCTprefix;
			// Get the raw result and format.
			$rawres = $this->get_result_raw($sid,$year);
			if(!isset($rawres))
				return("-");
			else
			{
				$resi = round($rawres,0);
				$resd = number_format($rawres,1,",",".");
				// This is where we need to store the result in result tables! 
				// First find the tdid for this
				$mytdidqry = "SELECT tdid FROM SCTtestref LEFT JOIN ". $SCTprefix. "SCTtest USING(sctid) LEFT JOIN `class` USING(cid) LEFT JOIN sgrouplink USING(gid) WHERE sctid=". $this->get_id(). " AND sid=". $sid. " AND class.mid=". $SCTprefix. "SCTtest.mid";
				$mytdidqr = inputclassbase::load_query($mytdidqry);
				if($writeplt)
				{
					if(isset($mytdidqr['tdid'][0]))
					{
						$tdid = $mytdidqr['tdid'][0];
						// Now find all tests that refer to this tdid
						$tlqr = inputclassbase::load_query("SELECT DISTINCT sctid FROM SCTtestref WHERE tdid=". $tdid);
						$totw = 0.0;
						$totr = 0.0;
						foreach($tlqr['sctid'] AS $asctid)
						{
							if($asctid == $this->get_id())
							{
								$totw += $this->get_total_weight();
								$totr += round($rawres,1) * $this->get_total_weight();
							}
							else
							{
								$etobj = new SCTtest($asctid);
								$eraw = $etobj->get_result_raw($sid,$year);
								if(isset($eraw))
								{
									$totw += $etobj->get_total_weight();
									$totr += round($eraw,1) * $etobj->get_total_weight();
								}
							}
						}
						$pltres = round($totr / $totw,1);
						mysql_query("REPLACE INTO testresult (tdid,sid,result,tid) VALUES(". $tdid. ",". $sid. ",". $pltres. ",". $_SESSION['uid']. ")");
						echo(mysql_error());
						// Now we should recalc but for what cid and period? Do query based on tdid
						$cidperqr = inputclassbase::load_query("SELECT cid,period FROM testdef WHERE tdid=". $tdid);
						SA_calcGrades($sid,$cidperqr['cid'][0],$cidperqr['period'][0]);
					}
				}
				if(isset($mytdidqr['tdid'][0]))
					return("<span class=res". $resi. ">". $resd. "</span>");
				else
				{
					// If there is only 1 sub subject used, this counts as the result for the complete test
					$subsused = $this->get_subjectsused();
					if(count($subsused) == 1)
					{
						foreach($subsused AS $amid => $asubj)
						{
							return($this->get_subject_result($sid,$amid,$year,$writeplt));
						}
					}
				}
			}
		}
		
		protected function get_result_raw($sid,$year)
		{
			// Get the result a weight factors defined for this test, ommiting empty results.
			$totwres = 0.0;
			$totw = 0.0;
			if($this->get_catweight())
			{
				$ress = $this->get_phase_result_catweight($sid,"s",$year);
				$resc = $this->get_phase_result_catweight($sid,"c",$year);
				if($this->get_type() == "Punten")
					$rest = $this->get_phase_result_points($sid,"t",$year);
				else
					$rest = $this->get_phase_result_errors($sid,"t",$year);
			}
			else if($this->get_type() == "Punten")
			{
				$ress = $this->get_phase_result_points($sid,"s",$year);
				$resc = $this->get_phase_result_points($sid,"c",$year);
				$rest = $this->get_phase_result_points($sid,"t",$year);
			}
			else
			{
				$ress = $this->get_phase_result_errors($sid,"s",$year);
				$resc = $this->get_phase_result_errors($sid,"c",$year);
				$rest = $this->get_phase_result_errors($sid,"t",$year);
			}
			if(isset($ress))
			{
				$totwres += 1.0 * $this->get_item("signalweight") * $ress;
				$totw += 1.0 * $this->get_item("signalweight");
			}
			if(isset($resc))
			{
				$totwres += 1.0 * $this->get_item("controlweight") * $resc;
				$totw += 1.0 * $this->get_item("controlweight");
			}
			if(isset($rest))
			{
				$totwres += 1.0 * $this->get_item("terminalweight") * $rest;
				$totw += 1.0 * $this->get_item("terminalweight");
			}
			if($totw == 0.0)
				return(NULL);
			else
				return($totwres / $totw);
		}
		protected function get_phase_result_catweight($sid,$phase,$year)
		{
			$totres = 0.0;
			$totcnt = 0.0;
			$tstobjs = $this->get_testitems();
			foreach($tstobjs AS $tstobj)
			{
				if($tstobj->get_subject_mid() != "")
				{
					$tres = $tstobj->get_result($sid,$phase,$year);
					if(isset($tres))
					{
						$totres += $tres;
						$totcnt += 1.0;
					}
				}
			}
			if($totcnt > 0.5)
				return round($totres / $totcnt,1);
			else
				return null;
		}
		protected function get_phase_result_points($sid,$phase,$year)
		{
			global $SCTprefix;
			// First get phase result and return NULL if not defined.
			$sptsqr = inputclassbase::load_query("SELECT result FROM SCTresult WHERE sid=". $sid. " AND phase='". $phase. "' AND year='". $year. "' AND seqno=0 AND sctid=". $this->get_id());
			if(!isset($sptsqr['result'][0]))
				return NULL;
			$spts = $sptsqr['result'][0];
			// Get the maximum number of points
			$mptqr = inputclassbase::load_query("SELECT SUM(maxpoints) AS `mpt` FROM ". $SCTprefix. "SCTtestitem WHERE sctid=". $this->get_id());
			$mpt = $mptqr['mpt'][0];
			// Border (knikpunt 5,5) depends on phase, so get it (it's specified as percentage so divide by 100)
			$border = $this->get_item($phase=="s" ? "signalborder" : ($phase=="c" ? "controlborder" : "terminalborder"));
			$minpts = $this->get_item($phase=="s" ? "signalmin" : ($phase=="c" ? "controlmin" : "terminalmin"));
			$tpoint = (1.0 * $mpt * $border) / 100.0;
			if($spts < $tpoint)
				return($minpts + ((5.5 - $minpts) * $spts / $tpoint));
			else
				return(5.5 + ((4.5 / ($mpt - $tpoint)) * ($spts - $tpoint)));
		}
		protected function get_phase_result_catweight_subject($sid,$phase,$year,$mid)
		{
			if($mid != "")
			{
				$totres = 0.0;
				$totcnt = 0.0;
				$tstobjs = $this->get_testitems();
				foreach($tstobjs AS $tstobj)
				{
					if(substr($this->get_conversiontype($mid),0,1) != "*")
					{
						$tres = $tstobj->get_result_percentage($sid,$phase,$year);
						if(isset($tres) && $tstobj->get_subject_mid() == $mid)
						{
							$totres += $tres;
							$totcnt += 1.0;
						}
					}
					else
					{
						$tres = $tstobj->get_result($sid,$phase,$year);
						if(isset($tres) && $tstobj->get_subject_mid() == $mid)
						{
							$totres += $tres;
							$totcnt += 1.0;
						}						
					}
				}
				if($totcnt > 0.5)
					return round($totres / $totcnt,1);
				else
					return null;
			}
			else
				return null;
		}
		protected function get_phase_result_points_subject($sid,$phase,$year,$mid)
		{
			global $SCTprefix;
			if($mid != "")
			{
				// First get phase result and return NULL if not defined.
				$sptsqr = inputclassbase::load_query("SELECT SUM(result) AS sresult FROM SCTresult LEFT JOIN ". $SCTprefix. "SCTtestitem USING(sctid,seqno) WHERE sid=". $sid. " AND phase='". $phase. "' AND year='". $year. "' AND sctid=". $this->get_id(). " AND mid=". $mid);
				if(!isset($sptsqr['sresult'][0]))
					return NULL;
				$spts = $sptsqr['sresult'][0];
				// Get the maximum number of points
				$mptqr = inputclassbase::load_query("SELECT SUM(maxpoints) AS `mpt` FROM ". $SCTprefix. "SCTtestitem WHERE sctid=". $this->get_id(). " AND mid=". $mid);
				$mpt = $mptqr['mpt'][0];
				// Border (knikpunt 5,5) depends on phase, so get it (it's specified as percentage so divide by 100)
				$border = $this->get_item($phase=="s" ? "signalborder" : ($phase=="c" ? "controlborder" : "terminalborder"));
				$minpts = $this->get_item($phase=="s" ? "signalmin" : ($phase=="c" ? "controlmin" : "terminalmin"));
				$tpoint = (1.0 * $mpt * $border) / 100.0;
				if($spts < $tpoint)
					return($minpts + ((5.5 - $minpts) * $spts / $tpoint));
				else
					return(5.5 + ((4.5 / ($mpt - $tpoint)) * ($spts - $tpoint)));
			}
			else
				return "";
		}
		protected function get_phase_result_errors($sid,$phase,$year)
		{
			global $SCTprefix;
			// First get phase result and return NULL if not defined.
			$sptsqr = inputclassbase::load_query("SELECT result FROM SCTresult WHERE sid=". $sid. " AND phase='". $phase. "' AND year='". $year. "' AND seqno=0 AND sctid=". $this->get_id());
			if(!isset($sptsqr['result'][0]))
				return NULL;
			$spts = $sptsqr['result'][0];
			// Get the maximum number of points
			$mptqr = inputclassbase::load_query("SELECT SUM(maxpoints) AS `mpt` FROM ". $SCTprefix. "SCTtestitem WHERE sctid=". $this->get_id());
			$mpt = $mptqr['mpt'][0];
			// Border (knikpunt 6) depends on phase, so get it (it's specified as percentage so divide by 100)
			$border = $this->get_item($phase=="s" ? "signalborder" : ($phase=="c" ? "controlborder" : "terminalborder"));
			$minpts = $this->get_item($phase=="s" ? "signalmin" : ($phase=="c" ? "controlmin" : "terminalmin"));
			$rc = 4.0 / ($mpt -  ($mpt * $border / 100.0));
			$res = 10.0 - ($rc * $spts);
			if($res < $minpts)
				return($minpts);
			else
				return($res);
		}
		protected function get_phase_result_errors_subject($sid,$phase,$year,$mid)
		{
			global $SCTprefix;
			// First get phase result and return NULL if not defined.
			if($mid != "")
				$sptsqr = inputclassbase::load_query("SELECT SUM(result) AS sresult FROM SCTresult LEFT JOIN ". $SCTprefix. "SCTtestitem USING(sctid,seqno) WHERE sid=". $sid. " AND phase='". $phase. "' AND year='". $year. "' AND sctid=". $this->get_id(). " AND mid=". $mid);
			if(!isset($sptsqr['sresult'][0]))
				return NULL;
			$spts = $sptsqr['sresult'][0];
			// Get the maximum number of errors
			$mptqr = inputclassbase::load_query("SELECT SUM(maxpoints) AS `mpt` FROM ". $SCTprefix. "SCTtestitem WHERE sctid=". $this->get_id(). " AND mid=". $mid);
			$mpt = $mptqr['mpt'][0];
			// Border (knikpunt 6) depends on phase, so get it (it's specified as percentage so divide by 100)
			$border = $this->get_item($phase=="s" ? "signalborder" : ($phase=="c" ? "controlborder" : "terminalborder"));
			$minpts = $this->get_item($phase=="s" ? "signalmin" : ($phase=="c" ? "controlmin" : "terminalmin"));
			$rc = 4.0 / ($mpt -  ($mpt * $border / 100.0));
			$res = 10.0 - ($rc * $spts);
			if($res < $minpts)
				return($minpts);
			else
				return($res);
		}
		protected function get_total_weight()
		{ // return the total weight of this test
			return($this->get_item("signalweight") + $this->get_item("controlweight") + $this->get_item("terminalweight"));
		}
  }
  class SCTtestitem
  {
    protected $sctid,$seqno;
    public function __construct($sctid,$seqno)
    {
			$this->sctid = $sctid;
			$this->seqno = $seqno;
    }
    public function get_sctid()
    {
      return $this->sctid;
    }
    public function get_seqno()
    {
      return $this->seqno;
    }

    public function get_description()
    {
			$this->get_descriptionfld();
			return($this->descriptionfld->__toString());
    }
  
    private function get_descriptionfld()
    {
			global $SCTprefix;
			if(!isset($this->descriptionfld))
			{
				$this->descriptionfld = new inputclass_textfield("sctidesc". $this->sctid. ":". $this->seqno,60,NULL,"description","". $SCTprefix. "SCTtestitem",$this->sctid,"sctid");
				$this->descriptionfld->set_extrakey("seqno",$this->seqno);
				// If no subject can be selected for this item because the subject for the test has no sub-subjects, we set it as extra field
				$parenttest = new SCTtest($this->sctid);
				$sboptionlist = $parenttest->get_subjectoptions();
				if(isset($sboptionlist) && count($sboptionlist) == 1)
				{
					foreach($sboptionlist AS $amid => $dummy)
						$this->descriptionfld->set_extrafield("mid",$amid);
				}
			}
    }

    public function edit_description()
    {
      $this->get_descriptionfld();
			$this->descriptionfld->echo_html();
    }

    public function get_abreviation()
    {
			$this->get_abreviationfld();
			return($this->abreviationfld->__toString());
    }
  
    private function get_abreviationfld()
    {
			global $SCTprefix;
			if(!isset($this->abreviationfld))
			{
				$this->abreviationfld = new inputclass_textfield("sctiabrev". $this->sctid. ":". $this->seqno,10,NULL,"abbreviation","". $SCTprefix. "SCTtestitem",$this->sctid,"sctid");
				$this->abreviationfld->set_extrakey("seqno",$this->seqno);
				// If no subject can be selected for this item because the subject for the test has no sub-subjects, we set it as extra field
				$parenttest = new SCTtest($this->sctid);
				$sboptionlist = $parenttest->get_subjectoptions();
				if(isset($sboptionlist) && count($sboptionlist) == 1)
				{
					foreach($sboptionlist AS $amid => $dummy)
						$this->abreviationfld->set_extrafield("mid",$amid);
				}
			}
    }

    public function edit_abreviation()
    {
      $this->get_abreviationfld();
			$this->abreviationfld->echo_html();
    }

    public function get_category()
    {
			$this->get_categoryfld();
			return($this->categoryfld->__toString());
    }
  
    private function get_categoryfld()
    {
			global $SCTprefix;
      if(!isset($this->categoryfld))
			{
				$this->categoryfld = new inputclass_textfield("scticat". $this->sctid. ":". $this->seqno,10,NULL,"category","". $SCTprefix. "SCTtestitem",$this->sctid,"sctid");
				$this->categoryfld->set_extrakey("seqno",$this->seqno);
			}
    }

    public function edit_category()
    {
      $this->get_categoryfld();
			$this->categoryfld->echo_html();
    }

    public function get_subject()
    {
			$this->get_subjectfld();
			return($this->subjectfld->__toString());
    }
  
    private function get_subjectfld()
    {
			global $SCTprefix;
      if(!isset($this->subjectfld))
			{
				// Must get a list of possible options
				$parenttest = new SCTtest($this->sctid);
				$sboptionlist = $parenttest->get_subjectoptions();
				if(isset($sboptionlist) && count($sboptionlist) != 1)
				{
					$sbqry = "SELECT '' AS id, '' AS tekst";
					if(isset($sboptionlist))
					{
						foreach($sboptionlist AS $amid => $sbname)
						{
							$sbqry .= " UNION SELECT ". $amid. ",'". $sbname. "'";
						}
					}
				}
				else
				{
					foreach($sboptionlist AS $amid => $sbname)
					{
						$sbqry = "SELECT ". $amid. " AS id,'". $sbname. "' AS tekst";
					}
				}
				$this->subjectfld = new inputclass_listfield("sctisub". $this->sctid. ":". $this->seqno,$sbqry,NULL,"mid","". $SCTprefix. "SCTtestitem",$this->sctid,"sctid");
				$this->subjectfld->set_extrakey("seqno",$this->seqno);
			}
    }

    public function edit_subject()
    {
      $this->get_subjectfld();
			$this->subjectfld->echo_html();
    }
		
		public function get_subject_mid()
		{
      $this->get_subjectfld();
			return($this->subjectfld->get_state());			
		}
	
    public function get_maxpoints()
    {
			$this->get_maxpointsfld();
			return($this->maxpointsfld->__toString());
    }
  
    private function get_maxpointsfld()
    {
			global $SCTprefix;
      if(!isset($this->maxpointsfld))
			{
				$this->maxpointsfld = new inputclass_integer("sctimxp". $this->sctid. ":". $this->seqno,4,NULL,"maxpoints","". $SCTprefix. "SCTtestitem",$this->sctid,"sctid");
				$this->maxpointsfld->set_extrakey("seqno",$this->seqno);
			}
    }

    public function edit_maxpoints()
    {
      $this->get_maxpointsfld();
			$this->maxpointsfld->echo_html();
    }

    public function get_treshold()
    {
			$this->get_tresholdfld();
			return($this->tresholdfld->__toString());
    }
  
    private function get_tresholdfld()
    {
			global $SCTprefix;
      if(!isset($this->tresholdfld))
			{
				$this->tresholdfld = new inputclass_integer("sctitrh". $this->sctid. ":". $this->seqno,4,NULL,"treshold","". $SCTprefix. "SCTtestitem",$this->sctid,"sctid");
				$this->tresholdfld->set_extrakey("seqno",$this->seqno);
			}
    }

    public function edit_treshold()
    {
      $this->get_tresholdfld();
			$this->tresholdfld->echo_html();
    }
		
    public function get_itreshold()
    {
			$this->get_itresholdfld();
			return($this->itresholdfld->__toString());
    }
  
    private function get_itresholdfld()
    {
			global $SCTprefix;
      if(!isset($this->itresholdfld))
			{
				$this->itresholdfld = new inputclass_integer("sctiitrh". $this->sctid. ":". $this->seqno,4,NULL,"itreshold","". $SCTprefix. "SCTtestitem",$this->sctid,"sctid");
				$this->itresholdfld->set_extrakey("seqno",$this->seqno);
			}
    }

    public function edit_itreshold()
    {
      $this->get_itresholdfld();
			$this->itresholdfld->echo_html();
    }
		
		public function get_result($sid,$phase,$year)
		{ // Get this testitem result (only used when category have equal weights on parent test object)
			// First get the result
			$itemresqr = inputclassbase::load_query("SELECT result FROM SCTresult WHERE sid=". $sid. " AND sctid=". $this->sctid. " AND seqno=". $this->seqno. " AND year='". $year. "' AND phase='". $phase. "'");
			if(isset($itemresqr['result'][0]))
			{
				$tstobj = new SCTtest($this->sctid);
				$spts = $itemresqr['result'][0];
				if($tstobj->get_type() == "Punten")
				{
					// Get the maximum number of points
					$mpt = $this->get_maxpoints();
					if($mpt == 0)
						return null;
					// Border (knikpunt 5,5) depends on phase, so get it (it's specified as percentage so divide by 100)
					$border = $tstobj->get_item($phase=="s" ? "signalborder" : ($phase=="c" ? "controlborder" : "terminalborder"));
					$minpts = $tstobj->get_item($phase=="s" ? "signalmin" : ($phase=="c" ? "controlmin" : "terminalmin"));
					$tpoint = (1.0 * $mpt * $border) / 100.0;
					if($spts < $tpoint)
						return($minpts + ((5.5 - $minpts) * $spts / $tpoint));
					else
						return(5.5 + ((4.5 / ($mpt - $tpoint)) * ($spts - $tpoint)));
				}
				else
				{
					$mpt = $this->get_maxpoints();
					// Border (knikpunt 6) depends on phase, so get it (it's specified as percentage so divide by 100)
					$border = $tstobj->get_item($phase=="s" ? "signalborder" : ($phase=="c" ? "controlborder" : "terminalborder"));
					$minpts = $tstobj->get_item($phase=="s" ? "signalmin" : ($phase=="c" ? "controlmin" : "terminalmin"));
					$rc = 4.0 / ($mpt -  ($mpt * $border / 100.0));
					$res = 10.0 - ($rc * $spts);
					if($res < $minpts)
						return($minpts);
					else
						return($res);					
				}
			}
			else
				return null;
		}
		public function get_result_rnd($sid,$phase,$year)
		{
			$parent = new SCTtest($this->sctid);
			$mid = $this->get_subject_mid();
			if($mid == "" || substr($parent->get_conversiontype($mid),0,1) != "*")
			{ // With subject, conversion based on percentage or diagnostic so show percentage or result in color based on tresholds
				$itemresqr = inputclassbase::load_query("SELECT result FROM SCTresult WHERE sid=". $sid. " AND sctid=". $this->sctid. " AND seqno=". $this->seqno. " AND year='". $year. "' AND phase='". $phase. "'");
				if(isset($itemresqr['result'][0]))
				{
					$res = $itemresqr['result'][0];
					if($res >= $this->get_treshold() && $res >= $this->get_itreshold())
						$color=($parent->get_type() == "Punten" ? "GREEN" : "RED");
					else if ($res >= $this->get_treshold() || ($res >= $this->get_itreshold() && $this->get_itreshold() != ""))
						$color="ORANGE";
					else
						$color=($parent->get_type() == "Punten" ? "RED" : "GREEN");
					if($mid == "") // No conversion to percantage required
						return("<FONT color=". $color. ">". $res. "</font>");					
					else
					{
						// Now convert the result to a percentage
						$maxp = $this->get_maxpoints();
						if($parent->get_type() == "Punten")
						{
							$resper = round(100.0 * $res / $maxp);
						}
						else
						{
							$resper = round(100.0 - (100.0 * $res / $maxp));
						}
						return("<FONT color=". $color. ">". $resper. "%</font>");
					}
				}
				else
					return("");				
			}
			else
			{
				$res = $this->get_result($sid,$phase,$year);
				if(isset($res))
					return(round($res,1));
				else
					return null;
			}
		}
		public function get_result_percentage($sid,$phase,$year)
		{
			$parent = new SCTtest($this->sctid);
			$itemresqr = inputclassbase::load_query("SELECT result FROM SCTresult WHERE sid=". $sid. " AND sctid=". $this->sctid. " AND seqno=". $this->seqno. " AND year='". $year. "' AND phase='". $phase. "'");
			if(isset($itemresqr['result'][0]))
			{
				$res = $itemresqr['result'][0];
				$maxp = $this->get_maxpoints();
				if($maxp == 0)
					$maxp = 1000000000000.0;
				if($parent->get_type() == "Punten")
				{
					$resper = round(100.0 * $res / $maxp);
				}
				else
				{
					$resper = round(100.0 - (100.0 * $res / $maxp));
				}
				return($resper);
			}
			else
				return null;
		}
  }
	
	class SCTconversion
	{
		protected $convname;
		public function __construct($convname)
    {
			global $SCTprefix;
			$this->convname = $convname;
			if($convname=="new")
				mysql_query("INSERT INTO ". $SCTprefix. "SCTconversion (conversiontype,trippoint,result) VALUES('new',0,0)");
    }
		
		public function edit()
		{
			global $SCTprefix;
			echo("Conversietype: ");
			$this->edit_name();
			echo("<table><tr><th>Percentage</th><th>Resultaat</th></tr>");
			$tps = $this->get_trippoints();
			foreach($tps AS $tpid)
			{
				echo("<tr><td>");
				$tpfld = new inputclass_amount("SCTtp". $tpid,4,NULL,"trippoint","". $SCTprefix. "SCTconversion",$tpid,"convid");
				$tpfld->echo_html();
				echo("</td><td>");
				$tpfldr = new inputclass_amount("SCTtpr". $tpid,4,NULL,"result","". $SCTprefix. "SCTconversion",$tpid,"convid");
				$tpfldr->echo_html();
				echo("</td></tr>");
			}
			// Add additional empty records
			for($negix = -1; $negix > -10; $negix--)
			{
				echo("<tr><td>");
				$tpfld = new inputclass_amount("SCTtp". $negix,4,NULL,"trippoint","". $SCTprefix. "SCTconversion",$negix,"convid");
				$tpfld->set_extrafield("conversiontype",$this->convname);
				$tpfld->set_initial_value("");
				$tpfld->echo_html();
				echo("</td><td>");
				$tpfldr = new inputclass_amount("SCTtpr". $negix,4,NULL,"result","". $SCTprefix. "SCTconversion",$negix,"convid");
				$tpfldr->set_extrafield("conversiontype",$this->convname);
				$tpfldr->set_initial_value("");
				$tpfldr->echo_html();
				echo("</td></tr>");				
			}
			echo("</table>");
		}
		
		public function edit_name()
		{
			global $SCTprefix;
			$namefld = new inputclass_textfield("sctcname",40,NULL,"conversiontype",$SCTprefix. "SCTconversion","'". $this->convname. "'","conversiontype");
			$namefld->set_initial_value($this->convname);
			$namefld->echo_html();
		}
		
		protected function get_trippoints()
		{
			global $SCTprefix;
			$tpqr = inputclassbase::load_query("SELECT convid FROM ". $SCTprefix. "SCTconversion WHERE conversiontype='". $this->convname. "' ORDER BY trippoint DESC");
			foreach($tpqr['convid'] AS $tpix => $tpid)
				$retval[$tpix] = $tpid;
			return $retval;
		}
		
		public function convert_result($perc,$border,$min,$unformatted=false)
		{
			global $SCTprefix;
			//return $perc. "%";
			if($this->convname == "*K5.5")
			{
				if($perc >= $border)
					$r = 5.5 + 4.5 * (($perc-$border)/(100.0 - $border));
				else
					$r= $min + ((5.5 - $min) * ($perc / $border));
				if($unformatted)
					return($r);
				else
				return("<span class=res". round($r). ">". number_format($r,1,',','.'). "</span>");									
			}
			else if($this->convname == "*K6.0")
			{
				if($perc >= $border)
					$r = 6.0 + 4.0 * (($perc-$border)/(100.0 - $border));
				else
					$r= $min + ((6.0 - $min) * ($perc / $border));
				if($unformatted)
					return($r);
				else
					return("<span class=res". round($r). ">". number_format($r,1,',','.'). "</span>");									
			}
			else
			{ // percentage to result conversiontype
				$resq = inputclassbase::load_query("SELECT result FROM ". $SCTprefix. "SCTconversion WHERE conversiontype='". $this->convname. "' AND trippoint <= ". $perc. " ORDER BY trippoint DESC");
				if(isset($resq['result'][0]))
				{
					$r = $resq['result'][0];
					if($r < $min)
						$r=$min;
				if($unformatted)
					return($r);
				else
					return("<span class=res". round($r). ">". number_format($r,1,',','.'). "</span>");									
				}
				else
					return("-");				
			}
		}
		
		public function get_name()
		{
			return $this->convname;
		}
		

		static public function list_conversions()
		{
			global $SCTprefix;
			$listsqr = inputclassbase::load_query("SELECT DISTINCT conversiontype FROM ". $SCTprefix. "SCTconversion");
			if(isset($listsqr['conversiontype']))
			{
				foreach($listsqr['conversiontype'] AS $cix => $convname)
					$retval[$cix] = $convname;
				return $retval;
			}
			else
			{
				return NULL;
			}
		}
	}
?>
