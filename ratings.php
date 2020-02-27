<?
require ("bin/php/page.php");

$loc = getenv("HTTP_REFERER");
header("Location: $loc");
if(!$usrid) die("You must be logged in to rate");

if(!$gid = $_POST['gid']) die("Error: no game id given");
$grade = $_POST['grade'];
if($grade == "") die("Error: No rating given");

if($_POST['table'] == "forecast") {
	
	$q = "SELECT * FROM games_forecasts WHERE gid='$gid' AND usrid='$usrid' LIMIT 1";
	if(mysql_num_rows(mysql_query($q))) {
		$query = "UPDATE games_forecasts SET `rating`='$grade', `datetime`='".date("Y-m-d H:i:s")."' WHERE usrid='$usrid' AND gid='$gid' LIMIT 1";
	} else {
		$query = "INSERT INTO games_forecasts (gid, usrid, `rating`, `datetime`) VALUES 
			('$gid', '$usrid', '$grade', '".date('Y-m-d H:i:s')."')";
	}

} else {
	
	if($grade == "null") {
		$query = "DELETE FROM games_grades WHERE gid='$gid' AND usrid='$usrid' LIMIT 1";
	} else {
		$q = "SELECT * FROM games_grades WHERE gid='$gid' AND usrid='$usrid' LIMIT 1";
		if(mysql_num_rows(mysql_query($q))) {
			$query = "UPDATE games_grades SET grade='$grade', `datetime`='".date("Y-m-d H:i:s")."' WHERE gid='$gid' AND usrid='$usrid' LIMIT 1";
		} else {
			$query = "INSERT INTO games_grades (gid, usrid, `grade`, `datetime`) VALUES 
				('$gid', '$usrid', '$grade', '".date("Y-m-d H:i:s")."')";
		}
	}

}

if(!mysql_query($query)) {
	sendBug("User couldn't insert forecast rating.\n\nQUERY: $query");
	die("Error: Couldn't update the database! The administrative staff has been notified of this error.");
}

?>