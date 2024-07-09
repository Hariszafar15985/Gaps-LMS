@push('styles_top')
    <style>
        .btn-blue {
            background: #3589a1;
            color: white;
        }

        .btn-blue:hover {
            background: #166074;
            color: white;
        }
    </style>
@endpush
@php
    $requestedUser = session('requestedUser');
    $islearnt =  \App\Helpers\WebinarHelper::isLearnt('text_lesson', $textLesson->id);

    //text lesson url
    $lessonUrl =  $course->getUrl(). '/lessons/' . $textLesson->id . '/read';

    //classes for button
    $btnClass = 'course-content-btns btn-primary btn btn-sm flex-grow-1';

    // Check if the authenticated user is an admin or role other than student
    if (auth()->user() && (auth()->user()->isAdmin() || !auth()->user()->isUser())) {
        $btnClass .= 'btn-primary';

        //Button's Text
        $btnText = trans('public.preview');
    } else {
        // Check userHasDripFeedAccess
        $btnClass .= userHasDripFeedAccess($textLesson->id, auth()->user()->id)
            ? ($islearnt ? ' btn-primary' : ' btn-blue')
            : ' btn-grey disabled';
          //Button's Text
        $btnText = userHasDripFeedAccess($textLesson->id, auth()->user()->id)
            ? ($islearnt ? trans('public.completed') : trans('public.begin_lesson'))
            : trans('public.locked');
    }


@endphp

{{-- Read Button - starts--}}
<a href="{{ $lessonUrl }}" target="_blank" class="{{ $btnClass }}">
    {{ $btnText }}
</a>
{{-- Read Button - end --}}

{{-- Button for Admin to provide Manual Access to student to the lesson if it is on drip feed --}}
@if ($isAdmin && $requestedUser && (!empty($textLesson->drip_feed) && $textLesson->drip_feed == '1'))
    <a href="{{ route('show-manually', ['textLesson' => $textLesson->id, 'userId' => $requestedUser->id]) }}"
        class="course-content-btns btn btn-sm btn-primary mx-2">
        {{ trans('public.toggle_access') }}
    </a>
@endif
