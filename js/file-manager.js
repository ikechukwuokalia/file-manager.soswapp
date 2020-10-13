function populateDnav () {
  let val_param = {},
  val_param_count = 0;
  $.each(param, function(i, val) {
    if (val.length > 0) {
      val_param[i] = val;
      ++ val_param_count;
    }
  });
  if (val_param_count > 0) {
    $(document).find('#sos-dnav-wrap li a').each(function(_i){
      if ($(this).attr('href') !== '#') {
        let link = $(this).attr('href');

        $.each(val_param, function(k,v){
          if (url.parse(link,"pathname").indexOf(k)) {
            if (k !== 'type') link = set_get(link,k,v);
          } else {
            link = set_get(link,k,v);
          }
        });
        $(this).attr('href', link);
      }
    });
  }
};
if (typeof io == "undefined") window.io = {};
// function handleUpload (resp) {
// };
// files page operators
const alterFile = (fid = 0, action = "DELETE") => {
  let actions = ["DELETE", "LOCK", "WATERMARK"];
  if( confirm(`Are you sure you want to ${action.toLowerCase()} this file?`) ){
    $("#file-mod-form input[name=fid]").val(fid);
    $("#file-mod-form input[name=action]").val(action);
    $('#file-mod-form').submit();
  }
};
const rotateImage = (fid = 0, deg = 90) => {
  if( confirm(`Are you sure you want to rotate this image?`) ){
    $("#file-mod-form input[name=fid]").val(fid);
    $("#file-mod-form input[name=action]").val("ROTATE");
    $("#file-mod-form input[name=degree]").val(deg);
    $('#file-mod-form').submit();
  }
};
const fetchFiles = (data) => {
  // console.log(data);
  if( data && data.status == "0.0" && data.files.length > 0){
    $("#file-list").removeClass('no-file');
    $('#file-pages, .pages').text(data.pages);
    $('#file-records').text(data.records);
    $('#file-page').val(data.page);
    $('.page').text(data.page);
    $('#file-limit').val(data.limit);
    if( data.has_next_page ) $('#file-next-page').data('page',data.next_page);
    if( data.has_previous_page ) $('#file-previous-page').data('page',data.previous_page);
    $('#file-list').listFiles( data.files );
    removeAlert();
  }else{
    $('#file-pages, .pages').text(0);
    $('.page').text(0);
    $('#file-list').html('').addClass('no-file');
  }
}
$.fn.listFiles = function(obj){
  var html = "";
  return false;
  $.each(obj, function(i, el) {
    html += "<tr>";
      // html += ( "<td>" + el.id + "("+(el.active ? 'ACTIVE' : 'INACTIVE')+")</td>" );
      html += "<td>";
      html += (
        "<a class=\"inherit\" href=\"javascript:void(0)\" onclick=\"faderBox.url(location.origin + '/admin/work-domain-make',{name:'"+el.name+"',callback:'refreshList'},{exitBtn:true});\"> <i class=\"fas fa-edit\"></i> "+el.name + " ("+el.acronym+") </a>"
      );
      html+= "</td>";
      html += ( "<td>" +el.description+ "</td>" );
      html += ( "<td>" +el.work_paths+ "</td>" );

      html += "<td>";
        html += ("<a class=\"blue\" href=\"javascript:void(0)\" onclick=\"faderBox.url(location.origin + '/admin/work-path-make',{domain:'"+el.name+"'},{exitBtn:true});\"> <i class=\"fas fa-plus\"></i> Add work path </a>");

        html += (" | <a class=\"red\" href=\"javascript:void(0)\" onclick=\"doDelete('"+el.name+"')\"> <i class=\"fas fa-trash\"></i> Delete </a>");

      html+= "</td>";
    html += "</tr>";
  });
  $(this).html(html);
};
$.fn.pageFile = function(){
  let page = parseInt($(this).data('page')),
      cur_page = parseInt($('#file-page').val());
  if( page > 0 && page !== cur_page){
    $('#file-page').val(page);
    $('#file-query-form').submit();
  }
};

// ---------------------------------
// relative functions
const pConf = sos.config.page;
function checkPost (resp = {}) {
  if( resp && resp.status === "0.0"){
    setTimeout(function(){
      removeAlert();
      requery();
    },1800);
  }
}
window.requery = function () {
  if ($('#query-form').length > 0 ) $('#query-form').submit();
}
function listFiles (files) {
  let wrapr = $(pConf.datacontainer);
  if (wrapr.length > 0 && files.length > 0) {
    wrapr.removeClass("no-result");
    let html = "";
    $.each(files, function(_i, file){
      html += `<div class="grid-6-phone grid-4-tablet grid-3-laptop push-left">`;
      html += `<div class="file-box thumb ${file.type_group}">`;
      html += `<div class="thumb-icon-wrap"> <a href="#" onclick="sos.fbx.url('/app/ikechukwuokalia/file-manager.soswapp/service/file-viewer.php', {fid:${file.id}},{exitBtn:true});"><span class="thumb-icon"></span></a> </div>`;
      if (file.type_group === "image") {
        html += `<a href="#" onclick="sos.fbx.url('/app/ikechukwuokalia/file-manager.soswapp/service/file-viewer.php', {fid:${file.id}},{exitBtn:true});"> <img src="${setGet(file.url, {
          tn : (new Date()).getTime(),
          getsize : '320'
        })}" alt="${file.min_caption}"> </a>`;
      }
      html += `<div class="thumb-caption">`;
      html += `<button class="expand-tool sos-btn blue show-phone show-tablet" onclick="$(this).parent().toggleClass('xpand');"> <i class="fas fa-expand-alt fa-lg"></i></button>`;
      html += `<h3 class="fw-lighter">${file.min_caption}</h3>`;
      html += `<p class="btn-stack">`;
      if (param.set_as !== "" && param.set_as !== file.set_as) {
        html += `<button onclick="sos.fbx.url('/app/ikechukwuokalia/file-manager.soswapp/service/set-default.php',{set_as : '${param.set_as}', callback : '${param.set_cb}', fid : ${file.id}, set_ttl : '${param.set_ttl}'},{exitBtn : true});" title="Set as ${param.set_ttl}" class="file-action sos-btn green"> <i class="fas fa-check-circle"></i> <span class="btn-text">Set as</span></button>`;
      } if (file.set_as) {
        // unset button
        html += `<button onclick="sos.fbx.url('/app/ikechukwuokalia/file-manager.soswapp/service/unset-default.php',{callback : 'requery', fid : ${file.id}},{exitBtn : true});" title="Unset default" class="file-action sos-btn grey"> <i class="far fa-circle"></i> <span class="btn-text">Unset</span></button>`;
      }
      if (file.locked == false) {
        html += `<button onclick="sos.fbx.url('/app/ikechukwuokalia/file-manager.soswapp/service/set-caption.php',{fid:${file.id}, callback : 'requery'},{exitBtn: true});" class="file-action sos-btn blue"> <i class="fas fa-edit"></i> <span class="btn-text">Edit caption</span></button>`;
        if (file.type_group == "image") {
          html += `<button onclick="goCrop(${file.id},{crp_cb:'requery'})" class="file-action sos-btn blue"> <i class="fas fa-crop"></i> <span class="btn-text">Crop image</span></button>`;
          if (file.watermarked == false && file.can_watermark == true) {
            // html += `<button onclick="alterFile(${file.id}, 'WATERMARK');" class="file-action sos-btn black"> <i class="fas fa-sign"></i> <span class="btn-text">Watermark</span></button>`;
          }
          html += `<button onclick="rotateImage(${file.id},90);" class="file-action sos-btn yellow"> <i class="fas fa-undo"></i> <span class="btn-text">Rotate</span></button>`;
          html += `<button onclick="rotateImage(${file.id},-90);" class="file-action sos-btn yellow"> <i class="fas fa-redo"></i> <span class="btn-text">Rotate</span></button>`;
        }
        html += `<button onclick="alterFile(${file.id}, 'LOCK');" class="file-action sos-btn black"> <i class="fas fa-lock"></i> <span class="btn-text">Lock file</span></button>`
      } if (!file.set_as) {
        html += `<button onclick="alterFile(${file.id}, 'DELETE');" class="file-action sos-btn red"> <i class="fas fa-trash"></i> <span class="btn-text">Delete</span></button>`;
      }
      html += `</p>`;
      html += `</div>`;
      html += `</div>`;
      html += `</div>`;
    });
    wrapr.html(html);
  }
};
const doFetch = (resp) => {
  if( resp && resp.status == "0.0" && object_length(resp[pConf.datasearch]) > 0){
    $('.pages-text').text(resp.pages); $('.pages-val').val(resp.pages); sos.config.page["pages"] = resp.pages;
    $('.records-text').text(resp.records); $('.records-val').val(resp.records); sos.config.page["records"] = resp.records;
    $('.page-val').val(resp.page); $('.page-text').text(resp.page); sos.config.page["page"] = resp.page;
    $('.limit-val').val(resp.limit); $('.limit-text').text(resp.limit); sos.config.page["limit"] = resp.limit;
    if( resp.has_next_page ) $('#next-page').data('page',resp.next_page); sos.config.page["hasNextPage"] = resp.has_next_page;
    if( resp.has_previous_page ) $('#previous-page').data('page',resp.previous_page); sos.config.page["hasPreviousPage"] = resp.has_previous_page;
    if (typeof window[pConf.datahandle] === "function") {
      window[pConf.datahandle](resp[pConf.datasearch]);
    }
    removeAlert();
    pageNatr();
    if (history.pushState) {
      let newurl = setGet(location.href, {
        page    : resp.page,
        limit   : resp.limit,
        search  : resp.search,
        type    : resp.type,
        id      : resp.id,
        ids     : resp.ids
      });
      window.history.pushState({path:newurl},'',newurl);
    }
  } else {
    $(`${pConf.datacontainer}`).html('').addClass("no-result");
  }
  pageNatr();
};
const pageNatr = () => {
  let elem = $(`${pConf.datapager}`);
  if (pConf.hasPreviousPage) {
    $(document).find("button.prev-page-btn").remove();
    elem.append($(`<button class='sos-btn face-secondary prev-page-btn' onclick="pageTo(${pConf.page - 1});"> <i class="fas fa-2x fa-angle-left"></i></button>`));
  } else {
    $(document).find("button.prev-page-btn").remove();
  }
  if (pConf.hasNextPage) {
    $(document).find("button.next-page-btn").remove();
    elem.append($(`<button class='sos-btn face-secondary next-page-btn' onclick="pageTo(${pConf.page + 1});"> <i class="fas fa-2x fa-angle-right"></i></button>`));
  } else {
    $(document).find("button.next-page-btn").remove();
  }
};
const pageTo = (page = 0) => {
  if (page > 0) {
    $(`.page-val`).val(page);
    requery();
  }
};
function doPost(resp = {}) {
  if( resp && resp.status == '0.0' || resp.errors.length < 1 ){
    if( ('callback' in param) && typeof window[param.callback] === 'function' ){
      faderBox.close();
      window[param.callback](resp);
    }else{
      setTimeout(function(){
        faderBox.close();
        removeAlert();
        requery();
      },1500);
    }
  }
}

// ---------------------------------

// pre-activity
(function(){
  // sos.dnav.extend();
})();
