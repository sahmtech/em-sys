<!-- Modal -->
<div class="modal fade" id="captureModal" tabindex="-1" role="dialog" aria-labelledby="captureModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="captureModalLabel">@lang('essentials::lang.open_camera')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <video id="video" width="100%" height="auto" autoplay></video>
        <img id="capturedPhoto" src="" style="max-width: 100%; max-height: 200px; display: none;" />
      </div>
           <input type="file" id="hiddenProfilePicture" style="display: none;" accept="image/*">
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelButton">@lang('essentials::lang.cancel_camera')</button>
        <button type="button" class="btn btn-primary" id="takePhotoButton">@lang('essentials::lang.capture_photo')</button>
        <button id="doneButton" class="btn btn-primary" style="display: none;">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript code -->
<script>
document.addEventListener("DOMContentLoaded", function() {
  const video = document.getElementById('video');
  const capturedPhoto = document.getElementById('capturedPhoto');
  let stream = null;

  function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(function(streamObj) {
        stream = streamObj;
        video.srcObject = stream;
      })
      .catch(function(err) {
        console.log("An error occurred: " + err);
      });
  }

  function stopCamera() {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      stream = null;
    }
  }

  function takePhoto() {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    const imgData = canvas.toDataURL('image/jpeg');

    capturedPhoto.src = imgData;
    capturedPhoto.style.display = 'block';

    const byteString = atob(imgData.split(',')[1]);
    const mimeString = imgData.split(',')[0].split(':')[1].split(';')[0];
    const arrayBuffer = new ArrayBuffer(byteString.length);
    const uint8Array = new Uint8Array(arrayBuffer);
    for (let i = 0; i < byteString.length; i++) {
      uint8Array[i] = byteString.charCodeAt(i);
    }
    const blob = new Blob([uint8Array], { type: mimeString });

    const hiddenFileInput = document.getElementById('fileInputWrapper');
    const dataTransfer = new DataTransfer();
    const file = new File([blob], 'profile_picture.jpg', { type: mimeString });

    dataTransfer.items.add(file);
 
    hiddenFileInput.files = dataTransfer.files;
    $(hiddenFileInput).trigger('change');

   
    $('#captureModal').modal('hide');
   
  }

  document.getElementById('takePhotoButton').addEventListener('click', takePhoto);
  document.getElementById('captureButton').addEventListener('click', function() {
    $('#captureModal').modal('show');
    startCamera();
  });
  $('#captureModal').on('hidden.bs.modal', function() {
    stopCamera();
  });
});
</script>
