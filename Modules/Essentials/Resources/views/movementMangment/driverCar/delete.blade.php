<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete_driver_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fas fa-trash"></i> @lang('housingmovements::lang.confirm_delete')</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <section class="content">
                            {!! Form::open([
                                'url' => '#', // The URL will be set dynamically with JavaScript
                                'method' => 'delete',
                                'id' => 'delete_driver_form',
                                'enctype' => 'multipart/form-data',
                            ]) !!}

                            <!-- Hidden input for driver_car_id -->
                            <input type="hidden" name="driver_car_id" id="delete_driver_car_id">

                            <div class="row" style="margin-top: 5px;">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('next_change_oil', __('housingmovements::lang.next_change_oil') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::number('next_change_oil', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('housingmovements::lang.next_change_oil'),
                                            'id' => 'next_change_oil',
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('car_image', __('housingmovements::lang.car_image') . '  ') !!}
                                        {!! Form::file('car_image', [
                                            'class' => 'form-control',
                                            'accept' => 'image/*',
                                            'id' => 'delete_fileInputWrapper',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <video id="delete_video" width="100%" height="auto" autoplay
                                            style="display: none; transform: scaleX(-1);"></video>
                                        <img src="" id="delete_popupImage"
                                            style="max-width: 100%; height: auto; display: none; " />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="delete_capturePhoto">
                                            @lang('essentials::lang.open_camera')
                                        </button>
                                        <button type="button" class="btn btn-danger deleteImage">
                                            @lang('messages.delete')
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="delete_takePhotoBtn"
                                            style="display: none">
                                            @lang('essentials::lang.capture_photo')
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="delete_cancelCameraBtn"
                                            style="display: none">
                                            @lang('essentials::lang.cancel_camera')
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="margin-top: 220px;">
                                <div class="col-sm-12" style="display: flex; justify-content: center;">
                                    <button type="submit" style="width: 50%; border-radius: 28px;" id="delete_car_type"
                                        class="btn btn-danger pull-right btn-flat">
                                        @lang('messages.confirm_delete')
                                    </button>
                                </div>
                            </div>

                            {!! Form::close() !!}
                        </section>
                    </div>
                </div>
            </div>
        </div> <!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script>
    $(document).ready(function() {
        let imageChanged = false;
        let videoStream = null;

        // Open the modal and set the ID of the driver to be deleted
        $(document).on('click', '.delete_user_button', function() {
            const driverCarId = $(this).data('id');
            const deleteUrl = '{{ url('movment/cardrivers-delete') }}/' +
            driverCarId; // Construct the delete URL

            // Set the form action and hidden field value
            $('#delete_driver_form').attr('action', deleteUrl);
            $('#delete_driver_car_id').val(driverCarId);

            // Show the modal
            $('#delete_driver_modal').modal('show');
        });

        $('#delete_capturePhoto').on('click', function() {
            $('#delete_popupImage').hide();
            $('#delete_video').show();
            $('#delete_takePhotoBtn').show();
            $('#delete_cancelCameraBtn').show();
            startVideoStream();
        });

        $('#delete_takePhotoBtn').on('click', function() {
            takePhoto();
        });

        $('#delete_cancelCameraBtn').on('click', function() {
            stopVideoStream();
            $('#delete_video').hide();
            $('#delete_takePhotoBtn').hide();
            $('#delete_cancelCameraBtn').hide();
            $('#delete_popupImage').show();
        });

        $('.deleteImage').on('click', function() {
            $('#delete_popupImage').attr('src', ''); // Remove image source
            $('input[type="file"]').val(''); // Clear file input
            imageChanged = true;
            enableSaveButton();
        });

        $('input[type="file"]').on('change', function() {
            previewImage(event);
            imageChanged = true;
            enableSaveButton();
        });

        function enableSaveButton() {
            $('#delete_car_type').prop('disabled', !imageChanged);
        }

        function startVideoStream() {
            navigator.mediaDevices.getUserMedia({
                    video: {
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    }
                })
                .then(function(stream) {
                    var video = document.getElementById('delete_video');
                    videoStream = stream;
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function(err) {
                    console.log("An error occurred: " + err);
                });
        }

        function stopVideoStream() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        }

        function takePhoto() {
            var video = document.getElementById('delete_video');
            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var context = canvas.getContext('2d');
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            var imageDataUrl = canvas.toDataURL('image/jpeg');
            $('#delete_popupImage').attr('src', imageDataUrl).show();

            var byteString = atob(imageDataUrl.split(',')[1]);
            var mimeString = imageDataUrl.split(',')[0].split(':')[1].split(';')[0];
            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            var blob = new Blob([ab], {
                type: mimeString
            });

            const hiddenFileInput = document.getElementById('delete_fileInputWrapper');
            const dataTransfer = new DataTransfer();
            const file = new File([blob], 'car_image.jpg', {
                type: mimeString
            });

            dataTransfer.items.add(file);

            hiddenFileInput.files = dataTransfer.files;

            stopVideoStream();
            imageChanged = true;

            $('#delete_video').hide();
            enableSaveButton();
        }

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('delete_popupImage');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    });
</script>
