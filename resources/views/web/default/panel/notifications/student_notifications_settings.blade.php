@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
        <form method="post" action="/panel/notifications/students/settings/update" id="settingForm" class="setting-form">
            {{ csrf_field() }}

            <section class="mt-3">
                <h2 class="section-title after-line">{{ trans('panel.student_notifications_settings') }}</h2>
                <div class="row">
                    <div class="col-12 col-md-6">

                        @foreach($default as $setting )

                        <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                            <label class="" for="{{$setting}}Switch">{{ trans( 'panel.' . $setting ) }}</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="{{$setting}}" class="custom-control-input" id="{{$setting}}Switch" {{ in_array($setting, $settings) && $active ? 'checked' : '' }}>
                                <label class="custom-control-label" for="{{$setting}}Switch"></label>
                            </div>
                        </div>

                        @endforeach

                        <div class="form-group">
                            <button type="submit" class="btn btn-gray">{{ trans('admin/main.save_change') }}</button>
                        </div>

                    </div>
                </div>

            </section>

        </form>

    <div class="mt-5 d-none" id="messageModal">
        <div class="text-center">
            <h3 class="modal-title font-16 font-weight-bold text-dark-blue"></h3>
            <span class="modal-time d-block font-12 text-gray mt-5"></span>
            <span class="modal-message text-gray mt-20"></span>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script>
        (function ($) {
            "use strict";

        })(jQuery)
    </script>
@endpush
