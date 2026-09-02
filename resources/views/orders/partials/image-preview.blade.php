<!-- Image Preview -->
<div id="imagePreview"
     style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.85);
        z-index:9999;
        justify-content:center;
        align-items:center;
    ">

    <img id="previewImage"
         src=""
         style="
            max-width:90%;
            max-height:90%;
            border-radius:10px;
            box-shadow:0 0 30px rgba(0,0,0,.6);
         ">

</div>

<script>

document.querySelectorAll('.preview-image').forEach(function(img){

    img.addEventListener('click', function(){

        document.getElementById('previewImage').src =
            this.dataset.image;

        document.getElementById('imagePreview').style.display =
            'flex';

    });

});

document.getElementById('imagePreview')
.addEventListener('click', function(){

    this.style.display='none';

});