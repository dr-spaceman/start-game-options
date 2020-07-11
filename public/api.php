<?php

require_once dirname(__FILE__) . '/../config/bootstrap_api.php';

use Vgsite\API\CollectionJson;
use Vgsite\API\Exceptions\APIException;
use Vgsite\HTTP\Request;
use Vgsite\HTTP\Response;
use Vgsite\Registry;

// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
// header("Access-Control-Max-Age: 3600");
// header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

ob_start();

try {
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$body = file_get_contents('php://input');
$request = new Request($method, $uri, getallheaders(), $body);

$base = $request->getPath()[0];

$schema = [
	'search/'		=> ['GET'],
	'games' 		=> ['GET'],
	'games/{id}'	=> ['GET'],
	'users' 		=> ['GET'],
	'users/{id}'	=> ['GET'],
	'posts/'		=> ['GET', 'POST', 'PUT'],
	'posts/{id}'	=> ['GET', 'PUT', 'DELETE'],
	'_PARAMETERS_'	=> ['q={query}', 'page={page}', 'per_page={number_per_page}', 'sort={field}[:asc|desc]', 'fields={list,of,fields}'],
];

// foreach ($request->getHeaders() as $header_name => $headers) {
// 	echo(sprintf("%s: %s", $header_name, $request->getHeaderLine($header_name))).PHP_EOL;
// };

// $show = Array('uri' => $uri, 'req' => $req, '_ENV' => $_ENV, '_SERVER' => $_SERVER);header("Content-Type: application/json; charset=UTF-8");die(json_encode($show));

switch ($base) {
	case 'search':
		$controller = new Vgsite\API\SearchController($request);
		$controller->processRequest();
		break;
	
	case 'games':
		$controller = new Vgsite\API\GameController($request);
		$controller->processRequest();
		break;
		
	default:
		$cj = new CollectionJson();
		$cj->setLinks($schema);
		$response = new Response(200, [], $cj);
		$response->render();
}
} catch (Exception | Error $e) {
	Registry::get('logger')->warning($e);

	ob_flush();

	if (headers_sent() || in_array('API-Body-Rendered: true', headers_list())) {
		exit;
	}

	$code = $e->getCode() && array_key_exists($e->getCode(), Response::$phrases) ? $e->getCode() : 500;

	if ($e instanceof APIException) {
		$error = $e->getErrorMessage();
	} else {
		$error = ['title' => 'Server error', 'message' => $e->getMessage()];
	}

	$cj = new CollectionJson();
	$cj->setError($error);

	$response = new Response($code);
	$response->render($cj);
} finally {
	ob_end_flush();
}