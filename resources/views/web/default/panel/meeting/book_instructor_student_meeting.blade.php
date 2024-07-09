@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/persian-datepicker/persian-datepicker.min.css"/>
    
@endpush

@section('content')
<div class="row">
    <div class="col-md-6 instructor-col">
        <div class="form-group">
            <label>{{ trans('panel.teacher') }}</label>
            <select class="form-control" id="instructor" name="instructor">
                @if(!isset($instructors) || $instructors->count() < 1)
                    <option disabled selected>{{ trans('meeting.no_instructors_found') }}</option>
                @else
                    @foreach($instructors as $instructor)
                        <option value="{{$instructor->id}}"
                            {{ (isset($selectedInstructor->id) && $selectedInstructor->id === $instructor->id) ? "selected='selected'" : "" }}
                            >{{$instructor->full_name}}</option>
                    @endforeach
                @endif
            </select>
            @error('organization_site')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>
    <div class="col-md-6 student-col">
        <div class="form-group">
            <label>{{ trans('panel.student') }}</label>
            <select class="form-control" id="student" name="student">
                @if(!isset($students) || $students->count() < 1)
                    <option disabled selected>{{ trans('meeting.no_students_found') }}</option>
                @else
                    @foreach($students as $student)
                        <option value="{{$student->id}}"
                            {{ (isset($selectedStudent->id) && $selectedStudent->id === $student->id) ? "selected='selected'" : "" }}
                            >{{$student->full_name}}</option>
                    @endforeach
                @endif
            </select>
            @error('organization_site')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>
</div>


    @if(!empty($meeting) and !empty($meeting->meetingTimes) and $meeting->meetingTimes->count() > 0)
        <div class="row">

            <div class="col-md-6 mt-40">
                <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('site.view_available_times') }}</h3>

                <div class="mt-35">
                    <div class="row align-items-center justify-content-center">
                        <input type="hidden" id="inlineCalender" class="form-control">
                        <div class="inline-reservation-calender"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mt-40 pick-a-time" id="PickTimeContainer" data-user-id="{{ $selectedInstructor->id }}">
                <div class="loading-img d-none text-center">
                    <img src="/assets/default/img/loading.gif" width="80" height="80">
                </div>

                <form action="{{ (!$meeting->disabled) ? '/meetings/reserve' : '' }}" method="post" id="PickTimeBody" class="d-none">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="day" id="selectedDay" value="">
                    <input type="hidden" name="studentId" id="studentId" value="{{(isset($selectedStudent->id) ? $selectedStudent->id : '')}}">

                    <h3 class="font-16 font-weight-bold text-dark-blue">
                        @if($meeting->disabled)
                            {{ trans('public.unavailable') }}
                        @else
                            {{ trans('site.pick_a_time') }}
                            @if(!empty($meeting) and !empty($meeting->discount) and !empty($meeting->amount) and $meeting->amount > 0)
                                <span class="badge badge-danger text-white font-12">{{ $meeting->discount }}% {{ trans('public.off') }}</span>
                            @endif
                        @endif
                    </h3>

                    <div class="d-flex flex-column mt-10">
                        @if($meeting->disabled)
                            <span class="font-14 text-gray">{{ trans('public.unavailable_description') }}</span>
                        @else
                            <span class="font-14 text-gray font-weight-500">
                                {{ trans('site.instructor_hourly_charge') }}

                                @if(!empty($meeting->amount) and $meeting->amount > 0)
                                    @if(!empty($meeting->discount))
                                        <span class="text-decoration-line-through">{{ $currency }}{{ $meeting->amount }}</span>
                                        <span class="text-primary">{{ $currency }}{{ $meeting->amount - (($meeting->amount * $meeting->discount) / 100) }}</span>
                                    @else
                                        <span class="text-primary">{{ $currency }}{{ $meeting->amount }}</span>
                                    @endif
                                @else
                                    <span class="text-primary">{{ trans('public.free') }}</span>
                                @endif
                        </span>
                        @endif

                        <span class="font-14 text-gray mt-5 selected_date font-weight-500">{{ trans('site.selected_date') }}: <span></span></span>
                    </div>

                    <div id="availableTimes" class="d-flex flex-wrap align-items-center mt-25">

                    </div>

                    @if(!$meeting->disabled && (isset($instructors) && $instructors->count() > 0) && (isset($students) && $students->count() > 0))
                        <button type="submit" class="btn btn-sm btn-primary mt-30">{{ trans('meeting.reserve_appointment') }}</button>
                    @endif
                </form>
            </div>
        </div>
    @else

        @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'meet.png',
        'title' => trans('site.instructor_not_available'),
        'hint' => '',
        ])

    @endif

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script>
        let currentInstructor = {{ isset($selectedInstructor->id) ? $selectedInstructor->id : "null" }};
        var instructor_contact_information_lang = '{{ trans('panel.instructor_contact_information') }}';
        var student_contact_information_lang = '{{ trans('panel.student_contact_information') }}';
        var email_lang = '{{ trans('public.email') }}';
        var phone_lang = '{{ trans('public.phone') }}';
        var close_lang = '{{ trans('public.close') }}';
        var linkSuccessAdd = '{{ trans('panel.add_live_meeting_link_success') }}';
        var linkFailAdd = '{{ trans('panel.add_live_meeting_link_fail') }}';
        var finishReserveHint = '{{ trans('meeting.finish_reserve_modal_hint') }}';
        var finishReserveConfirm = '{{ trans('meeting.finish_reserve_modal_confirm') }}';
        var finishReserveCancel = '{{ trans('meeting.finish_reserve_modal_cancel') }}';
        var finishReserveTitle = '{{ trans('meeting.finish_reserve_modal_title') }}';
        var finishReserveSuccess = '{{ trans('meeting.finish_reserve_modal_success') }}';
        var finishReserveSuccessHint = '{{ trans('meeting.finish_reserve_modal_success_hint') }}';
        var finishReserveFail = '{{ trans('meeting.finish_reserve_modal_fail') }}';
        var finishReserveFailHint = '{{ trans('meeting.finish_reserve_modal_fail_hint') }}';

        
        $("#instructor").change(function(){
            let instructor = $(this).val();
            if (instructor !== currentInstructor && instructor > 0) {
                let student = $("#student").val();
                let params = `?instructor=${instructor}`;
                if (typeof student !== 'undefined' && student > 0) {
                    params += `&student=${student}`;
                }
                url = location.protocol + '//' + location.host + location.pathname;
                if (typeof params !== 'undefined' && params.length > 0) {
                    url += params;
                }
                location.replace(url);
            }
        });
        $("#student").change(function() {
            let studentId = $(this).val();
            if(typeof studentId !== 'undefined' && studentId > 0) {
                $("#studentId").val(studentId);
            }
        });
    </script>


    <script>
        var reservedLang = '{{ trans('meeting.reserved') }}';
        var availableDays = {{ json_encode($times) }};
        var messageSuccessSentLang = '{{ trans('site.message_success_sent') }}';
    </script>

    <script src="/assets/default/vendors/persian-datepicker/persian-date.js"></script>
    <script src="/assets/default/vendors/persian-datepicker/persian-datepicker.js"></script>

    <script src="/assets/default/js/parts/profile.min.js"></script>
@endpush