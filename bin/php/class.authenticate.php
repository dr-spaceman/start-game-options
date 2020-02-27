<?

// Test authentication form:
/*if($_POST){
	print_r($_POST);
	$a_real = $auth_forms[base64_decode($_POST['auth_form_num'])]['a'];
	$a_given = trim(strtolower($_POST['auth_form_input']));
	$auth_passed = $a_real == $a_given ? true : false;
	echo " { $a_real ; $a_given } " . ($auth_passed ? " PASS " : " FAIL ");
}
$auth = new authenticate($_GET[n]);
?>
<form action="test.php?n=<?=($_GET[n] + 1)?>" method="post">
	<?=$auth->form?>
	<button type="submit">Submit</button>
</form>
<?*/

$auth_forms = array(
	array("q" => '', "a" => ''), // blank for $auth_form_num[0] (doesnt exist)
	array("q" => 'The __ of Zelda', "a" => "legend"),
	array("q" => 'Super __ World', "a" => "mario"),
	array("q" => 'Kirby\'s __ Land', "a" => "dream"),
	array("q" => 'Mike Tyson\'s __-Out!!', "a" => "punch"),
	array("q" => 'Metal __ Solid', "a" => "gear"),
	array("q" => 'Donkey __ Country', "a" => "kong"),
	array("q" => 'World of __Craft', "a" => "war"),
	array("q" => 'Mario\'s twin brother __', "a" => "luigi"),
	array("q" => 'Shigeru __', "a" => "miyamoto"),
	array("q" => 'Metroid heroine __ Aran', "a" => "samus"),
	array("q" => 'Sony __Station', "a" => "play"),
	array("q" => 'Lara Croft: __ Raider', "a" => "tomb"),
	array("q" => '__ the Hedgehog', "a" => "sonic"),
	array("q" => '"Thank you Mario! But our princes is in another __!"', "a" => "castle"),
	array("q" => 'Sega __cast', "a" => "dream"),
);

function checkAuthentication($auth_form_num, $given){
	
	# Check submitted authentication input
	# @var $auth_form_num .
	# @var given .
	# @return boolean
	
	global $auth_forms;
	$a_real = $auth_forms[base64_decode($auth_form_num)]['a'];
	$a_given = trim(strtolower($given));
	return $a_real == $a_given ? true : false;
	
}

class authenticate {
	
	public $auth_form_num;
	public $rand2;
	public $math1;
	public $math2;
	public $hidden;
	public $label;
	public $input;
	
	function __construct($n='') {
		global $auth_forms;
		$this->auth_form_num = $n ? $n : rand(1, 15);
		$this->form = '<div class="authform">' . str_replace("__", '<input type="text" name="auth_form_input" style="width:70px;margin:0;padding:0;font-size:13px !important;color:black;background-color:transparent;border-width:0 0 1px;border-style:solid;border-color:black;border-radius:0 !important;"/>', $auth_forms[$this->auth_form_num]['q']) . '<input type="hidden" name="auth_form_num" value="'.base64_encode($this->auth_form_num).'"/></div>';
	}
	
}
?>