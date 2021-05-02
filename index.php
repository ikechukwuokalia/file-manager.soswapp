<?php
namespace IkechukwuOkalia;
use \TymFrontiers\Generic,
    \TymFrontiers\InstanceError;

require_once ".appinit.php";

\require_login(true);
$gen =  new Generic;
$params = $gen->requestParam([
  "fid" => ["fid","int"],
  "ids" => ["ids","text",1,256],
  "fname" => ["fname","username", 3,128, [], "LOWER", ["-","_","."]],
  "type" => ["type","option",\array_keys($file_upload_groups)],
  "frc_type" => ["frc_type","option",\array_keys($file_upload_groups)],
  "owner" => ["owner", "username", 3,12],
  "set_user" => ["set_user", "text", 2,0],
  "set_as" => ["set_as", "text", 2,0],
  "set_multiple" => ["set_multiple", "boolean"],
  "set_ttl" => ["set_ttl", "text", 5, 32],
  "set_cb" => ["set_cb", "username", 3, 32, [], "MIXED",["_","."]],
  "upl_multiple" => ["upl_multiple", "boolean"],
  "upl_cb" => ["upl_cb", "username", 3, 32, [], "MIXED",[".","_"]],
  "crp_cb" => ["crp_cb", "username", 3, 32, [], "MIXED",[".","_"]],
  "crp_shape" => ["crp_shape", "option", ["square","rectangle"]],
  "crp_ratio" => ["crp_ratio", "float" ],
  "rtt" => ["rtt","url"],
  "rtt_ttl" => ["rtt_ttl","text", 3, 28]
],$_GET,[]);
// @ $params["upl_multiple"] = isset($_GET["upl_multiple"]) && $_GET["upl_multiple"] == "" ? true : ((bool)$_GET["upl_multiple"] ? 1 : 0) ;
// @ $params["set_multiple"] = isset($_GET["set_multiple"]) && $_GET["set_multiple"] == "" ? true : ((bool)$_GET["set_multiple"] ? 1 : 0) ;
// echo "<tt> <pre>";
// var_dump($params);
// echo "</pre></tt>";
// exit;
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>File Manager | <?php echo PRJ_TITLE; ?></title>
    <?php include PRJ_INC_ICONSET; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="author" content="<?php echo PRJ_AUTHOR; ?>">
    <meta name="creator" content="<?php echo PRJ_CREATOR; ?>">
    <meta name="publisher" content="<?php echo PRJ_PUBLISHER; ?>">
    <meta name="robots" content='nofollow'>
    <!-- Theming styles -->
    <link rel="stylesheet" href="/app/soswapp/font-awesome.soswapp/css/font-awesome.min.css">
    <link rel="stylesheet" href="/app/soswapp/theme.soswapp/css/theme.min.css">
    <link rel="stylesheet" href="/app/soswapp/theme.soswapp/css/theme-<?php echo PRJ_THEME; ?>.min.css">
    <link rel="stylesheet" href="/app/soswapp/fancybox.soswapp/css/fancybox.min.css">
    <link rel="stylesheet" href="/app/soswapp/jcrop.soswapp/css/jcrop.min.css">
    <!-- optional plugin -->
    <link rel="stylesheet" href="/app/soswapp/plugin.soswapp/css/plugin.min.css">
    <link rel="stylesheet" href="/app/soswapp/dnav.soswapp/css/dnav.min.css">
    <link rel="stylesheet" href="/app/soswapp/faderbox.soswapp/css/faderbox.min.css">
    <!-- Project styling -->
    <link rel="stylesheet" href="<?php echo \html_style("base.min.css"); ?>">
    <link rel="stylesheet" href="/app/ikechukwuokalia/file-manager.soswapp/css/file-manager.min.css">
  </head>
  <body>
    <input type="hidden" id="setup-page" data-datapager=".data-pager .pager-btn" data-datacontainer="#file-list" data-datasearch="files" data-datahandle="listFiles">
    <input type="hidden" id="rparam" <?php if ($params) { foreach($params as $k=>$v){ echo "data-{$k}=\"{$v}\" "; } }?>>
    <?php \TymFrontiers\Helper\setup_page((!empty($params['type']) ? "file-manager-{$params['type']}" : "file-manager"), "file-manager", true, PRJ_HEADER_HEIGHT); ?>

    <?php foreach (\array_keys($file_upload_groups) as $group): ?>
      <?php echo "<input type='hidden' class='sos-dnav-extend' data-icon=\"<i class='fas fa-". file_group_nav_icon($group) ."'></i>\" data-path='/file-manager/{$group}' data-name='file-manager-{$group}' data-title='" . file_group_nav_title($group) ."'> \r\n" ?>
    <?php endforeach; ?>

    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <form
        id="file-mod-form"
        method="post"
        action="/app/ikechukwuokalia/file-manager.soswapp/src/AlterFile.php"
        data-validate="false"
        onsubmit="sos.form.submit(this,checkPost); return false;"
      >
      <input type="hidden" name="form" value="file-mod-form">
      <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("file-mod-form");?>">
      <input type="hidden" name="fid" value="">
      <input type="hidden" name="action" value="">
      <input type="hidden" name="degree" value="">
    </form>
      <div class="view-space">
        <?php if (!empty($params['rtt'])): ?>
          <p class="padding -p20">
            <a href="<?php echo $params['rtt']; ?>"><i class="fas fa-angle-double-left"></i> <?php echo empty($params['rtt_ttl']) ? "Go back" : "Back to: {$params['rtt_ttl']}"; ?></a>
          </p>
        <?php endif; ?>
        <br class="c-f">
          <div class="grid-8-tablet center-tablet">
            <form
              id="query-form"
              class="block-ui color face-primary"
              method="post"
              action="/app/ikechukwuokalia/file-manager.soswapp/src/FetchFile.php"
              data-validate="false"
              data-processresp = "0"
              onsubmit="sos.form.submit(this, doFetch);return false;"
              >
              <input type="hidden" name="form" value="file-query-form">
              <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("file-query-form");?>">
              <input type="hidden" name="ids" value="<?php echo !empty($params['ids']) ? $params["ids"] : ''; ?>">
              <input type="hidden" class="page-val" name="page" value="1">
              <input type="hidden" class="limit-val" name="limit" value="35">

              <div class="grid-12-tablet align-c">
                <input type="radio" <?php echo !empty($params['frc_type']) ? "disabled" : ""; ?> name="type" id="ftype-all" value="" <?php echo empty($params['type']) ? "checked" : ""; ?>>
                <label for="ftype-all">* All files</label>
                <?php
                  foreach (\array_keys($file_upload_groups) as $ftype) {
                    echo "<input type='radio' value='{$ftype}' name='type' id='ftype-{$ftype}' ";
                    echo !empty($params['type']) && $params["type"] == $ftype ? " checked " : "";
                    echo (!empty($params['frc_type']) && $params['frc_type'] !== $ftype)
                      ? " disabled "
                      : "";
                    echo ">";
                    echo "<label for='ftype-{$ftype}'>".file_group_nav_title($ftype)."</label>";
                  }
                 ?>
              </div>
              <br class="c-f">
              <div class="grid-6-phone grid-3-tablet"> <br class="c-f">
                <button type="button" onclick="faderBox.url('/app/ikechukwuokalia/file-manager.soswapp/service/uploader-popup.php',{
                  owner : '<?php echo !empty($params['owner']) ? $params['owner'] : ((\defined('FILE_ACCESS_SCOPE') && FILE_ACCESS_SCOPE == 'USER') ? $session->name : "SYSTEM.{$session->access_group}") ?>',
                  type : '<?php echo !empty($params['type']) ? $params['type'] : '' ?>',
                  set_user : '<?php echo !empty($params['set_user']) ? $params['set_user'] : '' ?>',
                  set_as : '<?php echo !empty($params['set_as']) ? $params['set_as'] : '' ?>',
                  upl_cb : '<?php echo (!empty($params['set_as']) && !empty($params['set_cb'])) ? $params['set_cb'] : !empty($params['upl_cb']) ? $params['upl_cb'] : '' ?>',
                  upl_multiple : <?php echo \is_bool($params['upl_multiple']) && isset($_GET['upl_multiple']) ? ((bool)$params['upl_multiple'] ? 1 : 0) : 1 ?>, set_multiple : <?php echo (bool)$params['set_multiple'] ? 1 : 0 ?>
                },{exitBtn : true})" class="btn face-secondary"> <i class="fas fa-cloud-upload-alt"></i> Upload</button>
              </div>
              <div class="grid-6-tablet">
                <label for="search"> <i class="fas fa-tag"></i> Enter caption</label>
                <input type="search" name="search" placeholder="File caption" value="<?php echo !empty($_GET['search']) ? $_GET['search'] :''; ?>" id="search">
              </div>
              <div class="grid-6-phone grid-3-tablet"> <br>
                <button type="submit" class="btn face-primary"> <i class="fas fa-search"></i> Search</button>
              </div>
              <div class="grid-12-phone">
                <p class="align-c margin -mnone">
                  <a href="#" onclick="$('#query-form').trigger('reset');"> <i class="fas fa-undo"></i> Reset query</a>
                </p>
              </div>
              <br class="c-f">
            </form>
            <p class="align-c">
              <b>Files:</b> <span id="file-records" class="records-text">00</span> |
              <b>Pages:</b> <span id="file-pages" class="pages-text">00</span>
            </p>
          </div>

          <div class="sec-div padding -p10">
            <div id="file-list" class="no-result"></div>

            <br class="c-f">
            <div class="data-pager">
              <div class="pager-num">
                <span class="page-text">0</span>/<span class="pages-text">00</span>
              </div>
              <div class="pager-btn">
              </div>
            </div>

            <br class="c-f">
          </div>


        <br class="c-f">
      </div>
    </section>
    <?php include PRJ_INC_FOOTER; ?>
    <!-- Required scripts -->
    <script src="/app/soswapp/jquery.soswapp/js/jquery.min.js">  </script>
    <script src="/app/soswapp/js-generic.soswapp/js/js-generic.min.js">  </script>
    <script src="/app/soswapp/fancybox.soswapp/js/fancybox.min.js">  </script>
    <script src="/app/soswapp/jcrop.soswapp/js/jcrop.min.js">  </script>
    <script src="/app/soswapp/theme.soswapp/js/theme.min.js"></script>
    <!-- optional plugins -->
    <script src="/app/soswapp/plugin.soswapp/js/plugin.min.js"></script>
    <script src="/app/soswapp/dnav.soswapp/js/dnav.min.js"></script>
    <script src="/app/soswapp/faderbox.soswapp/js/faderbox.min.js"></script>
    <!-- project scripts -->
    <script src="<?php echo \html_script ("base.min.js"); ?>"></script>
    <script src="/app/ikechukwuokalia/file-manager.soswapp/js/file-manager.min.js"></script>
    <script src="/app/ikechukwuokalia/file-manager.soswapp/js/file-manager-light.min.js"></script>
    <script type="text/javascript">
      var param = $("#rparam").data();
      function refreshList(){  $('#file-query-form').submit(); }
      if (typeof populateDnav === "function") $(document).on("dnavLoaded", populateDnav);
      $(document).ready(function() {
        requery();
      });
    </script>
  </body>
</html>
