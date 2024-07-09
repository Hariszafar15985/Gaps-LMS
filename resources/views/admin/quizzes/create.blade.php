@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <style>
        .dropzone {
            border: 2px dashed var(--primary);
            min-height: 240px;
            text-align: center;
        }

        .upload-icon {
            margin: 25px 2px 2px 2px;
        }

        .upload-input {
            position: relative;
            bottom: 0;
            left: 15%;
            opacity: 1;
        }

        /* For modern browsers */
        input[type="file"]::file-selector-button {
            display: none;
        }

        /* For Internet Explorer */
        input[type="file"]::-ms-browse {
            display: none;
        }

        .custom-file-upload {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .custom-file-upload input[type="file"] {
            display: none;
        }

        .custom-file-upload:hover {
            background-color: #0056b3;
        }
    </style>
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css" />
@endpush

@section('content')

    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.quizzes') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/admin/">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.quizzes') }}</div>
            </div>
        </div>

        <div class="section-body">


            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post"
                                action="{{ !empty($quiz) ? '/admin/quizzes/' . $quiz->id . '/update' : '/admin/quizzes/store' }}"
                                id="webinarForm" class="webinar-form">
                                {{ csrf_field() }}
                                <section>

                                    <div class="row">
                                        <div class="col-12 col-md-4">


                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="">
                                                    <h2 class="section-title">{{ trans('quiz.edit_quiz') }} -
                                                        {{ $quiz->title }}</h2>
                                                    <p>{{ trans('admin/main.instructor') }}: {{ $creator->full_name }}</p>
                                                </div>
                                            </div>

                                            @if (!empty(getGeneralSettings('content_translate')))
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('auth.language') }}</label>
                                                    <select name="locale"
                                                        class="form-control {{ !empty($quiz) ? 'js-edit-content-locale' : '' }}">
                                                        @foreach ($userLanguages as $lang => $language)
                                                            <option value="{{ $lang }}"
                                                                @if (mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>
                                                                {{ $language }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('locale')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            @else
                                                <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                            @endif

                                            <div class="form-group mt-3">
                                                <label class="input-label">{{ trans('panel.webinar') }}</label>
                                                <select id="webinarSelectionInQuizForm" name="webinar_id"
                                                    class="custom-select">
                                                    <option {{ !empty($quiz) ? 'disabled' : 'selected disabled' }}
                                                        value="">{{ trans('panel.choose_webinar') }}</option>
                                                    @foreach ($webinars as $webinar)
                                                        <option value="{{ $webinar->id }}"
                                                            {{ (!empty($quiz) and $quiz->webinar_id == $webinar->id) ? 'selected' : '' }}>
                                                            {{ $webinar->title }}</option>
                                                    @endforeach
                                                </select>

                                            </div>

                                            <div class="form-group">
                                                <label class="input-label">{{ trans('public.chapter') }}</label>
                                                <select id="chapterSelectionInQuizForm" name="chapter_id"
                                                    class="js-ajax-chapter_id form-control">
                                                    @foreach ($quizWebinarChapters as $chapter)
                                                        <option value="{{ $chapter->id }}"
                                                            {{ $chapter->id == $quiz->chapter_id ? 'selected' : '' }}>
                                                            {{ $chapter->title }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>

                                            <div class="form-group">
                                                <label class="input-label">Text Lesson</label>
                                                <select id="textLessonSelectionInQuizForm" name="text_lesson_id"
                                                    class="js-ajax-chapter_id form-control">
                                                    @foreach ($quizChapterTextLessons as $lesson)
                                                        <option value="{{ $lesson->id }}"
                                                            {{ $lesson->id == $quiz->text_lesson_id ? 'selected' : '' }}>
                                                            {{ $lesson->title }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>

                                            <div class="form-group">
                                                <label class="input-label">{{ trans('quiz.quiz_title') }}</label>
                                                <input type="text"
                                                    value="{{ !empty($quiz) ? $quiz->title : old('title') }}"
                                                    name="title"
                                                    class="form-control @error('title')  is-invalid @enderror"
                                                    placeholder="" />
                                                @error('title')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label class="input-label">{{ trans('public.time') }} <span
                                                        class="braces">({{ trans('public.minutes') }})</span></label>
                                                <input type="text"
                                                    value="{{ !empty($quiz) ? $quiz->time : old('time') }}" name="time"
                                                    class="form-control @error('time')  is-invalid @enderror"
                                                    placeholder="{{ trans('forms.empty_means_unlimited') }}" />
                                                @error('time')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label class="input-label">{{ trans('quiz.number_of_attemps') }}</label>
                                                <input type="text" name="attempt"
                                                    value="{{ !empty($quiz) ? $quiz->attempt : old('attempt') }}"
                                                    class="form-control @error('attempt')  is-invalid @enderror"
                                                    placeholder="{{ trans('forms.empty_means_unlimited') }}" />
                                                @error('attempt')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label class="input-label">{{ trans('quiz.pass_mark') }}</label>
                                                <input type="text" name="pass_mark"
                                                    value="{{ !empty($quiz) ? $quiz->pass_mark : old('pass_mark') }}"
                                                    class="form-control @error('pass_mark')  is-invalid @enderror"
                                                    placeholder="" />
                                                @error('pass_mark')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                                                <label class="cursor-pointer"
                                                    for="certificateSwitch">{{ trans('quiz.certificate_included') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="certificate" class="custom-control-input"
                                                        id="certificateSwitch"
                                                        {{ !empty($quiz) && $quiz->certificate ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="certificateSwitch"></label>
                                                </div>
                                            </div>

                                            <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                                                <label class="cursor-pointer"
                                                    for="statusSwitch">{{ trans('quiz.active_quiz') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="status" class="custom-control-input"
                                                        id="statusSwitch"
                                                        {{ !empty($quiz) && $quiz->status ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="statusSwitch"></label>
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
                                                class="btn btn-primary btn-sm ml-2 mt-3"
                                                data_quiz_id = "{{ $quiz->id }}">{{ trans('quiz.add_information_text') }}</button>
                                            <button id="add_multiple_question" type="button"
                                                class="btn btn-primary btn-sm ml-2 mt-3"
                                                data_quiz_id = "{{ $quiz->id }}">{{ trans('quiz.add_multiple_choice') }}</button>
                                            <button id="add_descriptive_question" type="button"
                                                class="btn btn-primary btn-sm ml-2 mt-3"
                                                data_quiz_id = "{{ $quiz->id }}">{{ trans('quiz.add_descriptive') }}</button>
                                            <button id="add_fillInBlank_question" type="button"
                                                class="btn btn-primary btn-sm ml-2 mt-3"
                                                data_quiz_id = "{{ $quiz->id }}">{{ trans('quiz.add_fillInBlank') }}</button>
                                            <div class="btn-group">
                                                <button type="button" id="dropdownMatchingListButton"
                                                    data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false"class="quiz-form-btn btn btn-primary dropdown-toggle btn-sm ml-2 mt-3">{{ trans('quiz.add_matchingList') }}</button>
                                                <div class="dropdown-menu " aria-labelledby="dropdownMatchingListButton">
                                                    <button id="add_matchingListText_question"
                                                        data_quiz_id = "{{ $quiz->id }}" type="button"
                                                        class="dropdown-item quiz-form-btn btn btn-primary btn-sm ml-2 mt-3">{{ trans('quiz.add_matchingListText') }}</button>
                                                    <button id="add_matchingListImage_question"
                                                        data_quiz_id = "{{ $quiz->id }}" type="button"
                                                        class="dropdown-item quiz-form-btn btn btn-primary btn-sm ml-2 mt-3">{{ trans('quiz.add_matchingListImage') }}</button>
                                                </div>
                                            </div>
                                            <button id="add_fileUpload_question" data_quiz_id = "{{ $quiz->id }}"
                                                type="button"
                                                class="btn btn-primary btn-sm ml-2 mt-3">{{ trans('quiz.add_fileUpload') }}</button>

                                        </div>
                                        <div class="sortable">
                                            @if ($quizQuestions)
                                                @foreach ($quizQuestions as $question)
                                                    <div data-quest-id = "{{ $question->id }}"
                                                        class="quiz-question-card d-flex align-items-center mt-4">
                                                        <div class="flex-grow-1">
                                                            <h4 class="question-title">{!! str_ireplace('{blank}', '_', $question->title) !!}</h4>
                                                            <div class="font-12 mt-3 question-infos">
                                                                <span>
                                                                    @if ($question->type === App\Models\QuizzesQuestion::$multiple)
                                                                        {{ trans('quiz.multiple_choice') }}
                                                                    @elseif($question->type === App\Models\QuizzesQuestion::$fillInBlank)
                                                                        {{ trans('quiz.fillInBlank') }}
                                                                    @elseif($question->type === App\Models\QuizzesQuestion::$informationText)
                                                                        {{ trans('quiz.information_text') }}
                                                                    @elseif($question->type === App\Models\QuizzesQuestion::$matchingListText)
                                                                        {{ trans('quiz.matchingListText') }}
                                                                    @elseif($question->type === App\Models\QuizzesQuestion::$matchingListImage)
                                                                        {{ trans('quiz.matchingListImage') }}
                                                                    @elseif($question->type === App\Models\QuizzesQuestion::$fileUpload)
                                                                        {{ trans('quiz.fileUpload') }}
                                                                    @else
                                                                        {{ trans('quiz.descriptive') }}
                                                                    @endif
                                                                    | {{ trans('quiz.grade') }}: {{ $question->grade }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="btn-group dropdown table-actions">
                                                            <button type="button" class="btn-transparent dropdown-toggle"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <i class="fa fa-ellipsis-v"></i>
                                                            </button>
                                                            <div class="dropdown-menu text-left">
                                                                <button type="button"
                                                                    data-question-id="{{ $question->id }}"
                                                                    class="edit_question btn btn-sm btn-transparent">{{ trans('public.edit') }}</button>
                                                                @include('admin.includes.delete_button', [
                                                                    'url' =>
                                                                        '/admin/quizzes-questions/' .
                                                                        $question->id .
                                                                        '/delete',
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
                                <div class="mt-5 mb-5">
                                    <button type="submit"
                                        class="btn btn-primary">{{ !empty($quiz) ? trans('admin/main.save_change') : trans('admin/main.create') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    @include('admin.quizzes.modals.multiple_question')
    @include('admin.quizzes.modals.descriptive_question')
    @include('admin.quizzes.modals.fill_in_the_blank_question')
    @include('admin.quizzes.modals.file_upload_question')
    @include('admin.quizzes.modals.matching_list_text_question')
    @include('admin.quizzes.modals.matching_list_image_question')
    @include('admin.quizzes.modals.information_text')


@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>

    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var quizzesSectionLang = '{{ trans('quiz.quizzes_section') }}';
        var quizzesLessonLang = '{{ trans('quiz.quizzes_lesson') }}';
        var quizPairTextLabel = '{{ trans('quiz.text') }}';
        var quizPairImageLabel = '{{ trans('quiz.image') }}';
        var quizPairDescriptionLabel = '{{ trans('quiz.description') }}';
        var quizRemovePair = '{{ trans('quiz.removePair') }}';
    </script>
    <script src="/assets_1/default/js/admin/quiz.min.js"></script>
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
    <script src="/assets/default/js/jquery_ui.js"></script>
    <script>
        function updateQuizRelation(data) {
            let action = "{{ route('admin.questions.sort') }}"
            $.post(action, data, function(result) {
                //no need to reload page
                // location.reload();
            }).fail(err => {
                console.log(err.responseJSON);
            });
        }

        function getQuestionIds() {
            let questionIds = '';
            $(".quiz-question-card").each(function(index) {
                questionIds += $(this).attr("data-quest-id") + ","
            });
            return {
                "data": questionIds
            }
        }

        $(function() {
            $(".sortable").sortable({
                update: function(event, ui) {
                    updateQuizRelation(getQuestionIds())
                }
            });
        });
    </script>

    {{-- ajax calls to get chapters of webinar and text lessons of chapter --}}
    <script>
        // url to get the chapters against a webinar
        let chapterUrl = "{{ route('webinar.getChapters', ['webinarId' => ':webinarId']) }}";
        // url for get textlessons against a chapter
        let lessonUrl = "{{ route('chapter.getTextLesson', ['chapterId' => ':chapterId']) }}";

        $(document).ready(function() {
            //this function will be called on change of webinar dropdown in quiz create form
            $('#webinarSelectionInQuizForm').on("change", function() {
                let selectedWebinarId = $('#webinarSelectionInQuizForm').val();
                let action = chapterUrl.replace(':webinarId', selectedWebinarId);
                $.ajax({
                    url: action,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let chapters = response;
                        $('#chapterSelectionInQuizForm').removeClass("loadingbar");
                        // will empty the text lessons also on change of the webinar/course
                        $('#textLessonSelectionInQuizForm').removeClass("loadingbar");
                        $('#textLessonSelectionInQuizForm').empty().append(
                            '<option value = "0"> no text lesson association </option>'
                        );

                        $('#chapterSelectionInQuizForm').empty().append(
                            '<option disabled selected>Select Section</option>');
                        $.each(chapters, function(key, value) {
                            $('#chapterSelectionInQuizForm').append($('<option>', {
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

            //this function will be called to get the text lessons for a chapter
            $('#chapterSelectionInQuizForm').on("change", function() {
                let selectedChapterId = $('#chapterSelectionInQuizForm').val();
                let action = lessonUrl.replace(':chapterId', selectedChapterId);
                $.ajax({
                    url: action,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let lessons = response.data;
                        if (lessons.length > 0) {
                            $('#textLessonSelectionInQuizForm').removeClass("loadingbar");
                            $('#textLessonSelectionInQuizForm').empty().append(
                                '<option disabled selected>Select Text Lesson</option> <option value = "0"> no text lesson association </option>'
                            );
                            $.each(lessons, function(key, value) {
                                $('#textLessonSelectionInQuizForm').append($(
                                    '<option>', {
                                        value: value.id,
                                        text: value.title
                                    }));
                            });
                        } else {
                            $('#textLessonSelectionInQuizForm').removeClass("loadingbar");
                            $('#textLessonSelectionInQuizForm').empty().append(
                                '<option disabled selected>Select Text Lesson</option> <option value = "0"> no text lesson association </option>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle errors here
                        console.log("AJAX Error:", status, error);
                    }
                });

            });

        });
    </script>
@endpush
