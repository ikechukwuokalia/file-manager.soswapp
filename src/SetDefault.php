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
    "user" =>["user","username", 3, 16],
    "set_user" =>["set_user","username",5,21,[], "MIXED", [".","_","-","/"]],
    "fid" => ["fid","int"],
    "set_as" => ["set_as", "text", 2,0],
    "set_avatar" => ["set_avatar", "boolean"],
    "set_multiple" => ["set_multiple", "boolean"],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["fid", "set_as", 'CSRF_token','form']
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
if (empty($params["user"])) $params["user"] = $session->name;
$params["set_user"] = \defined('FILE_ACCESS_SCOPE') && FILE_ACCESS_SCOPE == "USER" ? (!empty($params["set_user"]) ? $params["set_user"] : $session->name) : "SYSTEM";
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
try {
  Helper\setting_set_file_default($params['set_user'], $params['set_as'], $file->id, (bool)$params["set_multiple"]);
  if ((bool)$params["set_avatar"]) {
    $session->user->avatar = $_SESSION['user']->avatar = $file->url();
  }
} catch (\Exception $e) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to complete setting due to error: ({$e->getMessage()})"],
    "message" => "Request failed."
  ]);
  exit;
}


echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request completed successfully."
]);
exit;
