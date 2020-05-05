<?php

/**
 * Process HTTPPOST input, check creds, update relevant info, redirect
 */

require_once dirname(__FILE__) . '/../config/bootstrap.php';

try {
    if (!isset($_POST['login'])) {
        throw new Exception("No login credentials found");
    }

    $username = filter_input(INPUT_POST, "username");
    if (!$username) {
        throw new Exception('Username or email is required to login');
    }

    $email = '';
    if(strstr($username, "@")) {
        $email = filter_var($username, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception("The e-mail address '$email' couldn't be validated. Please try again!");
        }
    }

    $password = filter_input(INPUT_POST, "password");
    if (!$password) {
        throw new Exception('Password is required.');
    }

    $user_mapper = new UserMapper();
    
    $user = isset($email) ? $user_mapper->findByEmail($email) : $user_mapper->findByUsername($username);

    if (password_verify($password, $user->getPassword()) === false) {
        throw new Exception('Invalid password', 401);
    }

    // Re-hash password if necessary
    $currentHashAlgorithm = PASSWORD_DEFAULT;
    $passwordNeedsRehash = password_needs_rehash($user->getPassword(), $currentHashAlgorithm);
    if ($passwordNeedsRehash === true) {
        // Save new password hash
        $user->setPassword($password);
        $user_mapper->save($user);
    }

    $_SESSION['user_id'] = $user->getId();
    $_SESSION['logged_in'] = 'true';

    // Everything's ok... do something now
} catch (Exception $e) {
    switch ($e->getCode()) {
        case 401:
            header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized", true, 401);
        default:
            header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", false, 400);
            echo $e->getMessage();
    }
}

header($_SERVER["SERVER_PROTOCOL"]." 200 OK", true, 200);

try {

    $user_details = $user_mapper->getAllDetails($user);

// check for new badges earned since last login
// return array badge IDs
$sql = "SELECT bagde_id FROM badges_earned WHERE usrid=? AND `new`=1";
$statement = $pdo->prepare($sql);
$statement->execute([$user->getId()]);
$_SESSION['new_badges']
if ($rows = $statement->fetchAll(PDO::FETCH_COLUMN)) {
    $_SESSION['newbadges'] = $rows;
}

//badges
//check birthday
$dob = str_replace("-", "", $user_details['dob']);
$dob = substr($dob, 4);
if ($dob == date("md")) {
    Badges::earn(37, $user);
}

// Update activity
$sql = sprintf("UPDATE users SET activity='%s', previous_activity='%s' WHERE usrid=%d LIMIT 1", date("Y-m-d H:i:s"), $user->getDetail('activity'), $user->getId());
$statement = $pdo->query();

//record current scores and counts
$score = calculateScore($user);

if ($score['total'] >= 1) {
    $sql = "INSERT INTO users_data (usrid, `date`, ".implode(", ", array_keys($score['vars'])).", score_forums, score_pages, score_sblogs, score_total) VALUES 
        (?, '".date("Y-m-d")."', '".implode("', '", array_values($score['vars']))."', '".$score['forums']."', '".$score['pages']."', '".$score['sblogs']."', '".$score['total']."');";
    $statement = $pdo->prepare($sql);
    $statement->execute([$user->getId()]);
}

//fb login
$check_1 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE usrid='$usrid' AND oauth_provider='facebook' LIMIT 1"));
$check_2 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE usrid='$usrid' LIMIT 1"));
if(!$fbuser && $check_1) {
    require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/facebook.php";
    $fb = array(
      'appId'  => '142628175764082',
      'secret' => '5913f988087cecedd1965a3ed6e91eb1'
    );
    $facebook = new Facebook($fb);
    $fbuser = $facebook->getUser();
    if ($fbuser) {
      try {
        // Proceed knowing you have a logged in user who's authenticated.
        $fbuser_data = $facebook->api('/me');
      } catch (FacebookApiException $e) {
        error_log($e);
        $fbuser = null;
      }
    }
/*} elseif(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q)) && !mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q2)) && $_COOKIE['no_oauth'] != "ignore" && $userdat['registered'] != $userdat['activity']){
    setcookie("no_oauth", "1", time()+60*60*24*100, "/");*/
} elseif(!$check_1 && !$check_2 && $userdat['registered'] != $userdat['activity']){
    $GLOBALS['no_oauth'] = true;
}

//remember?
if($_POST['remember']) {
    // time()+60*60*24*100 = 100 days
    $usrsession = $_SESSION['usrid']."```".$_POST['password'];
    $usrsession = base64_encode($usrsession);
    setcookie("usrsession", $usrsession, time()+60*60*24*100, "/");
}
