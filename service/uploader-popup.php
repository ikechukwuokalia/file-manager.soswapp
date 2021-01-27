<?php
namespace IkechukwuOkalia;
use \TymFrontiers\Generic,
    \TymFrontiers\InstanceError;
require_once "../.appinit.php";

\require_login(false);

$errors = [];
$gen = new Generic;
$required = [];
$pre_params = [
  "type" => ["type","option",["image", "audio", "video", "document"]],
  "owner" =>["owner","username",5,21,[], "MIXED", [".","_","-"]],
  "set_avatar" => ["set_avatar", "boolean"],
  "set_as" => ["set_as", "text", 2,0],
  "set_multiple" => ["set_multiple", "boolean"],
  "upl_multiple" => ["upl_multiple", "boolean"],
  "upl_cb" => ["upl_cb","username",3,35,[],'MIXED',["_","_"]],
  "crp_ratio" => ["crp_ratio", "float"],
  "crp_cb" => ["crp_cb","username",3,35,[],'MIXED',["_","_"]],
];
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
if (!empty($file_upload_groups) && !empty($params["type"])) {
  if (!\array_key_exists($params["type"],$file_upload_groups)) {
    $errors[] = "Unsupported file group/type: {$params["type"]}";
  }
}
$upload_accept = [];
if (!empty($params['type']) && \array_key_exists($params['type'], $file_upload_groups)) {
  foreach ($file_upload_groups[$params['type']] as $ext=>$mime) {
    $upload_accept[] = ".{$ext}";
    $upload_accept[] = $mime;
  }
}
if (empty($params['upl_cb'])) $params['upl_cb'] = "handleUpload";
if (empty($params['crp_cb'])) $params['crp_cb'] = "requery";
?>
<style media="screen">
  @-webkit-keyframes rotating /* Safari and Chrome */ {
    from {
      -webkit-transform: rotate(0deg);
      -o-transform: rotate(0deg);
      transform: rotate(0deg);
    }
    to {
      -webkit-transform: rotate(360deg);
      -o-transform: rotate(360deg);
      transform: rotate(360deg);
    }
  }
  @keyframes rotating {
    from {
      -ms-transform: rotate(0deg);
      -moz-transform: rotate(0deg);
      -webkit-transform: rotate(0deg);
      -o-transform: rotate(0deg);
      transform: rotate(0deg);
    }
    to {
      -ms-transform: rotate(360deg);
      -moz-transform: rotate(360deg);
      -webkit-transform: rotate(360deg);
      -o-transform: rotate(360deg);
      transform: rotate(360deg);
    }
  }
  #do-wait{
    position: absolute;
    top: 60%;
    left: 55%;
    width: 100px;
    height: 100px;
    margin-top: -100px;
    margin-left: -100px;
    text-align: center;
    /* background: grey; */
    background-color: rgba(0,0,0,0.85);
    -webkit-border-radius:12px;
    -ms-border-radius:12px;
    -moz-border-radius:12px;
    border-radius:12px;
    z-index: 200;
    padding: 20px;
  }
  #do-wait .spinner{
    position: relative;
    width: 55px;
    height: 55px;
    -webkit-font-smoothing: antialiased;
    cursor:progress;
    -webkit-border-radius:100%;
  	-ms-border-radius:100%;
  	-moz-border-radius:100%;
  	border-radius:100%;
    border: solid 5px white;
    -webkit-animation: rotating 1.5s linear infinite;
    -moz-animation: rotating 1.5s linear infinite;
    -ms-animation: rotating 1.5s linear infinite;
    -o-animation: rotating 1.5s linear infinite;
    animation: rotating 1.5s linear infinite;
  }
  #do-wait .spinner::before{
    content: "";
    display: inline-block;
    height: 6px; width: 5px;
    background-color: rgba(0,0,0,0.85);
    margin-left: -50px;
    margin-top: 20px;
  }
  #acti-view{
    opacity: 0;
  }
</style>
<input type="hidden" id="upldata" <?php if ($params) { foreach($params as $k=>$v) { echo "data-{$k}=\"{$v}\" "; } }?>>
<div id="fader-flow">
  <div class="view-space">
    <div class="padding -p20">&nbsp;</div>
    <br class="c-f">
    <div class="grid-8-tablet grid-6-desktop center-tablet">
      <div class="sec-div color face-primary bg-white drop-shadow">
        <div id="do-wait"> <div class="spinner"></div>  </div>

        <header class="padding -p20 color-bg">
          <h1> <i class="fas fa-cloud-upload-alt"></i> Uploader</h1>
        </header>

        <div class="padding -p20" id="acti-view">
          <?php if(!empty($errors)){ ?>
            <h3>Unresolved error(s)</h3>
            <ol>
              <?php foreach($errors as $err){
                echo " <li>{$err}</li>";
              } ?>
            </ol>
          <?php }else{ ?>
            <form
            id="file-uploader-form"
            class="block-ui"
            method="post"
            action="/app/ikechukwuokalia/file-manager.soswapp/src/UploadFile.php"
            data-validate="false"
            onsubmit="sos.form.upload(this, {}, '<?php echo $params['upl_cb']; ?>'); return false;"
            >
            <input type="hidden" name="form" value="file-uploader-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("file-uploader-form");?>">

            <input type="hidden" name="owner" value="<?php echo !empty($params['owner']) ? $params['owner'] : ((\define('FILE_ACCESS_SCOPE') && FILE_ACCESS_SCOPE == 'USER') ? $session->name : "SYSTEM.{$session->access_group}"); ?>">
            <input type="hidden" name="file_type" value="<?php echo $params['type']; ?>">
            <input type="hidden" name="set_as" value="<?php echo $params['set_as']; ?>">
            <input type="hidden" name="set_avatar" value="<?php echo (bool)$params['set_sesskey'] ? 1 : 0; ?>">
            <input type="hidden" name="set_multiple" value="<?php echo $params['set_multiple']; ?>">

            <input
              id="input-file"
              class="hidden"
              data-action="sos-file-init"
              data-stats="#upload-stats"
              type="file"
              <?php echo (bool)$params['upl_multiple'] ? 'multiple' : ''; ?>
              accept="<?php echo \implode(',',$upload_accept); ?>"
            required>

            <div class="grid-12-tablet">
              <div id="upload-trigger" class="sos-file-trigger hide-on-submit" onclick="$('#input-file').trigger('click');">
                <span class="fa-stack fa-2x">
                  <i class="fas fa-circle fa-stack-2x"></i>
                  <i class="fas fa-file-upload fa-stack-1x fa-inverse"></i>
                </span>
                <br> Choose <?php echo (bool)$params['upl_multiple'] ? 'files' : 'file' ?></div>

              <div id="upload-stats"></div>
            </div>
            <div class="grid-7-phone grid-4-tablet">
              <button id="submit-form" type="submit" class="btn face-primary"> <i class="fas fa-upload"></i> Upload </button>
            </div>

            <br class="c-f">
          </form>
        <?php } ?>
      </div>
    </div>
  </div>
  <br class="c-f">
</div>
</div>

<script type="text/javascript">
  var required_scripts = [
    { // font-awesome
      search : /((\/soswapp\/font\-awesome\.soswapp)|(\/tymfrontiers\/font\-awesome\-pro.soswapp)\/css\/)/i,
      script : `/app/soswapp/font-awesome.soswapp/css/font-awesome.min.css`,
      type : "css"
    },
    { // 7os/theme
      search : `/soswapp/theme.soswapp/css`,
      script : `/app/soswapp/theme.soswapp/css/theme.min.css`, // script to load
      type : "css" // type: js|css
    },
    {
      search : "/soswapp/theme.soswapp/js",
      script : `/app/soswapp/theme.soswapp/js/theme.min.js`,
      type : "js"
    },
    { // 7os/jcrop
      search : `/soswapp/jcrop-soswapp/css`,
      script : `/app/soswapp/jcrop.soswapp/css/jcrop.min.css`, // script to load
      type : "css" // type: js|css
    },
    {
      search : "/soswapp/jcrop-soswapp/js",
      script : `/app/soswapp/jcrop.soswapp/js/jcrop.min.js`,
      type : "js"
    },
    { // 7os/plugin
      search : `/soswapp/plugin-soswapp/css`,
      script : `/app/soswapp/plugin.soswapp/css/plugin.min.css`, // script to load
      type : "css" // type: js|css
    },
    {
      search : "/soswapp/plugin-soswapp/js",
      script : `/app/soswapp/plugin.soswapp/js/plugin.min.js`,
      type : "js"
    },
    { // 7os/faderbox
      search : `/soswapp/faderbox-soswapp/css`,
      script : `/app/soswapp/faderbox.soswapp/css/faderbox.min.css`, // script to load
      type : "css" // type: js|css
    },
    {
      search : "/soswapp/faderbox-soswapp/js",
      script : `/app/soswapp/faderbox.soswapp/js/faderbox.min.js`,
      type : "js"
    },
    { // ikechukwuokalia/file-manager
      search : "/file-manager.soswapp/css",
      script : `/app/ikechukwuokalia/file-manager.soswapp/css/file-manager.min.css`, // script to load
      type : "css" // type: js|css
    },
    {
      search : "/file-manager.soswapp/js",
      script : `/app/ikechukwuokalia/file-manager.soswapp/js/file-manager-light.min.js`,
      type : "js"
    }
  ];
  function doInit() {
    if (typeof sos !== "undefined" && typeof sos.form !== "undefined") sos.form.resetFiles();
    if (typeof window['param'] === "undefined") window.param = {};
    $.each($('#upldata').data(),function(k,v){
      window.param[k] = v;
    });
    $("#acti-view").css("opacity", 1);
    $("#do-wait").fadeOut("slow").remove();
  }
  function initScripts (reqd = [], callback) {
    let doCount = 0;
    reqd.forEach(function(obj, index){
      ++ doCount
      require_script(obj.script, obj.search, obj.type);
      if (doCount == Object.keys(reqd).length && typeof callback == "function") callback();
    });
  }

  if (typeof $ === "undefined") {
    // no jquery | load it up
    let jq = document.createElement('script');
    jq.type = 'text/javascript';
    jq.src = `/app/soswapp/jquery.soswapp/js/jquery.min.js`;
    document.getElementsByTagName('head')[0].appendChild(jq);
    jq.onload = function() {
      if (typeof jsGenericLoaded == "undefined" || jsGenericLoaded == false) {
        // load 7os/js-generic
        // $.getScript(`${location.origin}/7os/js-generic-soswapp/js/js-generic.min.js`, function() {
        // });
        let jsGenScr = document.createElement('script');
        jsGenScr.src = `/app/soswapp/js-generic.soswapp/js/js-generic.min.js`;
        jsGenScr.type = 'text/javascript';
        document.getElementsByTagName('head')[0].appendChild(jsGenScr);
        jsGenScr.onload = function () {
          initScripts(required_scripts, doInit);
          // console.log("its now loaded");
        }
      } else {
        initScripts(required_scripts, doInit);
      }
    }
  } else {
    // load
    initScripts(required_scripts, doInit);
  }
</script>
