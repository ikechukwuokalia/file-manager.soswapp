window.handleUpload = function(resp) {
  sos.fbx.close();
  if (typeof resp.uploaded_files !== "undefined" && object_length(resp.uploaded_files) === 1) {
    let images = [];
    $.each(resp.uploaded_files, function(fid,obj){
      if (obj.group == "image") images.push(obj.id);
    });
    if (object_length(images) === 1) {
      // initialize crop
      setTimeout(function(){
        goCrop(images[0]);
      },550);
    }
  }
};
function goCrop (imgId, exParam = {}) {
  let crp_shp = {
    square : 1,
    rectangle : 3/2
  };
  let prm = {
    fid : imgId,
    crp_ratio : (
      in_array(param.crp_shape, ["square","rectangle"])
        ? crp_shp[param.crp_shape]
        : param.crp_ratio
    ),
    crp_cb : param.crp_cb,
    crp_select : param.crp_select,
    crp_minwidth : param.crp_minwidth
  };
  if (object_length(exParam) > 0) {
    $.each(exParam, function(k, v){
      prm[k] = v;
    });
  }
  sos.fbx.url(`/app/ikechukwuokalia/file-manager.soswapp/service/crop-image.php`,prm,{exitBtn:true});
};
function handleCrop (resp) {
  if (resp.status === "0.0" || object_length(resp.errors) === 0) {
    sos.fbx.close();
    if (typeof window[param.crp_cb] === "function") window[param.crp_cb](resp);
  }
};
