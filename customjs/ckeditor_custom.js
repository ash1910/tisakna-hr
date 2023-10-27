CKEDITOR.on( 'instanceReady', function( ev ) {
    ev.editor.dataProcessor.htmlFilter.addRules({
        elements: {
            $: function (element) {
                // Add inline-media class name to embedded media
                if (element.name == 'div' && element.children[0].name == 'iframe') {
                    element.attributes.class = 'video-container';
                    return element;
                }
            }
        }
    });
});