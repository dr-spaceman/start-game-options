<?
require_once $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
?><!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>[404 Page not found] Videogam.in, a site about videogames</title>
	<link rel="shortcut icon" href="/favicon.ico"/>
</head>
<body>

<h1>Page not found</h1>

<a href="/">Videogam.in Home Page</a>

<? if($usrrank == 9) print_r($_SERVER); ?>

</body>
</html>
<?

exit;