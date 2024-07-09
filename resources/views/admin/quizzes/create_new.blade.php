@extends('admin.layouts.app')
@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
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
                            @include('admin.quizzes.create_quiz_form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var quizzesSectionLang = '{{ trans('quiz.quizzes_section') }}';
    </script>
    <script>
        // url to get the chapters against a webinar
        let chapterUrl = "{{ route('webinar.getChapters', ['webinarId' => ':webinarId']) }}";
        // url for get textlessons against a chapter
        let lessonUrl = "{{ route('chapter.getTextLesson', ['chapterId' => ':chapterId']) }}";
        $(document).ready(function() {
            //this function will be called on change of webinar dropdown in quiz create form on creation time
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

            //this function will be called to get the text lessons for a chapter on the quiz create method
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
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/js/panel/quiz_new.min.js"></script>
    <script src="/assets/default/js/panel/webinar_content_locale.min.js"></script>
@endpush
