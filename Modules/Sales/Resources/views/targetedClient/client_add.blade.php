<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      {!! Form::open(['url' => action([\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'saveQuickClient']), 'method' => 'post', 'id' => 'quick_add_cient_form' ]) !!}
  
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle">@lang( 'client.add_new_client' )</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              {!! Form::label('profession', __('client.profession') . ':*') !!}
                {!! Form::text('profession', null, ['class' => 'form-control', 'required',
                'placeholder' => __('client.profession')]); !!}
            </div>
          </div>
  
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('specialization', __('client.specialization') . ':*') !!}
                  {!! Form::text('specialization', null, ['class' => 'form-control', 'required',
                  'placeholder' => __('client.specialization')]); !!}
              </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('nationality', __('client.nationality') . ':*') !!}
                  {!! Form::text('nationality', null, ['class' => 'form-control', 'required',
                  'placeholder' => __('client.nationality')]); !!}
              </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('gender', __('client.gender') . ':*') !!}
                  {!! Form::select('gender',  ['male' => __('sales::lang.male'), 'female' => __('sales::lang.female')],null, ['class' => 'form-control', 'required',
                  'placeholder' => __('client.gender')]); !!}
                  
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
                {!! Form::label('Salary', __('client.salary') . ':*') !!}
                  {!! Form::number('Salary', null, ['class' => 'form-control', 'required',
                  'placeholder' => __('client.salary')]); !!}
              </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('food_allowance', __('client.food_allowance') . ':*') !!}
                  {!! Form::select('food_allowance',  ['cash' => __('sales::lang.cash'), 'insured_by_the_other' => __('sales::lang.insured_by_the_other')],null, ['class' => 'form-control', 'required',
                  'placeholder' => __('client.food_allowance')]); !!}
                  
              </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('housing_allowance', __('client.housing_allowance') . ':*') !!}
                  {!! Form::select('housing_allowance',  ['cash' => __('sales::lang.cash'), 'insured_by_the_other' => __('sales::lang.insured_by_the_other')],null, ['class' => 'form-control', 'required',
                  'placeholder' => __('client.housing_allowance')]); !!}
                  
              </div>
          </div>
          <div class="clearfix"></div>
          @php
          $custom_labels = json_decode(session('business.custom_labels'), true);
          $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
          $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
          $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
          $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
        @endphp
          
          <div class="clearfix"></div>
          <div class="col-sm-3">
            <div class="form-group">
              {!! Form::label('product_custom_field1',  $product_custom_field1 . ':') !!}
              {!! Form::text('product_custom_field1', null, ['class' => 'form-control', 'placeholder' => $product_custom_field1]); !!}
            </div>
          </div>
  
          <div class="col-sm-3">
            <div class="form-group">
              {!! Form::label('product_custom_field2',  $product_custom_field2 . ':') !!}
              {!! Form::text('product_custom_field2',null, ['class' => 'form-control', 'placeholder' => $product_custom_field2]); !!}
            </div>
          </div>
  
          <div class="col-sm-3">
            <div class="form-group">
              {!! Form::label('product_custom_field3',  $product_custom_field3 . ':') !!}
              {!! Form::text('product_custom_field3', null, ['class' => 'form-control', 'placeholder' => $product_custom_field3]); !!}
            </div>
          </div>
  
          <div class="col-sm-3">
            <div class="form-group">
              {!! Form::label('product_custom_field4',  $product_custom_field4 . ':') !!}
              {!! Form::text('product_custom_field4', null, ['class' => 'form-control', 'placeholder' => $product_custom_field4]); !!}
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
