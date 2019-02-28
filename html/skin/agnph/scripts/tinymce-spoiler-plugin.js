tinymce.PluginManager.add('spoiler', function(editor, url) {
    /* Add a button that opens a window */
    editor.addButton('spoiler', {
        text: 'Spoiler',
        icon: false,
        onclick: function() {
            /* Open window */
            console.log(editor.selection.getContent());
            editor.selection.setContent("<span class='spoiler'>"+editor.selection.getContent()+"</span>");
        }
    });
});