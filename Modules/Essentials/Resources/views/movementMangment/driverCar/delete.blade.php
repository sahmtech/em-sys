<!-- Delete Confirmation Modal -->
<div class="modal fade" id="modal_delete_car_driver" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" id="modal_delete_car_driver_dialog" role="document">
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
                                'url' => '#',
                                'method' => 'delete',
                                'id' => 'form_delete_car_driver',
                                'enctype' => 'multipart/form-data',
                            ]) !!}

                            <input type="hidden" name="driver_car_id" id="input_delete_car_driver_id">

                            <div class="row" style="margin-top: 5px;">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('next_change_oil', __('housingmovements::lang.next_change_oil') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                        {!! Form::number('next_change_oil', '', [
                                            'class' => 'form-control',
                                            'required',
                                            'placeholder' => __('housingmovements::lang.next_change_oil'),
                                            'id' => 'input_next_change_oil',
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! Form::label('car_image', __('housingmovements::lang.car_image') . '  ') !!}
                                        {!! Form::file('car_image', [
                                            'class' => 'form-control',
                                            'accept' => 'image/*',
                                            'id' => 'input_delete_car_image',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <video id="video_delete_car_driver" width="100%" height="auto" autoplay
                                            style="display: none; transform: scaleX(-1);"></video>
                                        <img src="" id="image_delete_car_driver"
                                            style="max-width: 100%; height: auto; display: none; " />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="btn_delete_open_camera">
                                            @lang('essentials::lang.open_camera')
                                        </button>
                                        <button type="button" class="btn btn-danger btn_delete_image">
                                            @lang('messages.delete')
                                        </button>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="btn_delete_capture_photo"
                                            style="display: none">
                                            @lang('essentials::lang.capture_photo')
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="btn_delete_cancel_camera"
                                            style="display: none">
                                            @lang('essentials::lang.cancel_camera')
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="margin-top: 220px;">
                                <div class="col-sm-12" style="display: flex; justify-content: center;">
                                    <button type="submit" style="width: 50%; border-radius: 28px;"
                                        id="btn_confirm_delete" class="btn btn-danger pull-right btn-flat">
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
</div>

<script>
    $(document).ready(function() {
        let imageChanged = false;
        let videoStream = null;

        $(document).on('click', '.delete_user_button', function() {
            const driverCarId = $(this).data('id');
            $('#input_delete_car_driver_id').val(driverCarId);
            $('#modal_delete_car_driver').modal('show');

            // Start video stream when the modal is fully shown
            $('#modal_delete_car_driver').on('shown.bs.modal', function() {
                startVideoStream();
            });
        });

        $('#btn_delete_open_camera').on('click', function() {
            $('#image_delete_car_driver').hide();
            $('#video_delete_car_driver').show();
            $('#btn_delete_capture_photo').show();
            $('#btn_delete_cancel_camera').show();
        });

        $('#btn_delete_capture_photo').on('click', function() {
            takePhoto();
        });

        $('#btn_delete_cancel_camera').on('click', function() {
            stopVideoStream();
            $('#video_delete_car_driver').hide();
            $('#btn_delete_capture_photo').hide();
            $('#btn_delete_cancel_camera').hide();
            $('#image_delete_car_driver').show();
        });

        $('.btn_delete_image').on('click', function() {
            $('#image_delete_car_driver').attr('src', ''); // Remove image source
            $('#input_delete_car_image').val(''); // Clear file input
            imageChanged = true;
            enableSaveButton();
        });

        $('#input_delete_car_image').on('change', function() {
            previewImage(event);
            imageChanged = true;
            enableSaveButton();
        });

        function enableSaveButton() {
            $('#btn_confirm_delete').prop('disabled', !imageChanged);
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
                    var video = document.getElementById('video_delete_car_driver');
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
            var video = document.getElementById('video_delete_car_driver');
            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var context = canvas.getContext('2d');
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            var imageDataUrl = canvas.toDataURL('image/jpeg');
            $('#image_delete_car_driver').attr('src', imageDataUrl).show();

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

            const hiddenFileInput = document.getElementById('input_delete_car_image');
            const dataTransfer = new DataTransfer();
            const file = new File([blob], 'car_image.jpg', {
                type: mimeString
            });

            dataTransfer.items.add(file);
            hiddenFileInput.files = dataTransfer.files;

            stopVideoStream();
            imageChanged = true;

            $('#video_delete_car_driver').hide();
            enableSaveButton();
        }

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('image_delete_car_driver');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    });
</script>
