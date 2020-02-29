<?

require_once ($_SERVER["DOCUMENT_ROOT"]."/pages/class.pages.php");
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
    $this->sessid = date("YmdHis").sprintf("%07d", $usrid).sprintf("%09d", $this->pgid);
  }
  
  function checkSession(){
  	
  	// Check the current session for errors
  	// establishes $this->sessionrow with db data (if session is established)
  	
		if(!preg_match("/^[\d]{30}$/", $this->sessid)) throw new Exception("Invalid session ID");
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
			"Game console" => "<!--intro/summary here-->\n\n; Manufacturer :: \n; Release Dates :: January 00, 1900 (Japan) :: January 00, 1901 (North America)\n; Media :: \n; Predecessor :: \n; Successor :: \n; Units sold :: 100,000 (as of January 00, 2010)[cite=URL]SOURCE NAME[/cite]\n; Best-selling Game :: :: [[GAME TITLE]] 999.99 million[cite=URL]SOURCE NAME[/cite]\n",
			"Game developer" => "<!--intro/summary here-->\n\n; Founded :: 1901\n; Founder(s) :: [[Hulk Hogan]]\n; Headquarters :: Redwood City, California, United States\n; Key people :: [[Larry Probst]] (Chairman) :: [[John Riccitiello]] (CEO)\n; Major Franchises\n:: [[Madden NFL series]]\n:: [[Mass Effect series]]\n; Official Site :: [url]http://www.foobar.com/[/url]"
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
				$pg->addChild('roles');
				$pg->addChild('developers');
				$pg->addChild('credits_list');
				break;
			case "template":
				$categories->addChild("category", '[[Category:Page Template]]');
				break;
		}
		$pg->addChild('content', ($templateContent[$this->subcategory] ? htmlspecialchars($templateContent[$this->subcategory]) : ''));
		$pg->addChild("rep_image");
		$pg->addChild("heading_image");
		$pg->addChild("background_image");
		//$pg->addChild("wikipedia_title", htmlspecialchars($this->title));
		
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
  	
  	$this->length_old = $length;
		
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
			elseif($this->length = !$doc->save($_SERVER['DOCUMENT_ROOT']."/pages/xml/".$this->pgid.".xml")) throw new Exception("Couldn't update version. Changes are not saved!");
		}
  	
  }
  
  function fieldView($field){
  	
  	// output an view of a field
  	// @param $field fieldname
  	// @return html field preview
  	
  	$subj = $this->data->{$field};
  	
  	switch($field){
  		
  		case "apis":
  			$ret = '<ul class="inline">'.
  				'<li>'.((string)$this->data->wikipedia_title ? '<span style="color:#3EC144">&#10003;</span>' : '<span style="color:#D02F2F">&#10007;</span>') . ' Wikipedia</li>'.
  				'<li>'.((string)$this->data->twitter_id ? '<span style="color:#3EC144">&#10003;</span>' : '<span style="color:#D02F2F">&#10007;</span>') . ' Twitter</li>';
  			if($this->type == "game"){
  				$ret.= '<li>'.((string)$this->data->steam_appid ? '<span style="color:#3EC144">&#10003;</span>' : '<span style="color:#D02F2F">&#10007;</span>') . ' Steam</li>';
  				$ret.= '<li>'.((string)$this->data->amazon_asin ? '<span style="color:#3EC144">&#10003;</span>' : '<span style="color:#D02F2F">&#10007;</span>') . ' Amazon.com</li>';
  			}
  			return $ret;
  			
  		case "personal":
  			$ret = '<ul class="inline">'.
  				'<li>'.((string)$this->data->dob ? '<span style="color:#3EC144">&#10003;</span>' : '<span style="color:#D02F2F">&#10007;</span>') . ' Birthdate</li>'.
  				'<li>'.((string)$this->data->nationality ? '<span style="color:#3EC144">&#10003;</span>' : '<span style="color:#D02F2F">&#10007;</span>') . ' Nationality</li>';
  			return $ret;
  		
  		case "keywords":
  			if( (string)$this->data->keywords ) return (string)$this->data->keywords;
  			return '<i class="null">none &ndash; input alternate spellings to facilitate better searching for this page</i>';
  		
  		case "description":
  			if( (string)$this->data->description ){
  				$desc = $this->data->description;
					$desc = links($desc);
					return nl2br($desc);
				} else return '<i class="null">none &ndash; input a single sentence to describe this '.$this->type.'</i>';
  		
  		case "content":
  			if($cont = (string)$this->data->{$field}){
  				$cont = str_replace("\r", "", $cont);
  				if($pos = strpos($cont, "\n\n")){
  					$cont = substr($cont, 0, $pos) . ' &hellip;';
  				} else {
  					$cont = wordwrap($cont, 300, '<!--break-->');
  					$cont = str_replace(strstr($cont, '<!--break-->'), '', $cont);
						if(strlen($cont) >= 298) $cont.= '&hellip;';
					}
					$cont = links($cont);
					return $cont;
				} else return '<i class="null">none</i>';
				
  		case "characters":
  		case "locations":
  		case "credits":
  			$cont = $subj->asXML();
  			$cont = str_replace("<$field/>", "", $cont);
  			$cont = str_replace("<$field>", "", $cont);
  			$cont = str_replace("</$field>", "", $cont);
  			$links = new pglinks();
  			$ret = '';
				if($exlinks = $links->extractFrom($cont)){
					foreach($exlinks as $link){
						if($field != "credits" && $link['namespace'] != "Category") continue;
						$num++;
						if(!$maxlinks){
							$ret.= '<li>'.$link['original'].'</li>';
							if(strlen($ret) >= 298){
								$ret.= '<li>&hellip;</li>';
								$maxlinks = 1;
							}
						}
					}
				}
				if($ret == '' && strlen($cont)){
					$cont = wordwrap($cont, 300, '<!--break-->');
  				$cont = str_replace(strstr($cont, '<!--break-->'), '', $cont);
					$cont = bb2html($cont);
					$cont = closeTags($cont);
					if(strlen($cont) >= 298) $cont.= '&hellip;';
					return nl2p($cont);
				} elseif($ret == '') return '<i class="null">none &ndash; list '.$field.' featured in this game</i>';
  			$ret = '<ul class="inline"><li><i>'.$num.' '.($num != 1 ? $field : substr($field, 0, -1)).'</i></li>'.$ret.'</ul>';
  			return bb2html($ret, "pages_only");
  		
  		case "genres": $nullmsg = '<i class="null">none &ndash; assign one or more genres</i>';
  		case "developers": $nullmsg = '<i class="null">none &ndash; assign developers</i>';
  		case "series": $nullmsg = '<i class="null">none &ndash; assign game series</i>';
  		case "categories": $nullmsg = '<i class="null">none &ndash; assign <b>parent categories</b> and <b>related concepts</b></i>';
  		case "credits_list": $nullmsg = '<i class="null">none &ndash; add games and albums credits</i>';
  		case "roles": $nullmsg = '<i class="null">none &ndash; add competencies</i>';
  		case "online": $nullmsg = '<i class="null">none</i>';
  			if(!$subj || !$subj->count() || !count($subj->children())) return $nullmsg;
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
				$ret = $num_pubs. ' publication'.($num_pubs != 1 ? 's' : '').' for '.implode(", ", $pubs);
				$ret = bb2html($ret, "pages_only");
				return $ret;
  		
  		/*case "credits":
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
  			return $credits;*/
  		
  		default: return strlen((string)$this->data->{$field}) ? substr((string)$this->data->{$field}, 0, 200)."&hellip;" : '<i class="null">none</i>';
  	}
  	
  }
  
  function fieldInput($field, $data='', $opts=''){
  	
  	// output an edit field
  	
  	// @param $field str 							fieldname
  	// @param $data SimpleXMLElement	data with which to fill the form (automatically be provided by $this->data)
  	// @param opts str								addl options
  	// @return str 										html field input
  	
  	global $platforms, $mediadist_options, $publishers_options;
  	
  	if(!$data) $data = $this->data->{$field};
  	$ret = '<form action="/pages/edit_process.php" method="post" id="pgedin-'.$field.'" class="pgedfield" onsubmit="return false" data-field="'.$field.'"><!--start input-->';
  	
  	switch($field){
  		
  		case "apis":
  			$wikipedia_title = $this->data->wikipedia_title ? (string)$this->data->wikipedia_title : '';
  			$twitter_id = (string)$this->data->twitter_id ? substr((string)$this->data->twitter_id, 1) : '';
  			if($twitter_id){
  				$tw = substr((string)$this->data->twitter_id, 0, 1);
  				if($tw == "@") $twitter_select['id'] = 'selected';
  				elseif($tw == "#") $twitter_select['tag'] = 'selected';
  			}
  			$ret.= '<dl>'.
  				'<dt>Wikipedia</dt><dd><input type="text" placeholder="Article title" name="wikipedia_title" value="'.htmlsc($wikipedia_title).'" size="40" id="wikipedia-title"/> <button style="line-height:16px;">&#10003;</dd><dd id="wikipedia-title-description"></dd>'.
  				'<dt>Twitter</dt><dd><select name="twitter_type"><option value="@" '.$twitter_select['id'].'>@</option><option value="#" '.$twitter_select['tag'].'>#</option></select> <input type="text" placeholder="" name="twitter_id" value="'.htmlsc($twitter_id).'" size="20" id="twitter-id"/>'.
  				($this->type == "game" ? 
  					'<dt>Steam</dt><dd><input type="text" placeholder="App ID" name="steam_appid" value="'.htmlsc((string)$this->data->steam_appid).'" size="10"/> <button type="button" title="Try to automatically fetch the Steam App ID" id="api-steam-fetch-appid">Fetch</button></dd><dd>The App ID can be found in the URL at SteamPowered.com <samp>&lt;http://store.steampowered.com/app/<b>200900</b>/&gt;</samp></dd><dd>SteamPowered.com search: <a href="http://store.steampowered.com/search/?term='.urlencode($this->title).'" target="_blank" class="arrow-link">store.steampowered.com/search/?term='.urlencode($this->title).'</a></dd>'.
  					'<dt>Amazon.com</dt><dd><input type="text" placeholder="ASIN" name="amazon_asin" value="'.htmlsc((string)$this->data->amazon_asin).'" size="10"/></dd><dd>The ASIN ID can be found in the <b>Product Details</b> section of a product page on Amazon.com, or it can be found in the URL <samp>&lt;http://www.amazon.com/Mass-Effect-Xbox-360/dp/<b>B000OLXX86</b>/ref=1_1&gt;</samp>)</dd><dd>Amazon.com search: <a href="http://www.amazon.com/s/?url=search-alias%3Dvideogames&field-keywords='.urlencode($this->title).'" target="_blank" class="arrow-link">Amazon.com/s/?url=search-alias%3Dvideogames&field-keywords='.urlencode($this->title).'</a>' : '').
  			'</dl>';
  			break;
  		
  		case "keywords":
  			$ret.= '<textarea name="'.$field.'" rows="2" class="focusonme">'.$this->data->keywords.'</textarea>';
  			break;
  		
  		case "categories":
  			if($this->type == "category"){
  				//if this page type is Category, add additional form details like Subcategory and Immediate Parent
	  			$ret.= 
	  			'<div id="inp-subcategory">
	  				Page Subcategory: 
	  				<select name="subcategory" onchange="subcatCh()">
	  					<option value="">General</option>';
	  					foreach($GLOBALS['pgsubcategories'] as $cname => $cvalue) $ret.= '<option value="'.$cname.'" '.($this->subcategory==$cname ? 'selected="selected"' : '').'>'.$cname.'</option>';
	  					$ret.= '
	  				</select>
	  				<p></p>
	  				<span class="arrow-left"></span>Check immediate parent categories on the left. <a class="" onclick="$(\'#help-parentcategory\').fadeIn()"><span class="helpinfo"><span>?</span></span>How this works</a>
	  			</div>';
	  		}
	  		$links = new pglinks();
  			$links->attr['target'] = "_blank";
  			$ret.= '<ul class="datalist">';
  			if($data && $data->count()){
	  			foreach($data->children() as $ch){
	  				foreach($links->extractFrom($ch) as $link){
		  				if($this->type=="category"){
		  					$q = "SELECT subcategory FROM pages WHERE title = '".mysqli_real_escape_string($GLOBALS['db']['link'], $link['tag'])."' LIMIT 1";
								$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q));
								$links->attrs['data-subcategory'] = $row['subcategory'];
		  					$chbox =  '<input type="checkbox" name="'.$field.'_parent[]" value="'.htmlsc($link['tag']).'"'.($this->subcategory!=$row['subcategory'] ? ' disabled' : '').($ch['ancestor']=="parent" ? ' checked' : '').' title="mark as Immediate Parent" class="tooltip"/>';
							}
		  				$ret.= '<li data-tag="'.htmlsc($ch).'">'.$links->outputLink($link['tag'], $link['namespace'], $link['link_words']).$chbox.'<a class="rm"></a><textarea name="'.$field.'[]">'.$ch.'</textarea></li>';
		  			}
	  			}
	  		}
  			$ret.= '</ul><div style="clear:right"></div>';
  			break;
  			
  		case "genres":
  		case "developers":
  		case "series":
  		case "credits_list":
  		case "roles":
  			$links = new pglinks();
  			$links->attr['target'] = "_blank";
  			$ret.= '<ul class="datalist">';
  			if($data && $data->count()){
	  			foreach($data->children() as $ch){
	  				foreach($links->extractFrom($ch) as $link){
		  				$ret.= '<li data-tag="'.htmlsc($ch).'">'.$links->outputLink($link['tag'], $link['namespace'], $link['link_words']).'<a class="rm"></a><textarea name="'.$field.'[]">'.$ch.'</textarea></li>';
		  			}
	  			}
	  		}
  			$ret.= '</ul><div style="clear:right"></div>';
  			break;
  		
  		case "description":
  			$ret.= '<textarea name="description" rows="2" id="inp-description" class="focusonme tagging">'.$this->data->description.'</textarea>';
  			if($this->type == "game") $ret.= '<p><a class="arrow-right" onclick="$(\'#secf-official_description\').click()">Input an <b>official description</b></a></p>';
				break;
  		
  		case "content":
  			
  			$ret.= 
  				'<div>'.
						'<textarea name="content" id="inp-content" class="wmd-input autosize focusonme tagging">'.$this->data->content.'</textarea>'.
					'</div>';
				break;
				
			case "publications":
			
				$platforms = getPlatforms(true);
				
				$mediadist_options = '';
				$media = '';
				$dist  = '';
				$query = "SELECT `to`, `title`, `keywords` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game media' OR `to` = 'Online distribution platform') AND `namespace` = 'Category' AND `redirect_to` = '' ORDER BY `title`";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)){
					$li = '<li class="fauxselect-option" data-value="[['.htmlsc($row['title']).']]" title="'.$row['keywords'].'">'.$row['title'].'</li>';
					if(strtolower($row['to']) == "game media") $media.= $li;
					else $dist.= $li;
				}
				$mediadist_options = $media . '<li class="break"></li>' . $dist;
				
				$publishers_options = '';
				$query = "SELECT `title`, `keywords` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game publisher') AND `namespace` = 'Category' AND `redirect_to` = '' ORDER BY `title`";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)){
					$publishers[] = $row['title'];
					$publishers_options.= '<li class="fauxselect-option" data-value="[['.htmlsc($row['title']).']]" title="'.$row['keywords'].'">'.$row['title'].'</li>';
				}
				$publishers_options.= '<li class="break"></li>';
				$query = "SELECT `title`, `keywords` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Game developer') AND `namespace` = 'Category' AND `redirect_to` = '' ORDER BY `title`";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				while($row = mysqli_fetch_assoc($res)){
					if(in_array($row['title'], $publishers)) continue;
					$publishers_options.= '<li class="fauxselect-option" data-value="[['.htmlsc($row['title']).']]" title="'.$row['keywords'].'">'.$row['title'].'</li>';
				}
				
				$ret.= '<div class="pubforms">';
				if($data && $data->count()){
  				foreach($data->children() as $pub){
  					$ret.= $this->fieldInput("publication", $pub);
  				}
				}
				$ret.= '
					<div id="addpub">
						Add a new&hellip;&nbsp;&nbsp;
						<b><button type="button" data-distribution="digital"><b>+</b> Digital Publication</button></b>&nbsp;&nbsp;
						<b><button type="button" data-distribution="retail"><b>+</b> Retail Publication</button></b>&nbsp;&nbsp;
						<span class="a" id="addpub-help">What\'s this?</span>
						<p style="display:none">A <b>Digital</b> publication is downloaded from an <a href="/categories/Online_distribution_platform" target="_blank">online distribution platform</a> like Xbox Live, PlayStation Network, or Nintendo eShop, whereas a <b>Retail</b> release is a physical publication with packaging and <a href="/categories/Game_media" target="_blank">game media</a> like CD, DVD, or ROM cartridge.</p>
					</div>
				</div>';
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
				$pub['publisher'] = str_replace("[[Category:", "[[", $pub['publisher']);
				$pub['media_distribution'] = str_replace("[[Category:", "[[", $pub['media_distribution']);
				
				$platforms_options = '';
				foreach($platforms as $pf){
					$o_pf = htmlsc($pf['title']);
					$o_kw = $o_pf.', '.htmlsc($pf['keywords']);
					$sel = $pub['platform'] == '[['.$pf['title'].']]' ? 'selected' : '';
					$platforms_options.= '<li class="fauxselect-option" title="'.$o_kw.'" data-value="[['.$o_pf.']]" class="'.$sel.'">'.$pf['title'].'</li>';
				}
				
				$sel_region = '';
				foreach($GLOBALS['pf_regions'] as $region => $abbr){
					$sel_region.= '<li class="fauxselect-option" data-value="'.$region.'" onclick="$(\'#publications-'.$pub_i.'-region-output\').html($(this).html()); $(\'#pub-'.$pub_i.'-release\').focus();"><a rel="'.$abbr.'" title="'.$region.'" style="background-image:url(/bin/img/flags/'.$abbr.'.png)">'.$region.'</a></li>';
				}
				
				$pub['release'] = $pub['release_tentative'] ? $pub['release_tentative'] : ($pub['release_year'] ? $pub['release_year']."-".$pub['release_month']."-".$pub['release_day'] : '');
				
				if(!$pub['region']) $pub['region'] = "North America";
				
				$img_fields = array(
					array("name" => "Box art", "type" => "box", "category_id" => 4, "xml_field_name" => 'img_name'),
					array("name" => "Title screen", "type" => "titlescreen", "category_id" => 11, "xml_field_name" => 'img_name_title_screen'),
					array("name" => "Logo / Icon", "type" => "logo", "category_id" => 15, "xml_field_name" => 'img_name_logo')
				);
					
				for($i = 0; $i < 3; $i++){
				
					$tn = '';
					$tn_href = '';
					if($pub[$img_fields[$i]['xml_field_name']]){
						try{ $img = new img($pub[$img_fields[$i]['xml_field_name']]); }
						catch(Exception $e){ unset($img); }
						$tn = $img->src['tn'];
						$tn_href = $img->src['url'];
					}
					
					$img_fields[$i]['output'] = '
						<div class="container">
							'.$img_fields[$i]['name'].'
							<div class="opt toggle">
								<a onclick="img.init({fieldId:\'img-'.$img_fields[$i]['type'].'-'.$pub_i.'-filename\', fieldSrc:$(\'#img-'.$img_fields[$i]['type'].'-'.$pub_i.'\'), action:\'select\', \'onSelect\':changePubImgEval, uploadVars:{img_category_id:'.$img_fields[$i]['category_id'].', img_tag:$(\'#pgtitle\').val(), overwrite:\'true\', handler:\''.base64_encode("sessid=".$GLOBALS['img_sessid']).'\'}, nav:\'upload\'})">Upload an image</a><br/>
								<a onclick="img.init({fieldId:\'img-'.$img_fields[$i]['type'].'-'.$pub_i.'-filename\', fieldSrc:$(\'#img-'.$img_fields[$i]['type'].'-'.$pub_i.'\'), action:\'select\', \'onSelect\':changePubImgEval, uploadVars:{img_category_id:'.$img_fields[$i]['category_id'].', img_tag:$(\'#pgtitle\').val(), overwrite:\'true\', handler:\''.base64_encode("sessid=".$GLOBALS['img_sessid']).'\'}})">Browse uploads</a>
							</div>
						</div>
						<a href="'.$tn_href.'" class="imgupl'.(!$tn ? " off" : "").'"><img src="'.$tn.'" border="0" id="img-'.$img_fields[$i]['type'].'-'.$pub_i.'" width="50"/></a>
						<a onclick="changePubImg(\'img-'.$img_fields[$i]['type'].'-'.$pub_i.'\', \'\', \'\')" title="Remove this image" class="rm">X</a>
						<div class="blank" title="Drop an image file from your desktop here to upload" style="'.($tn ? "display:none;" : "").'" ondragenter="event.stopPropagation(); event.preventDefault();" ondragover="event.stopPropagation(); event.preventDefault();" ondrop="event.stopPropagation(); event.preventDefault(); handleFileSelect(event, {parent_key:\'img-'.$img_fields[$i]['type'].'-'.$pub_i.'\', img_category_id:\''.$img_fields[$i]['category_id'].'\', handler:\''.base64_encode('sessid='.$GLOBALS['img_sessid']).'\'});"><span class="ttip toggle"><span class="arrow-left">Drop here</span></span></div>
						<input type="hidden" class="pubimgfilename" id="img-'.$img_fields[$i]['type'].'-'.$pub_i.'-filename" name="publications['.$pub_i.']['.$img_fields[$i]['xml_field_name'].']" value="'.$pub[$img_fields[$i]['xml_field_name']].'"/>
					';
					//<iframe src="/pages/upload_handle.php?img_tag[]='.urlencode($this->title).'&parent_key=img-'.$img_fields[$i]['type'].'-'.$pub_i.'&sessid='.$GLOBALS['img_sessid'].'&img_category_id='.$img_fields[$i]['category_id'].'" frameborder="0"></iframe>
				}
				
				$ret.= '
					<div class="pubitem" id="pub-'.$pub_i.'" data-pubid="'.$pub_i.'">
						
						<div class="pubopts">
							<label class="tooltip primary'.($data['primary'] ? ' on' : '').'" title="The primary publication gives this page it\'s default box art, release date, and platform. It should be the first North American publication under most circumstances.">Primary<input type="radio" name="publications_primary" value="'.$pub_i.'" '.($data['primary'] ? 'checked="checked"' : '').' style="display:none;"/></label>
							<a class="duplicatepub" title="Add a new publication using this data">Duplicate</a>
							<a class="removepub" title="Delete this publication">Delete</a>
						</div>
						
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tbody>
								<tr>
									<td width="55%">
										<input type="checkbox" name="publications['.$pub_i.'][reissue]" value="1" '.($data['reissue'] ? 'checked' : '').' id="pub-'.$pub_i.'-reissue" class="fauxcheckbox showoncheck"/>
										<label for="pub-'.$pub_i.'-reissue" class="tooltip" title="This is a re-release of an earlier version. Examples include \'Greatest Hits\' or digital releases (ie. &lt;i&gt;Super Mario Bros.&lt;/i&gt; on Virtual Console). Reissues won\'t appear on their specified Platform page (they aren\'t automatically categorized).">Reissue</label>
										<div style="margin-right:90px;">
											<input type="text" id="pub-'.$pub_i.'-title" name="publications['.$pub_i.'][title]" value="'.($pub['title'] ? htmlSC($pub['title']) : htmlSC($this->title)).'" placeholder="Publication title"/>
										</div>
									</td>
									<td width="45%" rowspan="2">
										<div class="pubimg">
											'.$img_fields[0]['output'].'
										</div>
									</td>
								</tr>
								<tr>
									<td class="pub-release">
										<div class="pubregion fauxselect">
											<input type="hidden" name="publications['.$pub_i.'][region]" value="'.$pub['region'].'" id="publications-'.$pub_i.'-region-input" class="fauxselect-input"/>
											<div class="pubregion-output" id="publications-'.$pub_i.'-region-output"><a style="background-image:url(/bin/img/flags/'.$GLOBALS['pf_regions'][$pub['region']].'.png)" title="'.$pub['region'].'">'.$pub['region'].'</a></div>
											<ol class="fauxselect-options">'.$sel_region.'</ol>
										</div>
										<input type="checkbox" name="publications['.$pub_i.'][release_tentative]" value="1" '.($pub['release_tentative'] ? 'checked' : '').' id="pub-'.$pub_i.'-releasetentative" class="fauxcheckbox showoncheck"/>
										<label for="pub-'.$pub_i.'-releasetentative" class="tooltip" title="A release date that is subject to change, or isn\'t a fixed day. For example: Q1 2015, Spring 2015, March 2015">Tentative</label>
										<div style="margin-right:90px; margin-left:41px;">
											<input type="text" id="pub-'.$pub_i.'-release" name="publications['.$pub_i.'][release]" value="'.$pub['release'].'" size="10" placeholder="Release date"/>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="fauxselect">
											<input type="text" id="pub-'.$pub_i.'-platform" class="fauxselect-input fauxselect-autocomplete" name="publications['.$pub_i.'][platform]" value="'.htmlSC($pub['platform']).'" placeholder="Platform"/>
											<ol class="fauxselect-options" style="width:300px; max-height:159px;">'.$platforms_options.'</ol>
										</div>
									</td>
									<td rowspan="2">
										<div class="pubimg">
											'.$img_fields[1]['output'].'
										</div>
									</td>
								</tr>
								<tr>
									<td class="pub-mediadist">
										<input type="checkbox" name="publications['.$pub_i.'][distribution]" value="digital" '.($pub['distribution'] == "digital" ? 'checked' : '').' class="input-distribution fauxcheckbox showoncheck" id="pub-'.$pub_i.'-digital"/>
										<label for="pub-'.$pub_i.'-digital" class="tooltip" title="This publication is a digital download rather than a physical retail release">Digital</label>
										<div class="fauxselect fauxselect-autocomplete" style="margin-right:100px">
											<input type="text" name="publications['.$pub_i.'][media_distribution]" value="'.htmlSC($pub['media_distribution']).'" placeholder="Media / Distribution platform" id="pub-'.$pub_i.'-media" class="fauxselect-input tooltip" title="The format this publication was distributed in"/>
											<ol class="fauxselect-options" style="width:300px; max-height:159px;">'.$mediadist_options.'</ol>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="fauxselect fauxselect-autocomplete">
											<input type="text" id="pub-'.$pub_i.'-publisher" name="publications['.$pub_i.'][publisher]" value="'.htmlSC($pub['publisher']).'" placeholder="Publisher" class="fauxselect-input"/>
											<ol class="fauxselect-options" style="width:300px; max-height:159px;">'.$publishers_options.'</ol>
										</div>
									</td>
									<td rowspan="2">
										<div class="pubimg">
											'.$img_fields[2]['output'].'
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<textarea name="publications['.$pub_i.'][notes]" placeholder="Release notes" class="inputfield">'.$pub['notes'].'</textarea>
									</td>
								</tr>
							</tbody>
						</table>
					</div>';
				return $ret;
			
			case "online":
				$online_networks = array();
				if($data && $data->count()){
	  			foreach($data->children() as $ch) $online_networks[] = $ch;
	  		}
				$query = "SELECT `title` FROM pages_links LEFT JOIN pages ON (pages_links.from_pgid = pages.pgid) WHERE (`to` = 'Online gaming network') AND `namespace` = 'Category' AND `redirect_to` = '' ORDER BY `title`";
				$res = mysqli_query($GLOBALS['db']['link'], $query);
				$ret.= '<ol style="margin:0; padding:0; list-style:none;">';
				while($row = mysqli_fetch_assoc($res)){
					$ret.= '<li><label><input type="checkbox" name="online_network[]" value="[[Category:'.htmlsc($row['title']).']]" '.(in_array('[[Category:'.$row['title'].']]', $online_networks) ? 'checked' : '').'> '.$row['title'].'</label></li>';
				}
				$ret.='<li><label><input type="checkbox" name="online_network[]" value="other" '.(in_array('other', $online_networks) ? 'checked' : '').'> other</label></li></ol>';
				break;
				
			case "credits":
				$ret.= 
					'<textarea name="credits" class="autosize tagging focusonme" id="inp-credits" wrap="off">'.(string)$this->data->credits.'</textarea>'.
					'<table border="0" cellpadding="5" cellspacing="0" style="margin:7px 1px; border:1px solid #CCC;"><tr><td><label class="tooltip" title="Credits copied directly from the credits sequence of the game, or from a source claiming so (ie Mobygames)."><input type="radio" name="credits_source" value="official" '.($this->data->credits['source'] == "official" ? 'checked="checked"' : '').'/> Official credits</label></td><td><label class="tooltip" title="Credits from another source, such as Wikipedia."><input type="radio" name="credits_source" value="unofficial" '.($this->data->credits['source'] != "official" ? 'checked="checked"' : '').'/> Other source</label></td></tr>'.
					'<tr><td style="border-top:1px solid #CCC;"><label><input type="radio" name="credits_completion" value="complete" '.($this->data->credits['completion'] == "complete" ? 'checked="checked"' : '').'/> Complete credits</label></td><td style="border-top:1px solid #CCC;"><label><input type="radio" name="credits_completion" value="incomplete" '.($this->data->credits['completion'] != "complete" ? 'checked="checked"' : '').'/> Partial/Incomplete</label></td></tr></table>';
				break;
			
			case "characters":
			case "locations":
				$ret.= '<input type="hidden" name="inputtype['.$field.']" value="'.($data['inputtype'] == "open" ? "open" : "list").'" id="'.$field.'-inputtype"/>';
				$ret.= '<ul class="datalist inputtype list '.($data['inputtype'] != "open" ? "" : "hidden").'">';
				$child_name = substr($field, 0, -1);
				if($inputtype != "open" && $data->{$child_name}[0]){
			  	$links = new pglinks();
			  	$links->attr['target'] = "_blank";
				  foreach($data->children() as $ch){
				  	$ret.= '<li data-tag="'.htmlsc($ch).'">'.$links->parse($ch).'<a class="rm"></a><textarea name="'.$field.'[]">'.$ch.'</textarea></li>';
				  }
				}
				$ret.= '</ul>';
				$ret.= '<div class="inputtype open '.($data['inputtype'] == "open" ? "" : "hidden").'">'.
  				'<textarea name="'.$field.'[open]" id="inp-'.$field.'" class="wmd-input autosize tagging">'.(string)$data.'</textarea>
  			</div>';
				break;
			
			case "personal":
				include $_SERVER['DOCUMENT_ROOT']."/bin/php/country_codes.php";
				$dob = $this->data->dob ? explode("-", $this->data->dob) : array('0000', '00', '00');
				$year[$dob[0]] = 'selected';
				$month[$dob[1]] = 'selected';
				$day[$dob[2]] = 'selected';
				$ret.= '<select name="dob[0]">
					<option value="0000">year</option>';
					for($y=date('Y')-15; $y >= 1900; $y--) $ret.= '<option value="'.$y.'" '.$year[$y].'>'.$y.'</option>';
			  	$ret.='<option value="0000">??</option></select> <select name="dob[1]"><option value="00">month</option>
			  			<option value="01" '.$month['01'].'>Jan</option>
			  			<option value="02" '.$month['02'].'>Feb</option>
			  			<option value="03" '.$month['03'].'>March</option>
			  			<option value="04" '.$month['04'].'>April</option>
			  			<option value="05" '.$month['05'].'>May</option>
			  			<option value="06" '.$month['06'].'>June</option>
			  			<option value="07" '.$month['07'].'>July</option>
			  			<option value="08" '.$month['08'].'>Aug</option>
			  			<option value="09" '.$month['09'].'>Sept</option>
			  			<option value="10" '.$month['10'].'>Oct</option>
			  			<option value="11" '.$month['11'].'>Nov</option>
			  			<option value="12" '.$month['12'].'>Dec</option>
			  			<option value="00">??</option>
			  		</select> <select name="dob[2]"><option value="00">day</option>';
			  		for($d=1; $d <= 31; $d++){
			  			if($d < 10) $d = '0'.$d;
			  			$ret.= '<option value="'.$d.'" '.$day[$d].'>'.$d."</option>\n";
			  		}
			  		$ret.= '<option value="00">??</option></select> Birthday
					<p></p>
					<select name="nationality">
						<option value="">Unknown</option>';
						foreach($cc as $code => $countryname) $ret.= '<option value="'.$code.'"'.($code == $this->data->nationality ? ' selected' : '').'>'.$countryname.'</option>';
						$ret.= '
					</select> Nationality
					';
				break;
			
  		default:
  			$ret.= '<textarea name="'.$field.'" rows="4" class="focusonme">'.$this->data->{$field}.'</textarea>';
  	}
  	
  	$ret.= '<!--end input--><div style="margin:5px 0 0 1px;"><button type="submit" class="pgedin-submit" rel="'.$field.'" title="Save changes to this field" style="font-weight:bold">Save</button> <button type="button" class="pgedin-cancel" rel="'.$field.'" id="pgedin-cancel-'.$field.'" title="Cancel changes to this field"">Cancel</button></div></form>';
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
		$i = 0;
		while($row = mysqli_fetch_assoc($res)){
			
			if($row['total_score'] < .5) continue;
			
			$contr[$row['usrid']] = $row['total_score'];
			
			//watching
			$q = "SELECT * FROM pages_watch WHERE usrid='".$row['usrid']."' AND `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' LIMIT 1";
			if(mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $contr[$row['usrid']] = $contr[$row['usrid']] + 1;
			
			//sblog posts
			$q = "SELECT * FROM posts_tags LEFT JOIN posts USING(nid) WHERE tag = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' AND posts.usrid='".$row['usrid']."'";
			if($sblogs = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $contr[$row['usrid']] = $contr[$row['usrid']] + ($sblogs * .5);
			
			//images
			$q = "SELECT * FROM images_tags LEFT JOIN images USING(img_id) WHERE tag = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' AND images.usrid='".$row['usrid']."'";
			if($images = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q))) $contr[$row['usrid']] = $contr[$row['usrid']] + ($images * .2);
			
		}
		
		arsort($contr);
		foreach($contr as $key => $val){
			$this->contributors[(int)$key] = $val;
		}
		$contr_new = count($this->contributors) ? array_keys($this->contributors) : array();
		
		if(!$nopub){
			$q = "UPDATE pages SET contributors = '".json_encode($contr_new)."' WHERE title='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' LIMIT 1";
			mysqli_query($GLOBALS['db']['link'], $q);
		}
		
		return $contr_new[0];
		
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
			),
			"template" => array()
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
			$result = $this->data->xpath("//publication");
			$json['platforms'] = array();
			//while(list( , $node) = each($result)){
			foreach($result as $foobar => $node){
				$pf = (string)$node->platform;
				$pglinks = new pglinks();
				if($exlinks = $pglinks->extractFrom((string)$node->platform)){
					$pf = $exlinks[0]['tag'];
				}
				if(!in_array($pf, $json['platforms'])) $json['platforms'][] = $pf;
				if($node['primary']){
					$xml_->addChild("platform", htmlspecialchars($pf));
					$json['platform'] = $pf;
					$rel = (string)$node->release_year."-".(string)$node->release_month."-".(string)$node->release_day;
					$xml_->addChild("release", $rel);
					$json['release'] = $rel;
				}
			}
			
			//first release
			$result = $this->data->xpath("//publication[1]");
			if($node = $result[0]){
				$rel = (string)$node->release_year."-".(string)$node->release_month."-".(string)$node->release_day;
				$xml_->addChild("first_release", $rel);
				$json['first_release'] = $rel;
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
			<h2><?=(!$this->row ? 'Page not yet started' : 'A <b>'.$this->type.'</b> added to the database on <b>'.formatDate($this->row['created']).'</b> by <b>'.outputUser($this->row['creator'], FALSE).'</b>; &nbsp; Last edited <b>'.timeSince($this->row['modified']).' ago</b>; &nbsp; Page ID #<b>'.$this->pgid)?></b></h2>
			
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
						<div id="watchpages">
							<input type="checkbox" name="watch" value="<?=htmlsc($this->title)?>" <?=($is_watching ? 'checked' : '')?> class="fauxcheckbox watchpage" id="watchpage"/>
							<label for="watchpage" class="tooltip" title="Closely monitor pages you're watching">Watch</label>
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
			
			<h3><span class="arrow"></span> Submit</h3>
			
			<!-- METADATA & SUBMISSION FIELDS -->
			<div id="pged-submit-outer">
				<form id="pged-submit" name="pged-submit">
					
					<?=$this->formHandle()?>
					<?=($this->editsource ? '<input type="hidden" name="editsource" value="'.$this->editsource.'"/>' : '')?>
					
					<div style="float:right; width:250px;">
						<fieldset style="padding:15px; background-color:#EEE;">
							<legend>Edit Options</legend>
							<?
							if(!$watching = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM pages_watch WHERE `title`='".mysqli_real_escape_string($GLOBALS['db']['link'], $this->title)."' AND usrid='".$usrid."' LIMIT 1"))){
							?>
								<label>
									<input type="checkbox" name="watch" value="1"<?=($this->juststarted && !$this->new_redirect ? ' checked="checked"' : '')?>/> 
									Watch this page <a title="Track changes made by other users to this page. You can even receive e-mail notifications if you set this option on your account preferences page (from the top menu: <?=$GLOBALS['usrname']?> &gt; Your Account &gt; Preferences)" class="tooltip" href="#help">?</a>
								</label><p style="margin:5px 0 0;"></p>
								<?
							}
							?>
							<label><input type="checkbox" name="minoredit" value="1"<?=($this->sessionrow->minoredit || $this->new_redirect ? ' checked="checked"' : '')?>/> This is a minor edit <a href="#help" class="tooltip" title="Mark this edit as minor if it only corrects spelling or formatting, performs minor rearrangements of text, or tweaks only a few words or inconsequential attributes.">?</a></label>
							<?
							if($GLOBALS['usrrank'] > 6){
								?><p style="margin:5px 0 0;"></p><label><input type="checkbox" name="withholdpts" value="1"<?=($this->sessionrow->withholdpts || $this->new_redirect ? ' checked="checked"' : '')?>/> Withhold my points for this edit</label><?
							}
							?>
						</fieldset>
						<fieldset style="margin-top:-1px; padding:15px; background-color:#EEE;">
							<a href="<?=$this->url?>" title="Close this edit session without publishing changes" class="red tooltip">Cancel</a> or
							<a href="edit.php?destroysession=<?=$this->sessid?>&returnonfail=<?=formatNameURL($this->title)?>" title="Completely destroy this edit session" class="red tooltip">Destroy</a> this session
						</fieldset>
					</div>
					
					<div style="margin-right:270px;">
						
						<fieldset id="editsummary" style="padding:8px 15px 15px; background-color:#EEE;">
							<legend>Edit Summary</legend>
							Please briefly summarize edits, making clear your intention and purpose for editing. This will help keep better records and allow the editors and future contributors to better understand your contributions.
							<div style="margin-top:5px; margin-right:5px;">
								<textarea name="edit_summary" rows="2" tabindex="2" id="edit_summary" style="width:100%;" onfocus="$.address.value('?')"><?=$this->sessionrow->edit_summary?></textarea>
							</div>
						</fieldset>
						
						<div class="buttons" style="margin-top:20px;">
							<button type="button" class="blue" id="pged-submitbtn" tabindex="3" style="font-weight:bold; font-size:14px; padding:5px 10px;">Publish Changes</button>
						</div>
						
					</div>
					
				</form>
			</div>
			
			<div class="tracker">
				<span id="draftsaved"><i style="opacity:.5">Not saved yet</i></span>&nbsp;&nbsp;
				<button type="button" disabled="disabled" id="act-savenow" onclick="changes.saveAll()" style="display:none">Save Draft</button>
				<span id="permalink">&nbsp;&nbsp;<a href="history.php?view_version=<?=$this->sessid?>" title="Permanent link to this version" target="_blank" style="text-decoration:none;">&infin; <u>Permalink</u></a></span>
			</div>
			<? $compl = $this->calculateCompletion(); ?>
			<div id="completion">
				<b>Page Completion</b>
				<div id="completion-pc"><?=$compl?>%</div>
				<div id="completion-scale"><span style="width:<?=$compl?>%"></span></div>
				<div id="completion-needed" class="tooltip-bubble above"><?=($compl != 100 ? 'Still needed: '.implode(", ", $this->completion_needed) : '')?></div>
				<div id="completion-victory"><img src="/bin/img/icons/sprites/ken_victory.png" width="33" height="107"/></div>
			</div>
			<div class="switch editswitcher">
				<div class="editswitcher-inset"></div>
				<div class="editswitcher-nub left" id="pgedswitchnub"></div>
				<a href="#edit" title="Edit" class="editswitcher-edit">Edit</a>
				<a href="#preview" title="Preview" class="editswitcher-view">Preview</a>
			</div>
			
			<div id="msg-draftsaved" class="tooltip-stationary">
				A draft of your changes has been saved! Your changes haven't been published yet though. In the meantime, you can continue to develop your article, ask others to help work on it, and eventually publish it when it's ready. Your draft will be saved indefinitely; Pick up where you left off at any time. You can safely exit this page.
			</div>
			<?
			
			$page->closeSection();
			
		}
		
		$page->footer();
		exit;
	}
	
	function calculateCompletion(){
		$this->completion = 0;
		$this->completion_needed = array();
		$compl = 0;
		$num = 0;
		
		//Stuff for all pages
		if((string)$this->data->content) $compl++; else $this->completion_needed[] = ".Incyclopedia Article";
		if((string)$this->data->rep_image) $compl++; else $this->completion_needed[] = "Main Picture";
		
		switch($this->type){
			
			case "game":
				if((string)$this->data->description) $compl++; else $this->completion_needed[] = "Description";
				if($this->data->genres->genre[0]) $compl++; else $this->completion_needed[] = "Genre(s)";
				if($this->data->developers->developer[0]) $compl++; else $this->completion_needed[] = "Developer(s)";
				if($this->data->publications->publication[0]) $compl++; else $this->completion_needed[] = "Publication(s)";
				if((string)$this->data->credits && $this->data->credits['completion'] == "complete") $compl++; else $this->completion_needed[] = "Complete Credits";
				if((string)$this->data->characters || $this->data->characters->character[0]) $compl++; else $this->completion_needed[] = "Characters";
				if((string)$this->data->locations || $this->data->locations->location[0]) $compl++; else $this->completion_needed[] = "Game Locations";
				if((string)$this->data->img_titlescreen && (string)$this->data->img_gameplay_1 && (string)$this->data->img_gameplay_2 && (string)$this->data->img_gameplay_3 && (string)$this->data->img_gameover) $compl++; else $this->completion_needed[] = "Game Images";
				if((string)$this->data->video_trailer) $compl++; else $this->completion_needed[] = "Trailer";
				break;
				
			case "person":
				if($this->data->credits_list && count($this->data->credits_list->children())) $compl++; else $this->completion_needed[] = "Credits";
				if($this->data->developers && count($this->data->developers->children())) $compl++; else $this->completion_needed[] = "Developers";
				if($this->data->roles && count($this->data->roles->children())) $compl++; else $this->completion_needed[] = "Roles";
				if((string)$this->data->dob) $compl++; else $this->completion_needed[] = "Birthdate";
				if((string)$this->data->nationality) $compl++; else $this->completion_needed[] = "Nationality";
				break;
		}
		
		$num = $compl + count($this->completion_needed);
		
		$total = $compl / $num * 100;
		$this->completion = round($total);
		return $this->completion;
		
	}
}

class catTree {
	
	function loop($parent){
		
		$this->files[] = $parent;
		//if($this->debug) echo "\nFinding children of <b>$parent</b> ";//
		$query = "SELECT `title` FROM pages_links LEFT JOIN pages ON (from_pgid = pgid) WHERE `to` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $parent)."' AND `ancestor` = 'parent' ORDER BY `title`";
		$res = mysqli_query($GLOBALS['db']['link'], $query);
		if(!mysqli_num_rows($res)) return;
		$this->tree->{$parent} = $this->tree->{$parent}->addChild('ul');
		while($row = mysqli_fetch_assoc($res)) $children[] = $row['title'];
		foreach($children as $child){
			$this->tree->{$child} = $this->tree->{$parent}->addChild('li', htmlentities($child, ENT_COMPAT | ENT_XML1, "UTF-8"));
		}
		foreach($children as $child){
			$this->loop($child);
		}
		
	}
	
}
	

function categoryTreeTemplate($title){
	
	$c = new catTree($title);
	
	if($GLOBALS['debug']) {
		$c->debug = true;
		echo "<pre>Create category tree template [source: $title]\n";//
	}
	
	try{ $pg = new pg($title); }
	catch(Exception $e){ if($c->debug) die("Page not found."); return false; }
	
	$parents = array();
	$q = "SELECT `to` FROM pages_links WHERE from_pgid = '$pg->pgid' AND ancestor = 'parent'";
	$r = mysqli_query($GLOBALS['db']['link'], $q);
	while($row = mysqli_fetch_assoc($r)){
		$parents[] = $row['to'];
	}
	
	if(count($parents)){
		
		$has_parent = true;
		$parent = $parents[0];
		$child = $title;
		
		//find the root ancestor
		while($has_parent==true){
			if($i++ > 50) break;
			$children[$parent][] = $child;
			$q = "SELECT `to` FROM pages_links LEFT JOIN pages ON (from_pgid = pgid) WHERE `title` = '".mysqli_real_escape_string($GLOBALS['db']['link'], $parent)."' AND `ancestor` = 'parent' LIMIT 1";
			if(!$row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
				$has_parent = false;
			} else {
				$child = $parent;
				$parent = $row['to'];
			}
		}
		
		$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><tree></tree>');
		$tree = $xml->addChild('tree');
		$c->ul = $tree->addChild('ul');
		$c->tree = new stdClass();
		$c->tree->{$parent} = $c->ul->addChild('li', htmlentities($parent, ENT_COMPAT | ENT_XML1, "UTF-8"));
		
		$c->loop($parent);
		
		$xml = '<?xml version="1.0"?>'."\n".$c->ul->asXML()."\n";		
		$xml = preg_replace('@\<li\>([^\<]+)@', '<li>[[\1]]', $xml);
		
	} else {
		
		if($c->debug) echo "No parents found\n";
		$xml = '<?xml version="1.0"?>';
		$c->files[] = $title;
		
	}
	
	if($c->debug) echo '<fieldset><legend>Result</legend>'.links($xml)."\n".htmlentities($xml).'</fieldset>';//
	
	if($c->debug) echo '<br/><fieldset><legend>Saved Files</legend>';
	foreach($c->files as $file){
		$file_loc = "/pages/xml/categorytrees/".formatNameURL($file, 1).".xml";
		file_put_contents($_SERVER['DOCUMENT_ROOT'].$file_loc, $xml);
		if($c->debug) echo "<a href=$file_loc target=_blank>$file_loc</a>\n";
	}
	if($c->debug) echo '</fieldset>';
	
	if($c->debug) echo '</pre>';
	
}