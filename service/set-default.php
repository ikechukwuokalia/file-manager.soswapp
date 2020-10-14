<?php
namespace IkechukwuOkalia;
use \TymFrontiers\Generic,
    \TymFrontiers\InstanceError,
    \TymFrontiers\File;
require_once "../.appinit.php";

\require_login(false);

$errors = [];
$gen = new Generic;
$required = ["fid", "set_as", "set_ttl"];
$pre_params = [
  "fid" => ["fid","int"],
  "set_as" => ["set_as", "text", 2,0],
  "set_multiple" => ["set_multiple", "boolean"],
  "set_ttl" => ["set_ttl", "text", 5, 32],
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
if (empty($params['callback'])) $params['callback'] = "requery";
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
              id="file-set-form"
              class="block-ui"
              method="post"
              action="/app/ikechukwuokalia/file-manager.soswapp/src/SetDefault.php"
              data-validate="false"
              onsubmit="sos.form.submit(this, doneSet); return false;"
            >
            <input type="hidden" name="form" value="file-set-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("file-set-form");?>">

            <input type="hidden" name="user" value="<?php echo $session->name; ?>">
            <input type="hidden" name="fid" value="<?php echo $file->id; ?>">
            <input type="hidden" name="set_as" value="<?php echo $params['set_as']; ?>">
            <input type="hidden" name="set_multiple" value="<?php echo (bool)$params['set_multiple'] ? 1 : 0; ?>">

            <div class="grid-12-tablet">
              <p>Do you what to set <b><?php echo $file->caption; ?> (<?php echo $file->nice_name; ?>)</b> [default] as <b><?php echo $params['set_ttl']; ?></b> ? </p>
            </div>
            <div class="grid-6-tablet">
              <button type="submit" class="sos-btn asphalt"> <i class="far fa-check-circle fa-lg"></i> Set file</button>
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
  function doneSet(resp = {}) {
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
