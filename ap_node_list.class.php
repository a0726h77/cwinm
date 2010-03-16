<?
require_once("ap_node.class.php");

class ap_node_list
{
	private $_iface;
	public $node_arr;

############################################################################
	public function ap_node_list($iface)
############################################################################
	{
		$this->_iface = $iface;
	}

############################################################################
	public function scan()
############################################################################
	{
		$cmd = "iwlist $this->_iface scanning";
		exec($cmd, $result);
		$this->node_arr = $this->_parse_node_information($result);
		return $this->node_arr;
	}

############################################################################
	private function _parse_node_information($data)
############################################################################
	{
		$node_arr = array();
		$node = new ap_node();
	
		foreach($data as $l)
		{
			if(preg_match('/Address: (.*)/', $l, $m)) 
			{
				$node->mac = $m[1];
			}
			else if(preg_match('/ESSID:"(.*)"/', $l, $m)) 
			{
				$node->essid = $m[1];
			}
			else if(preg_match('/Quality[=|:](\d{1,3})\/(\d{1,3})/', $l, $m)) 
			{
				$node->quality = $m[1];
				$node->quality_all = $m[2];
			}
			else if(preg_match('/Encryption\ key:(.*)/', $l, $m))
			{
				$node->encryption = $m[1];
			}
	
			if(isset($node->mac) && isset($node->essid) && isset($node->quality) && isset($node->quality_all) && isset($node->encryption))
			{
				array_push($node_arr, $node);
				$node = NULL;
			}
		}
	
		return $node_arr;
	}

############################################################################
	public static function cmp($n1, $n2)
############################################################################
	{
		if($n1->quality == $n2->quality)
		{
			return 0;
		}
		return ($n1->quality > $n2->quality) ? 1 : 0;
/*
		if($n1->encryption == "off" && $n2->encryption == "on")
		{
			return 1;
		}
		else
		{
			if($n1->quality == $n2->quality)
			{
				return 0;
			}
			return ($n1->quality > $n2->quality) ? 1 : 0;
		}
*/
	}

############################################################################
	private function sort()
############################################################################
	{
		usort($this->node_arr, 'ap_node_list::cmp');
		$this->node_arr = array_reverse($this->node_arr);
	}

############################################################################
	public function get_format_node()
############################################################################
	{
	
		foreach($this->node_arr as $n)
		{
			$e_width = @max($e_width, strlen($n->essid));
			$q_width = @max($q_width, strlen($n->quality));
			$q_all_width = @max($q_all_width, strlen($n->quality_all));
		}
	
		$node = array();
		$this->sort();
		foreach($this->node_arr as $n)
		{
			if($n->encryption == "on")
			{
				$node["essid \"$n->essid\" ap $n->mac key "] = sprintf("%{$e_width}s - [%{$q_width}d/%{$q_all_width}d]...%3s", $n->essid, $n->quality, $n->quality_all, $n->encryption);
			}
			else
			{
				$node["essid \"$n->essid\" ap $n->mac"] = sprintf("%{$e_width}s - [%{$q_width}d/%{$q_all_width}d]...%3s", $n->essid, $n->quality, $n->quality_all, $n->encryption);
			}
//			printf("%{$e_width}s - [%{$q_width}d/%{$q_all_width}d]...%3s\n", $n->essid, $n->quality, $n->quality_all, $n->encryption);
		}
	
		return $node;
	}
}
?>
