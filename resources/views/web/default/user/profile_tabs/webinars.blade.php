@if(!empty($webinars) and !$webinars->isEmpty())
    <div class="mt-20 row">

        @foreach($webinars as $webinar)
            <div class="col-lg-12 mt-20">
                @if($user->role_name === \App\Models\Role::$user)
                    @include('web.default.includes.webinar.student-grid-card',['webinar' => $webinar])
                @else
                    @include('web.default.includes.webinar.grid-card',['webinar' => $webinar])
                @endif
            </div>
        @endforeach
    </div>
@else
    @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'webinar.png',
        'title' => trans('site.instructor_not_have_webinar'),
        'hint' => '',
    ])
@endif

