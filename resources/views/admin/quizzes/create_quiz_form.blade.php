@php
    $quizId = null;
    if (isset($quiz->id)) {
        $quizId = $quiz->id;
    }
@endphp
<div data-action="{{ getAdminPanelUrl() }}/quizzes/{{ !empty($quizId) ?  $quizId . '/update' : 'store' }}"
    class="js-content-form quiz-form webinar-form">
    {{ csrf_field() }}
    <section>
        <div class="row">
            <div class="col-12 col-md-4">


                <div class="d-flex align-items-center justify-content-between">
                    <div class="">
                        <h2 class="section-title">
                            {{ !empty($quiz) ? trans('public.edit') . ' (' . $quiz->title . ')' : trans('quiz.new_quiz') }}
                        </h2>

                        @if (!empty($creator))
                            <p>{{ trans('admin/main.instructor') }}: {{ $creator->full_name }}</p>
                        @endif
                    </div>
                </div>

                @if (!empty(getGeneralSettings('content_translate')))
                    <div class="form-group">
                        <label class="input-label">{{ trans('auth.language') }}</label>
                        <select name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][locale]"
                            class="form-control {{ !empty($quiz) ? 'js-edit-content-locale' : '' }}">
                            @foreach ($userLanguages as $lang => $language)
                                <option value="{{ $lang }}" @if (mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>
                                    {{ $language }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                @else
                    <input type="hidden" name="[{{ !empty($quizId) ?  $quizId : 'new' }}][locale]"
                        value="{{ getDefaultLocale() }}">
                @endif
                @if (empty($selectedWebinar))
                    @if (!empty($webinars) and count($webinars))
                        <div class="form-group mt-3">
                            <label class="input-label">{{ trans('panel.webinar') }}</label>
                            <select id="webinarSelectionInQuizForm"
                                name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][webinar_id]"
                                class="js-ajax-webinar_id custom-select">
                                <option {{ !empty($quiz) ? 'disabled' : 'selected disabled' }} value="">
                                    {{ trans('panel.choose_webinar') }}</option>
                                @foreach ($webinars as $webinar)
                                    <option value="{{ $webinar->id }}"
                                        {{ (!empty($quiz) and $quiz->webinar_id == $webinar->id) ? 'selected' : '' }}>
                                        {{ $webinar->title }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    @else
                        <div class="form-group">
                            <label class="input-label d-block">{{ trans('admin/main.webinar') }}</label>
                            <select name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][webinar_id]"
                                class="js-ajax-webinar_id form-control search-webinar-select2"
                                data-placeholder="{{ trans('admin/main.search_webinar') }}">

                            </select>

                            <div class="invalid-feedback"></div>
                        </div>
                    @endif
                @else
                    <input type="hidden" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][webinar_id]"
                        value="{{ $selectedWebinar->id }}">
                @endif

                @if (!empty($quiz))
                    <div class="form-group">
                        <label class="input-label">{{ trans('public.chapter') }}</label>
                        <select id="sectionChapterInQuizForm"
                            name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][chapter_id]"
                            class="form-control sectionChapterInQuizForm">
                            @foreach ($chapters as $ch)
                                <option value="{{ $ch->id }}"
                                    {{ $quiz->chapter_id == $ch->id ? 'selected' : '' }}>{{ $ch->title }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label class="input-label">Text Lessons</label>
                        <select id="sectionLessonInQuizForm"
                            name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][text_lesson_id]" class="form-control">
                            <option value="0" {{ !$quiz->text_lesson_id ? 'selected' : '' }}>-- No TextLesson
                                Association --</option>
                            @forelse ($quiz->chapter->textLessons as $chapterTextLesson)
                                <option value="{{ $chapterTextLesson->id }}"
                                    {{ $chapterTextLesson->id == $quiz->text_lesson_id ? 'selected' : '' }}>
                                    {{ $chapterTextLesson->title }}</option>
                            @empty
                            @endforelse
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                @elseif (Request::segment(2) == 'webinars' && Request::segment(4) == 'edit' && empty($quiz))
                    <input type="hidden" name="ajax[new][chapter_id]" value="" class="chapter-input">
                    <div class="form-group">
                        <label class="input-label">Text Lessons</label>
                        <select id="selectedSelectionTextlessonInQuizForm" name="ajax[new][text_lesson_id]"
                            class="js-ajax-chapter_id form-control">
                            <option value="" disabled selected>Select Text Lesson</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                @else
                    <div class="form-group">
                        <label class="input-label">{{ trans('public.chapter') }}</label>
                        <select id="chapterSelectionInQuizForm"
                            name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][chapter_id]"
                            class="js-ajax-chapter_id form-control">
                            <option value="" disabled selected>Select {{ trans('public.chapter') }}</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label class="input-label">Text Lesson</label>
                        <select id="textLessonSelectionInQuizForm"
                            name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][text_lesson_id]"
                            class="js-ajax-chapter_id form-control">
                            <option value="" disabled selected>Select Text Lesson</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="input-label">{{ trans('quiz.quiz_title') }}</label>
                    <input type="text" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][title]"
                        value="{{ !empty($quiz) ? $quiz->title : old('title') }}" class="js-ajax-title form-control "
                        placeholder="" />
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label class="input-label">{{ trans('public.time') }} <span
                            class="braces">({{ trans('public.minutes') }})</span></label>
                    <input type="text" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][time]"
                        value="{{ !empty($quiz) ? $quiz->time : old('time') }}" class="js-ajax-time form-control "
                        placeholder="{{ trans('forms.empty_means_unlimited') }}" />
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label class="input-label">{{ trans('quiz.number_of_attemps') }}</label>
                    <input type="text" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][attempt]"
                        value="{{ !empty($quiz) ? $quiz->attempt : old('attempt') }}"
                        class="js-ajax-attempt form-control "
                        placeholder="{{ trans('forms.empty_means_unlimited') }}" />
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label class="input-label">{{ trans('quiz.pass_mark') }}</label>
                    <input type="text" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][pass_mark]"
                        value="{{ !empty($quiz) ? $quiz->pass_mark : old('pass_mark') }}"
                        class="js-ajax-pass_mark form-control @error('pass_mark')  is-invalid @enderror"
                        placeholder="" />
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label class="input-label">{{ trans('update.expiry_days') }}</label>
                    <input type="number" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][expiry_days]"
                        value="{{ !empty($quiz) ? $quiz->expiry_days : old('expiry_days') }}"
                        class="js-ajax-expiry_days form-control @error('expiry_days')  is-invalid @enderror"
                        min="0" />
                    <div class="invalid-feedback"></div>

                    <p class="font-12 text-gray mt-1">{{ trans('update.quiz_expiry_days_hint') }}</p>
                </div>
                <div class="form-group">

                    <label class="input-label">{{ trans('public.drip_feed') }}</label>
                    <select class="custom-select selectFeed" id="selectFeed" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][drip_feed]">
                        <option value="0" {{(!empty($quiz) && $quiz->drip_feed == 0) ? 'selected' : ''}}>{{ trans('public.no') }}</option>
                        <option value="1" {{( !empty($quiz) && $quiz->drip_feed == 1) ? 'selected' : ''}}>{{ trans('public.yes') }}</option>
                    </select>
                    <div class="invalid-feedback"></div>

                </div>
                <div class="feedDate" id="feedDate" style="@if (!empty($quizId) && ($quiz->drip_feed !== 0)) display:block; @else display: none; @endif">
                    <div class="form-group">
                        <label class="input-label">{{ trans('public.drip_feed_date') }}</label>
                        <input type="number" required id="dateFeed" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][show_after_days]" class="form-control dateFeed" value="{{ !empty($quiz) ? $quiz->show_after_days : '' }}"/>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                @if (!empty($quiz))
                    <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                        <label class="cursor-pointer input-label"
                            for="displayLimitedQuestionsSwitch{{ $quizId }}">{{ trans('update.display_limited_questions') }}</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][display_limited_questions]"
                                class="js-ajax-display_limited_questions custom-control-input"
                                id="displayLimitedQuestionsSwitch{{ $quizId }}"
                                {{ $quiz->display_limited_questions ? 'checked' : '' }}>
                            <label class="custom-control-label"
                                for="displayLimitedQuestionsSwitch{{ $quizId }}"></label>
                        </div>
                    </div>

                    <div
                        class="form-group js-display-limited-questions-count-field {{ $quiz->display_limited_questions ? '' : 'd-none' }}">
                        <label class="input-label">{{ trans('update.number_of_questions') }}
                            ({{ trans('update.total_questions') }}: {{ $quiz->quizQuestions->count() }})</label>
                        <input type="number"
                            name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][display_number_of_questions]"
                            value="{{ $quiz->display_number_of_questions }}"
                            class="js-ajax-display_number_of_questions form-control " min="1" />
                        <div class="invalid-feedback"></div>
                    </div>
                @endif

                <div class="form-group mt-20 d-flex align-items-center justify-content-between">
                    <label class="cursor-pointer input-label"
                        for="displayQuestionsRandomlySwitch{{ !empty($quizId) ? $quizId : 'record' }}">{{ trans('update.display_questions_randomly') }}</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                            name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][display_questions_randomly]"
                            class="js-ajax-display_questions_randomly custom-control-input"
                            id="displayQuestionsRandomlySwitch{{ !empty($quizId) ? $quizId : 'record' }}"
                            {{ !empty($quiz) && $quiz->display_questions_randomly ? 'checked' : '' }}>
                        <label class="custom-control-label"
                            for="displayQuestionsRandomlySwitch{{ !empty($quizId) ? $quizId : 'record' }}"></label>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                    <label class="cursor-pointer"
                        for="certificateSwitch{{ !empty($quizId) ? $quizId : 'record' }}">{{ trans('quiz.certificate_included') }}</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][certificate]"
                            class="custom-control-input"
                            id="certificateSwitch{{ !empty($quizId) ? $quizId : 'record' }}"
                            {{ !empty($quiz) && $quiz->certificate ? 'checked' : '' }}>
                        <label class="custom-control-label"
                            for="certificateSwitch{{ !empty($quizId) ? $quizId : 'record' }}"></label>
                    </div>
                </div>

                <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                    <label class="cursor-pointer"
                        for="statusSwitch{{ !empty($quizId) ? $quizId : 'record' }}">{{ trans('quiz.active_quiz') }}</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][status]"
                            class="custom-control-input" id="statusSwitch{{ !empty($quizId) ? $quizId : 'record' }}"
                            {{ !empty($quiz) && $quiz->status ? 'checked' : '' }}>
                        <label class="custom-control-label" for="statusSwitch{{ !empty($quizId) ? $quizId : 'record' }}"></label>
                    </div>
                </div>

            </div>
        </div>
    </section>
    @if (!empty($quiz))
        <section class="mt-5">
            <div class="d-flex justify-content-between align-items-center pb-20">
                <h2 class="section-title after-line">{{ trans('public.questions') }}</h2>
                <button id="add_infoText_question" type="button"
                    class="btn btn-primary btn-sm ml-2 mt-3" data_quiz_id = "{{$quizId}}">{{ trans('quiz.add_information_text') }}</button>
                <button id="add_multiple_question" type="button"
                    class="btn btn-primary btn-sm ml-2 mt-3" data_quiz_id = "{{$quizId}}">{{ trans('quiz.add_multiple_choice') }}</button>
                <button id="add_descriptive_question" type="button"
                    class="btn btn-primary btn-sm ml-2 mt-3" data_quiz_id = "{{$quizId}}">{{ trans('quiz.add_descriptive') }}</button>
                <button id="add_fillInBlank_question" type="button"
                    class="btn btn-primary btn-sm ml-2 mt-3" data_quiz_id = "{{$quizId}}">{{ trans('quiz.add_fillInBlank') }}</button>
                <div class="btn-group">
                    <button type="button" id="dropdownMatchingListButton" data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"class="quiz-form-btn btn btn-primary dropdown-toggle btn-sm ml-2 mt-3">{{ trans('quiz.add_matchingList') }}</button>
                    <div class="dropdown-menu " aria-labelledby="dropdownMatchingListButton">
                        <button id="add_matchingListText_question" data_quiz_id = "{{$quizId}}" type="button"
                            class="dropdown-item quiz-form-btn btn btn-primary btn-sm ml-2 mt-3">{{ trans('quiz.add_matchingListText') }}</button>
                        <button id="add_matchingListImage_question" data_quiz_id = "{{$quizId}}"
                            type="button"
                            class="dropdown-item quiz-form-btn btn btn-primary btn-sm ml-2 mt-3">{{ trans('quiz.add_matchingListImage') }}</button>
                    </div>
                </div>
                <button id="add_fileUpload_question" data_quiz_id = "{{$quizId}}" type="button"
                    class="btn btn-primary btn-sm ml-2 mt-3">{{ trans('quiz.add_fileUpload') }}</button>

            </div>
            <div class="sortableQuizQuestions">
                @if ($quizQuestions)
                    @foreach ($quizQuestions as $question)
                        <div data-quest-id="{{ $question->id }}" class="quiz-question-card all-scroll d-flex align-items-center mt-4">
                            <div class="flex-grow-1">
                                <h4 class="question-title">{!! str_ireplace('{blank}', '_', $question->title) !!}</h4>
                                <div class="font-12 mt-3 question-infos">
                                    <span>
                                        @if ($question->type === App\Models\QuizzesQuestion::$informationText)
                                            {{ trans('quiz.information_text') }}
                                        @elseif ($question->type === App\Models\QuizzesQuestion::$descriptive)
                                        {{ trans('descriptive') }}
                                        @elseif ($question->type === App\Models\QuizzesQuestion::$multiple)
                                            {{ trans('quiz.multiple_choice') }}
                                        @elseif($question->type === App\Models\QuizzesQuestion::$fillInBlank)
                                            {{ trans('quiz.fillInBlank') }}
                                        @else
                                            {{ trans('quiz.descriptive') }}
                                        @endif
                                        | {{ trans('quiz.grade') }}: {{ $question->grade }}
                                    </span>
                                </div>
                            </div>
                            <div class="btn-group dropdown table-actions">
                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu text-left">
                                    <button type="button" data-question-id="{{ $question->id }}"
                                        class="edit_question btn btn-sm btn-transparent">{{ trans('public.edit') }}</button>
                                    @include('admin.includes.delete_button', [
                                        'url' => '/admin/quizzes-questions/' . $question->id . '/delete',
                                        'btnClass' => 'btn-sm btn-transparent',
                                        'btnText' => trans('public.delete'),
                                    ])
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </section>
    @endif

    <input type="hidden" name="ajax[{{ !empty($quizId) ? $quizId : 'new' }}][is_webinar_page]"
        value="@if (!empty($inWebinarPage) and $inWebinarPage) 1 @else 0 @endif">

    <div class="mt-20 mb-20">
        <button type="button"
            class="js-submit-quiz-form btn btn-sm btn-primary">{{ !empty($quiz) ? trans('public.save_change') : trans('public.create') }}</button>

        @if (empty($quiz) and !empty($inWebinarPage))
            <button type="button"
                class="btn btn-sm btn-danger ml-10 cancel-accordion">{{ trans('public.close') }}</button>
        @endif
    </div>
</div>

@if (!empty($quiz))
    @include('admin.quizzes.modals.information_text', ['quiz' => $quiz, 'locale' => $locale ?? null])
    @include('admin.quizzes.modals.multiple_question', ['quiz' => $quiz, 'locale' => $locale ?? null])
    @include('admin.quizzes.modals.descriptive_question', ['quiz' => $quiz, 'locale' => $locale ?? null])
    @include('admin.quizzes.modals.fill_in_the_blank_question', ['quiz' => $quiz, 'locale' => $locale ?? null])
    @include('admin.quizzes.modals.file_upload_question', ['quiz' => $quiz, 'locale' => $locale ?? null])
    @include('admin.quizzes.modals.matching_list_text_question', ['quiz' => $quiz, 'locale' => $locale ?? null])
    @include('admin.quizzes.modals.matching_list_image_question', ['quiz' => $quiz, 'locale' => $locale ?? null])
@endif
@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
    <style>
        .textareaField{
            width: 100% !important;
            resize: both !important;
        }
        .all-scroll {cursor: all-scroll;}
    </style>
@endpush
@push('scripts_bottom')
    {{-- <script src="/assets/default/js/admin/quiz.min.js"></script> --}}
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
    <script src="/assets/default/js/jquery_ui.js"></script>

    <script>

        function updateQuizQuestionRelation(data)
        {
            let action = "{{ route('admin.questions.sort') }}"
            console.log(action);
            $.post(action, data, function (result) {
                // no need to reload page
                // location.reload();
            }).fail(err => {
                console.log(err.responseJSON);
            });
        }

        function getQuestionIds() {
            let questionIds = '';
            $( ".quiz-question-card" ).each(function( index ) {
                questionIds += $(this).attr("data-quest-id") + ","
            });
            return {"data" : questionIds}
        }

        $( function() {
            $( ".sortableQuizQuestions" ).sortable({
                update: function( event, ui ) {
                    updateQuizQuestionRelation(getQuestionIds())
                }
            });
        });
    </script>
@endpush
