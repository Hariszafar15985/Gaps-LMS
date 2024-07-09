@php
    $requestedUser = session('requestedUser');
    $islearnt =  \App\Helpers\WebinarHelper::isLearnt('file', $file->id);
    $downloadIcon = '<i data-feather="download-cloud" width="20" height="20" class="text-gray"></i>';

    //files url
    if ($file->isVideo() && ($file->storage == 'upload' || $file->storage == 'youtube')) {

        $fileUrl = $course->getUrl(). '/file/'. $file->id . '/play';
        //Button's Text
        $btnText = trans('public.play');
    } elseif ($file->storage === 'upload_archive') {

        $fileUrl = $course->getUrl(). '/file/'. $file->id . '/showHtml';
        //Button's Text
        $btnText = trans('public.begin_scorm');
    } else {

        $fileUrl = $course->getUrl(). '/file/'. $file->id . '/download';
        $btnText = trans('home.download');
    }
    //classes for button
    $btnClass = 'course-content-btns btn-primary btn btn-sm flex-grow-1';

    // Check if the authenticated user is an admin or role other than student
    if (auth()->user() && (auth()->user()->isAdmin() || !auth()->user()->isUser())) {
        $btnClass .= 'btn-primary';

        //Button's Text
        $btn = $btnText;
    } else {
        // Check userHasDripFeedAccess
        $btnClass .= userCanAttemptFile($file->id, auth()->user()->id)
            ? ($islearnt ? ' btn-primary' : ' btn-blue')
            : ' btn-grey disabled';
        //Button's Text
        $btn = userCanAttemptFile($file->id, auth()->user()->id)
            ? ($islearnt ? trans('public.completed') : $btnText)
            : trans('public.locked');
    }

@endphp
<div class="mt-15">
    <div class="row">
        <div class="col-9 col-md-6 font-12 text-gray"><span class="pl-10">{{ trans('public.title') }}</span></div>
        <div class="col-md-3 font-12 text-gray text-center d-none d-md-block">{{ trans('public.volume') }}</div>
        <div class="col-3"></div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="accordion-content-wrapper mt-15" id="filesAccordion" role="tablist" aria-multiselectable="true">

                <div class="accordion-row rounded-sm shadow-lg border mt-20 p-15">
                    <div class="row align-items-center" role="tab" id="files_{{ $file->id }}">
                        <div class="col-9 col-md-6 d-flex align-items-center"
                            href="#collapseFiles{{ $file->id }}"
                            aria-controls="collapseFiles{{ $file->id }}" data-parent="#filesAccordion"
                            role="button" data-toggle="collapse" aria-expanded="true">
                            <span class="d-flex align-items-center justify-content-center mr-15">
                                <span class="chapter-icon chapter-content-icon">
                                    <i data-feather="{{ $file->getIconByType() }}" width="20" height="20"
                                        class="text-gray"></i>
                                </span>
                            </span>

                            @if ($file->downloadable)
                                <a href="{{ $fileUrl }}"
                                    class="mr-15 {{ !userCanAttemptFile($file->id, auth()->user()->id) ? 'disabled' : '' }}"
                                    data-toggle="tooltip" data-placement="top"
                                    title="{{ trans('home.download') }}">

                                    {!!$downloadIcon!!}
                                </a>

                            @endif

                            <span class="font-weight-bold text-secondary font-14 file-title">{{ $file->title }}</span>
                        </div>

                        <div class="col-md-3 text-gray font-14 text-center d-none d-md-block">{{ $file->volume }}
                        </div>

                        <div class="col-3 d-flex justify-content-end">

                            {{-- File Button - starts--}}
                            <a href="{{ $fileUrl }}" target="_blank" class="{{ $btnClass }}">
                                {{ $btn }}
                            </a>
                            {{-- File Button - end --}}

                            {{-- Button for Admin to provide Manual Access to student to the file if it is on drip feed --}}
                            @if (auth()->user()->isAdmin() && $requestedUser && (!empty($file->drip_feed) && $file->drip_feed == '1'))
                                <a href="{{ route('file.show-manually', ['file_id' => $file->id, 'userId' => $requestedUser->id]) }}"
                                    class="course-content-btns btn btn-sm btn-primary mx-2">
                                    {{ trans('public.toggle_access') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- DripFeed course will show on date --- section starts --}}
                    @if (!empty($file->drip_feed) && !userCanAttemptFile($file->id, auth()->user()->id))
                        <div class="text-center font-weight-bold text-secondary font-12 file-title">
                            {{ trans('public.file_available_from', [
                                    'date' => courseWillShowOn(
                                    getCoursePurchaseDate($file->webinar_id, auth()->user()->id),
                                    $file->show_after_days,
                                    ),
                                ])
                            }}
                        </div>
                    @endif

                    {{-- Description section  --}}
                    <div id="collapseFiles{{ $file->id }}" aria-labelledby="files_{{ $file->id }}"
                        class=" collapse d-none" role="tabpanel">
                        <div class="panel-collapse">
                            <div class="text-gray text-14">
                                {!! nl2br(clean($file->description)) !!}
                            </div>

                            {{-- Section I have passed this --}}
                            @if (!empty($user) and $hasBought)
                                <div class="d-flex align-items-center mt-20">
                                    <label class="mb-0 mr-10 cursor-pointer font-weight-500"
                                        for="fileReadToggle{{ $file->id }}">{{ trans('public.i_passed_this_lesson') }}</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" id="fileReadToggle{{ $file->id }}"
                                            data-file-id="{{ $file->id }}" value="{{ $course->id }}"
                                            class="js-file-learning-toggle custom-control-input"
                                            @if (!empty($file->learningStatus)) checked @endif>
                                        <label class="custom-control-label"
                                            for="fileReadToggle{{ $file->id }}"></label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


{{-- some commented code that may be used in future --}}
{{-- @if ($file->isVideo())
@if ($file->storage == 'upload' || $file->storage == 'youtube')
    <a href="{{ $course->getUrl() }}/file/{{ $file->id }}/play"
        class="mr-15 {{ !userCanAttemptFile($file->id, auth()->user()->id) ? 'disabled' : '' }}"
        data-toggle="tooltip" data-placement="top"
        title="{{ trans('public.play') }}">
        <i data-feather="play-circle" width="20" height="20"
            class="text-gray"></i>
    </a>
@else
    <button type="button" data-id="{{ $file->id }}"
        data-title="{{ $file->title }}"
        class="js-play-video btn-transparent mr-15 {{ !userCanAttemptFile($file->id, auth()->user()->id) ? 'disabled' : '' }}"
        data-toggle="tooltip" data-placement="top"
        title="{{ trans('public.play_online') }}">
        <i data-feather="play-circle" width="20" height="20"
            class="text-gray"></i>
    </button>
@endif
@endif --}}
