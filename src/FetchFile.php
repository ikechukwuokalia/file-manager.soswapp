<?php
namespace IkechukwuOkalia;
use \TymFrontiers\Generic,
    \TymFrontiers\Data,
    \TymFrontiers\BetaTym,
    \TymFrontiers\File,
    \TymFrontiers\MultiForm,
    \TymFrontiers\InstanceError,
    \TymFrontiers\API AS API;
require_once "../.appinit.php";

\header("Content-Type: application/json");
\require_login(false);

$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : []
);
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if ( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
$required = [];
$params = [
    "user"   => ["user","username",3,12,[], "MIXED"],
    "type"  => ["type","option", \array_keys($file_upload_groups)],
    "search" => ["search","text",3,55],
    "ids" => ["ids","text",1,256],

    "id" =>["id","int",1,0],
    "page" =>["page","int",1,0],
    "limit" =>["limit","int",1,0],
    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ];
if (!$http_auth) {
  $required[] = "form";
  $required[] = "CSRF_token";
}
$params = $gen->requestParam($params, $post, $required);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed"
  ]);
  exit;
}

if( !$http_auth ){
  if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ) {
    $errors = (new InstanceError($gen,true))->get("checkCSRF",true);
    echo \json_encode([
      "status" => "3." . \count($errors),
      "errors" => $errors,
      "message" => "Request failed."
    ]);
    exit;
  }
}
$params["user"] = $session->name;
$file = new MultiForm(MYSQL_FILE_DB, MYSQL_FILE_TBL, "id");
$file->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$ids = [];
if (!empty($params['ids'])) {
  foreach (\explode(',', $params['ids']) as $id) {
    if ((int)$id > 0) $ids[] = (int)$id;
  }
}
$ids = !empty($ids) ? \implode(',',$ids) : false;

$base_db = MYSQL_BASE_DB;
$admin_db = MYSQL_ADMIN_DB;
$whost = WHOST;
$query =
"SELECT fi.id, fi._locked AS locked, _watermarked AS watermarked, fi._checksum AS 'checksum', fi.nice_name,
        fi.type_group, fi.caption, fi._path AS 'path', fi.owner, fi.privacy,
        fi._name AS name, fi._type AS type, fi._size AS size, fi._creator AS creator, fi._updated AS updated, fi._created AS created,
        fd.`user` AS set_user, fd.set_key AS set_as, ";
if (\defined('FILE_ACCESS_SCOPE') && FILE_ACCESS_SCOPE == 'user') {
  $query .= " CONCAT(usp.name, ' ', usp.surname) AS creator_name, ";
}
$query .= " CONCAT('/app/file/',fi._name) AS url
 FROM :db:.:tbl: AS fi ";
$join = " LEFT JOIN :db:.`file_default` AS fd ON fd.file_id = fi.id ";
if (\defined('FILE_ACCESS_SCOPE') && FILE_ACCESS_SCOPE == 'user') {
  $join .= " LEFT JOIN `{$base_db}`.`user_profile` AS usp ON usp.user=fi._creator ";
}
$cond = "";
if (\defined('FILE_ACCESS_SCOPE') && FILE_ACCESS_SCOPE === "ADMIN.RANK") {
  $cond .= " WHERE (
    SELECT `rank`
    FROM `{$admin_db}`.`work_group`
    WHERE name = REPLACE(fi.owner, 'SYSTEM.','')
    LIMIT 1
    ) <= (
      SELECT `rank`
      FROM `{$admin_db}`.`work_group`
      WHERE name = (
        SELECT work_group
        FROM `{$admin_db}`.`user`
        WHERE `_id` = '{$database->escapeValue($params['user'])}'
        LIMIT 1
        )
      LIMIT 1
      )";
} else {
  $cond .= " WHERE (fi._creator = '{$database->escapeValue($params['user'])}' OR fi.owner = '{$database->escapeValue($params['user'])}') ";
}
if (!empty($params['id'])) {
  $cond .= " AND fi.id = {$params['id']} ";
}else{
  if (!empty($params['type'])) {
    $cond .= " AND fi.type_group='{$params['type']}' ";
  } if ($ids) {
    $cond .= " AND fi.id IN({$ids}) ";
  } if( !empty($params['search']) ){
    $params['search'] = $db->escapeValue(\strtolower($params['search']));
    $cond .= " AND (
      fi.id = '{$params['search']}'
      OR LOWER(fi.caption) LIKE '%{$params['search']}%'
      OR LOWER(fi.nice_name) LIKE '%{$params['search']}%'
    ) ";
  }
}

$count = $file->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS fi {$cond} ");
// echo $db->last_query;
$count = $file->total_count = $count ? $count[0]->cnt : 0;
$file->per_page = $limit = !empty($params['id']) ? 1 : (
    (int)$params['limit'] > 0 ? (int)$params['limit'] : 35
  );

$query .= $join;
$query .= $cond;
$sort = " GROUP BY fi.id ORDER BY fi.`_created` DESC ";

$query .= $sort;
$query .= " LIMIT {$file->per_page} ";
$query .= " OFFSET {$file->offset()}";

// echo \str_replace(':tbl:',MYSQL_FILE_TBL,\str_replace(':db:',MYSQL_FILE_DB,$query));
// exit;
$records = $file->findBySql($query);

if( !$records ){
  die( \json_encode([
    "message" => "Request completed.",
    "errors" => [],
    "status" => "0.2"
    ]) );
}
// process result
$result = [
  'records' => (int)$count,
  'search'  => (!empty($params['search']) ? $params['search'] : ""),
  'id'  => (!empty($params['id']) ? $params['id'] : ""),
  'ids'  => (!empty($params['ids']) ? $params['ids'] : ""),
  'type'  => (!empty($params['type']) ? $params['type'] : ""),
  'page'  => $file->current_page,
  'pages' => $file->totalPages(),
  'limit' => $limit,
  'has_previous_page' => $file->hasPreviousPage(),
  'has_next_page' => $file->hasNextPage(),
  'previous_page' => $file->hasPreviousPage() ? $file->previousPage() : 0,
  'next_page' => $file->hasNextPage() ? $file->nextPage() : 0
];
$tym = new BetaTym;
$data_obj = new Data;
foreach ($records as $k=>$obj) {
  unset($records[$k]->errors);
  unset($records[$k]->current_page);
  unset($records[$k]->per_page);
  unset($records[$k]->total_count);
  unset($records[$k]->_checksum);
  unset($records[$k]->_creator);
  unset($records[$k]->_watermarked);
  unset($records[$k]->_locked);
  unset($records[$k]->_name);
  unset($records[$k]->_path);
  unset($records[$k]->_size);
  unset($records[$k]->_type);

  $records[$k]->id = (int)$records[$k]->id;
  $records[$k]->set_as = !empty($records[$k]->set_as) ? $records[$k]->set_as : false;
  $records[$k]->min_caption = $data_obj->getLen($records[$k]->caption, 98);
  $records[$k]->locked = (bool)$records[$k]->locked;
  $records[$k]->watermarked = (bool)$records[$k]->watermarked;
  $records[$k]->can_watermark = $session->access_rank() > 1;
  $records[$k]->size = (int)$records[$k]->size;
  $records[$k]->updated_date = $records[$k]->updated;
  $records[$k]->updated = !empty($records[$k]->updated_date) ? $tym->MDY($records[$k]->updated_date) : null;
  $records[$k]->created_date = $records[$k]->created;
  $records[$k]->created = !empty($records[$k]->created_date) ? $tym->MDY($records[$k]->created_date) : null;
}

$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";
$result["files"] = $records;

echo \json_encode($result);
exit;
