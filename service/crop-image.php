<?php
namespace IkechukwuOkalia;
use \TymFrontiers\Generic,
    \TymFrontiers\InstanceError,
    \TymFrontiers\File;
require_once "../.appinit.php";

\require_login(false);

$errors = [];
$gen = new Generic;
$required = ["fid"];
$pre_params = [
  "fid" => ["fid","int"],
  "crp_ratio" => ["crp_ratio", "float"],
  "crp_cb" => ["crp_cb","username",3,35,[],'MIXED',["_","."]],
  "crp_minwidth" => ["crp_minwidth", "int"],
];
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen, false))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
// if ($params && empty($params["crp_ratio"])) $params["crp_ratio"] = 3/2 ;
if ($params && empty($params["crp_select"])) $params["crp_select"] = 320;
if (!empty($params['fid'])) {
  $image = File::findById($params["fid"]);
  if (!$image || $image->type_group !== "image") $errors[] = "No image file found for given [fid]: {$params["fid"]}";
}
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
    position: fixed;
    top: 50%;
    left: 50%;
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
  /* #acti-view{
    opacity: 0;
  } */
</style>
<input type="hidden" id="upldata" <?php if ($params) { foreach($params as $k=>$v) { echo "data-{$k}=\"{$v}\" "; } }?>>
<input type="hidden" id="do-crp" value="<?php echo empty($errors) ? 1 : 0; ?>">
<div id="fader-flow">
  <div class="view-space">
    <div class="grid-10-desktop center-desktop">
      <div class="sec-div color face-primary bg-white drop-shadow">
        <header class="padding -p10 color-bg align-c">
          <h2 class="margin -mnone"> <i class="fas fa-crop"></i> Crop image</h2>
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
            <div id="do-wait"> <div class="spinner"></div>  </div>
            <form
            id="image-cropper-form"
            method="post"
            action="/app/ikechukwuokalia/file-manager.soswapp/src/CropImage.php"
            data-validate="false"
            onsubmit="sos.form.submit(this, handleCrop); return false;"
            >
            <input type="hidden" name="form" value="image-cropper-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("image-cropper-form");?>">

            <input type="hidden" name="owner" value="<?php echo $session->name; ?>">
            <input type="hidden" name="fid" value="<?php echo $image->id; ?>">
            <input type="hidden" name="x" value="">
            <input type="hidden" name="y" value="">
            <input type="hidden" name="w" value="">
            <input type="hidden" name="h" value="">
          </form>
          <div id="crp-port">
            <div id="crp-tgt-wrap"> <img id="crp-target" class="ini-view" src="<?php echo $image->url(); ?>"></div>

            <p class="padding -p10 push-left" style="font-size:0.9em"> <i class="fas fa-info-circle"></i> Drag on image to select crop area. </p>
            <button type="submit" id="crp-save" disabled class="sos-btn blue push-right" form="image-cropper-form"> <i class="fas fa-check"></i> Crop &amp; save</button>
            <br class="c-f">
          </div>

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
    if (typeof window['param'] === "undefined") window.param = {};
    $.each($('#upldata').data(),function(k,v){
      window.param[k] = v;
    });
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
  (function(){
    if (parseBool($("#do-crp").val()) == true ) {
      $("#do-wait").fadeOut("slow").remove();
      let img = document.getElementById('crp-target'),
          imgW = img.naturalWidth,
          imgH = img.naturalHeight,
          canvasW = $(document).find('#crp-tgt-wrap').innerWidth(),
          canvasH = $(document).find('#crp-tgt-wrap').innerHeight(),
          selWidth =  imgW * (40/100),
          selHeight = imgH * (40/100), //imgH / 2,
          posX = ((imgW / 2) - (selWidth / 2)) ,
          posY = ((imgH / 2) - (selHeight / 2));
      // console.log(`imgW: ${imgW}\n imgH: ${imgH}\n canvasW: ${canvasW} \n canvasH: ${canvasH}\n selWidth: ${selWidth}\n selHeight: ${selHeight}\n posX: ${posX}\n posY: ${posY}`);
      const updateJprop = (sel) => {
        $.each(sel, function(name, value) {
          $(`input[name="${name}"]`).val(value);
        });
        $('#crp-save').prop("disabled", false);
      }
      $('#crp-target').removeClass('ini-view').Jcrop({
        aspectRatio: param.crp_ratio,
        onSelect: updateJprop,
        minSize : [380, 380],
        boxWidth : canvasW - 10
        // setSelect:   [ selWidth, selHeight, posX, posY ]
      });

    };
  })();
</script>
