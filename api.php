<?
require $_SERVER["DOCUMENT_ROOT"]."/bin/php/page.php";
require $_SERVER["DOCUMENT_ROOT"]."/pages/class.pages.php";

$method = $_SERVER['REQUEST_METHOD'];
$path = $_REQUEST['request'];
$paths = array();
$paths = explode("/", $path);
$resource = array_shift($paths);
 
if (!$resource) {
  ?>
  <!DOCTYPE html>
  <html>
  <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>Videogam.in API</title>
      <meta name="description" content="">
      <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <h1>Videogam.in API</h1>
    <p>The Videogam.in API is a REST API. It is currently a work in progress.</p>
    <dl>
      <dt>GET [resource]</dt>
      <dd>Return a JSON string listing a given resource.</dd>
      <dd>Resources include <b>games</b>, <b>people</b>, <b>categories</b>, and <b>topics</b>.</dd>

      <dt>GET [resource]/[title]</dt>
      <dd>Return a JSON string with the data of the given resource.</dd>
      <dd>For example: games/Final_Fantasy_VI, people/Shigeru_Miyamoto</dd>
    </dl>

  </body>
  </html>
  <?
  exit;
}

switch($method) {
 
  case 'GET':
      $get_resource = true;
      break;
 
  case 'PUT':
  case 'DELETE':
  default:
      header('HTTP/1.1 405 Method Not Allowed');
      header('Allow: GET');
      break;
}

if($get_resource){

  $title = array_shift($paths);

  if (empty($title)) {
    handle_base();
  } else {

    $title = formatName($title);

    $pg = new pg($title);
     
    try{ $pg->loadData(); }
    catch(Exception $e){
      header('HTTP/1.1 500 Internal Server Error');
      header('There was an error loading data from the current version of this page: ' . $e->getMessage());
      exit;
    }

    if(!$pg->pgid){
      header('HTTP/1.1 404 Not Found');
    } else {
      unset($pg->data->{@attributes});
      header('HTTP/1.1 200 OK');
      header('Content-type: application/json');
      echo json_encode($pg->data);
    }
  }
}

function handle_base(){
  global $resource;
  $pgtypesF = array_flip($GLOBALS['pgtypes']);
  if(!$pgtypesF[$resource]){
    header('HTTP/1.1 500 Internal Server Error');
    header('['.$resource.'] isn\'t a valid resource type');
  }
  $resource = $pgtypesF[$resource];
  $query = "SELECT `title` FROM pages WHERE `type` = '".mysql_real_escape_string($resource)."' AND redirect_to = '' ORDER BY title_sort";
  $res   = mysql_query($query);
  while($row = mysql_fetch_assoc($res)){
    $titles[] = $row['title'];
  }
  if(count($titles)){
    header('HTTP/1.1 200 OK');
    header('Content-type: application/json');
    echo json_encode($titles);
  } else {
    header('HTTP/1.1 500 Internal Server Error');
    header('Couldn\'t fetch the index from resource ['.$resource.']');
  }
}