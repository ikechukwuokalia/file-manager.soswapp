<?php
namespace IkechukwuOkalia;
use \TymFrontiers\File,
    \TymFrontiers\InstanceError,
    \TymFrontiers\Generic,
    \TymFrontiers\Helper as Helper;
require_once "../.appinit.php";
\require_login(false);

\header("Content-Type: application/json");

$post = $_POST; // json data
$gen = new Generic;
$req = ["fid", "action", 'CSRF_token','form'];
if (!empty($post['action'] && $post['action'] == "ROTATE")) $req[] = "degree";
$params = $gen->requestParam( [
    "user" =>["user","username", 3, 16],
    "fid" => ["fid","int"],
    "action" => ["action","option",["WATERMARK", "DELETE", "LOCK", "ROTATE"]],
    "degree" => ["degree","float"],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ], $post, $req );
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen, false))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed"
  ]);
  exit;
}

if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ) {
  $errors = (new InstanceError($gen, false))->get("checkCSRF",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed."
  ]);
  exit;
}
if (empty($params["user"])) $params["user"] = $session->name;
$file_db = MYSQL_FILE_DB;
$file_tbl = MYSQL_FILE_TBL;
// crop file
$file = File::findById($params["fid"]);
if (empty($file->id)) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["No file was found for given [fid]"],
    "message" => "Request halted."
  ]);
  exit;
}
if ($params["action"] == "LOCK") {
  try {
    $file->lock();
  } catch (\Exception $e) {
    echo \json_encode([
      "status" => "4.1",
      "errors" => ["Failed to lock file ({$e->getMessage()})"],
      "message" => "Request failed."
    ]);
    exit;
  }
} else if ($params["action"] == "DELETE") {
  if (!$file->destroy()) {
    echo \json_encode([
      "status" => "4.1",
      "errors" => ["Failed to delete file, try again later"],
      "message" => "Request failed."
    ]);
    exit;
  }
} else if ($params["action"] == "WATERMARK" && $file->type_group == "image") {
  if (!\defined('PRJ_IMAGE_WATERMARK') || !\file_exists(PRJ_IMAGE_WATERMARK) || !\is_readable(PRJ_IMAGE_WATERMARK)) {
    echo \json_encode([
      "status" => "4.1",
      "errors" => ["Watermark image is not defined, does not exist or it is not readable"],
      "message" => "Request failed."
    ]);
    exit;
  }
} else if ($params["action"] == "ROTATE" && $file->type_group == "image") {
  if (!$file->rotateImage($params["degree"])) {
    echo \json_encode([
      "status" => "4.1",
      "errors" => ["Failed to rotate image, try again later"],
      "message" => "Request failed."
    ]);
    exit;
  }
} else {
  echo \json_encode([
    "status" => "0.1",
    "errors" => [],
    "message" => "No task was performed."
  ]);
  exit;
}
echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request completed successfully."
]);
exit;
