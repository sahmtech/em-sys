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
        <!-- Add a video element to display camera stream -->
        <div class="clearfix"></div>
        <video id="cameraStream" width="100%" height="auto" style="display: none;" autoplay></video>
        <br>
        <div class="clearfix"></div>
        <!-- Add buttons to capture a photo and close the camera -->
        <div class="btn-group">
            <button id="captureButton" class="btn btn-primary btn-big" style="display: none; padding:10px; margin-right: 5px; !important">@lang('essentials::lang.capture_photo')</button>
            <button id="stopCameraButton" class="btn btn-danger btn-big" style="display: none;">@lang('essentials::lang.close')</button>
        </div>
    </div>
</div>
@endcomponent
@stop

@section('javascript')
<script>
    
    const video = document.getElementById('cameraStream');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    let stream;

    
    function startCamera() {
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(streamObj => {
                video.srcObject = streamObj;
                video.style.display = 'block'; 
                document.getElementById('captureButton').style.display = 'block'; 
                document.getElementById('stopCameraButton').style.display = 'block'; 
                
                stream = streamObj;
            })
            .catch(error => {
                console.error('Error accessing the camera:', error);
            });
    }

    
    function stopCamera() {
        if (stream) {
            const tracks = stream.getTracks();
            tracks.forEach(track => {
                track.stop(); 
            });
            video.srcObject = null; 
            video.style.display = 'none'; 
            document.getElementById('captureButton').style.display = 'none'; 
            document.getElementById('stopCameraButton').style.display = 'none'; 
        }
    }

    
    function capturePhoto() {
        
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const imageDataURL = canvas.toDataURL('image/png');

        
        const downloadLink = document.createElement('a');
        downloadLink.href = imageDataURL;
        downloadLink.download = 'captured_photo.png'; 
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink); 
    }

    
    document.getElementById('startCameraButton').addEventListener('click', startCamera);

    
    document.getElementById('stopCameraButton').addEventListener('click', stopCamera);

    
    document.getElementById('captureButton').addEventListener('click', capturePhoto);
</script>
@endsection
