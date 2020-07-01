<?php

/**
 * Search the whole database
 * @param query Search query
 * @return JSONobject
 */

use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APIException;

require_once dirname(__FILE__) . '/../config/bootstrap.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$req_method = $_SERVER["REQUEST_METHOD"];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$req = explode('/', $uri);
array_shift($req); //_blank_
array_shift($req); //api

$api_version = array_shift($req);
$base = array_shift($req);
$query = $req[0];
$filter = '';

// populate with hits
$results = [];

// Schema
/*	GET search/{query}
	GET games/ 
	GET game/{id}
	GET users/
	GET user/{id} */
$schema = [
	"resources" => [
		"/search" => [
			"href-template" => "/search/{query}",
			"allow" => ["GET"],
		],
		"/games" => [
			"href" => "/games/",
			"allow" => ["GET"],
		],
		"/game" => [
			"href-template" => "/game/{id}",
			"allow" => ["GET"],
		],
		"/users" => [
			"href" => "/users/",
		],
		"/user" => [
			"href-template" => "/user/{id}",
		],
	]
];

// $show = Array('uri' => $uri, 'req' => $req, '_ENV' => $_ENV, '_SERVER' => $_SERVER);die(json_encode($show));

try {
	switch ($base) {
		case 'game':
			$controller = new Vgsite\API\GameController($req_method, $req);
			$controller->processRequest();

			break;
			
		case 'search':
			$controller = new Vgsite\API\SearchController($req_method, $req);
			$controller->processRequest();

			break;
		
		default:
			header('HTTP/1.1 200 OK');
			echo json_encode($schema);
	}
} catch (APIException | APIInvalidArgumentException $e) {
	$code = $e->getCode();
	if ($code == 422) {
		$response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
		$response['body'] = json_encode([
			'error' => $e->getMessage(),
		]);
	} else {
		$response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
		$response['body'] = json_encode([
			'error' => $e->getMessage() || 'Your request could not be processed.',
		]);
	}

	header($response['status_code_header']);
	echo json_encode($response['body']);
}
