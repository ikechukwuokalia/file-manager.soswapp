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
    "fid" => ["fid","int"],
    "caption" => ["caption","text",5,124],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ["fid", 'CSRF_token','form']
);
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
$file->caption = $params['caption'];
if (!$file->update()) {
  $file->mergeErrors();
  $do_errors = [];
  $more_errors = (new InstanceError($file,true))->get('update',true);
  if (!empty($more_errors)) {
    foreach ($more_errors as $err){
      $do_errors[] = $err;
    }
    echo \json_encode([
      "status" => "4." . \count($do_errors),
      "errors" => $do_errors,
      "message" => "Request incomplete."
    ]);
    exit;
  } else {
    echo \json_encode([
      "status" => "0.1",
      "errors" => [],
      "message" => "Request completed with no changes made."
    ]);
    exit;
  }
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request completed successfully."
]);
exit;
