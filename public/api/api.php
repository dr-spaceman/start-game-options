<?php

require_once dirname(__FILE__) . '/../../config/bootstrap_api.php';

use Vgsite\API\AccessToken;
use Vgsite\API\CollectionJson;
use Vgsite\API\Exceptions\APIAuthorizationException;
use Vgsite\API\Exceptions\APIException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;
use Vgsite\HTTP\Response;
use Vgsite\Registry;

/**
 * @OA\Server(url=API_BASE_URI)
 */

/**
 * @OA\Info(title="Videogamesite API", version=API_VERSION)
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
 * 
 * @OA\Schema(schema="token",
 *     type="object",
 *     @OA\Property(property="access_token", type="string", description="Provides the requested access token to authorize requests to the API."),
 *     @OA\Property(property="created_at", type="date-time", description="Time at which the access token was generated."),
 *     @OA\Property(property="expires_in", type="integer", description="Indicates that the generated access token expires in 36,000 seconds, 600 minutes, or 10 hours."),
 * )
 * 
 * @OA\Post(
 *     path="/token",
 *     summary="Generate Token",
 *     description="Use this method to generate an access token to authorize requests to the API.",
 *     operationId="Auth:token",
 *     @OA\Parameter(
 *         name="client_id",
 *         in="path",
 *         required=true,
 *         description="The client ID given by the API for a specific app",
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Parameter(
 *         name="client_secret",
 *         in="path",
 *         required=true,
 *         description="The client secret given by the API for a specific app",
 *         @OA\Schema(type="string"),
 *     ),
 *     @OA\Parameter(
 *         name="grant_type",
 *         in="path",
 *         required=true,
 *         description="enum('authorization_code', 'password', 'client_credentials')",
 *         @OA\Schema(type="string"),
 *     ),
 *     @OA\Response(response="200",
 *         description="Successfully generated token",
 *         @OA\JsonContent(ref="#/components/schemas/token"),
 *     ),
 *     @OA\Response(response="401",
 *         description="Unauthorized: client_id or client_secret are invalid.",
 *     ),
 * )
 */

ob_start();

try {
	$method = $_SERVER['REQUEST_METHOD'];
	$uri = $_SERVER['REQUEST_URI'];
	$body = file_get_contents('php://input');
	$request = new Request($method, $uri, getallheaders(), $body);

	$base = $request->getPath()[0];

	// foreach ($request->getHeaders() as $header_name => $headers) {
	// 	echo(sprintf("%s: %s", $header_name, $request->getHeaderLine($header_name))).PHP_EOL;
	// };

	// $show = Array('uri' => $uri, 'path' => $request->getPath(), '_ENV' => $_ENV, '_SERVER' => $_SERVER);header("Content-Type: application/json; charset=UTF-8");die(json_encode($show));
	
	// OAUTH Token requested via HTTP POST
	if ($base == "token") {
		if ($request->getMethod() != "POST") {
			throw new APIAuthorizationException('To get an access token, POST a request.', null, 'INVALID_REQUEST_METHOD', 405);
		}

		$token_params = [
			$request->getQuery()['grant_type'],
			$request->getQuery()['client_id'],
			$request->getQuery()['client_secret']
		];
		$access = new AccessToken(...$token_params);

		$response = new Response();
		$response->render(json_encode(["access_token" => $access->getToken(), "created" => date('Y-m-d H:i:s'), "expires_in" => $access::EXPIRES]));
	}

	$controllers = [
		'search' => 'Vgsite\API\SearchController',
		'games' => 'Vgsite\API\GameController',
		'people' => 'Vgsite\API\PersonController',
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
