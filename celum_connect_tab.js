var Celum = Celum || {};

var interval = setInterval(function() {
    if(wp.media) {
        let l10n = wp.media.view.l10n;
        wp.media.view.MediaFrame.Select.prototype.browseRouter = function( routerView ) {
            routerView.set({
                upload: {
                    text:     l10n.uploadFilesTitle,
                    priority: 20
                },
                browse: {
                    text:     l10n.mediaLibraryTitle,
                    priority: 40
                },
                celum_connect: {
                    text:     "Celum:connect",
                    priority: 60
                }
            });
        };
        clearInterval(interval);
    }
}, 1000);



jQuery(document).ready(function($){
    if ( wp.media ) {
        wp.media.view.Modal.prototype.on( "open", function() {
            if($('body').find('.media-modal-content .media-frame-router button.media-menu-item.active')[0].innerText === "Celum:connect"){
                prepareTab();
                celum_connect_createAssetPickerTab();
            }
        });
        $(wp.media).on('click', '.media-frame-router button.media-menu-item.active', function(e){
            if(e.target.innerText === "Celum:connect"){
                prepareTab();
                celum_connect_createAssetPickerTab();
            }else {
                restoreTab();
            }

        });
    }


    function restoreTab() {
        jQuery( '.media-frame-toolbar' ).css({'display': 'block'});
        jQuery( '.media-frame-content' ).css({'bottom': '61px'});
        picker_tab = null;
    }

    function prepareTab() {
        jQuery( '.media-frame-toolbar' ).css({'display': 'none'});
        jQuery( '.media-frame-content' ).css({'bottom': '0'});
        creat_celum_connect_tab();
    }

    function creat_celum_connect_tab() {
        var html = '<div class="celum-connect-tab">';
        html +='<div id="asset_picker_message_box"></div>\n' +
            '<div id="table-wrap">' +
            '<table id=urlTable>\n<tr><th>ID</th><th>Name</th><th>Urls</th><th>Public Url</th></tr>' +
            '</table>\n' +
            '</div>\n' +
            '<div id="picker-control">\n' +
            '    <label class="radio-inline">\n' +
            '        <input type="radio" checked name="optradio" value="download">Load assets to Media Library\n' +
            '    </label>\n' +
            '    <label class="radio-inline">\n' +
            '        <input type="radio" name="optradio" value="link">Show Download Links\n' +
            '    </label>\n' +
            '</div>' +
            '<div id="picker-wrap"></div>';
        html += '</div>';
        if (jQuery("body .media-modal-content .media-frame-content").length > 1) {
            jQuery("body .media-modal-content .media-frame-content")[0].remove();
        }
        jQuery('body .media-modal-content .media-frame-content')[0].innerHTML = html;


    }
});


