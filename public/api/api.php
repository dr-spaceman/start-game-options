<?php

require_once dirname(__FILE__) . '/../../config/bootstrap_api.php';

/**
 * @OA\Server(url=API_BASE_URL)
 */

/**
 * @OA\Info(title="Vigeogamesite API", version=API_VERSION)
 */

/** 
 * @OA\Parameter(
 *     name="q",
 *     in="query",
 *     description="Search term",
 *     @OA\Schema(type="string")
 * )
 * @OA\Parameter(
 *     name="sort",
 *     in="query",
 *     description="Custom sorted results. Format: `?sort=fieldname[:asc|desc]`",
 *     example="release:desc",
 *     @OA\Schema(type="string")
 * )
 * @OA\Parameter(
 *     name="fields",
 *     in="query",
 *     description="A list of comma-separated fields to include in the response object.",
 *     example="title,release_date,tags",
 *     @OA\Schema(type="string")
 * )
 * @OA\Parameter(
 *     name="page",
 *     in="query",
 *     description="Page number",
 *     @OA\Schema(type="integer")
 * )
 * @OA\Parameter(
 *     name="per_page",
 *     in="query",
 *     description="Number of results per page",
 *     @OA\Schema(type="integer")
 * )
 * 
 * @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="The numeric ID for the requested item",
 *     @OA\Schema(type="integer")
 * )
 */

use Vgsite\API\CollectionJson;
use Vgsite\API\Exceptions\APIException;
use Vgsite\API\Exceptions\APINotFoundException;
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
		'games' 		=> ['GET', 'POST'],
		'games/{id}'	=> ['GET', 'PATCH', 'DELETE'],
		'users' 		=> ['GET', 'POST'],
		'users/{id}'	=> ['GET', 'PATCH', 'DELETE'],
		'posts/'		=> ['GET', 'POST'],
		'posts/{id}'	=> ['GET', 'PATCH', 'DELETE'],
		'_PARAMETERS_'	=> ['q={query}', 'page={page}', 'per_page={number_per_page}', 'sort={field}[:asc|desc]', 'fields={list,of,fields}'],
	];

	// foreach ($request->getHeaders() as $header_name => $headers) {
	// 	echo(sprintf("%s: %s", $header_name, $request->getHeaderLine($header_name))).PHP_EOL;
	// };

	// $show = Array('uri' => $uri, 'path' => $request->getPath(), '_ENV' => $_ENV, '_SERVER' => $_SERVER);header("Content-Type: application/json; charset=UTF-8");die(json_encode($show));

	$controllers = [
		'search' => 'Vgsite\API\SearchController',
		'games' => 'Vgsite\API\GameController',
		'users' => 'Vgsite\API\UserController',
		'albums' => 'Vgsite\API\AlbumController',
		'badges' => 'Vgsite\API\BadgeController',
	];
	
	if (! $controller = $controllers[$base]) {
		throw new APINotFoundException();
	}

	$controller = new $controller($request);
	$controller->processRequest();
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
