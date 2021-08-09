let allowed_preview_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];

document.getElementById("fileLoadInput").addEventListener("change", function (ev) {

    let files_count = this.files.length,
        form_data   = new FormData();

    for (let i = 0; i < files_count; i++) {
        form_data.append("file", this.files[i]);

        let progressBar     = readURL(this.files[i]),
            progressBarWrap = Array.from(progressBar.childNodes).find(node => node.className === "custom-progress-bar"),
            request         = new XMLHttpRequest();

        request.upload.addEventListener("progress", function (ev) {

            if (ev.lengthComputable) {
                let percentComplete  = ev.loaded / ev.total,
                    progressBarInner = progressBarWrap.getElementsByClassName("inner")[0];

                percentComplete = percentComplete * 100;

                progressBarInner.style.width = percentComplete.toString() + "%";
            }
        }, false);

        request.addEventListener("load", function () {
            let response = JSON.parse(this.responseText);

            progressBarWrap.style.background = response.success ? "#70b860" : "#843534";
            progressBarWrap.innerHTML        = response.success ? "Done!" : "Can't load file!";

            setTimeout(function () {
                progressBarWrap.remove();
            }, 3000);
        });

        request.upload.addEventListener("error", function () {
            progressBarWrap.style.background = "#843534";
            progressBarWrap.innerHTML        = "Can't load file!";
        })

        request.open("post", "/api/file-loader.php", true);

        request.send(form_data);
    }

    document.getElementById("fileLoaderForm").reset();

}, false);


function readURL(file) {
    let reader             = new FileReader(),
        templateParentNode = document.getElementById("fileUploadTemplate"),
        template           = templateParentNode.getElementsByClassName("file-upload-content"),
        preview            = template[0].cloneNode(true);

    document.getElementsByClassName("file-upload")[0].insertBefore(preview, templateParentNode);

    reader.onload = function (e) {
        (Array.from(preview.childNodes).find(node => node.className === "file-upload-image")).src      = allowed_preview_types.includes(file.type) ? e.target.result : "/images/preview.png";
        (Array.from(preview.childNodes).find(node => node.className === "image-title-wrap")).innerHTML = file.name;
        (Array.from(preview.childNodes).find(node => node.className === "remove-upload-file-preview")).addEventListener("click", function () {
            this.parentNode.remove();
        }, false);
    };

    reader.readAsDataURL(file);

    return preview;
}
