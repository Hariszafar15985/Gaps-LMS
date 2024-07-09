<!-- Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.upload_document') }}</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="upload-document-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="uuid" name="user_id" value="{{ $user['id'] }}">

                    <div class="form-group">
                        <label class="input-label">{{ trans('public.title') }}</label>
                        <input type="text" id="ud-title" name="title" class="form-control my-title-cls" maxlength="60" placeholder=""/>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label class="input-label">{{ trans('public.document_type') }}</label>
                        <select id="ud-type" name="type" class="form-control ">
                            @if(!empty($allUserDocumentTypes))
                                <option value="">{{ trans('public.choose_document') }}</option>
                                @foreach($allUserDocumentTypes as $documentKey => $documentName)
                                    <option value="{{ $documentKey }}">{{ $documentName }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="input-label">{{ trans('public.description') }}</label>
                        <textarea type="text" id="ud-description" name="description" class="form-control" maxlength="100"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label class="input-label">{{ trans('public.image') }}</label>
                        <input type="file" name="document" id="ud-document" class="form-control" placeholder=""/>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mt-30 d-flex align-items-center justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="uploadDocument();">{{ trans('public.upload_document') }}</button>
                        <button type="button" class="btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-none">

            </div>
        </div>
    </div>
</div>
