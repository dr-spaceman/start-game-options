<?

// Upload and process a heading image

use Vgsite\Page;
use Verot\Upload;

$error = '';

if($_FILES['audiofile']['error']==0) {

do if($_POST && $_FILES['audiofile']['name']){

$dir = $_SERVER['DOCUMENT_ROOT']."/bin/uploads/audio/";

$flag = 0; // Safety net, if this gets to 1 at any point in the process, we don't upload.

$file = $_FILES['audiofile'];
$filename = $file['name'];
$filesize = $file['size'];
$mimetype = $file['type'];

$filename = htmlentities($filename);
$filesize = htmlentities($filesize);
$mimetype = htmlentities($mimetype);

$target_path = $dir . preg_replace("/[^a-z0-9_\-\.]/i", "", basename( $filename ) );

//Check for empty file
if($filename == ""){
	$error = "No File Exists!";
	break;
}

//Now we check that the file doesn't already exist.
if(file_exists($target_path)){
	$error = "The target file already exists";
	break;
}

//Whitelisted files - Only allow files with MP3 extention onto server...

$whitelist = array(".mp3");
foreach ($whitelist as $ending) {
if(substr($filename, -(strlen($ending))) != $ending) {
 $error = "The file type or extention you are trying to upload is not allowed! You can only upload MP3 files to the server!";
break;
}
}


//Now we check the filesize.  If it is too big or too small then we reject it
//MP3 files should be at least 1MB and no more than 6.5 MB

if($filesize > 6920600){
//File is too large

if($flag == 0){
$error = "The file you are trying to upload is too large! Your file can be up to 6.5 MB in size only. Please upload a smaller MP3 file or encode your file with a lower bitrate.";
}

$flag = $flag + 1;
}

if($filesize < 1048600 && $_SESSION['user_rank'] < 4){
//File is too small
$error = "The file you are trying to upload is too small. Your file has been marked as suspicious because our system has determined that it is too small to be a valid MP3 file.";
break;
}

//Check the mimetype of the file
$mp3_mimes = array(
    'audio/mpeg',
    'audio/x-mpeg',
    'audio/mp3',
    'audio/x-mp3',
    'audio/mpeg3',
    'audio/x-mpeg3',
    'audio/x-mpeg-3',
    'audio/mpg',
    'audio/x-mpg',
    'audio/x-mpegaudio',
    'video/mpeg',
    'video/x-mpeg',
);
if(!in_array($mimetype, $mp3_mimes)){
$error = "The file you are trying to upload does not contain expected data ($mimetype). Only MP3 files are allowed";
break;
}

//Check that the file really is an MP3 file by reading the first few characters of the file
/*$f = @fopen($file['tmp_name'],'r');
$s = @fread($f,3);
@fclose($f);
if($s != "ID3"){
$error = "The file you are attempting to upload does not appear to be a valid MP3 file. [$s]";
break;
}*/

//All checks are done, actually move the file...

if(@move_uploaded_file($file['tmp_name'], $target_path)) {
	$moved_file = $target_path;
} else{
    $error = "There was an error uploading the file, please try again!";
    if($_SESSION['user_rank'] >= 8) $error.= $file['tmp_name'] ." -- ". $target_path;
    break;
}

} while(false);

} else {
	if($_FILES['audiofile']['error']==1 || $_FILES['audiofile']['error']==1) $error = "The file is too large.";
}

?><?=Page::HTML_TAG?>
<head>
	<title>Videogam.in Sblog upload audio file</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script type="text/javascript" src="/bin/script/jquery-1.4.2.js"></script>
	<script type="text/javascript">
		function nnuploadaudioformsubmit(){
			$("form").hide();
			document.nnuploadaudioform.submit();
		}
		<?
		if($error){
			echo 'alert("'.htmlsc($error).'");';
		} elseif($moved_file){
			$moved_file = str_replace($_SERVER['DOCUMENT_ROOT'], "", $moved_file);
			$audio_filename = substr($moved_file, strrpos($moved_file, "/"));
			?>
			$('#inp-audiofile', window.parent.document).val('<?=$moved_file?>');
			$('#audiofile-link', window.parent.document).attr("src", "<?=$moved_file?>").text('<?=$audio_filename?>');
			$('#audiofile', window.parent.document).show().siblings('iframe').hide();
			<?
		}
		?>
	</script>
</head>
<body style="margin:0; padding:0; font:normal 13px arial; background-color:transparent; background-image:none;">
<form action="uploadaudio.php" method="post" name="nnuploadaudioform" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="7340032"/>
	<input type="file" name="audiofile" onchange="nnuploadaudioformsubmit()"/>&nbsp;&nbsp;<span style="color:#666">MP3 files only; Size limit is 7MB</span>
</form>
<div style="padding-left:22px; background:url('/bin/img/loading_ball.gif') no-repeat left center;">Uploading</div>
</body>
</html>
<?

function NNdie($msg) {
	$msg = str_replace("\n", "", $msg);
	$msg = addslashes($msg);
	?><script type="text/javascript">alert('<?=$msg?>');</script><?
	$GLOBALS['err'] = 1;
}