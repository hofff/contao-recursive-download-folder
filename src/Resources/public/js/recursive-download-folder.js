document.addEventListener("DOMContentLoaded", function(event) {
    var links = document.querySelectorAll('.hofff-recursive-download-folder-toggleable .folder.download-element > a');

    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', function (event) {
            event.preventDefault();

            event.target.parentElement.classList.toggle('folder-open');
        })
    }
});

