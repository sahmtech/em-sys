@extends('layouts.app')
@section('title', __('followup::lang.requests'))

@section('content')


<section class="content-header">
    <h1>
        <span>@lang('followup::lang.requests')</span>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    
  
    @component('components.widget', ['class' => 'box-primary'])

  

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="requests_table">
                    <thead>
                        <tr>
                      
                            <th>@lang('followup::lang.worker_name')</th>
                            <th>@lang('followup::lang.request_date')</th>

                            <th>@lang('followup::lang.status')</th>
                            <th>@lang('followup::lang.note')</th>
                            <th>@lang('followup::lang.reason')</th>
                            <th>@lang('followup::lang.action')</th>

                        </tr>
                    </thead>
                </table>
            </div>
 
    @endcomponent
    <div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">@lang('followup::lang.return_the_request')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="returnModalForm">
                        <div class="form-group">
                            <label for="reasonInput">@lang('followup::lang.reason')</label>
                            <input type="text" class="form-control" id="reasonInput" required>
                        </div>
                        <button type="submit" class="btn btn-primary">@lang('followup::lang.update')</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('followup::lang.close')</button>
                </div>
            </div>
        </div>
    </div>
    @include('followup::requests.change_status_modal')
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">

    $(document).ready(function () {
       
    
     var requests_table=$('#requests_table').DataTable({
         processing: true,
         serverSide: true,

        ajax: { url: "{{ route('ess_residenceCard') }}"},
     
                 columns: [
            
                { data: 'user' },
                 { data: 'created_at' },
          
                { data: 'status' } ,
                 { data: 'status_note' },
                { data: 'reason' },
              
                {
                    data: 'can_return',
                    
                
                    render: function (data, type, row) {
                        if (data == 1 && row.start) {
                            return '<button class="btn btn-danger btn-sm btn-return" data-request-id="' + row.id + '">@lang('followup::lang.return_the_request')</button>';

                        }
                        return '';
                    }
                },
     

            ],
          
            

     });
     $(document).on('click', 'a.convert-to-proforma', function(e){
        e.preventDefault();
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(confirm => {
            if (confirm) {
                var url = $(this).attr('href');
                $.ajax({
                    method: 'GET',
                    url: url,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            sale_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
    $(document).on('click', 'a.change_status', function(e) {
            e.preventDefault();
   
            $('#change_status_modal').find('select#status_dropdown').val($(this).data('orig-value')).change();
            $('#change_status_modal').find('#request_id').val($(this).data('request-id'));
            // $('#change_status_modal').find('#reason').val($(this).data('reason'));
            // $('#change_status_modal').find('#note').val($(this).data('note'));
            $('#change_status_modal').modal('show');


            
        });

    $(document).on('submit', 'form#change_status_form', function(e) {
            e.preventDefault();
            var data = $(this).serialize();
            var ladda = Ladda.create(document.querySelector('.update-offer-status'));
            ladda.start();
            $.ajax({
                method: $(this).attr('method'),
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    ladda.stop();
                    if (result.success == true) {
                        $('div#change_status_modal').modal('hide');
                        toastr.success(result.msg);
                        requests_table.ajax.reload();
                
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });
  
   


        $('#requests_table').on('click', '.btn-return', function () {
        var requestId = $(this).data('request-id');
        $('#returnModal').modal('show');
        $('#returnModal').data('id', requestId);
    });


    $('#returnModalForm').submit(function (e) {
        e.preventDefault();

        var requestId = $('#returnModal').data('id');
        var reason = $('#reasonInput').val();

        $.ajax({
            url: "{{ route('ess_returnReq') }}",
            method: "POST",
            data: { requestId: requestId, reason: reason },
            success: function(result) {
                   
                    if (result.success == true) {
                        $('#returnModal').modal('hide');
                        toastr.success(result.msg);
                        requests_table.ajax.reload();
                
                    } else {
                        toastr.error(result.msg);
                    }
                },
        });
    });
    });

</script>
 
@endsection
