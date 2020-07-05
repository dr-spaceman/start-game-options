<?php

use Vgsite\HTTP\Request;

require_once dirname(__FILE__) . '/../config/bootstrap.php';

// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
// header("Access-Control-Max-Age: 3600");
// header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER['REQUEST_URI'];
$body = file_get_contents('php://input');
$request = new Request($method, $uri, getallheaders(), $body);

$base = 'games';

// Schema
$schema = <<<EOF
search/{query}
	GET
games/
	GET
game/{id}
	GET
users/
	GET
users/{id}
	GET
EOF;

// $schema = [
// 	"resources" => [
// 		"/search" => [
// 			"href-template" => "/search/{query}",
// 			"allow" => ["GET"],
// 		],
// 		"/games" => [
// 			"href" => "/games/",
// 			"allow" => ["GET"],
// 		],
// 		"/game" => [
// 			"href-template" => "/game/{id}",
// 			"allow" => ["GET"],
// 		],
// 		"/users" => [
// 			"href" => "/users/",
// 		],
// 		"/user" => [
// 			"href-template" => "/user/{id}",
// 		],
// 	]
// ];

// foreach ($request->getHeaders() as $header_name => $headers) {
// 	echo(sprintf("%s: %s", $header_name, $request->getHeaderLine($header_name))).PHP_EOL;
// };

// $show = Array('uri' => $uri, 'req' => $req, '_ENV' => $_ENV, '_SERVER' => $_SERVER);header("Content-Type: application/json; charset=UTF-8");die(json_encode($show));

switch ($base) {
	case 'search':
		$controller = new Vgsite\API\SearchController($request);
		$controller->processRequest()->render();
		break;
	
	case 'games':
		$controller = new Vgsite\API\GameController($request);
		$controller->processRequest()->render();
		break;
		
	default:
		header('HTTP/1.1 200 OK');
		header("Content-Type: text/plain; charset=UTF-8");
		echo $schema;
}
