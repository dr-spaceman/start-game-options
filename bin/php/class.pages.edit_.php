<?

require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/class.pages.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/htmltoolbox.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bin/php/bbcode.php");

class pgedit extends pg {
	
	public $sessid;
	public $sessionest; // t or f on $this->checkSession()
	public $sessionrow; // session details (if session is established); set upon $this->checkSession()
	public $length;     //length in bytes of the active xml file, meaning a measure of $this->data
	private $publication_iteration = -1;
	
	function __construct($title=''){
    parent::__construct($title);
    $this->sessionStart();
  }
  
  function sessionStart(){
  	global $usrid;
  	//create a new session ID
    $this->sessid = date("YmdHis").sprintf("%07d", $usrid);
  }
  
  function checkSession(){
  	
  	// Check the current session for errors
  	// establishes $this->sessionrow with db data (if session is established)
  	
		if(!preg_match("/^[\d]{21}$/", $this->sessid)) throw new Exception("Invalid session ID");
		$q = "SELECT * FROM pages_edit WHERE session_id = '".$this->sessid."' LIMIT 1";
		if($this->sessionrow = mysqli_fetch_object(mysqli_query($GLOBALS['db']['link'], $q))) $this->sessionest = true;
		
	}
	
	function template(){
		
		// build an XML structure based on $this->type
		// returned data can be edited, assigned to $this->data manually, then saved by calling $this->save()
		// @return SimpleXMLElement
		
		if(!$this->type) throw new Exception("Page type not specified; Couldn't build a template.");
		if(!$this->title) throw new Exception("Page title not specified; Couldn't build a template.");
		
		$templateContent = array(
			"Game character" => "<!--summary/bio here-->\n\n==Dossier==\n; First Appearance :: \n; Game Series :: \n; Affiliations :: ",
			"Game console" => "<!--intro/summary here-->\n\n; Manufacturer :: \n; Release Dates :: January 00, 1900 (Japan) :: January 00, 1901 (North America)\n; Media :: \n; Predecessor :: \n; Successor :: \n; Units sold :: 100,000 (as of January 00, 2010)[cite=URL]SOURCE NAME[/cite]\n; Best-selling Game :: :: [[GAME TITLE]] 999.99 million[cite=URL]SOURCE NAME[/cite]\n"
		);
		
		$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><pages></pages>');
		$pg = $xml->addChild('page');
		$pg->addAttribute('type', $this->type);
		$pg->addAttribute('pgid', $this->pgid);
		$pg->addChild('title', htmlspecialchars($this->title));
		$pg->addChild('keywords');
		$pg->addChild('description');
		$categories = $pg->addChild('categories');
		if($this->subcategory){
			$categories->addChild("category", '[[Category:'.htmlspecialchars($this->subcategory).']]');
		}
		switch($this->type){
			case "game":
				$pg->addChild('categories');
				$pg->addChild('genres');
				$pg->addChild('developers');
				$pg->addChild('series');
				$pg->addChild('publications');
				$pg->addChild('credits');
				break;
			case "person":
				$pg->addChild('dob');
				$pg->addChild('nationality');
				$pg->addChild('professions');
				$pg->addChild('developers');
				$pg->addChild('credits_list');
		}
		$pg->addChild('content', ($templateContent[$this->subcategory] ? htmlspecialchars($templateContent[$this->subcategory]) : ''));
		$pg->addChild("rep_image");
		$pg->addChild("heading_image");
		$pg->addChild("background_image");
		
		return $pg;
		
	}
  
  function construct(){
		
		// build an XML file from $this->data
		// @return SimpleXMLElement
		
		$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><page></page>');
		$page = $xml->addChild('page', $this->data->asXML());
		
		return $page;
  	
  }
  
  function save($draft='', $publish=''){
  	
  	// @param $draft save draft
  	// @param $publish === true ? overwrite the current version
  	
  	//echo '<pre>'.($this->data->asXML());
  	
  	if(!(string)$this->data['pgid'] && $this->pgid) $this->data['pgid'] = $this->pgid;
		
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->xmlStandalone = false;
		$doc->preserveWhiteSpace = false;
		if(!$doc->loadXML($this->data->asXML())) throw new Exception("Couldn't load XML from this->data");
		$doc->formatOutput = true;
		//echo ($doc->saveXML());exit;
		if($draft){
			if(!$this->length = $doc->save($_SERVER['DOCUMENT_ROOT']."/pages/xml/drafts/".$this->sessid.".xml")) throw new Exception("Couldn't save draft. Changes are not recorded!");
		}
		if($publish === true){
			if(!$this->pgid) throw new Exception("Couldn't publish page because no page ID can be found.");
			elseif(!$doc->save($_SERVER['DOCUMENT_ROOT']."/pages/xml/".$this->pgid.".xml")) throw new Exception("Couldn't update version. Changes are not saved!");
		}
  	
  }
  
  function fieldView($field){
  	
  	// output an view of a field
  	// @param $field fieldname
  	// @return html field preview
  	
  	$subj = $this->data->{$field};
  	
  	switch($field){
  		case "keywords":
  			if( (string)$this->data->keywords ) return (string)$this->data->keywords;
  			return '<i class="null">none &ndash; input alternate spellings to facilitate better searching for this page</i>';
  		
  		case "description":
  			if( (string)$this->data->description ){
  				$desc = $this->data->description;
					$desc = bb2html($desc);
					return nl2br($desc);
				} else return '<i class="null">none &ndash; input a single sentence to describe this '.$this->type.'</i>';
  		
  		case "content":
  		case "characters":
  		case "locations":
  			$nullmsg = array(
  				"content"     => '<i class="null">none</i>',
  				"characters" => '<i class="null">none &ndash; list characters featured in this game</i>',
  				"locations" => '<i class="null">none &ndash; list locations featured in this game</i>'
  			);
  			if($cont =  (string)$this->data->{$field}){
  				$cont = wordwrap($cont, 300, '<!--break-->');
  				$cont = str_replace(strstr($cont, '<!--break-->'), '', $cont);
					$cont = bb2html($cont);
					$cont = closeTags($cont);
					if(strlen($cont) >= 298) $cont.= '&hellip;';
					return nl2p($cont);
				} else return $nullmsg[$field];
  		
  		case "genres":
  		case "developers":
  		case "series":
  		case "categories":
  		case "credits_list":
  			$nullmsg = array(
  				"genres"     => '<i class="null">none &ndash; assign one or more genres</i>',
  				"developers" => '<i class="null">none &ndash; assign developers</i>',
  				"series" => '<i class="null">none &ndash; assign game series</i>',
  				"categories" => '<i class="null">none &ndash; assign <b>parent categories</b> and <b>related concepts</b></i>',
  				"credits_list" => '<i class="null">none &ndash; add games and albums credits</i>'
  			);
  			if(!$subj || !$subj->count() || !count($subj->children())) return $nullmsg[$field];
				$ret = '<ul class="inline">' . ($field == "credits_list" ? '<li>'.count($subj->children()).' credits</li>' : '');
				foreach($subj->children() as $ch){
					if(strlen($ret) > 200){ $ret.= '<li>&hellip;</li>'; break; }
					$ret.= '<li>'.$ch.'</li>';
				}
				$ret.= '</ul>';
				return bb2html($ret, "pages_only");
			
			case "publications":
				if(!$subj || !$subj->count() || !count($subj->children())) return '<i class="null">none &ndash; add release dates and box art</i>';
				$pubs = array();
				$num_pubs = 0;
				foreach($subj->children() as $pub){
					$num_pubs++;
					if(!in_array((string)$pub->platform, $pubs)) $pubs[] = (string)$pub->platform;
				}
				$ret = $num_pubs . " publication".($num_pubs != 1 ? 's' : '')." for " . implode(", ", $pubs);
				$ret = bb2html($ret, "pages_only");
				return $ret;
  		
  		case "credits":
  			if(!$subj) return '<i class="null">none &ndash; add credits</i>';
  			$credits = (string)$this->data->credits;
  			$num_credits = substr_count($credits, '::');
  			$credits = substr($credits, 0, 200);
  			$credits = closeTags($credits);
  			if(strlen($credits) >= 200) $credits.= '&hellip;';
  			$credits = '<ul class="inline"><li>'.$num_credits.' credits</li>' . bb2html($credits). '</ul>';
  			$credits = str_replace('</dd><dd>', ', ', $credits);
  			$credits = str_replace('</dt><dd>', ': ', $credits);
  			$credits = str_replace('dt>', 'li>', $credits);
  			$credits = str_replace('dd>', 'li>', $credits);
  			$credits = str_replace('<h5', '<li style="display:none;"', $credits);
  			$credits = str_replace('</h5>', '</li>', $credits);
  			$credits = str_replace('<dl>', '', $credits);
  			$credits = str_replace('</dl>', '', $credits);
  			$credits = str_replace('[[', '', $credits);
  			return $credits;
  		
  		default: return (string)$this->data->{$field};
  	}
  	
  }
  
  function fieldInput($field, $data='', $opts=''){
  	
  	// output an edit field
  	
  	// @param $field str 							fieldname
  	// @param $data SimpleXMLElement	data with which to fill the form (automatically be provided by $this->data)
  	// @param opts str								addl options
  	// @return str 										html field input
  	
  	if(!$data) $data = $this->data->{$field};
  	$ret = '<form action="/pages/edit_process.php" method="post" id="pgedin-'.$field.'" class="pgedfield" onsubmit="return false">';
  	
  	switch($field){
  		case "keywords":
  			$ret.= '<textarea name="'.$field.'" rows="2" class="focusonme">'.($this->data->keywords == '' ? $this->title : $this->data->keywords).'</textarea>';
  			break;
  		
  		case "genres":
  		case "developers":
  		case "series":
  		case "categories":
  		case "credits_list":
  			$ret.= '<textarea name="str_'.$field.'" id="inp-'.$field.'" rows="'.($field == "credits_list" ? '10' : '5').'">';
  			if($data && $data->count()){
	  			foreach($data->children() as $ch){
	  				$ret.= $ch."\n";
	  			}
	  		}
  			$ret = trim($ret);
  			$ret.= '</textarea>';
  			break;
  		
  		case "description":
  			$ret.= 
					'<textarea name="description" rows="2" id="inp-description" class="focusonme">'.$this->data->description.'</textarea>';
				break;
  		
  		case "content":
  			$ret.=
  				outputToolbox("inp-content", array("b", "i", "a", "big", "small", "links", "h5", "h6", "img", "cite", "ol", "li"), "bbcode").
					'<textarea name="content" rows="20" id="inp-content" class="focusonme">'.$this->data->content.'</textarea>';
				break;
			
			case "publication": // One iteration
				
				$ret = ''; //no <field> tags plz
				
				if($data && $data->count()){
					foreach($data->children() as $key => $val){
						$pub[$key] = (string)$val;
					}
					$pub_i = ++$this->publication_iteration;
				} else {
					$pub_i = '%s';
				}
				
				//rm platform Category namespaces (we'll add them later upon saving)
				$pub['platform'] = str_replace("[[Category:", "[[", $pub['platform']);
				
				$ret.= '
					<div id="pub-'.$pub_i.'" class="pubitem">
						<a class="rm ximg" title="Delete this publication">X</a>
						<dl>
							<dt class="pubimg">
								<iframe src="upload_handle.php?component=dragdropform&imgtype=boxart&fdir='.formatNameURL($this->title).'&retelid=boximg-'.$pub_i.'" frameborder="0" class="dropframe tooltip" title="Drop new box image here"></iframe>
								';
								$tn = '/bin/img/blank.png';
								if($pub['filename']){
									$f = explode("/", $pub['filename']);
									$tn = count($f) - 1;
									$f[$tn] = "sm_".substr($f[$tn], 0, -3)."png";
									$tn = implode("/", $f);
									$imgx = "";
									if(file_exists($_SERVER['DOCUMENT_ROOT'].$pub['filename'])) {
										//check img size
										$img  = @GetImageSize($_SERVER['DOCUMENT_ROOT'].$pub['filename']);
										$imgx = $img[0];
									}
								}
								$ret.= '
								<div class="boximg">
									<img src="'.$tn.'" border="0" id="boximg-'.$pub_i.'"/>
								</div>
								<div class="blank">
									<a href="#uplbox" title="Upload new box image" class="tooltip" rel="'.$pub_i.'">Upload</a>
								</div>
								<input type="hidden" id="boximg-'.$pub_i.'-filename" name="publications['.$pub_i.'][filename]" value="'.$pub['filename'].'"/>
							</dt>
							<dd>
								<table border="0" cellpadding="0" cellspacing="0" width="386px">
									<tr>
										<td nowrap="nowrap" class="fftt">
											<input type="text" id="pub-'.$pub_i.'-title" name="publications['.$pub_i.'][title]" value="'.($pub['title'] ? htmlSC($pub['title']) : htmlSC($this->title)).'" class="ff"/>
											<label for="pub-'.$pub_i.'-title" class="tt">Title</label>
										</td>
										<td width="100%">
											<label class="tooltip primary'.($data['primary'] ? ' on' : '').'" title="The primary publication gives this page it\'s default box art, release date, and platform. It should be the first North American publication under most circumstances.">Primary<input type="radio" name="publications_primary" value="'.$pub_i.'" '.($data['primary'] ? 'checked="checked"' : '').' style="display:none;"/></label>
										</td>
									</tr>
								</table>
							</dd>
							<dd class="fftt">
								<input type="text" id="pub-'.$pub_i.'-platform" name="publications['.$pub_i.'][platform]" value="'.htmlSC($pub['platform']).'" class="acplatforms autocomplete ff"/> 
								<label for="pub-'.$pub_i.'-platform" class="tt">Platform</label>
							</dd>
							<dd class="fftt">
								<input type="text" id="pub-'.$pub_i.'-publisher" name="publications['.$pub_i.'][publisher]" value="'.htmlSC($pub['publisher']).'" class="acpublishers autocomplete ff"/> 
								<label for="pub-'.$pub_i.'-publisher" class="tt">Publisher</label>
							</dd>
							<dd>
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td nowrap="nowrap">
											<select name="publications['.$pub_i.'][region]">
												'.(!$pub['region'] ? '<option value="">Region...</option>' : '').'
												<option value="North America"'.($pub['region'] == "North America" ? ' selected="selected"' : '').'>North America</option>
												<option value="Europe"'.($pub['region'] == "Europe" ? ' selected="selected"' : '').'>Europe</option>
												<option value="Japan"'.($pub['region'] == "Japan" ? ' selected="selected"' : '').'>Japan</option>
												<option value="Australia"'.($pub['region'] == "Australia" ? ' selected="selected"' : '').'>Australia</option>
											</select>&nbsp;
										</td>
										<td nowrap="nowrap">
											<select name="publications['.$pub_i.'][release_year]" class="year">
												';
												for($j = (date('Y') + 2); $j >= 1980; $j--) {
													$ret.= '<option value="'.$j.'"'.($pub['release_year'] == $j ? ' selected="selected"' : '').'>'.$j.'</option>'."\n";
												}
												$ret.= '
											</select>&nbsp;
										</td>
										<td nowrap="nowrap">
											<select name="publications['.$pub_i.'][release_month]">
												';
												$release_month = $pub['release_month'];
												$msel[$release_month] = ' selected="selected"';
												$ret.= '
												<option value="00"'.$msel['00'].'>Month</option>
												<option value="01"'.$msel['01'].'>1 Jan</option>
												<option value="02"'.$msel['02'].'>2 Feb</option>
												<option value="03"'.$msel['03'].'>3 Mar</option>
												<option value="04"'.$msel['04'].'>4 Apr</option>
												<option value="05"'.$msel['05'].'>5 May</option>
												<option value="06"'.$msel['06'].'>6 Jun</option>
												<option value="07"'.$msel['07'].'>7 Jul</option>
												<option value="08"'.$msel['08'].'>8 Aug</option>
												<option value="09"'.$msel['09'].'>9 Sep</option>
												<option value="10"'.$msel['10'].'>10 Oct</option>
												<option value="11"'.$msel['11'].'>11 Nov</option>
												<option value="12"'.$msel['12'].'>12 Dec</option>
											</select>&nbsp;
										</td>
										<td nowrap="nowrap">
											<select name="publications['.$pub_i.'][release_day]">
												<option value="00">Day</option>
												';
												for($day = 1; $day <= 31; $day++){
													$ret.= '<option value="'.sprintf("%02d", $day).'"'.($pub['release_day'] == $day ? ' selected="selected"' : '').'>'.$day.'</option>'."\n";
												}
												$ret.= '
											</select>&nbsp;
										</td>
									</tr>
								</table>
							</dd>
							<dd style="width:298px">
								<span style="float:right;'.($pub['url'] ? 'display:none;' : '').'"><a href="#addLink" title="Add related external link (official site, official download source, etc)" class="preventdefault tooltip" onclick="$(this).hide(); $(this).closest(\'dd\').next().show();" style="text-decoration:none;"><b style="font-size:14px">+</b> <u>http://</u></a></span>
								<label><input type="radio" name="publications['.$pub_i.'][media]" value="" '.(!$data['media'] ? 'checked="checked"' : '').'/> Retail</label> &nbsp; 
								<label><input type="radio" name="publications['.$pub_i.'][media]" value="download" '.($data['media'] == "download" ? 'checked="checked"' : '').'/> Download</label> <a href="#help" title="A &lt;b&gt;Retail&lt;/b&gt; release consists of packaging and tangible media like a cartridge, CD, or DVD.&lt;br/&gt;Examples of &lt;b&gt;Download&lt;/b&gt; releases are games on Steam, PlayStation Network, Xbox Live Arcade, Virtual Console, WiiWare, etc." class="tooltip preventdefault">?</a>
							</dd>
							<dd style="'.(!$pub['url'] ? 'display:none;' : '').'">
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td nowrap="nowrap" width="298">
											<div class="fftt">
												<input type="text" id="pub-'.$pub_i.'-url" name="publications['.$pub_i.'][url]" value="'.htmlSC($pub['url']).'" class="ff" style="color:blue; text-decoration:underline;"/>
												<label for="pub-'.$pub_i.'-url" class="tt" style="color:blue; text-decoration:underline;">http://</label>
											</div>
										</td>
										<td>&nbsp;</td>
										<td>
											<div class="fftt">
												<input type="text" id="pub-'.$pub_i.'-link_description" name="publications['.$pub_i.'][link_description]" value="'.htmlSC($pub['link_description']).'" class="ff" style="width:150px"/>
												<label for="pub-'.$pub_i.'-link_description" class="tt">Link description</label>
											</div>
										</td>
									</tr>
								</table>
							</dd>
							<dd class="clear"></dd>
						</dl>
					</div>';
					return $ret;
				
			case "publications":
				$ret.= '<ol class="pubforms">';
				if($data && $data->count()){
  				foreach($data->children() as $pub){
  					$ret.= '<li>' . $this->fieldInput("publication", $pub) . '</li>';
  				}
				}
				$ret.= '</ol><br style="clear:both;"/>';
				break;
			
			case "credits":
				$ret.= '<textarea name="credits" rows="15" class="focusonme" wrap="off" style="">'.(string)$this->data->credits.'</textarea>';
				break;
			
  		case "characters":
  		case "locations":
  			$ret.= '<textarea name="'.$field.'" id="inp-'.$field.'" rows="5">'.(string)$this->data->{$field}.'</textarea>';
  			break;
			
  		default:
  			$ret.= '<textarea name="'.$field.'" rows="2" class="focusonme">'.$this->data->{$field}.'</textarea>';
  	}
  	
  	$ret.= '<div style="margin:5px 0 0 1px;"><button type="submit" class="pgedin-submit" rel="'.$field.'" title="Save changes to this field" style="font-weight:bold">Save</button> <button type="button" class="pgedin-cancel" rel="'.$field.'" id="pgedin-cancel-'.$field.'" title="Cancel changes to this field"">Cancel</button></div></form>';
  	return $ret;
  	
  }
  
  function formHandle(){
  	
  	$handle = array("pgid" => $this->pgid, "title" => $this->title, "sessid" => $this->sessid);
		$handle = http_build_query($handle);
		$handle = base64_encode($handle);
		return '<textarea name="handle" id="pghandle" style="display:none">'.$handle.'</textarea>';
		
	}
  
  function recalculatePageContr($nopub=''){
  	
  	// Recalculate score
  	// @param $nopub Don't publish it to DB row
  	// @ret str usrid of Patron Saint
		
		$query = "SELECT SUM(`score`) AS total_score, usrid FROM pages_edit WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' AND `published` = '1' GROUP BY usrid ORDER BY total_score DESC;";
		$res   = mysqli_query($GLOBALS['db']['link'], $query);
		$contr = array();
		while($row = mysqli_fetch_assoc($res)){
			
			if($row['total_score'] < .5) continue;
			
			if(++$i == 1) $ps = $row['usrid'];
			
			$contr[$row['usrid']] = $row['total_score'];
			
			//watching
			$q = "SELECT * FROM pages_watch WHERE usrid='".$row['usrid']."' AND `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' LIMIT 1";
			if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $contr[$row['usrid']] = $contr[$row['usrid']] + 1;
			
			//sblog posts
			$q = "SELECT * FROM posts_tags LEFT JOIN posts USING(nid) WHERE tag = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' AND posts.usrid='".$row['usrid']."'";
			if($sblogs = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $contr[$row['usrid']] = $contr[$row['usrid']] + ($sblogs * .5);
			
		}
		
		arsort($contr);
		
		if(!$nopub){
			$q = "UPDATE pages SET contributors = '".implode("|", array_keys($contr))."' WHERE title='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' LIMIT 1";
			mysqli_query($GLOBALS['db']['link'], $q);
		}
		
		return $ps;
		
	}
	
	/*function appendIndex(){
		
		// update the index with the current data
		
		$indexes = array(
			"games" => array("genres", "developers", "series", "publications")
		);
		
		$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><index></index>');
		$in = $xml->addChild('index');
		$in->addAttribute('type', $type);
		
	}*/
	
	function buildIndexRow($xml='', $json_blob=array()){
		
		// build an index row with this data
		
		// @param $xml        (optional) XML data to append to
		// @param $json_blob  (optional) JSON data to append to
		
		$index_fields = array(
			"game" => array(
				"genres" => "genre",
				"developers" => "developer",
				"series" => "game_series",
				"categories" => "category",
				"rep_image" => ''
			),
			"person" => array(
				"dob" => '',
				"nationality" => '',
				"professions" => "profession",
				"developers" => "developer",
				"categories" => "category",
				"rep_image" => ''
			),
			"category" => array(
				"categories" => "category",
				"rep_image" => ''
			),
			"topic" => array(
				"categories" => "category",
				"rep_image" => ''
			)
		);
		
		$json = array(
			"keywords"    => (string)$this->data->keywords,
			"description" => (string)$this->data->description
		);
		
		if(!$xml){
			$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><'.$this->type.'/>');
		}
		$xml_ = $xml->addChild($this->type);
		$xml_->addAttribute("pgid", $this->pgid);
		$xml_->addChild("title", htmlspecialchars($this->title));
		$xml_->addChild("description", htmlspecialchars($this->data->description));
		
		foreach($index_fields[$this->type] as $parent => $ch){
			if($ch == ''){
				$json[$parent] = (string)$this->data->{$parent};
				$xml_->addChild($parent, htmlspecialchars($this->data->{$parent}));
			} else {
				$th = $xml_->addChild($parent);
				if($this->data->{$parent}){
					foreach($this->data->{$parent}->children() as $i){
						$i = str_replace('[[Category:', '', $i);
						$i = str_replace('[[', '', $i);
						$i = str_replace(']]', '', $i);
						$json[$parent][] = (string)$i;
						$th->addChild($ch, htmlspecialchars($i));
					}
				}
			}
		}
		if($this->type == "game"){
			$result = $this->data->xpath("//publication[@primary='primary']");
			if($node = $result[0]){
				
				$pf = (string)$node->platform;
				
				$pglinks = new pglinks();
				if($exlinks = $pglinks->extractFrom((string)$node->platform)){
					$pf = $exlinks[0]['tag'];
				}
				
				$xml_->addChild("platform", htmlspecialchars($pf));
				$json['platform'] = $pf;
				$rel = (string)$node->release_year."-".(string)$node->release_month."-".(string)$node->release_day;
				$xml_->addChild("release", $rel);
				$json['release'] = $rel;
			}
		}
		
		if($json_blob) $json_blob[$this->title] = $json;
		else $json_blob = $json;
		return array($xml, $json_blob);
		
	}
  
  function header(){
  	global $page, $usrid;
  	
  	$page->css[] = "/pages/pages_screen.css";
		$page->css[] = "/pages/pages_edit.css";
		
		$page->bodyattr['class'][] = "pged";
		
		$page->first_section = array("id"=>"pgedwrap");
		
		$page->header();
  	
  	if($usrid) $is_watching = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM pages_watch WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' AND usrid='$usrid' LIMIT 1"));
		
		$here[$_SERVER['SCRIPT_NAME']] = "on";
		$titleurl = formatNameURL($this->title);
		
		?>
		<input type="hidden" name="pgid" value="<?=$this->pgid?>" id="pgid"/>
		<input type="hidden" name="pgtitle" value="<?=htmlsc($this->title)?>" id="pgtitle"/>
		
		<div id="pgedhead">
			
			<h1><?=$this->title?></h1>
			<h2><?=(!$this->row ? 'Page not yet started' : 'A <b>'.$this->type.'</b> added to the database on <b>'.formatDate($this->row->created).'</b> by <b>'.outputUser($this->row->creator, FALSE).'</b> <span>|</span> Last edited <b>'.timeSince($this->row->modified).' ago</b> <span>|</span> Page ID #<b>'.$this->pgid)?></b></h2>
			
			<div class="nav">
				<ul>
					<li><span><a href="<?=$this->url?>" class="arrow-left">Overview</a></span></li>
					<li class="<?=$here['/pages/edit.php']?>"><a href="edit.php?title=<?=$titleurl?>">Edit</a></li>
					<?=($GLOBALS['usrrank'] >= 4 ? '<li class="'.$here['/pages/configure.php'].'"><a href="configure.php?title='.$titleurl.'">Advanced</a></li>' : '')?>
					<li class="<?=$here['/pages/move.php']?>"><a href="move.php?title=<?=$titleurl?>">Rename</a></li>
					<li class="<?=$here['/pages/history.php']?>"><a href="history.php?title=<?=$titleurl?>">History</a></li>
					<li class="<?=$here['/pages/links.php']?>"><a href="links.php?to=<?=$titleurl?>">Links</a></li>
					<li><a href="#">Discussion</a></li>
					<li class="watch">
						<div>
							<span class="chbox<?=($is_watching ? ' checked' : '')?> tooltip watchpage" title="Closely monitor pages you're watching"><span class="inp"></span>Watch</span>
						</div>
					</li>
				</ul>
			</div>
			
		</div>
		<?
	}
	
	function footer($opts=''){
		global $page;
		
		$page->closeSection(); // #pgedwrap
		
		if(strstr($opts, "incl_console")){
			
			$sec = array("id"=>"pgedconsole");
			$page->openSection($sec, true);
				
			?>
			<div class="tracker">
				<span id="draftsaved">Not saved yet</span>&nbsp;&nbsp;
				<button type="button" disabled="disabled" id="act-savenow" onclick="changes.saveAll()">Save Draft</button>
				<span id="permalink">&nbsp;&nbsp;<a href="history.php?view_version=<?=$this->sessid?>" title="Permanent link to this version" target="_blank" style="text-decoration:none;">&infin; <u>Permalink</u></a></span>
				<span id="submitlink">&nbsp;&nbsp;<b><a href="#submit" class="preventdefault arrow-toggle" onclick="$(this).toggleClass('arrow-toggle-on'); $('#pged-submit-outer').slideToggle(); $('#edit_summary').focus();">Submit Changes</a></b></span>
			</div>
			<div class="switch">
				<div class="el-inset"></div>
				<div id="pgedswitchnub" class="el-nub left"></div>
				<a href="#edit" title="Edit" class="el-edit">Edit</a>
				<a href="#preview" title="Preview" class="el-view">Preview</a>
			</div>
			
			<!-- METADATA & SUBMISSION FIELDS -->
			<div id="pged-submit-outer" style="display:none;">
				<form id="pged-submit" name="pged-submit" style="display:block;">
					
					<?=$this->formHandle()?>
					<?=($this->editsource ? '<input type="hidden" name="editsource" value="'.$this->editsource.'"/>' : '')?>
					
					<fieldset id="editsummary" style="margin:20px 0 0; padding:8px 15px 15px; background-color:#EEE;">
						<legend>Edit Summary</legend>
						Please briefly summarize edits, making clear your intention and purpose for editing. This will help keep better records and allow the editors and future contributors to better understand your contributions.
						<div style="margin-top:5px; margin-right:5px;">
							<textarea name="edit_summary" rows="2" tabindex="2" id="edit_summary" style="width:100%;" onfocus="$.address.value('?')"><?=$this->sessionrow->edit_summary?></textarea>
						</div>
					</fieldset>
					
					<?
					$q = "SELECT * FROM pages_watch WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' AND usrid='".$usrid."' LIMIT 1";
					$watching = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q));
					
					?>
					<fieldset style="margin:20px 0 0; padding:15px; background-color:#EEE;">
						<?
						if(!$watching){
						?>
							<label>
								<input type="checkbox" name="watch" value="1"<?=($this->juststarted ? ' checked="checked"' : '')?>/> 
								Watch this page <a title="Track changes made by other users to this page. You can even receive e-mail notifications if you set this option on your account preferences page (from the top menu: <?=$GLOBALS['usrname']?> &gt; Your Account &gt; Preferences)" class="tooltip" href="#help">?</a>
							</label><p style="margin:5px 0 0;"></p>
							<?
						}
						?>
						<label><input type="checkbox" name="minoredit" value="1"<?=($this->sessionrow->minoredit ? ' checked="checked"' : '')?>/> This is a minor edit <a href="#help" class="tooltip" title="Mark this edit as minor if it only corrects spelling or formatting, performs minor rearrangements of text, or tweaks only a few words or inconsequential attributes.">?</a></label>
						<?
						if($GLOBALS['usrrank'] > 6){
							?><p style="margin:5px 0 0;"></p><label><input type="checkbox" name="withholdpts" value="1"<?=($this->sessionrow->withholdpts ? ' checked="checked"' : '')?>/> Withhold my points for this edit</label><?
						}
						?>
					</fieldset>
					
					<div class="buttons" style="margin:20px 0 0;">
						<div style="float:right; color:#888;">
							<a href="<?=$this->url?>" title="Close this edit session without publishing changes" class="red tooltip">Cancel</a> or
							<a href="edit.php?destroysession=<?=$this->sessid?>&returnonfail=<?=formatNameURL($this->title)?>" title="Completely destroy this edit session" class="red tooltip">Destroy</a> this session
						</div>
						<button type="button" id="pged-submitbtn" tabindex="3" style="font-weight:bold; font-size:14px; padding:5px 10px;">Publish Changes</button>
					</div>
					
				</form>
			</div>
			
			<div id="msg-draftsaved" class="tooltip-stationary">
				A draft of your changes has been saved! Your changes haven't been published yet though. In the meantime, you can continue to develop your article, ask others to help work on it, and eventually publish it when it's ready. Your draft will be saved indefinitely; Pick up where you left off at any time. You can safely exit this page.
			</div>
			
			<div id="pged-loading" class="popmsg" title="loading..."><img src="/bin/img/icons/sprites/littlemac_jog.gif" alt="Loading..."/></div>
			<?
			
			$page->closeSection();
			
		}
		
		$page->footer();
		exit;
	}
}