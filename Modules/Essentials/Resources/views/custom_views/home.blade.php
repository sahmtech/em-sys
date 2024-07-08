@extends('layouts.app')
@section('title', __('home.home'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header content-header-custom">
  
</section>
<!-- Main content -->
<section class="content content-custom no-print">
    <br>
    @if(auth()->user()->can('dashboard.data'))
        @if($is_admin)
        	
    	   <br>
    	   <div class="row">
                <!-- /.col -->
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">
                    
                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
              
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">
                      

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">
                      

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
              
              
    	    <!-- /.col -->
            </div>
          
       


            <br>
    	   <div class="row">
                <!-- /.col -->
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">
                    
                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
              
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">
                      

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">
                      

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
              
              
    	    <!-- /.col -->
            </div>



            <br>
    	   <div class="row">
                <!-- /.col -->
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">
                    
                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
              
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">
                      

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
                   <div class="info-box info-box-new-style">
                      

                        <div class="info-box-content">
                          <span class="info-box-text" >{{ __('home.total_sell') }}</span>
                          
                        </div>
                        <!-- /.info-box-content -->
                   </div>
                  <!-- /.info-box -->
                </div>
              
              
    	    <!-- /.col -->
            </div>
        @endif 
        <!-- end is_admin check -->


    @endif
   <!-- can('dashboard.data') end -->
</section>
<!-- /.content -->

@stop


