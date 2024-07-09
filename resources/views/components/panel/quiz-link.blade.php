<div>
    <a href="@if($released) {{ route('panel.quizzes.start', ['id' => $quiz->id]) }} @else{{'#'}}@endif"
        class="@if($nested) @endif d-block  rounded font-14 font-weight-500 text-ellipsis @if ($isActive) bg-primary text-white @else bg-info-lighter text-dark-blue @endif @if (!$released) alert alert-danger @endif"
        @if(!$released) data-toggle="tooltip" data-placement="top" title="{{trans('public.quiz_unavailable')}}" @endif >
        <span class="mr-15">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-award text-gray">
                <circle cx="12" cy="8" r="7"></circle>
                <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
            </svg>
        </span>
        {{ $quiz->title }}
    </a>
</div>
