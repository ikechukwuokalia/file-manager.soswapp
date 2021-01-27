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
    "file_type" => ["file_type","option", \array_keys($file_upload_groups)],
    "set_as" => ["set_as", "text", 2,0],
    "set_avatar" => ["set_avatar", "boolean"],
    "set_multiple" => ["set_multiple", "boolean"],
    "caption" => ["caption","text",5,55],
    "privacy" => ["privacy", "option",["PRIVATE","PUBLIC"]],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  ['CSRF_token','form']
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
if (empty($params["set_dmn"])) $params["set_dmn"] = PRJ_DOMAIN;

if (empty($_FILES)) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["No file was sent with request."],
    "message" => "Request halted."
  ]);
  exit;
}

$file_db = MYSQL_FILE_DB;
$whost = WHOST;
$save_dir = file_upload_path(FILE_UPLOAD_PATH, true);
// "fileid" => [
//   "id" => 56,
//   "type" => "image/jpeg",
//   "group" => "image",
//   "size" => 23444,
//   "url" => "domain.com/path/to/file",
//   "caption" => "the file caption",
// ]
$uploaded_files = [];
$failed_files = [];
$upload_errors = [];
$err_files = [];
// check availability of credentials

// check regularity
foreach ($_FILES as $pfid => $attached_file) {
  $file = new File();
  // var_dump($file->groupName($attached_file['type']));
  if (
    (!empty($params['type']) && !\in_array($attached_file['type'],$file_upload_groups[$params['file_type']])) || !\array_key_exists($file->groupName($attached_file['type']),$file_upload_groups)
  ){
    $upload_errors[] = "Unaccepted file type (#{$pfid} - {$attached_file["name"]})" . (empty($params['file_type']) ? '' : (", choose only: .".\implode(', .',\array_keys($file_upload_groups[$params['file_type']])).' file types'));
    $err_files[] = $pfid;
  }
  if ($attached_file['size'] < FILE_UPLOAD_MIN_SIZE || $attached_file['size'] > FILE_UPLOAD_MAX_SIZE ) {
    $upload_errors[] = "Irregular file byte/size (#{$pfid} - {$_FILES[$pfid]["name"]}). Must be Min: " . Helper\file_size_unit(FILE_UPLOAD_MIN_SIZE) . " | Max: " . Helper\file_size_unit(FILE_UPLOAD_MAX_SIZE);
    $err_files[] = $pfid;
  }
  if (!\in_array($pfid,$err_files)) {
    $file->load($save_dir);
    $file->owner = $params['owner'];
    $file->privacy = !empty($params['privacy']) ? $params['privacy'] : "PUBLIC";
    $file->caption = $params['caption'];
    if (!$file->upload($attached_file)) {
      $failed_files[] = $pfid;
    } else {
      $uploaded_files[$pfid] = [
        "id" => $file->id,
        "type" => $file->type(),
        "group" => $file->groupName(),
        "size" => $file->size(),
        "url" => $file->url(),
        "caption" => $file->caption
      ];
      // set file
      if (!empty($params["set_as"])) {
        try {
          Helper\setting_set_file_default((\defined('FILE_ACCESS_SCOPE') && FILE_ACCESS_SCOPE == 'USER' ? $params['owner'] : "SYSTEM"), $params['set_as'], $file->id, (bool)$params["set_multiple"]);
          if ((bool)$params["set_avatar"]) {
            $session->user->avatar = $_SESSION['user']->avatar = $file->url();
          }
        } catch (\Exception $e) {
          $upload_errors[] = "(#{$pfid} - {$_FILES[$pfid]["name"]}) - Failed to complete setting due to error: ({$e->getMessage()})";
        }
      }
    }
  } else {
    $failed_files[] = $pfid;
  }
}
if (!empty($upload_errors)) {
  echo \json_encode([
    "status" => "5." . \count($upload_errors),
    "errors" => $upload_errors,
    "message" => "Request has error(s).",
  ]);
  exit;
}
echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request completed successfully.",
  "uploaded_files" => $uploaded_files,
  "failed_files" => $failed_files
]);
exit;
