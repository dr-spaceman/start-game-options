<?php

namespace Vgsite;

$img_sizes = array(
    'tn'         => 'tn',
    'thumb'      => 'tn',
    'thumbnail'  => 'tn',
    'screen'     => 'ss',
    'screenshot' => 'ss',
    'ss'         => 'ss',
    'small'      => 'sm',
    'sm'         => 'sm',
    'medium'     => 'md',
    'med'        => 'md',
    'md'         => 'md',
    'large'      => 'op',
    'lg'         => 'op',
    'optimal'    => 'op',
    'op'         => 'op',
    'default'    => 'op',
    'original'   => 'or',
);
$img_sizes_widths = array(
    'tn' => 100,
    'ss' => 200,
    'sm' => 240,
    'md' => 350,
    'op' => 620
);
//This string includes resized image sizes that use the original aspect ratio
$img_normal_sizes_widths = array(
    'tn' => 100,
    'sm' => 240,
    'md' => 350,
    'op' => 620
);

class Image extends DomainObject
{
    public const IMAGES_DIR = ROOT_DIR.'/public/images';
    public const UPLOAD_TEMP_DIR = ROOT_DIR.'/var/uploads';
    public const DELETED_FILES_DIR = ROOT_DIR.'/var/deleted_files';
    public const PROPERTIES_KEYS = ['img_name','img_id','img_session_id','img_size',
        'img_width','img_height','img_bits','img_minor_mime','img_category_id',
        'img_title','img_description','sort','user_id'];

    /**
     * Image Categories
     */

    public const SCREENSHOT = 1;
    public const SCREENSHOT_TITLE = 11;
    public const SCREENSHOT_ENDING = 3;
    public const SCREENSHOT_CREDITS = 12;
    public const SCREENSHOT_GAMEOVER = 14;
    public const BOXART = 4;
    public const BOXART_DERIVATION = 16;
    public const OFFICIALART = 3;
    public const OFFICIALART_CONCEPT = 17;
    public const FANART = 8;
    public const SCAN = 2;
    public const COMMERCIAL = 5;
    public const PHOTO = 6;
    public const MAP = 7;
    public const WALLPAPER = 9;
    public const SPRITES = 10;
    public const LOGO = 15;

    protected static $categories = [
        self::SCREENSHOT => 'Screenshots, General',
        self::SCREENSHOT_TITLE => 'Screenshots, Title screen',
        self::SCREENSHOT_ENDING => 'Screenshots, Ending',
        self::SCREENSHOT_CREDITS => 'Screenshots, Credits',
        self::SCREENSHOT_GAMEOVER => 'Screenshots, Game over',
        self::BOXART => 'Box art, Official',
        self::BOXART_DERIVATION => 'Box art, Unofficial derivation or fan artwork',
        self::OFFICIALART => 'Official artwork, Illustration',
        self::OFFICIALART_CONCEPT => 'Official artwork, Concept art',
        self::FANART => 'Fan art',
        self::SCAN => 'Scans',
        self::COMMERCIAL => 'Commercial',
        self::PHOTO => 'Photos',
        self::MAP => 'Maps',
        self::WALLPAPER => 'Wallpaper',
        self::SPRITES => 'Pixel Art or Sprites',
        self::LOGO => 'Logos',
    ];

    protected static $categories_descriptions = [
        self::SCREENSHOT => 'Captures of the game during play',
        self::SCREENSHOT_TITLE => 'Capture of the introduction of the game',
        self::SCREENSHOT_ENDING => 'Capture of the game ending',
        self::SCREENSHOT_CREDITS => 'Capture of the game credits',
        self::SCREENSHOT_GAMEOVER => 'Capture of game over screen',
        self::BOXART => 'Scans of retail cover art and packaging',
        self::BOXART_DERIVATION => 'Theoretical or fan-made box art; Box art that was never printed for retail release',
        self::OFFICIALART => 'Official illustrations',
        self::OFFICIALART_CONCEPT => 'Concept art or sketches by an artist officially commissioned by the game publisher',
        self::FANART => 'Unofficial works rendered by a fan',
        self::SCAN => 'Full-page scans from print media',
        self::COMMERCIAL => 'Products, accessories, advertisements, etc.',
        self::PHOTO => 'Photographs of a person, place, or thing',
        self::MAP => 'Maps and visual guides of places',
        self::WALLPAPER => 'Background images for computers and mobile phones',
        self::SPRITES => 'Pixelated sprite graphics from a game',
        self::LOGO => 'Logos, icons, or other graphic marks or emblems',
    ];

    /**
     * Object properties
     */

    public $notfound = true;

    /**
     * Session ID for uploads
     * @var [type]
     */
    public $img_session_id;
    public $img_name = 'unknown.png';
    public $img_id;
    public $optimized; // t or f if optimized size exitst
    public $src;
    public $img_size = 3810;
    public $img_width = 601;
    public $img_height = 601;
    public $img_minor_mime = "png";
    public $img_category_id;
    public $img_title;
    public $img_description;
    public $sort;
    
    /**
     * Image construction
     * @param array $params Key-value pairs relevant to an image, e.g. DB row
     * Image::PROPERTIES_KEYS holds list of keys
     */
    public function __construct(array $params)
    {
        # var $img_params string Either the numeric img_id or the img_name (ie "DonkeyKong.jpg")
        # if not found, sets $notfound=true and grabs "unknown.png", a placeholder image
        
        if (empty($params) || !isset($params['img_id'])) {
            throw new InvalidArgumentException("Image class requires at least an img_id parameter passed to the constructor");
        }

        parent::__construct($params['img_id']);
        
        $this->img_name = $params['img_name'];
        $this->img_id = $params['img_id'];
        $this->img_session_id = $params['img_session_id'];
        $this->img_size = $params['img_size'];
        $this->img_width = $params['img_width'];
        $this->img_height = $params['img_height'];
        $this->img_bits = $params['img_bits']; 
        $this->img_minor_mime = $params['img_minor_mime'];
        $this->img_category_id = $params['img_category_id'];
        $this->img_title = $params['img_title'];
        $this->img_description = $params['img_description'];
        $this->sort = $params['sort'];
        $this->user_id = $params['user_id'];
        $this->img_timestamp = $params['img_timestamp'];
        $this->img_views = $params['img_views'];
        
        $this->src = $this->getSrc();
        
        if ($this->img_name && $this->img_id) {
            $this->notfound = false;
        }
    }

    public function setId(int $id)
    {
        $this->img_id = $id;
        parent::setId($id);
    }

    public function getProperties(): array
    {
        $props = array();
        foreach (self::PROPERTIES_KEYS as $key) {
            $props[$key] = $this->{$key};
        }

        return $props;
    }

    public function getDir()
    {
        return self::IMAGES_DIR.'/'.substr($this->img_session_id, 12, 7);
    }

    public function getUrl()
    {
        return '/image/'.$this->img_name;
    }
    
    /**
     * Return a list of img files and URLs for local access
     * @return [type] [description]
     */
    public function getSrc()
    {
        $src['original'] = '';
        $src['dir'] = $this->getDir();
        $src['original'] = $src['dir'].'/'.$this->img_name;
        $src['or']  = $src['original'];
        $src['url'] = $this->getUrl();
        $src['op']  = $src['dir'].'/op/'.$this->img_name;
        if (file_exists($src['op'])) {
            $this->optimized = true;
        } else {
            $this->optimized = false;
            $src['op'] = $src['original'];
        }
        $src['optimized'] = $src['op'];
        $src['md']  = $src['dir']."/md/".$this->img_name;
        if (!file_exists($src['md'])) {
            $src['md'] = $src['original'];
        }
        $src['box'] = $src['dir']."/box/".$this->img_name.".png";
        $src['sm']  = $src['dir']."/sm/".$this->img_name;
        $src['ss']  = $src['dir']."/ss/".$this->img_name.".png";
        $src['tn']  = $src['dir']."/tn/".$this->img_name.".png";

        return $src;
    }
    
        //@attr $rel image group
    /**
     * Render HTML tag
     * @param  string $size     One of the sizes in getSrc() like 'op', 'tn', etc.
     * @param  STRING $rel      iMAGE GROUP
     * @return string           HTML
     */
    public function render($size='op', $rel=null, $figstyle=null)
    {
        $alt = $this->img_title ? htmlsc($this->img_title) : $this->img_name;
        $src = $this->src[$size] ? $this->src[$size] : $this->src['op'];
        return '<div class="imagefigure"><a href="'.$this->src['url'].'" title="'.$alt.'" rel="'.$rel.'" class="imgupl" data-imgname="'.$this->img_name.'"><img src="'.$src.'" alt="'.$alt.'"/></a></div>';
    }

    public static function findByName(string $name): ?DomainObject
    {
        return self::getMapper()->findByName($name);
    }
    
    /**
     * Gget info about this image's session group, including previous & next files
     * @return [type] [description]
     */
    public function getSessionData()
    {
        return $this->getMapper()->getSession($this->img_session_id);
    }

    public static function getCategories(): array
    {
        return static::$categories;
    }

    public static function getCategoryName(int $category_id): string
    {
        if (!isset(static::$categories[$category_id])) {
            throw new \InvalidArgumentException('Category "'.$rank.'" is not defined, use one of: '.implode(', ', array_keys(static::$categories)));
        }

        return static::$categories[$category_id];
    }

    public static function getCategoryDescription(int $category_id): string
    {
        if (!isset(static::$categories[$category_id])) {
            throw new \InvalidArgumentException('Category "'.$rank.'" is not defined, use one of: '.implode(', ', array_keys(static::$categories)));
        }

        return static::$categories_descriptions[$category_id];
    }
}

class gallery {
    
    var $id;
    var $img_session_id;
    var $parsed; // t or f if parse()
    var $bbcode;
    var $html;
    var $caption;
    var $show;
    var $size;
    var $width;
    var $files; // array of img names, or str of {img} files ie '{img:foobar.jpg}{img:fuuuu.png}'
    var $opt_str; //str of options, ie caption=foo|show=3|50|thumbnail
    
    function __construct(){
        $this->id = rand(0, 99999);
    }
    
    function parse(){
        
        // parse data using $files and $opt_str
        
        if(!$this->files && !$this->opt_str) return false;
        
        if($this->opt_str){
            $opts = array();
            $opts = explode("|", $this->opt_str);
            foreach($opts as $opt){
                if(substr($opt, 0, 8)=="session=") $this->img_session_id = substr($opt, 8);
                if(substr($opt, 0, 8)=="caption=") $this->caption = substr($opt, 8);
                if(substr($opt, 0, 5)=="show=") $this->show = substr($opt, 5);
                if(in_array($opt, array_keys($GLOBALS['img_sizes']))) $this->size = $GLOBALS['img_sizes'][$opt];
                if(is_numeric($opt) === TRUE && (int)$opt == $opt) $this->width = $opt;
            }
        }
        
        if(is_numeric($this->show) === FALSE || (int)$this->show != $this->show) unset($this->show);
        if($this->width && $this->width < 25) $this->width = 25;
        if($this->width && $this->width > 240){ /*unset($this->width);*/ $this->size = "op"; }
        
        if(!$this->size && !$this->width) $this->size = 'tn';
        elseif(!$this->size && $this->width <= 100) $this->size = "tn";
        elseif(!$this->size && $this->width <= 240) $this->size = "sm";
        
        if(!in_array($this->size, array('tn', 'ss', 'sm', 'op'))) $this->size = 'tn';
        
        if($this->img_session_id){
            $q = "SELECT * FROM images_sessions WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->img_session_id)."' LIMIT 1";
            if(!$img_session = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))) return '{Error displaying gallery: session ID "'.$this->img_session_id.'" doesn\'t exist}';
            $query = "SELECT * FROM images WHERE img_session_id = '".mysqli_real_escape_string($GLOBALS['db']['link'], $this->img_session_id)."' ORDER BY `sort` ASC";
            $res   = mysqli_query($GLOBALS['db']['link'], $query);
            while($row = mysqli_fetch_assoc($res)){
                $img = new img($row['img_name']);
                $row['src'] = $img->src;
                $this->imgs[$row['img_name']] = $row;
            }
        } else {
            if(is_string($this->files)){
                //echo "STR:".htmlspecialchars($this->files);
                preg_match_all('@\{img:([a-z0-9-_!\.]+)\|?(?:.*?)\}@is', $this->files, $matches);
                if(count($matches[1])) $this->files = $matches[1];
                //echo "MATCHES:";print_r($matches);
            }
            if(is_array($this->files)){
                //echo "FILES:";print_r($this->files);
                foreach($this->files as $img_name){
                    $q = "SELECT * FROM images WHERE img_name = '".mysqli_real_escape_string($GLOBALS['db']['link'], $img_name)."' LIMIT 1";
                    if($row = mysqli_fetch_assoc(mysqli_query($GLOBALS['db']['link'], $q))){
                        $img = new img($img_name);
                        $row['src'] = $img->src;
                        $this->imgs[$row['img_name']] = $row;
                    }
                }
            }
        }
        
        $this->parsed = true;
        
    }
    
    function BBencode(){
        
        // build (or rebuild) a gallery code
        // sets code to $this->bbcode
        
        if(!$this->parsed) return false;
        
        if(is_array($this->files)){
            foreach($this->files as $file) $files_str.= '{img:'.$file.'}';
        } else {
            $files_str = $this->files;
        }
        
        $this->bbcode = '[gallery';
        if($this->img_session_id) $this->bbcode.= '|session='.$this->img_session_id;
        if($this->size) $this->bbcode.= '|'.$this->size;
        if($this->width) $this->bbcode.= '|'.$this->width;
        if($this->caption) $this->bbcode.= '|caption='.$this->caption;
        if($this->show) $this->bbcode.= '|show='.$this->show;
        $this->bbcode.= ']'.$files_str.'[/gallery]';
        
        return $this->bbcode;
        
    }
    
    function HTMLencode(){
        
        if(!$this->parsed) $this->parse();
        
        $ret = '<div class="gallery">'.
            ($this->caption ? '<div class="caption">'.$this->caption.'</div>' : '').
            '<div class="container">';
        if(is_array($this->imgs)){
            foreach($this->imgs as $img){
                $i++;
                $ret.= '<figure style="'.($this->show && $i > $this->show ? 'display:none;' : '').'"><a href="/image/'.$img['img_name'].'" class="imgupl" rel="'.$this->id.'" data-imgname="'.$img['img_name'].'" style="width:'.$this->width.'px;"><img src="'.$img['src'][$this->size].'"/></a></figure>';
            }
        }
        $ret.= '</div></div>'."\n\n";
        return $ret;
        
    }
    
}