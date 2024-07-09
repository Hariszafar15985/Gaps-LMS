@if(!empty($webinars) and !$webinars->isEmpty())
    <div class="mt-20 row">
        <ul class="nav nav-tabs d-flex align-items-center px-20 px-lg-50 pb-15" id="courses-tab" role="tablist">
            {{-- <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Home</button>
                </li> --}}
            @php $first = true; @endphp
            @foreach($webinars as $webinar)
            @php $slug = str_replace(" ", "-", $webinar->slug); @endphp
            <li class="nav-item mr-20 mr-lg-40 mt-30">
                <a class="position-relative text-dark-blue font-weight-500 font-16 {{ $first === true ? 'active' : ''}}"
                id="{{$slug}}-tab" data-toggle="tab" href="#{{$slug}}" role="course-tab"
                aria-controls="{{$slug}}" aria-selected="{{ $first===true ? "true" : "false"}}">{{ $webinar->title }}</a>
            </li>
            @php $first = false; @endphp
            @endforeach
        </ul>
    </div>
    @if(!empty($coursesDetails))
    <div class="tab-content" id="courseNav-tabContent">
        @php $first = true; @endphp
        @foreach($coursesDetails as $courseDetail)
        @php $slug = str_replace(" ", "-", $courseDetail->slug); @endphp
        <div class="tab-pane fade px-20 px-lg-50  {{ $first === true ? 'show active' : ''}}"
        id="{{$slug}}" role="tabpanel" aria-labelledby="{{$slug}}-tab">
        {!! $courseDetail->content !!}
    </div>
    @php $first = false; @endphp
    @endforeach
    </div>
    @endif
@else
    @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'webinar.png',
        'title' => trans('site.student_not_have_webinar'),
        'hint' => '',
    ])
@endif
