<?
use Vgsite\Page;
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

<? if($_SESSION['user_rank'] == User::SUPERADMIN) print_r($_SERVER); ?>

</body>
</html>
<?

exit;