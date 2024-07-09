<li data-id="{{ !empty($textLesson) ? $textLesson->id :'' }}" class="accordion-row bg-white rounded-sm border border-gray300 mt-20 py-15 py-lg-30 px-10 px-lg-20">
    <div class="d-flex align-items-center justify-content-between" role="tab" id="textlesson_{{ !empty($textLesson) ? $textLesson->id :'record' }}">

        <div class="d-flex align-items-center" href="#collapseTextLesson{{ !empty($textLesson) ? $textLesson->id :'record' }}" aria-controls="collapseTextLesson{{ !empty($textLesson) ? $textLesson->id :'record' }}" data-parent="#chapterContentAccordion{{ !empty($chapter) ? $chapter->id :'' }}" role="button" data-toggle="collapse" aria-expanded="true">
            <span class="chapter-icon chapter-content-icon mr-10">
                <i data-feather="file-text" class=""></i>
            </span>

            <div class="font-weight-bold text-dark-blue d-block">{{ !empty($textLesson) ? $textLesson->title : trans('public.add_new_test_lesson') }}</div>
        </div>

        <div class="d-flex align-items-center">
            @if(!empty($textLesson) and $textLesson->status != \App\Models\WebinarChapter::$chapterActive)
                <span class="disabled-content-badge mr-10">{{ trans('public.disabled') }}</span>
            @endif

            <i data-feather="move" class="move-icon mr-10 cursor-pointer" height="20"></i>

            @if(!empty($textLesson))
                <a href="/panel/text-lesson/{{ $textLesson->id }}/delete" class="delete-action btn btn-sm btn-transparent text-gray">
                    <i data-feather="trash-2" class="mr-10 cursor-pointer" height="20"></i>
                </a>
                <a href="/panel/text-lesson/{{ $textLesson->id }}/duplicate" class="duplicate-action btn btn-sm btn-transparent text-gray">
                    <i data-feather="copy" class="mr-10 cursor-pointer" height="20"></i>
                </a>
            @endif

            <i class="collapse-chevron-icon" data-feather="chevron-down" height="20" href="#collapseTextLesson{{ !empty($textLesson) ? $textLesson->id :'record' }}" aria-controls="collapseTextLesson{{ !empty($textLesson) ? $textLesson->id :'record' }}" data-parent="#chapterContentAccordion{{ !empty($chapter) ? $chapter->id :'' }}" role="button" data-toggle="collapse" aria-expanded="true"></i>
        </div>




    </div>
    {{-- <div class="row matching-quiz-row pt-35 px-35">
        <div class="font-weight-bold text-dark-blue d-block">{{ trans('quiz.quizzes') }}</div>
        <div id="chapter_{{ !empty($chapter) ? $chapter->id :'' }}_optionDiv" class="w-100 quiz-col droppable" data-chapter-id="{{ !empty($chapter) ? $chapter->id :'' }}" data-lesson-id="{{ !empty($textLesson) ? $textLesson->id :'' }}">
            <div class="accordion-content-wrapper mt-15" id="quizzesAccordion" role="tablist" aria-multiselectable="true">
                @if(!empty($textLesson->quizzes) and count($textLesson->quizzes))
                    <ul class="col-12 h-100" style="width: 95% !important;">
                    @php $count = 0; @endphp
                    @foreach($textLesson->quizzes as $quizInfo)
                        <li id="quiz_{{$quizInfo->id}}_{{$count}}" style="z-index:100;" class='draggable draggable-quiz quiz_{{$quizInfo->id}}_option' data-chapter-id="{{ !empty($chapter) ? $chapter->id :'' }}" data-lesson-id="{{ !empty($textLesson) ? $textLesson->id :'' }}" data-quiz-id="{{$quizInfo->id}}" data-initialTarget="initial_droppable_{{$quizInfo->id}}">
                            @include('web.default.panel.webinar.create_includes.accordions.quiz',['webinar' => $webinar,'quizInfo' => $quizInfo])
                            @php $count++; @endphp
                        </li>
                    @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div> --}}
    <div id="collapseTextLesson{{ !empty($textLesson) ? $textLesson->id :'record' }}" aria-labelledby="text_lesson_{{ !empty($textLesson) ? $textLesson->id :'record' }}" class=" collapse @if(empty($textLesson)) show @endif" role="tabpanel">
        <div class="panel-collapse text-gray">
            {{-- @dd($textLesson) --}}
            {{-- <form class="js-content-form text_lesson-form" method="POST" data-action="/panel/text-lesson/{{ !empty($textLesson) ? $textLesson->id . '/update' : 'store' }}"> --}}
            <div class="js-content-form text_lesson-form" data-action="/panel/text-lesson/{{ !empty($textLesson) ? $textLesson->id . '/update' : 'store' }}">
                <input type="hidden" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][webinar_id]" value="{{ !empty($webinar) ? $webinar->id :'' }}">
                <input type="hidden" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][chapter_id]" value="{{ !empty($chapter) ? $chapter->id :'' }}" class="chapter-input">

                <div class="row">
                    <div class="col-12 col-lg-6">
                        @if(!empty(getGeneralSettings('content_translate')))
                            <div class="form-group">
                                <label class="input-label">{{ trans('auth.language') }}</label>
                                <select name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][locale]"
                                        class="form-control {{ !empty($textLesson) ? 'js-webinar-content-locale' : '' }}"
                                        data-webinar-id="{{ !empty($webinar) ? $webinar->id : '' }}"
                                        data-id="{{ !empty($textLesson) ? $textLesson->id : '' }}"
                                        data-relation="textLessons"
                                        data-fields="title,summary,content"
                                >
                                    @foreach($userLanguages as $lang => $language)
                                        <option value="{{ $lang }}" {{ (!empty($textLesson) and !empty($textLesson->locale)) ? (mb_strtolower($textLesson->locale) == mb_strtolower($lang) ? 'selected' : '') : ($locale == $lang ? 'selected' : '') }}>{{ $language }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][locale]" value="{{ $defaultLocale }}">
                        @endif

                        <div class="form-group">
                            <label class="input-label">{{ trans('public.title') }}</label>
                            <input type="text" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][title]" class="js-ajax-title form-control" value="{{ !empty($textLesson) ? $textLesson->title : '' }}" placeholder=""/>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label class="input-label">{{ trans('public.study_time') }} (Min)</label>
                            <input type="text" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][study_time]" class="js-ajax-study_time form-control" value="{{ !empty($textLesson) ? $textLesson->study_time : '' }}" placeholder="{{ trans('forms.maximum_50_characters') }}"/>
                            <div class="invalid-feedback"></div>
                        </div>
                        @if(!empty($textLesson))
                        @php
                        $lessonAudio = $textLesson->audioFile()->first()
                        @endphp
                        @if($lessonAudio)
                        <div class="form-group d-flex align-items-center">
                            <button type="button" data-audio="{{ asset('storage/audio_files/'.$lessonAudio->file_name) }}" class="btn btn-sm rounded btn-primary audioPlayBtn" title="{{ trans('public.play_audio') }}">
                                <i class="fa fa-play-circle"></i>
                            </button>
                            <button type="button" class="btn btn-sm rounded btn-danger closeAudio" title="{{ trans('public.play_audio') }}">
                                <i class="fa fa-times"></i>
                            </button>
                            <a href="{{ route("panel.delete.audio", ["id" => $lessonAudio->id]) }}" class="btn-danger btn btn-sm rounded ml-1 text-white" title="{{ trans('public.delete_audio') }}"> <i class="fa fa-trash"></i> </a>
                        </div>
                        @else
                        <div class="form-group">
                            <x-course.text-lesson.attach-audio-component/>
                        </div>
                        @endif
                        @endif

                        {{-- <div class="form-group">
                            <label class="input-label">{{ trans('public.drip_feed') }}</label>
                            <select class="custom-select selectFeed" id="selectFeed" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][drip_feed]">
                                <option value="0">{{ trans('public.no') }}</option>
                                <option value="1" {{ (!empty($textLesson->drip_feed) && (int) $textLesson->drip_feed == 1) ? ' selected="selected" ' : ''}}>{{ trans('public.yes') }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group feedDate" id="feedDate">
                            <label class="input-label">{{ trans('public.drip_feed_date') }}</label>
                            <input type="number" required id="dateFeed" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][show_after_days]" class="form-control dateFeed" value="{{ (!empty($textLesson) && !empty($textLesson->show_after_days) && (int) $textLesson->show_after_days > 0) ? $textLesson->show_after_days : '' }}" />
                            <div class="invalid-feedback"></div>
                        </div> --}}
                        {{-- <div class="form-group">

                            <label class="input-label">{{ trans('public.drip_feed') }}</label>
                            <select class="custom-select selectFeed" id="selectFeed" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][drip_feed]">
                                <option value="0" {{(!empty($textLesson) && $textLesson->drip_feed == 0) ? 'selected' : ''}}>{{ trans('public.no') }}</option>
                                <option value="1" {{( !empty($textLesson) && $textLesson->drip_feed == 1) ? 'selected' : ''}}>{{ trans('public.yes') }}</option>
                            </select>
                            <div class="invalid-feedback"></div>

                        </div>
                        <div class="feedDate" id="feedDate" style="@if (!empty($textLesson) && ($textLesson->drip_feed !== 0)) display:block; @else display: none; @endif">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.drip_feed_date') }}</label>
                                <input type="number" required id="dateFeed" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][show_after_days]" class="form-control dateFeed" value="{{ !empty($textLesson) ? $textLesson->show_after_days : '' }}"/>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div> --}}
                        <div class="form-group">
                            <label class="input-label d-block">{{ trans('public.drip_feed') }}</label>
                            <select name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][drip_feed]" class="form-control selectFeed">
                                {{-- <option value="{{ isset($quiz->drip_feed) ? $quiz->drip_feed : 0 }}" disabled selected> {{ (isset($quiz->drip_feed) && $quiz->drip_feed == 1) ? "True" : "False" }} </option> --}}
                                <option value="0"> {{ trans('public.drip_feed_false') }}  </option>
                                <option value="1" {{ (isset($textLesson->drip_feed) && $textLesson->drip_feed == 1) ? 'selected' : ''}}> {{ trans('public.drip_feed_true') }} </option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group feedDate" style="{{ (isset($textLesson->drip_feed) && $textLesson->drip_feed == 1) ? 'visibility:visible; display:block;' : 'visibility:hidden; display:none;'}} ">
                            <label class="input-label d-block">{{ trans('public.show_after_days') }}</label>
                            <input type="number" value="{{ !empty($textLesson) ? $textLesson->show_after_days : '' }}" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][show_after_days]" class="form-control feedDateField">
                        </div>
                        <div class="form-group">
                            <label class="input-label">{{ trans('public.image') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="button" class="input-group-text panel-file-manager" data-input="image{{ !empty($textLesson) ? $textLesson->id :'record' }}" data-preview="holder">
                                        <i data-feather="arrow-up" width="18" height="18" class="text-white"></i>
                                    </button>
                                </div>
                                <input type="text" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][image]" id="image{{ !empty($textLesson) ? $textLesson->id :'record' }}" value="{{ !empty($textLesson) ? $textLesson->image : '' }}" class="js-ajax-image form-control"/>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="input-label">{{ trans('public.accessibility') }}</label>

                            <div class="d-flex align-items-center js-ajax-accessibility">
                                <div class="custom-control custom-radio">
                                    <input type="radio" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][accessibility]" value="free" @if(empty($textLesson) or (!empty($textLesson) and $textLesson->accessibility == 'free')) checked="checked" @endif id="accessibilityRadio1_{{ !empty($textLesson) ? $textLesson->id : 'record' }}" class="custom-control-input">
                                    <label class="custom-control-label font-14 cursor-pointer" for="accessibilityRadio1_{{ !empty($textLesson) ? $textLesson->id : 'record' }}">{{ trans('public.free') }}</label>
                                </div>

                                <div class="custom-control custom-radio ml-15">
                                    <input type="radio" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][accessibility]" value="paid" @if(!empty($textLesson) and $textLesson->accessibility == 'paid') checked="checked" @endif id="accessibilityRadio2_{{ !empty($textLesson) ? $textLesson->id : 'record' }}" class="custom-control-input">
                                    <label class="custom-control-label font-14 cursor-pointer" for="accessibilityRadio2_{{ !empty($textLesson) ? $textLesson->id : 'record' }}">{{ trans('public.paid') }}</label>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label class="input-label d-block">{{ trans('public.attachments') }}</label>

                            <select class="js-ajax-attachments @if(empty($textLesson)) form-control @endif attachments-select2" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][attachments]" data-placeholder="{{ trans('public.choose_attachments') }}">
                                <option></option>

                                @if(!empty($webinar->files) and count($webinar->files))
                                    @foreach($webinar->files as $filesInfo)
                                        @if($filesInfo->downloadable)
                                            <option value="{{ $filesInfo->id }}" @if(!empty($textLesson) and in_array($filesInfo->id,$textLesson->attachments->pluck('file_id')->toArray())) selected @endif>{{ $filesInfo->title }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="form-group">
                            <label class="input-label">{{ trans('public.summary') }}</label>
                            <textarea name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][summary]" class="js-ajax-summary form-control" rows="6">{{ !empty($textLesson) ? $textLesson->summary : '' }}</textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="input-label">{{ trans('public.content') }}</label>
                            <div class="content-summernote js-ajax-file_path">
                                <textarea class="js-content-summernote form-control {{ !empty($textLesson) ? 'js-content-'.$textLesson->id : '' }}">{{ !empty($textLesson) ? $textLesson->content : '' }}</textarea>
                                <textarea name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][content]" class="js-hidden-content-summernote {{ !empty($textLesson) ? 'js-hidden-content-'.$textLesson->id : '' }} d-none">{{ !empty($textLesson) ? $textLesson->content : '' }}</textarea>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="form-group mt-20">
                            <div class="d-flex align-items-center">
                                <label class="cursor-pointer mt-5 input-label" for="textLessonStatusSwitch{{ !empty($textLesson) ? $textLesson->id : '_record' }}">{{ trans('public.active') }}</label>
                                <div class="custom-control ml-10 custom-switch">
                                    <input type="checkbox" name="ajax[{{ !empty($textLesson) ? $textLesson->id : 'new' }}][status]" class="custom-control-input" id="textLessonStatusSwitch{{ !empty($textLesson) ? $textLesson->id : '_record' }}" {{ (empty($textLesson) or $textLesson->status == \App\Models\TextLesson::$Active) ? 'checked' : ''  }}>
                                    <label class="custom-control-label" for="textLessonStatusSwitch{{ !empty($textLesson) ? $textLesson->id : '_record' }}"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-30 d-flex align-items-center">
                    <button type="button" class="js-save-text_lesson btn btn-sm btn-primary">{{ trans('public.save') }}</button>

                    @if(empty($textLesson))
                        <button type="button" class="btn btn-sm btn-danger ml-10 cancel-accordion">{{ trans('public.close') }}</button>
                    @endif
                </div>
            </div>
            {{-- </form> --}}
        </div>
    </div>
</li>


