@extends('layouts.app')
@section('title', __('housingmovements::lang.car_drivers'))

@section('content')

    <section class="content-header">
        <h1>
            <span>@lang('housingmovements::lang.car_drivers')</span>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-solid'])
                    {!! Form::open([
                        'url' => action('\Modules\Essentials\Http\Controllers\DriverCarController@search'),
                        'method' => 'post',
                        'id' => 'carType_search',
                    ]) !!}
                    <div class="row">
                        <div class="col-sm-4">
                            {!! Form::label('carType_label', __('housingmovements::lang.carModel')) !!}

                            <select class="form-control" name="car_type_id" id='carTypeSelect' style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($carTypes as $type)
                                    <option value="{{ $type->id }}">
                                        {{ $type->name_ar . ' - ' . $type->name_en }}</option>
                                @endforeach
                            </select>

                        </div>

                        <div class="col-sm-4" style="margin-top: 0px;">
                            {!! Form::label('driver', __('housingmovements::lang.driver')) !!}<span style="color: red; font-size:10px"> *</span>

                            <select class="form-control " name="driver" id="driver_select" style="padding: 2px;">
                                <option value="all" selected>@lang('lang_v1.all')</option>
                                @foreach ($car_Drivers as $driver)
                                    <option value="{{ $driver->user_id }}">
                                        {{ $driver->user->id_proof_number . ' - ' . $driver->user->first_name . ' ' . $driver->user->last_name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ action('Modules\Essentials\Http\Controllers\DriverCarController@create') }}"
                                data-href="{{ action('Modules\Essentials\Http\Controllers\DriverCarController@create') }}"
                                data-container="#add_car_model">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="carDrivers_table" style="margin-bottom: 100px;">
                            <thead>
                                <tr>

                                    <th>@lang('housingmovements::lang.driver')</th>
                                    <th>@lang('housingmovements::lang.car_typeModel')</th>
                                    <th>@lang('housingmovements::lang.counter_number')</th>
                                    <th>@lang('housingmovements::lang.delivery_date')</th>
                                    <th>@lang('housingmovements::lang.plate_number')</th>
                                    <th>@lang('housingmovements::lang.status')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>

                        </table>
                    </div>

                    <div class="modal fade" id="add_car_model" tabindex="-1" role="dialog"></div>
                    <div class="modal fade" id="edit_driver_model" tabindex="-1" role="dialog"></div>
                @endcomponent
            </div>
            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="modal_delete_car_driver" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" id="modal_delete_car_driver_dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:red">
                                <span aria-hidden="true">&times;</span>
                            </button>
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
                                                    {!! Form::label('next_change_oil', __('housingmovements::lang.counter_number') . '  ') !!}
                                                    <span style="color: red; font-size:10px"> *</span>
                                                    {!! Form::number('next_change_oil', '', [
                                                        'class' => 'form-control',
                                                        'required',
                                                        'placeholder' => __('housingmovements::lang.counter_number'),
                                                        'id' => 'input_delete_next_change_oil',
                                                    ]) !!}
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    {!! Form::label('car_image', __('housingmovements::lang.car_image') . '  ') !!}
                                                    {!! Form::file('car_image', [
                                                        'class' => 'form-control',
                                                        'accept' => 'image/*',
                                                        'id' => 'fileInputDeleteWrapper',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <video id="video_delete" width="100%" height="auto" autoplay
                                                        style="display: none; transform: scaleX(-1);"></video>
                                                    <img src="" id="popupImageDelete"
                                                        style="max-width: 100%; height: auto; display: none; " />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-primary" id="capturePhotoDelete">
                                                        @lang('essentials::lang.open_camera')
                                                    </button>
                                                    <button type="button" class="btn btn-danger deleteImageDelete">
                                                        @lang('messages.delete')
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-primary" id="takePhotoBtnDelete"
                                                        style="display: none">
                                                        @lang('essentials::lang.capture_photo')
                                                    </button>
                                                    <button type="button" class="btn btn-secondary"
                                                        id="cancelCameraBtnDelete" style="display: none">
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

    </section>
    <!-- /.content -->

@endsection

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            let imageChangedDelete = false;
            let videoStreamDelete = null;

            $('#capturePhotoDelete').on('click', function() {
                $('#popupImageDelete').hide();
                $('#video_delete').show();
                $('#takePhotoBtnDelete').show();
                $('#cancelCameraBtnDelete').show();
                startVideoStreamDelete();
            });

            $('#takePhotoBtnDelete').on('click', function() {
                takePhotoDelete();
            });

            $('#cancelCameraBtnDelete').on('click', function() {
                stopVideoStreamDelete();
                $('#video_delete').hide();
                $('#takePhotoBtnDelete').hide();
                $('#cancelCameraBtnDelete').hide();
                $('#popupImageDelete').show();
            });

            $('.deleteImageDelete').on('click', function() {
                $('#popupImageDelete').attr('src', ''); // Remove image source
                $('#fileInputDeleteWrapper').val(''); // Clear file input
                imageChangedDelete = true;
                enableSaveButtonDelete();
            });

            $('#fileInputDeleteWrapper').on('change', function(event) {
                previewImageDelete(event);
                imageChangedDelete = true;
                enableSaveButtonDelete();
            });

            function enableSaveButtonDelete() {
                $('#btn_confirm_delete').prop('disabled', !imageChangedDelete);
            }

            function startVideoStreamDelete() {
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
                        videoStreamDelete = stream;
                        document.getElementById('video_delete').srcObject = stream;
                        document.getElementById('video_delete').play();
                    })
                    .catch(function(err) {
                        console.log("An error occurred: " + err);
                    });
            }

            function stopVideoStreamDelete() {
                if (videoStreamDelete) {
                    videoStreamDelete.getTracks().forEach(track => track.stop());
                    videoStreamDelete = null;
                }
            }

            function takePhotoDelete() {
                var video = document.getElementById('video_delete');
                var canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                var context = canvas.getContext('2d');
                context.translate(canvas.width, 0);
                context.scale(-1, 1);
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                var imageDataUrl = canvas.toDataURL('image/jpeg');
                $('#popupImageDelete').attr('src', imageDataUrl).show();

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

                const hiddenFileInput = document.getElementById('fileInputDeleteWrapper');
                const dataTransfer = new DataTransfer();
                const file = new File([blob], 'car_image.jpg', {
                    type: mimeString
                });

                dataTransfer.items.add(file);
                hiddenFileInput.files = dataTransfer.files;

                stopVideoStreamDelete();
                imageChangedDelete = true;

                $('#video_delete').hide();
                enableSaveButtonDelete();
            }

            function previewImageDelete(event) {
                var reader = new FileReader();
                reader.onload = function() {
                    var output = document.getElementById('popupImageDelete');
                    output.src = reader.result;
                    output.style.display = 'block';
                };
                reader.readAsDataURL(event.target.files[0]);
            }
        });

        $(document).ready(function() {

            // Handle delete button click
            $(document).on('click', '.delete_user_button', function() {
                const driverCarId = $(this).data('id'); // Get the ID from the button's data attribute
                const deleteUrl = '{{ url('movment/cardrivers-delete') }}/' +
                    driverCarId; // Construct the delete URL

                // Set the form action to the delete URL with the ID
                $('#form_delete_car_driver').attr('action', deleteUrl);

                // Open the modal
                $('#modal_delete_car_driver').modal('show');
            });

            // Initialize select2 for dropdowns
            $('#carTypeSelect').select2();
            $('#driver_select').select2();

            // Initialize DataTable
            carDrivers_table = $('#carDrivers_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('essentials.cardrivers') }}',
                    data: function(d) {
                        if ($('#carTypeSelect').val()) {
                            d.carTypeSelect = $('#carTypeSelect').val();
                        }
                        if ($('#driver_select').val()) {
                            d.driver_select = $('#driver_select').val();
                        }
                    }
                },
                columns: [{
                        "data": "driver"
                    },
                    {
                        "data": "car_typeModel"
                    },
                    {
                        "data": "counter_number"
                    },
                    {
                        "data": "delivery_date"
                    },
                    {
                        "data": "plate_number"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "action"
                    }

                ]
            });

            // Handle delete functionality via AJAX
            $(document).on('click', 'button.delete_user_button', function() {
                var href = $(this).data('href');
                var data = $(this).serialize();
                $.ajax({
                    method: "DELETE",
                    url: href,
                    dataType: "json",
                    data: data,
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            carDrivers_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            // Handle edit functionality via AJAX
            $(document).on('click', 'button.edit_user_button', function() {
                var href = $(this).data('href');
                var data = $(this).serialize();
                $.ajax({
                    method: "get",
                    url: href,
                    dataType: "json",
                    data: data,
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            // Reload DataTable on filter change
            $('#carTypeSelect,#driver_select').on('change', function() {
                carDrivers_table.ajax.reload();
            });
        });
    </script>

@endsection
