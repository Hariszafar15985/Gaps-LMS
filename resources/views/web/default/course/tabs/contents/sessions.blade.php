<div class="mt-15">
    <div class="row">
        <div class="col-6 col-md-4 font-12 text-gray"><span class="pl-10">{{ trans('public.title') }}</span></div>
        <div class="col-3 font-12 text-gray text-center">{{ trans('public.start_date') }}</div>
        <div class="col-2 font-12 text-gray text-center d-none d-md-block">{{ trans('public.duration') }}</div>
        <div class="col-3"></div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="accordion-content-wrapper mt-15" id="sessionsAccordion" role="tablist" aria-multiselectable="true">
                {{-- @foreach($sessions as $session) --}}
                    <div class="accordion-row rounded-sm shadow-lg border mt-20 p-15">
                        <div class="row align-items-center" role="tab" id="session_{{ $session->id }}">
                            <div class="col-6 col-md-4 d-flex align-items-center" href="#collapseSession{{ $session->id }}" aria-controls="collapseSession{{ $session->id }}" data-parent="#sessionsAccordion" role="button" data-toggle="collapse" aria-expanded="true">
                                @if($session->date > time())
                                    <a href="{{ $session->addToCalendarLink() }}" target="_blank" class="mr-15 d-flex" data-toggle="tooltip" data-placement="top" title="{{ trans('public.add_to_calendar') }}">
                                        <i data-feather="bell" width="20" height="20" class="text-gray"></i>
                                    </a>
                                @else
                                    <span class="mr-15 d-flex"><i data-feather="bell" width="20" height="20" class="text-gray"></i></span>
                                @endif
                                <span class="font-weight-bold text-secondary font-14">{{ $session->title }}</span>
                            </div>
                            <div class="col-3 text-gray text-center text-center font-14">{{ dateTimeFormat($session->date, 'j M Y | H:i') }}</div>
                            <div class="col-2 text-gray text-center text-center font-14 d-none d-md-block">{{ convertMinutesToHourAndMinute($session->duration) }}</div>
                            <div class="col-3 d-flex justify-content-end">
                                @if($session->date < time())
                                    <button type="button" class="course-content-btns btn btn-sm btn-gray disabled flex-grow-1 disabled session-finished-toast">{{ trans('public.finished') }}</button>
                                @elseif(empty($user))
                                    <button type="button" class="course-content-btns btn btn-sm btn-gray disabled flex-grow-1 disabled not-login-toast">{{ trans('public.go_to_class') }}</button>
                                @elseif($hasBought)
                                    <a href="{{ $session->getJoinLink(true) }}" target="_blank" class="course-content-btns btn btn-sm btn-primary flex-grow-1">{{ trans('public.go_to_class') }}</a>
                                @else
                                    <button type="button" class="course-content-btns btn btn-sm btn-gray flex-grow-1 disabled not-access-toast">{{ trans('public.go_to_class') }}</button>
                                @endif
                            </div>
                        </div>
                        <div id="collapseSession{{ $session->id }}" aria-labelledby="session_{{ $session->id }}" class=" collapse" role="tabpanel">
                            <div class="panel-collapse">
                                <div class="text-gray">
                                    {!! nl2br(clean($session->description)) !!}
                                </div>

                                @if(!empty($user) and $hasBought)
                                    <div class="d-flex align-items-center mt-20">
                                        <label class="mb-0 mr-10 cursor-pointer font-weight-500" for="sessionReadToggle{{ $session->id }}">{{ trans('public.i_passed_this_lesson') }}</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" @if($session->date < time()) disabled @endif id="sessionReadToggle{{ $session->id }}" data-session-id="{{ $session->id }}" value="{{ $course->id }}" class="js-text-session-toggle custom-control-input" @if(!empty($session->learningStatus)) checked @endif>
                                            <label class="custom-control-label" for="sessionReadToggle{{ $session->id }}"></label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                {{-- @endforeach --}}
            </div>
        </div>
    </div>
</div>
