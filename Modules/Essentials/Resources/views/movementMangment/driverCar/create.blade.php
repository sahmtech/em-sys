<div class="modal-dialog modal-lg" id="add_driver_model" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fas fa-plus"></i> @lang('housingmovements::lang.add_driver')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">

                    <section class="content">
                        {!! Form::open([
                            'url' => action('\Modules\Essentials\Http\Controllers\DriverCarController@store'),
                            'enctype' => 'multipart/form-data',
                            'method' => 'post',
                            'id' => 'carType_add_form',
                        ]) !!}

                        <div class="row">
                            <div class="col-sm-12" style="margin-top: 0px;">
                                {!! Form::label('carType_label', __('housingmovements::lang.driver')) !!}<span style="color: red; font-size:10px"> *</span>

                                <select class="form-control " name="user_id" id="worker__select" style="padding: 2px;">
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}">
                                            {{ $worker->id_proof_number . ' - ' . $worker->first_name . ' ' . $worker->last_name . ' - ' . $worker->essentialsEmployeeAppointmets->profession->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-12" style="margin-top: 5px;">
                                {!! Form::label('carType_label', __('housingmovements::lang.car')) !!}<span style="color: red; font-size:10px"> *</span>
                                <select class="form-control" id="car_id" name="car_id" style="padding: 2px;"
                                    required>
                                    <option value="">@lang('messages.please_select')</option>
                                    @foreach ($cars as $car)
                                        <option value="{{ $car->id }}">
                                            {{ $car->plate_number . ' - ' . $car->CarModel?->CarType?->name_ar . ' - ' . $car->CarModel?->name_ar . ' - ' . $car->color }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 5px;">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('counter_number', __('housingmovements::lang.counter_number') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::number('counter_number', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.counter_number'),
                                        'id' => 'counter_number',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('delivery_date', __('housingmovements::lang.delivery_date') . '  ') !!}<span style="color: red; font-size:10px"> *</span>
                                    {!! Form::input('date', 'delivery_date', '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => __('housingmovements::lang.delivery_date'),
                                        'id' => 'delivery_date',
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('car_image', __('housingmovements::lang.car_image') . '  ') !!}
                                    {!! Form::file('car_image', ['class' => 'form-control', 'accept' => 'image/*', 'id' => 'fileInputWrapper']) !!}
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">

                                    <video id="video" width="100%" height="auto" autoplay
                                        style="display: none; transform: scaleX(-1);"></video>
                                    <img src="" id="popupImage"
                                        style="max-width: 100%; height: auto; display: none; " />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="capturePhoto">
                                        @lang('essentials::lang.open_camera')
                                    </button>
                                    <button type="button" class="btn btn-danger deleteImage">
                                        @lang('messages.delete')
                                    </button>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="takePhotoBtn"
                                        style="display: none">
                                        @lang('essentials::lang.capture_photo')
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="cancelCameraBtn"
                                        style="display: none">
                                        @lang('essentials::lang.cancel_camera')
                                    </button>

                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 220px;">
                            <div class="col-sm-12" style="display: flex; justify-content: center;">
                                <button type="submit" style="width: 50%; border-radius: 28px;" id="add_car_type"
                                    class="btn btn-primary pull-right btn-flat journal_add_btn">
                                    @lang('messages.save')
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

<script>
    $(document).ready(function() {
        let imageChanged = false;
        let videoStream = null;

        $('#capturePhoto').on('click', function() {
            $('#popupImage').hide();
            $('#video').show();
            $('#takePhotoBtn').show();
            $('#cancelCameraBtn').show();
            startVideoStream();
        });

        $('#takePhotoBtn').on('click', function() {
            takePhoto();
        });

        $('#cancelCameraBtn').on('click', function() {
            stopVideoStream();
            $('#video').hide();
            $('#takePhotoBtn').hide();
            $('#cancelCameraBtn').hide();
            $('#popupImage').show();
        });

        $('.deleteImage').on('click', function() {
            $('#popupImage').attr('src', ''); // Remove image source
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
            $('#add_car_type').prop('disabled', !imageChanged);
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
                    var video = document.getElementById('video');
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
            var video = document.getElementById('video');
            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var context = canvas.getContext('2d');
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            // canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            var imageDataUrl = canvas.toDataURL('image/jpeg');
            $('#popupImage').attr('src', imageDataUrl).show();

            // Convert data URL to Blob
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

            const hiddenFileInput = document.getElementById('fileInputWrapper');
            const dataTransfer = new DataTransfer();
            const file = new File([blob], 'car_image.jpg', {
                type: mimeString
            });

            dataTransfer.items.add(file);

            hiddenFileInput.files = dataTransfer.files;

            stopVideoStream();
            imageChanged = true;

            $('#video').hide();
            enableSaveButton();
        }

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('popupImage');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    });
</script>
