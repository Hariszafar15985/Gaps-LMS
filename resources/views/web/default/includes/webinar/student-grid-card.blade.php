<div class="webinar-card row mx-2">
    {{-- <figure> --}}
        <div class="image-box col-lg-4">
           <span class="badge badge-primary">{{ trans('webinars.'.$webinar->type) }}</span>

            <a href="{{ $webinar->getUrl() }}">
                <img src="{{ $webinar->getImage() }}" class="img-cover" alt="{{ $webinar->title }}" >
            </a>
        </div>

        {{-- <figcaption class="webinar-card-body"> --}}
        <div class="col-lg-8 d-flex mt-auto mb-1 flex-column">
            <div class="user-inline-avatar d-flex align-items-center">
                <div class="avatar">
                    <img src="{{ $webinar->teacher->getAvatar() }}" class="img-cover" alt="{{ $webinar->teacher->full_name }}">
                </div>
            </div>

            <a href="{{ $webinar->getUrl() }}">
                <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($webinar->title,'title') }}</h3>
            </a>
            @php
                $percent = $webinar->getProgress($user->id);
                $expectedProgress = $webinar->getExpectedProgress($user->id);
                $expectedProgressPercent = 0;
                if ($expectedProgress > $percent) {
                    $expectedProgressPercent = $expectedProgress - $percent;
                }
            @endphp

            <div class="progress course-progress flex-grow-1 shadow-xs rounded-sm">
                <span class="progress-bar {{($percent >= $expectedProgress) ? "rounded-sm bg-primary" : "bg-warning"  }}" style="width: {{ $percent }}%"><span  class="popOver" data-toggle="tooltip" data-placement="top" title="{{$percent}}%"> </span></span>
                @if (isset($expectedProgressPercent) && $expectedProgressPercent > 0)
                    <span class="progress-bar rounded-right-sm bg-danger" style="width: {{ $expectedProgressPercent }}%"><span  class="popOver" data-toggle="tooltip" data-placement="top" title="{{$expectedProgress}}%"> </span></span>
                @endif
            </div>
            <div class="ml-15 mt-1 font-14 font-weight-500 text-right">
                <span class="font-14 font-weight-500 {{ ($percent >= $expectedProgress) ? "text-primary" : "text-danger"}}">
                    {{ trans('public.course_learning_passed',['percent' => $percent]) }}
                </span>
                <br />
                <span class="font-14 font-weight-500">
                    {{ trans('public.expected_progress',['percent' => $expectedProgress]) }}
                </span>
            </div>

            @if(!empty($webinar->category))
                <span class="d-block font-14 mt-10">{{ trans('public.in') }} <a href="{{ $webinar->category->getUrl() }}" target="_blank" class="text-decoration-underline">{{ $webinar->category->title }}</a></span>
            @endif

            <div class="d-flex justify-content-between mt-20">
                <div class="d-flex align-items-center">
                    <i data-feather="clock" width="20" height="20" class="webinar-icon"></i>
                    <span class="duration font-14 ml-5">{{ convertMinutesToHourAndMinute($webinar->duration) }} {{ trans('home.hours') }}</span>
                </div>

                <div class="vertical-line mx-15"></div>

                @if(!empty($webinar->registerDate))
                <div class="d-flex align-items-center">
                    <i data-feather="calendar" width="20" height="20" class="webinar-icon"></i>
                    <span class="date-published font-14 ml-5">{{ dateTimeFormat($webinar->registerDate,'j F Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- </figcaption> --}}
    {{-- </figure> --}}
</div>
