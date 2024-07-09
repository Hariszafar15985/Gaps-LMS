{{-- @if($user->offline)
    <div class="user-offline-alert d-flex mt-40">
        <div class="p-15">
            <h3 class="font-16 text-dark-blue">{{ trans('public.instructor_is_not_available') }}</h3>
            <p class="font-14 font-weight-500 text-gray mt-15">{{ $user->offline_message }}</p>
        </div>

        <div class="offline-icon offline-icon-right ml-auto d-flex align-items-stretch">
            <div class="d-flex align-items-center">
                <img src="/assets/default/img/profile/time-icon.png" alt="offline">
            </div>
        </div>
    </div>
@endif

@if((!empty($educations) and !$educations->isEmpty()) or (!empty($experiences) and !$experiences->isEmpty()) or (!empty($occupations) and !$occupations->isEmpty()) or !empty($user->about))
    @if(!empty($educations) and !$educations->isEmpty())
        <div class="mt-40">
            <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.education') }}</h3>

            <ul class="list-group-custom">
                @foreach($educations as $education)
                    <li class="mt-15 text-gray">{{ $education->value }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!empty($experiences) and !$experiences->isEmpty())
        <div class="mt-40">
            <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.experiences') }}</h3>

            <ul class="list-group-custom">
                @foreach($experiences as $experience)
                    <li class="mt-15 text-gray">{{ $experience->value }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!empty($user->about))
        <div class="mt-40">
            <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.about') }}</h3>

            <div class="mt-30">
                {!! nl2br($user->about) !!}
            </div>
        </div>
    @endif

    @if(!empty($occupations) and !$occupations->isEmpty())
        <div class="mt-40">
            <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.occupations') }}</h3>

            <div class="mt-20 d-flex align-items-center">
                @foreach($occupations as $occupation)
                    <div class="bg-gray200 font-14 rounded px-10 py-5 text-gray mr-15">{{ $occupation->category->title }}</div>
                @endforeach
            </div>
        </div>
    @endif

@else

    @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'bio.png',
        'title' => trans('site.not_create_bio'),
        'hint' => '',
    ])

@endif

--}}

<div class="mt-20 row">
    <div class="col-12">
        {{-- Full Name --}}
        @if (isset($user->full_name) && strlen(trim($user->full_name)) > 0)
            <div class="row mt-20">
                <div class="font-16 text-dark-blue font-weight-bold col-12 col-md-2 ">{{ trans('panel.full_name') }} : </div>
                <div class="col-12 col-md-10">
                    {{ ucwords(trim($user->full_name)) }}
                </div>
            </div>
        @endif

        {{-- Email --}}
        @if (isset($user->email) && strlen(trim($user->email)) > 0)
            <div class="row mt-20">
                <div class="font-16 text-dark-blue font-weight-bold col-12 col-md-2 ">{{ trans('panel.email') }} : </div>
                <div class="col-12 col-md-10">
                    {{ ucwords(trim($user->email)) }}
                </div>
            </div>
        @endif

        {{-- Phone Number --}}
        @if (isset($user->mobile) && strlen(trim($user->mobile)) > 0)
            <div class="row mt-20">
                <div class="font-16 text-dark-blue font-weight-bold col-12 col-md-2 ">{{ trans('panel.phone') }} : </div>
                <div class="col-12 col-md-10">
                    {{ ucwords(trim($user->mobile)) }}
                </div>
            </div>
        @endif

        {{-- USI --}}
        @if (isset($user->id) && $user->id > 0)
            <div class="row mt-20">
                <div class="font-16 text-dark-blue font-weight-bold col-12 col-md-2 ">{{ trans('panel.usi') }} : </div>
                <div class="col-12 col-md-10">
                    {{-- trim($user->id) --}}
                    TEST Value
                </div>
            </div>
        @endif

        {{-- Organization --}}
        @if (isset($user->organ_id) && $user->organ_id > 0)
            <div class="row mt-20">
                <div class="font-16 text-dark-blue font-weight-bold col-12 col-md-2 ">{{ trans('panel.organization') }} : </div>
                <div class="col-12 col-md-10">
                    {{ trim($user->organization->full_name) }}
                </div>
            </div>
        @endif

        {{-- Organization Managed By --}}
        @if (isset($user->manager->full_name) && $user->manager_id > 0)
            <div class="row mt-20">
                <div class="font-16 text-dark-blue font-weight-bold col-12 col-md-2 ">{{ trans('panel.managed_by') }} : </div>
                <div class="col-12 col-md-10">
                    {{ trim($user->manager->full_name) }}
                </div>
            </div>
        @endif

        {{-- Trainer --}}
        @if (isset($webinars) && count($webinars) > 0)
            <div class="row mt-20">
                @php
                    $trainers = [];
                    foreach($webinars as $webinar) {
                        if (isset($webinar->teacher->full_name) && !in_array(ucwords($webinar->teacher->full_name), $trainers) )
                        $trainers[] = ucwords($webinar->teacher->full_name);
                    }
                @endphp
                <div class="font-16 text-dark-blue font-weight-bold col-12 col-md-2 ">
                    @if (is_array($trainers) && count($trainers) > 1 )
                        {{trans('panel.trainers') }}
                    @elseif (is_array($trainers) && count($trainers) > 0 )
                        {{trans('panel.trainer') }}
                    @endif
                    :
                    </div>
                <div class="col-12 col-md-10">
                    {{ (is_array($trainers) && count($trainers) > 0) ? implode(", ", $trainers) : "" }}
                </div>
            </div>
        @endif

        {{-- Employment Service --}}
        @if(!empty($experiences) and !$experiences->isEmpty())
            <div class="mt-40">
                <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.experiences') }}</h3>

                <ul class="list-group-custom">
                    @foreach($experiences as $experience)
                        <li class="mt-15 text-gray">{{ $experience->value }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


    </div>
</div>
