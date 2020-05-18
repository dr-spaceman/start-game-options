<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/facebook.php";
use Vgsite\Page;

// Create our Application instance
$fb = array(
  'appId'  => '142628175764082',
  'secret' => '5913f988087cecedd1965a3ed6e91eb1'
);
$facebook = new Facebook($fb);

// Get User ID
$fbuser = $facebook->getUser();

// We may or may not have this data based on whether the user is logged in.
//
// If we have a $fbuser id here, it means we know the user is logged into
// Facebook, but we don't know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

if ($fbuser) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $fbuser_data = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $fbuser = null;
  }
}

$logoutUrl = $facebook->getLogoutUrl();
$loginUrl = $facebook->getLoginUrl(array("scope"=>"email,user_birthday,publish_stream"));

// This call will always work since we are fetching public data.
//$naitik = $facebook->api('/naitik');

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>php-sdk</h1>

    <?php if ($fbuser): ?>
      <a href="<?php echo $logoutUrl; ?>">Logout</a>
    <?php else: ?>
      <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>

    <h3>PHP Session</h3>
    <pre><?php print_r($_SESSION); ?></pre>

    <?php if ($fbuser): ?>
      <h3>You</h3>
      <img src="https://graph.facebook.com/<?php echo $fbuser; ?>/picture">

      <h3>Your User Object (/me)</h3>
      <pre><?php print_r($fbuser_data); ?></pre>
      
      <h3>Permissions</h3>
      <pre><?
      	$fb_permissions = $facebook->api(
			   '/me/permissions',
			   'GET',
			   array(
			      'access_token' => $access_token
			   )
			  );
			  print_r($fb_permissions);
			  ?>
			</pre>
      
    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>

    <h3>Public profile of Naitik</h3>
    <img src="https://graph.facebook.com/naitik/picture">
    <?php echo $naitik['name']; ?>
  </body>
</html>
