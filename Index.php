<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Picture Cropper</title>
    <link rel="stylesheet" href="css/profile.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
</head>

<body>

    <div class="container">
        <form id="imageForm" method="POST" action="saving.php" enctype="multipart/form-data">
            <input type="hidden" name="croppedImage" id="croppedImage" />

            <input type="file" id="fileInput" accept="image/*">
            <button class="upload-btn" type="button" onclick="document.getElementById('fileInput').click();">Upload
                Profile Photo</button>

            <div class="preview-container">
                <img id="previewImage" src="" alt="" />
            </div>

            <div class="cropped">
                <img id="image" src="" alt="Image for cropping" />
            </div>

            <button class="btn-save" type="button" onclick="saveCroppedImage()">Save</button>

            <div class="success-message" id="successMessage">Profile photo saved successfully!</div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script>
    let cropper;
    let previewImage = document.getElementById('previewImage');
    let imageElement = document.getElementById('image');
    let fileInput = document.getElementById('fileInput');
    let successMessage = document.getElementById('successMessage');
    let form = document.getElementById('imageForm');

    // Fetch the current profile image on page load
    window.onload = function() {
        fetch('saving.php', {
                method: 'GET',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.image_path) {
                    previewImage.src = data.image_path; // Display current image
                }
            })
            .catch(error => console.error('Error loading image:', error));
    };

    fileInput.addEventListener('change', function(event) {
        let file = event.target.files[0];
        if (file) {
            document.querySelector('.cropped').style.display = 'block';
            document.querySelector('.btn-save').style.display = 'inline-block';

            let reader = new FileReader();
            reader.onload = function(e) {
                imageElement.src = e.target.result;
                previewImage.src = e.target.result;
                imageElement.style.display = 'block';
                previewImage.style.display = 'none';

                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(imageElement, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    minCropBoxWidth: 100,
                    minCropBoxHeight: 100,
                });
            };
            reader.readAsDataURL(file);
        }
    });

    function saveCroppedImage() {
        // Get the cropped image data
        const canvas = cropper.getCroppedCanvas({
            width: 200,
            height: 200,
        });

        const croppedImageURL = canvas.toDataURL('image/png');
        previewImage.src = croppedImageURL;

        document.getElementById('croppedImage').value = croppedImageURL;

        // Create FormData object to send the image
        const formData = new FormData(form);

        // Perform AJAX (Fetch API) to send the image to the server
        fetch('saving.php', {
                method: 'POST',
                body: formData, // Send the form data including the cropped image
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successMessage.style.display = 'block'; // Show success message
                    console.log('Image uploaded successfully');
                    // Hide the success message after 1 second (1000 milliseconds)
                    setTimeout(() => {
                        successMessage.style.display = 'none';
                    }, 1000);
                } else {
                    console.error('Error uploading image:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });

        // Hide elements and show cropped image without page reload
        document.querySelector('.cropped').style.display = 'none';
        imageElement.style.display = 'none';
        previewImage.style.display = 'block';
        document.querySelector('.btn-save').style.display = 'none';
    }
    </script>

</body>

</html>