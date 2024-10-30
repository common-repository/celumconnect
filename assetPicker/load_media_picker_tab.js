var picker_tab = null;
var set_features_image = false;
var insert_images = false;
function celum_connect_createAssetPickerTab(){
    picker_tab = Celum.AssetPicker.create({
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
                celum_connect_sendAssetsToServerTab(assets);
            }
        }
    });
}

function celum_connect_sendAssetsToServerTab(assets){
    var option = jQuery("input[name='optradio']:checked").val();
    celum_connect_set_messageTab('Loading..', false, true);
    var post_id = jQuery("#post_ID").val();
    var data = {
        action: 'celum_connect_assets_to_media_library',
        assets:  assets,
        post_id: post_id,
        option: option
    };
    jQuery.ajax ({
        type: 'POST',
        url: ajaxurl,
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.error !== undefined && response.error !== "") {
                celum_connect_set_messageTab(response.error, true, false);
            } else {
                if (response.result !== "" && response.result !== undefined) {
                    jQuery('#picker-wrap').show();
                    jQuery("#asset_picker_message").slideUp();
                    jQuery("#loading_overlay").hide();
                    celum_connect_set_messageTab('Assets added to media library', true, false);
                    if (wp.media.frame.content !== undefined && wp.media.frame.content.get() !== null) {
                        wp.media.frame.content.get().collection._requery(true);
                        //wp.media.frame.content.get().options.selection.reset();
                    }
                    if(response.option === 'download') {
                        jQuery('#menu-item-browse').click();
                        if(wp.media.frame.content !== undefined && wp.media.frame.content.get() !== null && wp.media.frame.content.get().model.attributes['id'] === 'featured-image'){
                            wp.data.dispatch( 'core/editor' ).editPost({ featured_media: response.result[0].attachment_id });
                            parent.jQuery(".media-modal-close").click();
                        }
                        if(insert_images) {
                            parent.jQuery(".media-modal-close").click();
                        }else {
                            if(wp.media.frame.state() !== undefined) {
                                let selection =  wp.media.frame.state().get('selection');
                                response.result.forEach(function(asset) {
                                    let attachment = wp.media.attachment(asset['attachment_id']);
                                    attachment.fetch();
                                    selection.add( attachment ? [ attachment ] : [] );
                                });
                            }
                        }
                    }else {
                        jQuery("#urlTable").find("tr:gt(0)").remove();
                        let i = 0;
                        for(let result of response.result) {
                            let row = '<tr>' +
                                '<td>'+result['asset']['id']+'</td>' +
                                '<td>'+result['asset']['name']+'</td>' +
                                '<td>';

                            let j = 0;
                            for(const [key, value] of Object.entries(result['urls'])) {
                                if (j > 0) {
                                    row +='<br>'
                                }
                                row += key + ': ' + value['url'];
                                j++;
                            }
                            row += '</td>' +
                                '<td>';

                            let k = 0;
                            for(const [key, value] of Object.entries(result['urls'])) {
                                if (k > 0) {
                                    row +='<br>'
                                }
                                if(value['publicUrl_description'] != null && value['publicUrl']) {
                                    row += value['publicUrl_description'] + ': ' + value['publicUrl'];
                                    k++;
                                }
                            }

                            row += '</td>';
                            row += '</tr>';
                            /*row += '</td>' +
                                '<td>'+result['publicUrl']+'</td>' +
                                '<td>';
                            if(result['publicUrl']) {
                                row += '<button class="copyButton" type="button" title="Copy Public URL to Clipboard" onclick="copyPublicUrlToClipboard('+i+')">' +
                                    '<span class="  dashicons dashicons-clipboard"></span>\n</button>' +
                                    '<input type="text" class="copyText" value="'+result['publicUrl']+'" id="copyText_'+i+'">';

                            }
                            row += '</td>' +
                                '</tr>';*/

                            jQuery('#urlTable').append(row);
                            i++;
                        }
                        jQuery('#table-wrap').show()
                    }
                } else {
                    celum_connect_set_messageTab('ERROR', true, false);
                }
            }
        }
    });
}

function copyPublicUrlToClipboard(i) {
    /* Get the text field */
    var copyText = document.getElementById("copyText_" + i);
    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */
    /* Copy the text inside the text field */
    document.execCommand("copy");
    alert("Copied the text: " + copyText.value);
}
function celum_connect_set_messageTab(message, error, loading) {
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
