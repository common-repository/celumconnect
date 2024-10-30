const { createHigherOrderComponent } = wp.compose;
const { addFilter } = wp.hooks;
let isDownload;
(function (blocks, editor, components, i18n) {
    let el = wp.element.createElement;
    let registerBlockType = wp.blocks.registerBlockType;
    let InspectorControls = wp.editor.InspectorControls;
    let ToggleControl = components.ToggleControl;
    const getBlockList = () => wp.data.select( 'core/editor' ).getBlocks();
    let blockList = getBlockList();
    registerBlockType ('celum-connect/celum-connect-block', { // The name of our block. Must be a string with prefix. Example: my-plugin/my-custom-block.
        title: i18n.__ ('celum:connect'), // The title of our block.
        description: i18n.__ ('A custom block for CELUM assets'), // The description of our block.
        icon: 'admin-media', // Dashicon icon for our block. Custom icons can be added using inline SVGs.
        category: 'common', // The category of the block.
        attributes: { // Necessary for saving block content.
            isDownload: {
                default: true,
                type: 'boolean'
            },
        },
        edit: function (props) {
            let isExpired = plugin_data.expired;
            wp.data.subscribe(() => {
                const newBlockList = getBlockList();
                const blockListChanged = newBlockList !== blockList;
                blockList = newBlockList;
                if ( blockListChanged ) {
                }
            });
            this.props = props;
            isDownload = props.attributes.isDownload;
            if(!isExpired){
                return [
                    el(InspectorControls, { key: 'inspector' }, // Display the block options in the inspector panel.
                        el(components.PanelBody, {
                                title: i18n.__('celum:connect'),
                                className: 'celum-connect',
                                initialOpen: true
                            },
                            el('p', {}, i18n.__('Choose the insert type for the assets (Linking / Download).')),
                            el(ToggleControl, {
                                label: i18n.__('Insert type'),
                                help:  isDownload ? 'type is Download' : 'type is Linking' ,
                                checked: isDownload,
                                onChange: function (event) {
                                    props.setAttributes({ isDownload: event });
                                }
                            }))
                    ),
                    el ('div', {className: props.className},
                        el ('div', {
                            id: "assetPicker"
                        }),
                        el ('button', {
                            'class': 'picker-button',
                            id: 'picker-button',
                            'title': 'Add Assets',
                            onClick: function () {
                                startPicker (props);
                            }
                        }, i18n.__('Add Assets...')),
                    )
                ]
            } else {
                return [
                    el(InspectorControls, { key: 'inspector' }, // Display the block options in the inspector panel.
                        el(components.PanelBody, {
                                title: i18n.__('celum:connect'),
                                className: 'celum-connect',
                                initialOpen: true
                            },
                            el('p', {}, i18n.__('celum:connect license has expired')),
                        )
                    ),
                    el ('div', {className: props.className},
                        el ('div', {id: "assetPicker"}, i18n.__('celum:connect license has expired')),
                    )
                ]
            }
        },
        save: function (props) {

        }

    });
}) (
    window.wp.blocks,
    window.wp.editor,
    window.wp.components,
    window.wp.i18n,
    window.wp.element
);


