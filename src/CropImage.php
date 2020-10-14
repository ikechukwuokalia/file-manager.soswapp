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
$params = $gen->requestParam(
  [
    "owner" =>["owner","username",5,21,[], "MIXED", [".","_","-"]],
    "fid" => ["fid","int"],
    "w" => ["width", "float",],
    "h" => ["height", "float",],
    "x" => ["x_position", "float",],
    "y" => ["y_position", "float",],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["fid", "w", "h", "x", "y", 'CSRF_token','form']
);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed"
  ]);
  exit;
}

if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ) {
  $errors = (new InstanceError($gen,true))->get("checkCSRF",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed."
  ]);
  exit;
}
if (empty($params["owner"])) $params["owner"] = $session->name;
$file_db = MYSQL_FILE_DB;
$file_tbl = MYSQL_FILE_TBL;
// crop file
$file = File::findById($params["fid"]);
if (empty($file->id) || $file->type_group !== "image") {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["No image file was found for given [fid]"],
    "message" => "Request halted."
  ]);
  exit;
}
$file->crop_img = [
  "x" => $params['x'],
  "y" => $params['y'],
  "w" => $params['w'],
  "h" => $params['h']
];
if (!$file->cropImage()) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Failed to crop image, contact Developer."],
    "message" => "Request incomplete."
  ]);
  exit;
} else {
  // update file size
  if ($fsize = \filesize($file->fullPath())) {
    $database->query("UPDATE {$file_db}.`{$file_tbl}` SET _size = {$fsize} WHERE id={$file->id} LIMIT 1");
  }
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request completed successfully.",
  "id" => $file->id,
  "caption" => $file->caption,
  "url" => $file->url(),
]);
exit;
