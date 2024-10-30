var picker;
if(!picker){
    celum_connect_createAssetPickerMedia();
}
function celum_connect_createAssetPickerMedia(){
    picker = Celum.AssetPicker.create({
        container: 'picker-wrap',
        basePath: '../',
        jsConfigPath: '../../config.js?'+(new Date()).getTime(),
        listeners: {
            transfer: function (id, selections) {
                var assets = [];
                for (var i = 0; i < selections.length; i++) {
                    var asset = selections[i];
                    assets.push({
                        id: asset['id'],
                        selectedDownload: asset['selectedDownloads'],
                        fileCategory: asset['fileCategory'],
                        version: asset['versionInformation']['versionId'],
                        name: asset['name'],
                        fileExtension: asset['fileInformation']['fileExtension']
                    });
                }
                var option = jQuery("input[name='optradio']:checked").val();
                celum_connect_sendAssetsToServer(assets, option);
            }
        }
    });
}

function celum_connect_sendAssetsToServer(assets, picker_type){
    celum_connect_set_message(messages.loading, false, true);
    var imgcontent = "";
    var post_id = celum_connect_getQueryVariable('post_id');
    var data = {
        action: 'celum_connect_media_save_assets',
        wpnonce: plugin_data.my_security_nonce,
        assets:  assets,
        picker_type: picker_type,
        post_id: post_id
    };
    jQuery.ajax ({
        type: 'POST',
        url: ajaxurl,
        data: data,
        dataType: 'json',
        success: function(response) {
            var imgurl;
            var name;
            if (response.error !== undefined && response.error !== "") {
                celum_connect_set_message(response.error, true, false);
            } else {
                if (response.result !== "" && response.result !== undefined) {
                    for (var i = 0; i < response.result.length; i++) {
                        imgurl = response.result[i]['url'];
                        name = response.result[i]['name'];
                        if(response.result[i]['type'] === 'image'){
                            imgcontent = celum_connect_get_image_template(imgurl);
                        }else if(response.result[i]['type'] === 'document' || response.result[i]['type'] === 'unknown' || response.result[i]['type'] === 'text'){
                            imgcontent = celum_connect_get_document_template(imgurl, name);
                        }else if(response.result[i]['type'] === 'video'){
                            imgcontent = celum_connect_get_video_template(imgurl);
                        }else if(response.result[i]['type'] === 'audio'){
                            imgcontent = celum_connect_get_audio_template(imgurl);
                        }
                        celum_connect_add_to_editor(imgcontent);
                    }
                    jQuery('#picker-wrap').show();
                    parent.jQuery(".media-modal-close").click();
                    jQuery("#asset_picker_message").slideUp();
                    jQuery("#loading_overlay").hide();
                } else {
                    celum_connect_set_message(messages.save_error, true, false);
                }
            }
        }
    });
}

function celum_connect_getQueryVariable(variable)
{
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if(pair[0] === variable){return pair[1];}
    }
    return(false);
}

function celum_connect_add_to_editor(content) {
    if(content !== "") {
        parent.tinyMCE.execCommand('mceInsertContent',false,content);
    }
}

function celum_connect_get_image_template(img) {
    var template = '<img src="{src}"/>';
    template = template.replace('{src}', img);
    return template;
}

function celum_connect_get_document_template(url, name) {
    var template = '<a href="{src}">{name}</a>';
    template = template.replace('{src}', url);
    template = template.replace('{name}', name);
    return template;
}

function celum_connect_get_video_template(url) {
    var template = '[video mp4="{src}"][/video]';
    template = template.replace('{src}', url);
    return template;
}

function celum_connect_get_audio_template(url) {
    var template = '[audio mp3="{src}"][/audio]';
    template = template.replace('{src}', url);
    return template;
}

function celum_connect_set_message(message, error, loading) {
    var messageBox = jQuery('#asset_picker_message_box');
    var messageBoxMessage = jQuery('#asset_picker_message');
    var loadingOverlay = jQuery('#loading_overlay');
    var pickerWrap = jQuery('#picker-wrap'), eclass;
    if(error) {eclass = "picker_error";} else {eclass = "picker_msg";}
    if(!messageBoxMessage.length){
        messageBox.append('<div id="asset_picker_message" class="' + eclass + '">' + message + '</div>');
    }else{
        messageBoxMessage.removeClass("picker_msg");
        messageBoxMessage.addClass("picker_error");
        messageBoxMessage.show();
        messageBoxMessage.html(message);
    }
    if(loading){
        messageBoxMessage.removeClass("picker_error");
        messageBoxMessage.addClass("picker_msg");
        if(!loadingOverlay.length){
            pickerWrap.append('<div id="loading_overlay" class="loading">Loading&#8230;</div>');
        }else{
            loadingOverlay.show();
        }
    }else{
        loadingOverlay.hide();
    }
}
