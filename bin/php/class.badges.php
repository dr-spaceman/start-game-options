<?
require_once "page.php";
require_once "bbcode.php";

class badges {
	
	public $badges; //array of badge data
	
	function __construct(){
		$this->values = array("garbage", "bronze", "silver", "gold", "gold", "gold", "gold");
	}
	
	function get($bid){
		
		// look up badge info and est it as $this->badges[BADGE ID]
		
		if($this->badges[$bid]) return;
		$query = "SELECT * FROM badges WHERE bid='".mysql_real_escape_string($bid)."' LIMIT 1";
		$res   = mysql_query($query);
		while($row = mysql_fetch_assoc($res)){
			$this->badges[$row['bid']] = $row;
			$this->badges[$row['bid']]['value2'] = $this->values[$row['value']];
		}
	}
	
	function earn($bid, $uid=''){
		
		if(!$uid) $uid = $GLOBALS['usrid'];
		if(!$uid) return false;
		
		//make sure hasn't already earned
		$q = "SELECT * FROM badges_earned WHERE bid='$bid' AND usrid='$uid' LIMIT 1";
		if(mysql_num_rows(mysql_query($q))) return false;
		
		$this->get($bid);
		
		$q = "INSERT INTO badges_earned (usrid, bid, `datetime`) VALUES ('$uid', '$bid', '".date("Y-m-d H:i:s")."');";
		if(!mysql_query($q)) return false;
		
		if($uid == $GLOBALS['usrid']) $_SESSION['newbadges'][] = $bid;
		
		if(!$GLOBALS['usrname']) return true;
		
		//track for stream
		do if($bid){
			if($bid == 1) break;
			$url = '/~'.$GLOBALS['usrname'].'/#/badges/'.$bid.'/'.formatNameURL($this->badges[$bid]['name']);
			$action =
				'[[User:'.$GLOBALS['usrname'].']] earned the <a href="'.$url.'">'.$this->badges[$bid]['name'].'</a> badge.'.
				'<div class="badge"><a href="'.$url.'"><img src="/bin/img/badges/'.$bid.'.png" alt="badge: '.htmlSC($this->badges[$bid]['name']).'" width="50" height="50"/></a></div>';
			$q = "INSERT INTO stream (`action`, `action_type`, usrid) VALUES ('".mysql_real_escape_string($action)."', 'earn badge', '$uid');";
			mysql_query($q);
		} while(false);
		
		return true;
		
	}
	
	function showEarned($bid){
		
		//show an earned badge
		
		$this->get($bid);
		
		$ret = '
		<div class="badge badgeearn">
			<div class="container">
				<a href="#close" class="preventdefault ximg close">close</a>
				<h5>You earned a new badge!</h5>
				<h6>'.$this->badges[$bid]['name'].'</h6>
				<div class="badgeimg"><img src="/bin/img/badges/'.$bid.'.png" alt="badge: '.htmlSC($this->badges[$bid]['name']).'"/></div>
				<big>'.bb2html($this->badges[$bid]['description']).'</big>
				<div class="message"><b>Congratulations'.($GLOBALS['usrname'] ? ', '.$GLOBALS['usrname'] : '').'!</b> This <span class="value '.$this->badges[$bid]['value2'].'">'.ucwords($this->badges[$bid]['value2']).' badge</span> will be shown on your profile page along with dozens of other badges you can earn for contributing at Videogam.in.</div>
			</div>
		</div>
		';
		
		if($GLOBALS['usrid']){
			//mark this badge as shown
			$q = "UPDATE badges_earned SET `new` = '0' WHERE bid = '".mysql_real_escape_string($bid)."' AND usrid = '".$GLOBALS['usrid']."' LIMIT 1";
			mysql_query($q);
		}
		
		return $ret;
		
	}
	
	function badgesEarnedList($usrid=''){
		
		// Get all badges earned
		// return array
		
		if(!$usrid && $usrid != $GLOBALS['usrid']) return false;
		
		$query = "SELECT * FROM badges_earned LEFT JOIN badges USING (bid) WHERE usrid = '".mysql_real_escape_string($usrid)."' ORDER BY datetime";
		$res   = mysql_query($query);
		if(!mysql_num_rows($res)) return false;
		while($row = mysql_fetch_assoc($res)) $rows[] = $row;
		
		return $rows;
		
	}
	
	function collection($usrid, $usrname){
		
		//display a user's collection
		
		if(!$rows = $this->badgesEarnedList($usrid)) echo '<span class="none">'.$usrname.' hasn\'t earned any badges yet.</span>';
		else {
			$ret = '
			<ul class="badges">
				';
				foreach($rows as $row){
					$ret.= '<li><a href="/~'.$usrname.'/badges/'.$row['bid'].'/'.formatNameURL($row['name']).'" class="badge user-profile-nav"><img src="/bin/img/badges/'.$row['bid'].'.png" width="70" height="70" border="0" title="'.htmlSC($row['name']).'"/></a></li>';
				}
				$ret.= '
			</ul>
			<br style="clear:left;"/>
			';
		}
		
		return $ret;
		
	}
	
	function show($bid, $usrid=''){
		
		//show a given badge
		//if $usrid, show when user earned it
		
		$this->get($bid);
		
		if($usrid){
			$q = "SELECT * FROM badges_earned WHERE bid = '".mysql_real_escape_string($bid)."' AND usrid = '".$usrid."' LIMIT 1";
			if(!$earneddat = mysql_fetch_object(mysql_query($q))) return false;
		}
		
		$num = mysql_num_rows(mysql_query("SELECT * FROM badges_earned WHERE bid='".mysql_real_escape_string($bid)."';"));
		
		$ret = '
		<div class="showbadge badge">
			<div class="badgeimg"><img src="/bin/img/badges/'.$bid.'.png" alt="badge: '.$this->badges[$bid]['name'].'"/></div>
			<h6>'.$this->badges[$bid]['name'].'</h6>
			<big>'.bb2html($this->badges[$bid]['description']).'</big>
			'.($usrid ? '<div class="message">This <span class="value '.$this->badges[$bid]['value2'].'">'.$this->badges[$bid]['value2'].' badge</span> was earned by '.outputUser($usrid).' on '.formatDate($earneddat->datetime).'</div>' : '').'
			<small><span class="arrow-right">&nbsp;</span>&nbsp;&nbsp;<b>'.$num.'</b> '.($num != 1 ? 'people have' : 'person has').' earned this badge.</small>
		</div>
		<div class="clear"></div>';
		
		return $ret;
		
	}
	
}

?>