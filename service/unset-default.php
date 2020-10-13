<?php
namespace IkechukwuOkalia;
use \TymFrontiers\Generic,
    \TymFrontiers\InstanceError,
    \TymFrontiers\File;
require_once "../.appinit.php";
require_once  APP_ROOT . "/src/default.conf.php";

\require_login(false);

$errors = [];
$gen = new Generic;
$required = ["fid"];
$pre_params = [
  "fid" => ["fid","int"],
  "callback" => ["callback","username",3,35,[],'MIXED',["_","."]]
];
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
// if ($params && empty($params["crp_ratio"])) $params["crp_ratio"] = 3/2 ;
if (!empty($params['fid'])) {
  $file = File::findById($params["fid"]);
  if (!$file) $errors[] = "No file found for given [fid]: {$params["fid"]}";
}
?>
<input type="hidden" id="setdata" <?php if ($params) { foreach($params as $k=>$v) { echo "data-{$k}=\"{$v}\" \r\n"; } }?>>
<div id="fader-flow">
  <div class="view-space">
    <div class="sec-div padding -p20">&nbsp;</div>
    <div class="grid-6-tablet grid-5-desktop center-tablet">
      <div class="sec-div color asphalt bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h2 class="fw-lighter"> <i class="fas fa-file-alt"></i> File defaults</h2>
        </header>

        <div class="sec-div padding -p20">
          <?php if(!empty($errors)){ ?>
            <h3>Unresolved error(s)</h3>
            <ol>
              <?php foreach($errors as $err){
                echo " <li>{$err}</li>";
              } ?>
            </ol>
          <?php }else{ ?>
            <form
              id="file-unset-form"
              class="block-ui"
              method="post"
              action="/app/ikechukwuokalia/file-manager.soswapp/src/UnsetDefault.php"
              data-validate="false"
              onsubmit="sos.form.submit(this, unset); return false;"
            >
            <input type="hidden" name="form" value="file-unset-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("file-unset-form");?>">

            <input type="hidden" name="user" value="<?php echo $session->name; ?>">
            <input type="hidden" name="fid" value="<?php echo $file->id; ?>">

            <div class="grid-12-tablet">
              <p>Are you sure you want to clear default setting(s) for this file?</p>
              <p>
                <b><?php echo $file->nice_name; ?></b> <br>
                <?php echo $file->caption; ?> <br> <br>
                <i>- By <?php echo $file->creator(); ?></i>
              </p>
            </div>
            <div class="grid-6-tablet">
              <button type="submit" class="sos-btn asphalt"> <i class="far fa-circle fa-lg"></i> Unset file</button>
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
  var stData = $('#setdata').data();
  function unset(resp = {}) {
    if( resp && resp.status == '0.0' || resp.errors.length < 1 ){
      if( ('callback' in stData) && typeof window[stData.callback] === 'function' ){
        faderBox.close();
        window[stData.callback](resp);
      }else{
        setTimeout(function(){
          faderBox.close();
          removeAlert();
          // requery();
        },1500);
      }
    }
  }

  (function(){
  })();
</script>
