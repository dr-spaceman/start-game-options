<?php

use Vgsite\Registry;
use Vgsite\User;

$current_user = null;

if ($_SESSION['logged_in'] && $_SESSION['user_id']) {
    try {
        /** @var User */
        $current_user = Registry::getMapper(User::class)->findById($_SESSION['user_id']);
        Registry::set('current_user', $current_user);
        $_SESSION['user_rank'] = $current_user->getRank();
        $_SESSION['username'] = $current_user->getUsername();
    } catch (Exception $e) {
        unset($_SESSION['logged_in'], $_SESSION['user_id']);
        echo $template->render('error.html', ['message' => 'There was an error registering your user session. We have deleted your user cookies. Try logging in again. Details: ' . $e->getMessage()]);
        exit;
    }

    // Dicouraged old variable references
    // $usrname = $current_user->getUsername();
    // $usrid = $_SESSION['user_id'];
    // $usrlastlogin = $current_user->getLastLogin();
}

if ($_SESSION['user_rank'] == User::RESTRICTED) {
    die("*");
}