@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')

    {{-- Removing the previously applied Text lesson content styling --}}
    {{-- <style>
        /* For summernote fonts */
        /* .note-editor h1, .note-editor h1 * {
            font-family: Helvetica !important;
            font-size: 18px !important;
        }
        .note-editor h2, .note-editor h2 * {
            font-family: Helvetica !important;
            font-size: 17px !important;
        }
        .note-editor h3, .note-editor h3 * {
            font-family: Helvetica !important;
            font-size: 16px !important;
        }
        .note-editor h4, .note-editor h4 * {
            font-family: Helvetica !important;
            font-size: 15px !important;
        }
        .note-editor h5, .note-editor h5 *,
        .note-editor h6, .note-editor h6 *,
        .note-editor p, .note-editor p * {
            font-family: Helvetica !important;
            font-size: 14px !important;
        } */
    </style> --}}
@endpush

@section('content')
    <div class="">

        <form method="post" action="/panel/webinars/{{ !empty($webinar) ? $webinar->id .'/update' : 'store' }}" id="webinarForm" class="webinar-form mt-30">
            @include('web.default.panel.webinar.create_includes.progress')

            {{ csrf_field() }}
            <input type="hidden" name="current_step" value="{{ !empty($currentStep) ? $currentStep : 1 }}">
            <input type="hidden" name="draft" value="no" id="forDraft"/>
            <input type="hidden" name="get_next" value="no" id="getNext"/>
            <input type="hidden" name="get_step" value="0" id="getStep"/>


            @if($currentStep == 1)
                @include('web.default.panel.webinar.create_includes.step_1')
            @elseif(!empty($webinar))
                @include('web.default.panel.webinar.create_includes.step_'.$currentStep)
            @endif

        </form>


        <div class="create-webinar-footer d-flex flex-column flex-md-row align-items-center justify-content-between mt-20 pt-15 border-top">
            <div class="d-flex align-items-center">

                @if(!empty($webinar))
                    <a href="/panel/webinars/{{ $webinar->id }}/step/{{ ($currentStep - 1) }}" class="btn btn-sm btn-primary {{ $currentStep < 2 ? 'disabled' : '' }}">{{ trans('webinars.previous') }}</a>
                @else
                    <a href="" class="btn btn-sm btn-primary disabled">{{ trans('webinars.previous') }}</a>
                @endif

                <button type="button" id="getNextStep" class="btn btn-sm btn-primary ml-15" @if($currentStep >= 8) disabled @endif>{{ trans('webinars.next') }}</button>
            </div>

            <div class="mt-20 mt-md-0">
                <button type="button" id="sendForReview" class="btn btn-sm btn-primary">{{ trans('public.submit_course') }}</button>
                @if(env("SHOW_SAVE_AS_DRAFT_BUTTON",  false) == true)
                <button type="button" id="saveAsDraft" class=" btn btn-sm btn-primary">{{ trans('public.save_as_draft') }}</button>
                @endif
                @if(!empty($webinar) and $webinar->creator_id == $authUser->id)
                    <a href="/panel/webinars/{{ $webinar->id }}/delete?redirect_to=/panel/webinars" class="delete-action webinar-actions btn btn-sm btn-danger mt-20 mt-md-0">{{ trans('public.delete') }}</a>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var zoomJwtTokenInvalid = '{{ trans('webinars.zoom_jwt_token_invalid') }}';
        var hasZoomApiToken = '{{ (!empty($authUser->zoomApi) and $authUser->zoomApi->jwt_token) ? 'true' : 'false' }}';
        var editChapterLang = '{{ trans('public.edit_chapter') }}';
    </script>

    <script src="/assets/default/js/panel/webinar.min.js"></script>
    <script src="/assets/default/js/panel/webinar_content_locale.min.js"></script>
    <script src="/assets/default/js/panel/quiz.min.js"></script>
@endpush
