<?php

// DEPRECIATED

use Vgsite\Exceptions\LoginException;

/**
 * Process HTTPPOST input, check creds, update relevant info, redirect
 * Can be called independently via AJAX or included in a PHP file
 */

require_once dirname(__FILE__) . '/../config/bootstrap_app.php';

    if (!isset($_POST['login'])) {
        throw new LoginException('No login credentials found', 400);
    }

    $username = filter_input(INPUT_POST, "username");
    if (!$username) {
        throw new LoginException('Username or email is required to login', 400);
    }

    $email = '';
    if(strstr($username, "@")) {
        $email = filter_var($username, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new LoginException("The e-mail address '$email' couldn't be validated. Please try again!", 400);
        }
    }

    $password = filter_input(INPUT_POST, "password");
    if (!$password) {
        throw new LoginException('Password is required.', 400);
    }

    $user_mapper = new UserMapper();
    
    $user = isset($email) ? $user_mapper->findByEmail($email) : $user_mapper->findByUsername($username);
    if (is_null($user) || password_verify($password, $user->getPassword()) === false) {
        throw new LoginException('Invalid username or password', 401);
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
} catch (Exception $e) {
    switch ($e->getCode()) {
        case 401:
            header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized", true, 401);
            break;
        default:
            header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", false, 400);
    }

    echo str_replace($login_error_render, '{LoginError}', $e->getMessage());

    exit;
}

/**
 * Post-login buisiness
 */

try {
    $user_details = $user_mapper->getAllDetails($user);

    // check for new badges earned since last login
    // return array badge IDs
    $sql = "SELECT bagde_id FROM badges_earned WHERE user_id=? AND `new`=1";
    $statement = $pdo->prepare($sql);
    $statement->execute([$user->getId()]);
    if ($rows = $statement->fetchAll(PDO::FETCH_COLUMN)) {
        $_SESSION['newbadges'] = $rows;
    }

    //check birthday badge
    if (substr($user_details['dob'], 5) == date("m-d")) {
        Badge::findById(37)->earn($user);
    }

    // Update activity
    $sql = sprintf("UPDATE users SET activity='%s', previous_activity='%s' WHERE user_id=%d LIMIT 1", date("Y-m-d H:i:s"), $user->getDetail('activity'), $user->getId());
    $pdo->query($sql);

    //record current scores and counts
    $score = new UserScore($user);
    $score->save();

    //fb login
    $check_1 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE user_id=".$user->getId()." AND oauth_provider='facebook' LIMIT 1"));
    $check_2 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE user_id=".$user->getId()." LIMIT 1"));
    if (!$fbuser && $check_1) {
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
    } elseif (!$check_1 && !$check_2 && $user_details['registered'] != $user_details['activity']) {
        $GLOBALS['no_oauth'] = true;
    }

    if($_POST['remember']) {
        //TODO
    }
} catch (Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
    echo str_replace($login_error_render, '{LoginError}', $e->getMessage());

    exit;
}

header($_SERVER["SERVER_PROTOCOL"]." 200 OK", true, 200);
