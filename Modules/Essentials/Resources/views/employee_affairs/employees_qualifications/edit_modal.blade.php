<!-- Add this modal markup to your Blade file -->
<div class="modal fade" id="editQualificationModal" tabindex="-1" role="dialog"
    aria-labelledby="editQualificationModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {!! Form::open(['route' => ['updateQualification', 'qualificationId'], 'enctype' => 'multipart/form-data']) !!}

            <div class="modal-header">
                <h5 class="modal-title" id="editQualificationModalLabel">@lang('messages.edit')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="qualificationIdInput" name="qualificationId">

                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('employee', __('essentials::lang.employee') . ':*') !!}
                        {!! Form::select('employee', $users, null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.select_employee'),
                            'required',
                        ]) !!}

                    </div>

                    <div class="form-group col-md-6">
                        {!! Form::label('qualification_type', __('essentials::lang.qualification_type') . ':*') !!}
                        {!! Form::select(
                            'qualification_type',
                            [
                                'bachelors' => __('essentials::lang.bachelors'),
                                'master' => __('essentials::lang.master'),
                                'PhD' => __('essentials::lang.PhD'),
                                'diploma' => __('essentials::lang.diploma'),
                            ],
                            null,
                            ['class' => 'form-control', 'style' => 'width:100%;height:40px', 'placeholder' => __('lang_v1.all')],
                        ) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('major', __('essentials::lang.major') . ':*') !!}
                        {!! Form::select('major', $spacializations, null, [
                            'class' => 'form-control',
                            'style' => 'height:40px',
                            'placeholder' => __('essentials::lang.major'),
                            'required',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('graduation_year', __('essentials::lang.graduation_year') . ':') !!}
                        {!! Form::date('graduation_year', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.graduation_year'),
                            'required',
                        ]) !!}
                    </div>
                    <div class="clearfix"></div>

                    <div class="form-group col-md-6">
                        {!! Form::label('graduation_institution', __('essentials::lang.graduation_institution') . ':') !!}
                        {!! Form::text('graduation_institution', null, [
                            'class' => 'form-control',
                            'placeholder' => __('essentials::lang.graduation_institution'),
                            'required',
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('graduation_country', __('essentials::lang.graduation_country') . ':') !!}
                        {!! Form::select('graduation_country', $countries, null, [
                            'class' => 'form-control',
                            'style' => 'height:40px',
                            'placeholder' => __('essentials::lang.select_country'),
                            'required',
                        ]) !!}
                    </div>
                    {{-- <div class="form-group col-md-6">
                                {!! Form::label('degree', __('essentials::lang.degree') . ':') !!}
                                {!! Form::number('degree', null, ['class' => 'form-control', 'placeholder' => __('essentials::lang.degree'), 'required', 'step' => 'any']) !!}
                            </div> --}}

                    <div class=" col-md-6">
                        <div class="form-group">
                            {!! Form::label('degree', __('essentials::lang.degree') . ':') !!}
                            {!! Form::number('degree', null, [
                                'class' => 'form-control',
                                'placeholder' => __('essentials::lang.degree'),
                                'step' => 'any',
                                'onkeyup' => 'getGPA()',
                            ]) !!}
                        </div>
                    </div>
                 
                    <div class=" col-md-6">
                        <div class="form-group">
                            {!! Form::label('great_degree', __('essentials::lang.great_degree') . ':') !!}
                            {!! Form::number('great_degree', null, [
                                'class' => 'form-control',
                                'placeholder' => __('essentials::lang.great_degree'),
                                'step' => 'any',
                                'onkeyup' => 'getGPA()',
                            ]) !!}

                        </div>
                    </div>

                    <div class=" col-md-6">
                        <div class="form-group">
                            {!! Form::label('marksName', __('essentials::lang.marksName') . ':') !!}
                            {!! Form::text('marksName', null, [
                                'class' => 'form-control',
                                'placeholder' => __('essentials::lang.marksName'),
                                'step' => 'any',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>

                </div>
            </div>


            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

<script>
    function getGPA() {
        const GPA = [{
                PercentageTo: 100,
                PercentageFrom: 85,
                marksName: '{{ __('essentials::lang.veryExcellent') }}',
                Grade: "A+",
            },
            {
                PercentageTo: 84,
                PercentageFrom: 80,
                marksName: '{{ __('essentials::lang.excellent') }}',
                Grade: "A",
            },
            {
                PercentageTo: 79,
                PercentageFrom: 75,
                marksName: '{{ __('essentials::lang.veryGood') }}',
                Grade: "B+",
            },
            {
                PercentageTo: 74,
                PercentageFrom: 70,
                marksName: '{{ __('essentials::lang.veryGood') }}',
                Grade: "B",
            },
            {
                PercentageTo: 69,
                PercentageFrom: 65,
                marksName: '{{ __('essentials::lang.good') }}',
                Grade: "B-",
            },
            {
                PercentageTo: 64,
                PercentageFrom: 60,
                marksName: '{{ __('essentials::lang.good') }}',
                Grade: "C+",
            },
            {
                PercentageTo: 59,
                PercentageFrom: 55,
                marksName: '{{ __('essentials::lang.weak') }}',
                Grade: "C",
            },
            {
                PercentageTo: 54,
                PercentageFrom: 50,
                marksName: '{{ __('essentials::lang.weak') }}',
                Grade: "C-",
            },
            {
                PercentageTo: 49,
                PercentageFrom: 45,
                marksName: '{{ __('essentials::lang.bad') }}',
                Grade: "D",
            },
            {
                PercentageTo: 44,
                PercentageFrom: 40,
                marksName: '{{ __('essentials::lang.bad') }}',
                Grade: "D-",
            },
            {
                PercentageTo: 39,
                PercentageFrom: 0,
                marksName: '{{ __('essentials::lang.fail') }}',
                Grade: "F",
            },
        ];
        var great_degree = document.getElementById('great_degree').value;
        var degree = document.getElementById('degree').value;

        if (degree > great_degree) {
            document.getElementById("marksName").style.color = "red";
            document.getElementById('marksName').value =
                'يجب ان تكون الدرجة العطمة اعلى من الدرجة';
        }
        var greatDegree = 100 / great_degree;
        GPA.forEach(gpaMark => {
            if (degree >= gpaMark.PercentageFrom / greatDegree && degree <= gpaMark
                .PercentageTo /
                greatDegree) {

                document.getElementById('marksName').value = gpaMark.marksName +
                    '  ( ' + gpaMark.Grade + ' )'
            }

        });


    }
</script>
