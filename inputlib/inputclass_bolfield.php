<?
	require_once("inputclassbase.php");
	class inputclass_bolfield extends inputclassbase
	{
		protected $range,$selval,$readonly;
		public function __construct($fieldid,$range,$dbconnection = NULL,$dbfield = NULL, $dbtable = NULL, $dbkey = NULL, $dbkeyfield = NULL, $style = NULL, $handler = NULL, $extrafield = NULL, $extravalue = NULL)
		{
			parent::__construct($fieldid,$dbconnection,$dbfield,$dbtable,$dbkey,$dbkeyfield,$style,$handler,$extrafield,$extravalue);
			$this->range = $range;
			require_once("inputlib/bolclick.js");
		}

		public function handle_input()
		{
			if(isset($_POST[$this->fieldid]))
			{
				if($_POST[$this->fieldid] != $this->get_state())
				{
					$orgval = $this->get_state();
					if($_POST[$this->fieldid] == "")
						$newval = "NULL";
					else
						$newval = "\"". $_POST[$this->fieldid]. "\"";
					
					if(!$this->record_exists())
					{
						$query = "INSERT INTO ". $this->dbtable. " (". ($this->dbkey > 0 ? "`". $this->dbkeyfield. "`," : ""). "`". $this->dbfield. "`";
						if(isset($this->extrafield))
							foreach($this->extrafield AS $fnm => $fvl)
								$query .= ",`". $fnm. "`";
						if(isset($this->extrakeyfield))
							$query .= ",`". $this->extrakeyfield. "`";
							$query .= ") VALUES(". ($this->dbkey > 0 ? $this->dbkey. "," : "") .$newval;
						if(isset($this->extrafield))
							foreach($this->extrafield AS $fnm => $fvl)
								$query .= ",\"". $fvl. "\"";
						if(isset($this->extrakeyfield))
							$query .= ",\"". $this->extrakey. "\"";
						$query .= ")";
					}
					else
					{
						$query = "UPDATE ". $this->dbtable. " SET `". $this->dbfield. "`=". $newval;
						if(isset($this->extrafield))
							foreach($this->extrafield AS $fnm => $fvl)
								$query .= ",`". $fnm. "`=\"". $fvl. "\"";
						$query .= " WHERE `". $this->dbkeyfield. "`=". $this->dbkey;
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
				else
					echo("OK\r\n");
			}
		}

		public function __toString()
		{
			$retstr="";
			$butstate = $this->get_state();
			if(!isset($butstate))
				$butstate=0;
			for($bp = 1; $bp <= $this->range; $bp++)
			{
				$retstr .= "<input type=\"radio\" name=\"". $this->fieldid. "\" value=". $bp. ($bp == $butstate ? " checked" : " disabled"). ">";
			}
		}

		public function get_state()
		{
			if($this->dbkey > 0)
			{
				$getval = $this->load_query("SELECT `". $this->dbfield. "` AS val FROM ". $this->dbtable. "  WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
				if(isset($getval['val']))
					return $getval['val'][0];
				else
					return NULL;
			}
			else
			{
				return 0;
			}
		}

		public function record_exists()
		{
			if($this->dbkey > 0)
			{
				$getval = $this->load_query("SELECT ". $this->dbfield. " FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakeyfield) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
				if(isset($getval[$this->dbfield]))
					return true;
				else
					return false;
			}
			else
			{
					return false;
			}
		}

		public function echo_html()
		{
			if($this->readonly)
				echo($this->__toString());
			else
			{
				parent::echo_html();
				$this->selval = $this->get_state();
				for($bp=1;$bp<=$this->range;$bp++)
				{
					echo("<INPUT TYPE='radio' STYLE='padding: 0px; margin: 0px' NAME=\"". $this->fieldid. "\" ID=\"". $this->fieldid. "\" value=". $bp);
					echo($this->styledata());
					if($this->selval == $bp)
						echo(" CHECKED multiple");
					if($this->dbfield != NULL || $this->handlerpage != NULL)
						echo(" onClick='bolclick(this);'");
					echo(">");
				}
				//echo("<SCRIPT")
			}
		}
		
		public function set_readonly()
		{
			$this->readonly = TRUE;
		}
	}
?>