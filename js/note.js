var simplemde;
var changed = false;

$(document).ready(function() {
    simplemde = new SimpleMDE({
        element: document.getElementById("editor"),
        spellChecker: true,
        tabSize: 4,
        hideIcons: ["fullscreen", "side-by-side"]
    });

    if (preview) simplemde.togglePreview();

    simplemde.codemirror.on("change", function(){
        $("#saved").text("*");
        changed = true;
    });

    window.setInterval(save, 5*60000);
});

window.onkeydown = function(e) {
    if (!((e.metaKey || e.ctrlKey) && e.key == "s")) return;

    e.preventDefault();
    save();
};

// Save the file
function save() {
    if (!changed) return;
    $.ajax({
        type: "POST",
        url: "actions/saveNote.php",
        dataType: "text",
        data: {
            "notesId": notesId,
            "notes": simplemde.value(),
            "preview": simplemde.isPreviewActive(),
            "apiKey": apiKey
        },
        success: function(e) {
            console.log(e);
            $("#saved").text("");
            changed = false;
        }
    });
}

// Deletes the notes
function deleteNotes() {
    document.getElementById("deleteNotes").submit();
}