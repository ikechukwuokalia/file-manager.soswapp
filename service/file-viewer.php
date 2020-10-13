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
  "fid" => ["fid","int"]
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
<div id="fader-flow">
  <div class="view-space">
    <div class="sec-div padding -p10">&nbsp;</div>
    <div class="grid-12-tablet grid-10-laptop center-laptop">
      <div class="sec-div color asphalt bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h2 class="fw-lighter"> <i class="fas fa-info-circle"></i> <?php echo $file->nice_name; ?></h2>
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
            <div class="grid-5-tablet">
              <?php if ($file->type_group == "image"): ?>
                <a href="<?php echo $file->url(); ?>" data-caption="<?php echo $file->caption; ?>" data-fancybox="solo">
                  <img style="max-width:100%; height: auto" src="<?php echo Generic::setGet($file->url(), ["getsize" => "320"]) ?>" alt="<?php echo $file->caption; ?>">
                </a>
              <?php endif; ?>
              <p>
                <a class="sos-btn blue" target="_blank" href="<?php echo Generic::setGet($file->url(), ["dl"=>1]); ?>"> <i class="fas fa-download"></i> Download file</a>
              </p>
            </div>
            <div class="grid-7-tablet">
              <h3>File information</h3>
              <table class="horizontal">
                <tr>
                  <th>ID</th>
                  <td><?php echo $file->id; ?></td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td><?php echo (bool)$file->locked() ? "Locked" : "Unlocked"; ?></td>
                </tr>
                <tr>
                  <th>Checksum</th>
                  <td  class="form mat-ui"> <input class="border -bnone" readonly onclick="$(this).select()" type="text" value="<?php echo $file->checksum(); ?>"></td>
                </tr>
                <tr>
                  <th>Name</th>
                  <td><?php echo $file->nice_name, " ({$file->name()})"; ?></td>
                </tr>
                <tr>
                  <th>Caption</th>
                  <td><?php echo $file->caption; ?></td>
                </tr>
                <tr>
                  <th>Type</th>
                  <td><?php echo \ucfirst($file->type_group), " ({$file->type()})"; ?></td>
                </tr>
                <tr>
                  <th>Bytes</th>
                  <td><?php echo $file->size(); ?></td>
                </tr>
                <tr>
                  <th>Privacy</th>
                  <td><?php echo $file->privacy; ?></td>
                </tr>
                <tr>
                  <th>Owner</th>
                  <td><?php echo $file->owner; ?></td>
                </tr>
                <tr>
                  <th>Creator</th>
                  <td><?php echo $file->creator(); ?></td>
                </tr>
                <tr>
                  <th>Created</th>
                  <td><?php echo $file->created(); ?></td>
                </tr>
                <tr>
                  <th>Last updated</th>
                  <td><?php echo $file->updated(); ?></td>
                </tr>
              </table>
            </div>
            <br class="c-f">
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
