@extends('layouts.app')
@section('title', __('essentials::lang.connect_camera'))

@section('content')
<section class="content-header">
    <h1>
        @lang('essentials::lang.connect_camera')
    </h1>
</section>

@component('components.widget')
<div class="col-md-3">
    <div class="form-group">
        <!-- Add a button to start accessing the camera -->
        <button id="startCameraButton" class="btn btn-primary btn-big">@lang('essentials::lang.open_camera')</button>
        <!-- Add a button to close the camera -->
        <button id="stopCameraButton" class="btn btn-danger btn-big" style="display: none;">@lang('essentials::lang.close_camera')</button>
        <!-- Add a video element to display camera stream -->
        <div class="clearfix"></div>
        <video id="cameraStream" width="100%" height="auto" style="display: none;" autoplay></video>
        <br>
        <div class="clearfix"></div>
        <!-- Add a button to capture a photo -->
        <button id="captureButton" class="btn btn-primary btn-big" style="display: none;">@lang('essentials::lang.capture_photo')</button>
    </div>
</div>
@endcomponent
@stop

@section('javascript')
<script>
    // Get the video element and canvas element
    const video = document.getElementById('cameraStream');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    let stream;

    // Function to start accessing the camera when the button is clicked
    function startCamera() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
                video.style.display = 'block'; // Show the video element
                document.getElementById('captureButton').style.display = 'block'; // Show the capture button
                document.getElementById('stopCameraButton').style.display = 'block'; // Show the stop camera button
                // Store the stream for later use
                stream = stream;
            })
            .catch(error => {
                console.error('Error accessing the camera:', error);
            });
    }

    // Function to stop accessing the camera and hide the video element
    function stopCamera() {
        if (stream) {
            const tracks = stream.getTracks();
            tracks.forEach(track => {
                track.stop(); // Stop all tracks in the stream
            });
            video.srcObject = null; // Remove the video stream
            video.style.display = 'none'; // Hide the video element
            document.getElementById('captureButton').style.display = 'none'; // Hide the capture button
            document.getElementById('stopCameraButton').style.display = 'none'; // Hide the stop camera button
        }
    }

    // Function to capture a photo
    function capturePhoto() {
        // Draw the current frame from the video stream onto the canvas
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        // Convert the canvas content to a data URL representing the captured image
        const imageDataURL = canvas.toDataURL('image/png');

        // Create a temporary download link for the image data
        const downloadLink = document.createElement('a');
        downloadLink.href = imageDataURL;
        downloadLink.download = 'captured_photo.png'; // Set the filename for the downloaded image
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink); // Remove the download link from the document
    }

    // Start accessing the camera when the start camera button is clicked
    document.getElementById('startCameraButton').addEventListener('click', startCamera);

    // Stop accessing the camera when the stop camera button is clicked
    document.getElementById('stopCameraButton').addEventListener('click', stopCamera);

    // Capture a photo when the capture button is clicked
    document.getElementById('captureButton').addEventListener('click', capturePhoto);
</script>
@endsection
