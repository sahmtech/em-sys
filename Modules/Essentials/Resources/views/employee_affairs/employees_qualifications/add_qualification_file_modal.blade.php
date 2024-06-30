  <div class="modal fade" id="addQualificationFileModal" tabindex="-1" role="dialog"
      aria-labelledby="gridSystemModalLabel">
      <div class="modal-dialog" role="document">
          <div class="modal-content">

              {!! Form::open(['route' => 'storeQualDocFile', 'enctype' => 'multipart/form-data']) !!}
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                          aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">@lang('essentials::lang.add_doc_file')</h4>
              </div>

              <div class="modal-body">
                  <div class="row">
                      <div class="modal-body">
                          <iframe id="iframeQualDocViewer" width="100%" height="300px" frameborder="0"></iframe>
                      </div>
                  </div>

                  <div class="row">
                      {!! Form::hidden('delete_file', '0', ['id' => 'delete_file_input']) !!}
                      {!! Form::hidden('doc_id', null, ['id' => 'doc_id']) !!}
                      <div class="form-group col-md-6">
                          {!! Form::label('file', __('essentials::lang.file') . ':') !!}
                          <div class="input-group">
                              {!! Form::file('file', ['class' => 'form-control', 'style' => 'height:40px']) !!}
                              <div class="input-group-append">
                                  <button type="button" class="btn btn-danger deleteFile">@lang('messages.delete')</button>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="printDocButton">@lang('messages.print')</button>
                  <button type="submit" class="btn btn-primary saveFile" disabled>@lang('messages.save')</button>
                  <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
              </div>
              {!! Form::close() !!}
          </div>
      </div>
  </div>
