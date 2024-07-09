@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    @if(empty($new_user))
        @include('web.default.panel.setting.setting_includes.progress')
    @endif
    <style>
        .custom-control.custom-switch{
            padding-left: 3rem;
            padding-right: 4rem;
            margin-bottom: 20px;
            float:left;
            width: 425px;
        }
    </style>

    @if(Session::has('enrollmentMessage') || isset($enrollmentMessage))
        @php $enrollmentMessage = (Session::has('enrollmentMessage')) ? Session::get('enrollmentMessage') : $enrollmentMessage; @endphp
        <p class="alert alert-danger">{{ $enrollmentMessage }}</p>
    @endif
    <form method="post" id="userSettingForm" class="mt-30" action="{{ (!empty($new_user)) ? '/panel/manage/'. $user_type .'/new' : '/panel/setting' }}">
        {{ csrf_field() }}
        <input type="hidden" name="step" value="{{ !empty($currentStep) ? $currentStep : 1 }}">
        <input type="hidden" name="next_step" value="0">

        @if(!empty($organization_id))
            <input type="hidden" name="organization_id" value="{{ $organization_id }}">
            <input type="hidden" id="userId" name="user_id" value="{{ $user->id }}">
        @endif
        @if(isset($consultantSiteId) && (int)$consultantSiteId > 0)
            <input type="hidden" name="organization_site[]" value="{{ $consultantSiteId }}">
        @endif
        @if(!empty($new_user) && !empty($user) && $user->isOrganizationStaff())
            <input type="hidden" name="manager_id" value="{{ $user->id }}">
        @endif

        @if(!empty($new_user) or (!empty($currentStep) and $currentStep == 1))
            @include('web.default.panel.setting.setting_includes.basic_information')
        @endif

        @if(empty($new_user) and !empty($currentStep))
            @switch($currentStep)
                @case(2)
                @include('web.default.panel.setting.setting_includes.image')
                @break

                @case(3)
                {{--@include('web.default.panel.setting.setting_includes.about')--}}
                @include('web.default.panel.setting.setting_includes.participant_details')
                @break

                @case(4)
                @include('web.default.panel.setting.setting_includes.cultural_background')
                @break

                @case(5)
                @include('web.default.panel.setting.setting_includes.special_needs')
                @break

                @case(6)
                @include('web.default.panel.setting.setting_includes.employment')
                @break

                @case(7)
                @include('web.default.panel.setting.setting_includes.education')
                @break

                @case(8)
                @include('web.default.panel.setting.setting_includes.study_reasons')
                @break

                @case(9)
                @include('web.default.panel.setting.setting_includes.unique_student_identifier')
                @break

                @case(10)
                @include('web.default.panel.setting.setting_includes.student_declaration')
                @break

                @case(11)
                @include('web.default.panel.setting.setting_includes.experiences')
                @break

                @case(12)
                @include('web.default.panel.setting.setting_includes.occupations')
                @break

                @case(13)
                    @include('web.default.panel.setting.setting_includes.identity_and_financial')
                @break

                @case(14)
                @if(!$user->isUser())
                    @include('web.default.panel.setting.setting_includes.zoom_api')
                @endif
                @break
            @endswitch
        @endif
    </form>

    <div class="create-webinar-footer d-flex align-items-center justify-content-between mt-20 pt-15 border-top">
        <div class="">
            @if(empty($new_user) || $user_type != "students")
                @if(!empty($currentStep) and $currentStep > 1)
                    <a href="/panel/setting/step/{{ ($currentStep - 1) }}" class="btn btn-sm btn-primary">{{ trans('webinars.previous') }}</a>
                @else
                    <a href="" class="btn btn-sm btn-primary disabled">{{ trans('webinars.previous') }}</a>
                @endif

                <button type="button" id="getNextStep" class="btn btn-sm btn-primary ml-15" @if(!empty($currentStep) and $currentStep == 12) disabled @endif>{{ trans('webinars.next') }}</button>
            @endif
        </div>

        <button type="button" id="saveData" class="btn btn-sm btn-primary ml-15">{{ trans('public.save') }}</button>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/cropit/jquery.cropit.js"></script>
    <script src="/assets/default/js/parts/img_cropit.min.js"></script>

    <script>
        var editEducationLang = '{{ trans('site.edit_education') }}';
        var editExperienceLang = '{{ trans('site.edit_experience') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var saveErrorLang = '{{ trans('site.store_error_try_again') }}';
        var notAccessToLang = '{{ trans('public.not_access_to_this_content') }}';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        @if(
            $authUser->isAdmin() || $authUser->isOrganization()
            || $authUser->isOrganizationManager() || $authUser->isOrganizationSubManager()
        )
        $('#organization_site').change( function(event){
            event.preventDefault();
            let requestUrl = $(this).data('url');
            let data = {};
            data['organization_site_id'] = $(this).val();
            $.ajax({
                type: 'POST',
                url: requestUrl,
                dataType: 'json',
                data: data,
                success: function (data) {
                    if ( Object.keys(data).length > 0 && data.success) {
                        let managersElem = $('#manager_id');
                        if (Object.keys(data['managers']).length > 0) {
                            let managers = data['managers'];
                            managersElem.html("");
                            for (id in managers) {
                                let option = document.createElement('option');
                                option.setAttribute('value', managers[id]);
                                option.text = `${id}`;
                                managersElem.append(option);
                            }
                        } else {
                            managersElem.html("");
                            let option = document.createElement('option');
                            option.text = `{{ trans('panel.no_managers_in_selected_site') }}`;
                            managersElem.append(option);
                        }
                        if (typeof data.heading !== 'undefined' && data.heading.length > 0) {
                            $('#resultsModal .modal-title').html(data.heading);
                        }
                        if (typeof data.html !== 'undefined' && data.html.length > 0) {
                            $('#resultsModal .modal-body').html(data.html);
                        }
                    }
                },error:function(data){
                    console.log(data);
                }
            });
        });

        $('#organization_site').trigger('change');
        @endif
    </script>

    <script src="/assets/default/js/panel/user_setting.min.js"></script>
@endpush
