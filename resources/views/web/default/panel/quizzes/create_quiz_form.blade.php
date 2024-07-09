{{-- <div class=""> --}}
    <div data-action="{{ !empty($quiz) ? '/panel/quizzes/'. $quiz->id .'/update' : '/panel/quizzes/store' }}" class="js-content-form quiz-form webinar-form">
        <section>
            <h2 class="section-title after-line">{{ trans('quiz.new_quiz') }}</h2>

            <div class="row">
                <div class="col-12 col-md-4">

                    @if(!empty(getGeneralSettings('content_translate')))
                        <div class="form-group mt-25">
                            <label class="input-label">{{ trans('auth.language') }}</label>
                            <select name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][locale]"
                                    class="form-control {{ !empty($quiz) ? 'js-webinar-content-locale' : '' }}"
                                    data-webinar-id="{{ !empty($quiz) ? $quiz->webinar_id : '' }}"
                                    data-id="{{ !empty($quiz) ? $quiz->id : '' }}"
                                    data-relation="quizzes"
                                    data-fields="title"
                            >
                                @foreach($userLanguages as $lang => $language)
                                    <option value="{{ $lang }}" {{ (!empty($quiz) and !empty($quiz->locale)) ? (mb_strtolower($quiz->locale) == mb_strtolower($lang) ? 'selected' : '') : ($locale == $lang ? 'selected' : '') }}>{{ $language }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][locale]" value="{{ $defaultLocale }}">
                    @endif
                    @if(empty($selectedWebinar))
                        <div class="form-group mt-25">
                            <label class="input-label">{{ trans('panel.webinar') }}</label>
                            <select name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][webinar_id]" class="js-ajax-webinar_id custom-select">
                                <option {{ !empty($quiz) ? 'disabled' : 'selected disabled' }} value="">{{ trans('panel.choose_webinar') }}</option>
                                @foreach($webinars as $webinar)
                                    <option value="{{ $webinar->id }}" {{  (!empty($quiz) and $quiz->webinar_id == $webinar->id) ? 'selected' : '' }}>{{ $webinar->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][webinar_id]" value="{{ $selectedWebinar->id }}">
                    @endif
{{--
                    <div class="form-group mt-25">
                        <label class="input-label">{{ trans('public.chapter') }}</label>

                        <select name="ajax[chapter_id]" class="js-ajax-chapter_id custom-select">
                            <option value="">{{ trans('public.no_chapter') }}</option>

                            @if(!empty($chapters) and count($chapters))
                                @foreach($chapters as $chapter)
                                    <option value="{{ $chapter->id }}" {{  (!empty($quiz) and $quiz->chapter_id == $chapter->id) ? 'selected' : '' }}>{{ $chapter->title }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group mt-25">
                        <label class="input-label">{{ trans('public.text_lesson') }}</label>

                        <select name="ajax[lesson_id]" class="js-ajax-lesson_id custom-select">
                            <option value="">{{ trans('public.no_lesson') }}</option>
                            @if(!empty($lessons) and count($lessons))
                                @foreach($lessons as $lesson)
                                    <option value="{{ $lesson->id }}" {{  (!empty($quiz) and $quiz->text_lesson_id == $lesson->id) ? 'selected' : '' }}>{{ $lesson->title }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div> --}}
                    <div class="form-group">
                        <label class="input-label">{{ trans('public.chapter') }}</label>
                        <select id="sectionChapterInQuizFormss"
                            name="ajax[{{ !empty($quiz) ? $quiz->id : 'new' }}][chapter_id]"
                            class="form-control sectionChapterInQuizForm">
                            @if (empty($quiz))
                                <option value="select">select</option>
                                @foreach ($chapters as $ch)
                                <option value="{{ $ch->id }}">{{ $ch->title }}</option>
                                @endforeach
                            @else
                                @foreach ($chapters as $ch)
                                    <option value="{{ $ch->id }}" {{$ch->id == $quiz->chapter->id}}>{{ $ch->title }}</option>
                                @endforeach
                            @endif

                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label class="input-label">Text Lessons</label>
                        <select id="selectedSelectionTextlessonInQuizForm" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][text_lesson_id]"
                            class="js-ajax-chapter_id form-control">
                            @if (empty($quiz))
                                <option value="" disabled selected>Select Text Lesson</option>
                            @else
                            @foreach ($quiz->chapter->textLessons as $lesson)
                            <option value="{{$lesson->id}}" {{($lesson->id == $quiz->text_lesson_id) ? 'selected' : ''}}>{{$quiz->textLesson->title}}</option>
                            @endforeach
                            {{-- <option value="{{$quiz->text_lesson_id}}" selected>{{$quiz->textLesson->title}}</option> --}}
                            @endif
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group @if(!empty($selectedWebinar)) mt-25 @endif">
                        <label class="input-label">{{ trans('quiz.quiz_title') }}</label>
                        <input type="text" value="{{ !empty($quiz) ? $quiz->title : old('title') }}" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][title]" class="js-ajax-title form-control @error('title')  is-invalid @enderror" placeholder=""/>
                        <div class="invalid-feedback">
                            @error('title')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <x-drip-feed-quiz :quiz=$quiz??null></x-drip-feed-quiz>

                    <div class="form-group">
                        <label class="input-label">{{ trans('public.time') }} <span class="braces">({{ trans('public.minutes') }})</span></label>
                        <input type="text" value="{{ !empty($quiz) ? $quiz->time : old('time') }}" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][time]" class="js-ajax-time form-control @error('time')  is-invalid @enderror" placeholder="{{ trans('forms.empty_means_unlimited') }}"/>
                        <div class="invalid-feedback">
                            @error('time')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="input-label">{{ trans('quiz.number_of_attemps') }}</label>
                        <input type="text" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][attempt]" value="{{ !empty($quiz) ? $quiz->attempt : old('attempt') }}" class="js-ajax-attempt form-control @error('attempt')  is-invalid @enderror" placeholder="{{ trans('forms.empty_means_unlimited') }}"/>
                        <div class="invalid-feedback">
                            @error('attempt')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="input-label">{{ trans('quiz.pass_mark') }}</label>
                        <input type="text" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][pass_mark]" value="{{ !empty($quiz) ? $quiz->pass_mark : old('pass_mark') }}" class="js-ajax-pass_mark form-control @error('pass_mark')  is-invalid @enderror" placeholder=""/>
                        <div class="invalid-feedback">
                            @error('pass_mark')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mt-20 d-flex align-items-center">
                        <label class="cursor-pointer mt-5 input-label" for="certificateSwitch{{ $quiz ?? '' }}">{{ trans('quiz.certificate_included') }}</label>
                        <div class="custom-control ml-10 custom-switch">
                            <input type="checkbox" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][certificate]" class="js-ajax-certificate custom-control-input" id="certificateSwitch{{ $quiz ?? '' }}" {{ !empty($quiz) && $quiz->certificate ? 'checked' : ''}}>
                            <label class="custom-control-label" for="certificateSwitch{{ $quiz ?? '' }}"></label>
                        </div>
                    </div>

                    <div class="form-group mt-20 d-flex align-items-center">
                        <label class="cursor-pointer mt-5 input-label" for="statusSwitch{{ $quiz ?? '' }}">{{ trans('quiz.active_quiz') }}</label>
                        <div class="custom-control ml-10 custom-switch">
                            <input type="checkbox" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][status]" class="js-ajax-status custom-control-input" id="statusSwitch{{ $quiz ?? '' }}" {{ !empty($quiz) && $quiz->status ? 'checked' : ''}}>
                            <label class="custom-control-label" for="statusSwitch{{ $quiz ?? '' }}"></label>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        @if(!empty($quiz))
            <section class="mt-30">
                <div class="d-block d-md-flex justify-content-between align-items-center pb-20">
                    <h2 class="section-title after-line">{{ trans('public.questions') }}</h2>

                    <div class="d-flex align-items-center mt-20 mt-md-0">
                        <button id="add_infoText_question" type="button" data-quiz-id="{{ $quiz->id }}"  class="btn btn-primary btn-sm ml-3">{{ trans('quiz.add_information_text') }}</button>
                        <button id="add_multiple_question" data-quiz-id="{{ $quiz->id }}" type="button" class="btn btn-primary btn-sm ml-3">{{ trans('quiz.add_multiple_choice') }}</button>
                        <button id="add_descriptive_question" data-quiz-id="{{ $quiz->id }}" type="button" class="btn btn-primary btn-sm ml-3">{{ trans('quiz.add_descriptive') }}</button>
                        <div class="btn-group">
                            <button type="button" id="dropdownMatchingListButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"class="btn btn-primary dropdown-toggle btn-sm ml-3">{{ trans('quiz.add_matchingList') }}</button>
                            <div class="dropdown-menu " aria-labelledby="dropdownMatchingListButton">
                                <button id="add_matchingListText_question" data-quiz-id="{{ $quiz->id }}" type="button" class="dropdown-item btn btn-primary btn-sm ml-3">{{ trans('quiz.add_matchingListText') }}</button>
                                <button id="add_matchingListImage_question" data-quiz-id="{{ $quiz->id }}" type="button" class="dropdown-item btn btn-primary btn-sm ml-3">{{ trans('quiz.add_matchingListImage') }}</button>
                            </div>
                        </div>
                        <button id="add_fillInBlank_question" data-quiz-id="{{ $quiz->id }}" type="button" class="btn btn-primary btn-sm ml-3">{{ trans('quiz.add_fillInBlank') }}</button>
                        {{-- <!--<button id="add_fileUpload_question" data-quiz-id="{{ $quiz->id }}" type="button" class="btn btn-primary btn-sm ml-3">{{ trans('quiz.add_fileUpload') }}</button>--> --}}
                         <button id="add_fileUpload" data-quiz-id="{{ $quiz->id }}" type="button" class="btn btn-primary btn-sm ml-3">{{ trans('quiz.add_fileUpload') }}</button>
                    </div>
                </div>
                <div class="sortableQuizQuestions">
                    @if($quizQuestions)
                        @foreach($quizQuestions as $question)
                            <div data-quest-id="{{ $question->id }}" class="quiz-question-card all-scroll d-flex align-items-center mt-20">
                                <div class="flex-grow-1">
                                    <h4 class="question-title"> {!! $question->title !!}</h4>
                                    <div class="font-12 mt-5 question-infos">
                                        <span>
                                            @if ( $question->type === App\Models\QuizzesQuestion::$multiple )
                                                {{ trans('quiz.multiple_choice') }}
                                            @elseif( $question->type === App\Models\QuizzesQuestion::$fillInBlank )
                                                {{ trans('quiz.fillInBlank') }}
                                            @elseif( $question->type === App\Models\QuizzesQuestion::$matchingListText )
                                                {{ trans('quiz.matchingListText') }}
                                            @elseif( $question->type === App\Models\QuizzesQuestion::$matchingListImage )
                                                {{ trans('quiz.matchingListImage') }}
                                            @elseif( $question->type === App\Models\QuizzesQuestion::$fileUpload )
                                                {{ trans('quiz.fileUpload') }}
                                            @elseif( $question->type === App\Models\QuizzesQuestion::$informationText )
                                                {{ trans("quiz.information_text") }}
                                            @else
                                                {{ trans('quiz.descriptive') }}
                                            @endif
                                            {{ trans('quiz.grade') }}: {{ $question->grade }}</span>
                                    </div>
                                </div>

                                <div class="btn-group dropdown table-actions">
                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button type="button" data-question-id="{{ $question->id }}" class="edit_question btn btn-sm btn-transparent d-block">{{ trans('public.edit') }}</button>
                                        <a href="/panel/quizzes-questions/{{ $question->id }}/delete" class="delete-action btn btn-sm btn-transparent d-block">{{ trans('public.delete') }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

            </section>
        @endif

        <input type="hidden" name="ajax[{{ !isset($quiz) ? 'new' : $quiz->id }}][is_webinar_page]" value="@if(!empty($inWebinarPage) and $inWebinarPage) 1 @else 0 @endif">

        <div class="mt-20 mb-20">
            @if(!empty($inWebinarPage) and $inWebinarPage)
                <button type="button" class="js-save-quiz btn btn-sm btn-primary">{{ !empty($quiz) ? trans('public.save_change') : trans('public.create') }}</button>
            @else
                <button type="button" class="js-save-quiz btn btn-sm btn-primary">{{ !empty($quiz) ? trans('public.save_change') : trans('public.create') }}</button>
            @endif
            <button type="button" class="btn btn-sm btn-danger ml-10 cancel-accordion">{{ trans('public.close') }}</button>
        </div>
    </div>
{{-- </div> --}}

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

<!-- Modal -->
@if(!empty($quiz))
    @include(getTemplate() .'.panel.quizzes.modals.multiple_question',['quiz' => $quiz])
    @include(getTemplate() .'.panel.quizzes.modals.descriptive_question',['quiz' => $quiz])
    @include(getTemplate() .'.panel.quizzes.modals.fill_in_the_blank_question',['quiz' => $quiz])
    @include(getTemplate() .'.panel.quizzes.modals.matching_list_text_question',['quiz' => $quiz])
    @include(getTemplate() .'.panel.quizzes.modals.matching_list_image_question',['quiz' => $quiz])
    @include(getTemplate() .'.panel.quizzes.modals.file_upload_question',['quiz' => $quiz])
    @include(getTemplate() .'.panel.quizzes.modals.information_text',['quiz' => $quiz])

@endif
<script>
    // url for get textlessons against a chapter
    var routeUrl = "{{ route('chapter.getTextLesson', ['chapterId' => ':chapterId']) }}";
    $(document).ready(function() {
        //this function will be called on change of chapter dropdown in quiz create form
        $('#sectionChapterInQuizFormss').on("change", function() {
            let currentElement = $(this);
            console.log(currentElement);
            let parentSection = $(this).closest('section');
            let selectedChapterId = $(this).val();
            let action = routeUrl.replace(':chapterId', selectedChapterId);
            console.log(action, selectedChapterId);
            $.ajax({
                url: action,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let textLessons = response.data;
                    // $('#sectionLessonInQuizForm').empty().append(
                    parentSection.find('#selectedSelectionTextlessonInQuizForm').empty().append(
                        '<option disabled selected>Select Text Lesson</option> <option value="0">no text lesson association</option>'
                    );
                    $.each(textLessons, function(key, value) {
                        parentSection.find('#selectedSelectionTextlessonInQuizForm').append($(
                            '<option>', {
                                value: value.id,
                                text: value.title
                            }));
                    });
                },
                error: function(xhr, status, error) {
                    // Handle errors here
                    console.log("AJAX Error:", status, error);
                }
            });

        });
    });

</script>
@push('scripts_bottom')
    {{-- <script src="/assets/default/js/admin/quiz.min.js"></script> --}}
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
    <script src="/assets/default/js/jquery_ui.js"></script>

    <script>

        function updateQuizQuestionRelation(data)
        {
            let action = "{{ route('panel.questions.sort') }}"
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
