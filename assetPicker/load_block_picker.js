let picker;
function startPicker (props) {
    let assetPicker = jQuery('#assetPicker');
    assetPicker.show ();
    jQuery('#picker-button').hide ();
    if(picker === null || picker === undefined || picker.length === undefined){
        celum_connect_createAssetPickerBlock (props);
    }
    assetPicker.height (500);
}
function celum_connect_createAssetPickerBlock (props) {
    picker = Celum.AssetPicker.create ({
        container: 'assetPicker',
        basePath: '../',
        jsConfigPath: '../../config.js?' + (new Date ()).getTime (),
        listeners: {
            transfer: function (id, selections) {
                let assets = [];
                for (let i = 0; i < selections.length; i++) {
                    let asset = selections[i];
                    assets.push ({
                        id: asset['id'],
                        selectedDownload: asset['selectedDownloads'],
                        fileCategory: asset['fileCategory'],
                        version: asset['versionInformation']['versionId'],
                        name: asset['name'],
                        fileExtension: asset['fileInformation']['fileExtension']
                    });
                }
                celum_connect_sendAssetsToServer (assets, isDownload === undefined ? false : isDownload);
                picker.destroy();
                picker = null;
                removeBlock (props);
            }
        }
    });

}
function removeBlock (props) {
    wp.data.dispatch ('core/editor').removeBlock (props.clientId);
}

function createBlock (assets) {
    for (let i in assets) {
        if(Array.isArray(assets[i]['description'])){
            let localizedDesc = '';
            for(let j in assets[i]['description']){
                if(assets[i]['description'][j]['locale'] === plugin_data.locale.substring(0, 2)){
                    localizedDesc = assets[i]['description'][j]['value'];
                }
            }
            assets[i]['description'] = localizedDesc;
        }
        let block;
        switch (assets[i]['type']) {
            case 'image':
                block = wp.blocks.createBlock ('core/image', {
                        url: assets[i]['url'],
                        caption: assets[i]['description'] !== null && typeof assets[i]['description'] === 'string' ? assets[i]['description'] : ''
                    }
                );
                break;
            case 'video':
                block = wp.blocks.createBlock ('core/video', {
                        src: assets[i]['url'],
                        caption: assets[i]['description'] !== null && typeof assets[i]['description'] === 'string' ? assets[i]['description'] : ''
                    }
                );
                break;
            case 'audio':
                block = wp.blocks.createBlock ('core/audio', {
                        src: assets[i]['url']
                    }
                );
                break;
            default:
                block = wp.blocks.createBlock ('core/file', {
                        href: assets[i]['url'],
                        fileName: assets[i].name,
                        textLinkHref: assets[i].name,
                    }
                );
        }
        wp.data.dispatch ('core/editor').insertBlocks (block);
    }
}


function celum_connect_sendAssetsToServer (assets, isDownload) {
    wp.data.dispatch ('core/notices').createInfoNotice("Send Assets to Server...");
    let post_id = wp.data.select ("core/editor").getCurrentPostId ();
    let data = {
        action: 'celum_connect_block_save_assets',
        assets: assets,
        post_id: post_id,
        isDownload: isDownload
    };
    jQuery.ajax ({
        type: 'POST',
        url: ajaxurl,
        data: data,
        dataType: 'json',
        success: function (response) {
            console.log(response);
            if (response.error !== undefined && response.error !== "") {
                wp.data.dispatch ('core/notices').createErrorNotice (response.error);
            } else {
                if (response.result !== "" && response.result !== undefined) {
                        createBlock (response.result);
                    jQuery('#assetPicker').hide ();
                    jQuery('#picker-button').show ();
                    jQuery('.edit-post-layout .components-notice-list .components-notice .components-notice__dismiss').click();
                } else {
                    wp.data.dispatch ('core/notices').createErrorNotice (messages.save_error)
                }
            }
        }
    });
}
