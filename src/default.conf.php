<?php
namespace IkechukwuOkalia;
use \TymFrontiers\MultiForm;
// define file varables if not already defined
if (empty($file_upload_groups)) {
  $file_upload_groups = (new \TymFrontiers\File)->types;
}
\defined("FILE_UPLOAD_MIN_SIZE") ? NULL : \define("FILE_UPLOAD_MIN_SIZE", 1024 * 15); // 15 KB
\defined("FILE_UPLOAD_MAX_SIZE") ? NULL : \define("FILE_UPLOAD_MAX_SIZE", 1024 * 1024 * 50); // 50 MB
\defined("FILE_UPLOAD_PATH") ? NULL : \define("FILE_UPLOAD_PATH","/storage/User-Files/%{USER}/%{DATEYEAR}-%{DATEMONTH}");
\defined("FILE_ACCESS_SCOPE") ? NULL : \define("FILE_ACCESS_SCOPE", "USER"); // USER | ADMIN.RANK
\defined("FILE_DEFAULT_OWNER") ? NULL : \define("FILE_DEFAULT_OWNER", "%{USER}"); // %{USER} | SYSTEM.%{WORKGROUP}


// function file_upload_path (string $path = FILE_UPLOAD_PATH, bool $mkdir = false) {
//   if (!$path) return null;
//   global $session;
//   $path = \str_replace("%{USER}", $session->name, $path);
//   $path = \str_replace("%{DATEYEAR}", \strftime("%Y",\time()), $path);
//   $path = \str_replace("%{DATEMONTH}", \strftime("%m",\time()), $path);
//   if (!\file_exists(PRJ_ROOT . $path) && $mkdir ) {
//     if (!\mkdir(PRJ_ROOT . $path, 0777, true)) {
//       throw new \Exception(PRJ_ROOT . "{$path} does not exist and could be created. Check that directory is correct and has expected permission.", 1);
//     }
//   }
//   return PRJ_ROOT . $path;
// }
// function file_default_owner() {
//   global $session;
//   $userid = FILE_DEFAULT_OWNER;
//   if ($wg = (new MultiForm(MYSQL_ADMIN_DB,"user", "_id"))->findBySql("SELECT work_group FROM `:db:`.`:tbl:` WHERE `:pkey:` = '{$session->name}' LIMIT 1")) {
//     $userid = \str_replace('%{WORKGROUP}',$wg[0]->work_group, $userid);
//   }
//   $userid = \str_replace("%{USER}", $session->name, $userid);
//   return $userid;
// }
// function file_group_nav_title(string $group) {
//   $return = "File (other)";
//   switch ($group) {
//     case 'image':
//       $return = "Images";
//       break;
//     case 'audio':
//       $return = "Audio";
//       break;
//     case 'document':
//       $return = "Documents";
//       break;
//     case 'video':
//       $return = "Videos";
//       break;
//
//     default:
//       $return = "File (other)";
//       break;
//   }
//   return $return;
// }
// function file_group_nav_icon(string $group) {
//   $return = "file";
//   switch ($group) {
//     case 'image':
//       $return = "image";
//       break;
//     case 'audio':
//       $return = "headphones-alt";
//       break;
//     case 'document':
//       $return = "file-alt";
//       break;
//     case 'video':
//       $return = "file";
//       break;
//
//     default:
//       $return = "file";
//       break;
//   }
//   return $return;
// }
