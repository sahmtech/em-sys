<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      {!! Form::open(['url' => action([\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'saveQuickClient']), 'method' => 'post', 'id' => 'quick_add_client_form' ]) !!}
  
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle">@lang( 'sales::lang.add_new_client' )</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label('profession', __('sales::lang.profession') . ':*') !!}
                {!! Form::text('profession', null, ['class' => 'form-control', 'required',
                'placeholder' => __('sales::lang.profession')]); !!}
            </div>
          </div>
  
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('specialization', __('sales::lang.specialization') . ':*') !!}
                  {!! Form::text('specialization', null, ['class' => 'form-control', 'required',
                  'placeholder' => __('sales::lang.specialization')]); !!}
              </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('nationality', __('sales::lang.nationality') . ':*') !!}
                  {!! Form::text('nationality', null, ['class' => 'form-control', 'required',
                  'placeholder' => __('sales::lang.nationality')]); !!}
              </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('gender', __('sales::lang.gender') . ':*') !!}
                  {!! Form::select('gender',  ['male' => __('sales::lang.male'), 'female' => __('sales::lang.female')],null, ['class' => 'form-control', 'required',
                  'placeholder' => __('sales::lang.gender')]); !!}
                  
              </div>
          </div>
          {{-- <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('number_of_clients', __('client.number_of_clients') . ':*') !!}
                  {!! Form::number('number_of_clients', null, ['class' => 'form-control', 'required',
                  'placeholder' => __('client.number_of_clients')]); !!}
              </div>
          </div> --}}
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('Salary', __('sales::lang.salary') . ':*') !!}
                  {!! Form::number('Salary', null, ['class' => 'form-control', 'required',
                  'placeholder' => __('sales::lang.salary')]); !!}
              </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('food_allowance', __('sales::lang.food_allowance') . ':*') !!}
                  {!! Form::select('food_allowance',  ['cash' => __('sales::lang.cash'), 'insured_by_the_other' => __('sales::lang.insured_by_the_other')],null, ['class' => 'form-control', 'required',
                  'placeholder' => __('sales::lang.food_allowance')]); !!}
                  
              </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('housing_allowance', __('sales::lang.housing_allowance') . ':*') !!}
                  {!! Form::select('housing_allowance',  ['cash' => __('sales::lang.cash'), 'insured_by_the_other' => __('sales::lang.insured_by_the_other')],null, ['class' => 'form-control', 'required',
                  'placeholder' => __('sales::lang.housing_allowance')]); !!}
                  
              </div>
          </div>
         
          
        <div class="row">
          <div class="form-group col-sm-11 col-sm-offset-1">
            @include('sales::targetedClient.partials.single_client_form_part', ['quick_add' => true ])
          </div>
        </div>
      
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="submit_quick_client">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
{{-- <script type="text/javascript">
    $(document).ready(function(){
      $("form#quick_add_client_form").validate({
        submitHandler: function (form) {
          
          var form = $("form#quick_add_client_form");
          var url = form.attr('action');
          form.find('button[type="submit"]').attr('disabled', true);
          $.ajax({
              method: "POST",
              url: url,
              dataType: 'json',
              data: $(form).serialize(),
              success: function(data){
                  $('.quick_add_client_modal').modal('hide');
                  if( data.success){
                      toastr.success(data.msg);
                      // $(document).trigger({type: "quickProductAdded", 'product': data.client });
                      $(document).trigger({type: "quickProductAdded", 'product': data.client, 'variation': data.variation });
                  } else {
                      toastr.error(data.msg);
                  }
              }
          });
          return false;
        }
      });
    });
</script> --}}
<script src="{{ asset('js/client.js') }}"></script>
