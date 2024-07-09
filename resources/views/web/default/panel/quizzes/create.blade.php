@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css" />
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
            top: -62px;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
        }
    </style>
@endpush
@section('content')
    @include('web.default.panel.quizzes.create_quiz_form')
@endsection

@push('scripts_bottom')
    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var quizzesSectionLang = '{{ trans('quiz.quizzes_section') }}';
        var quizzesLessonLang = '{{ trans('quiz.quizzes_lesson') }}';
        var quizPairTextLabel = '{{ trans('quiz.text') }}';
        var quizPairImageLabel = '{{ trans('quiz.image') }}';
        var quizPairDescriptionLabel = '{{ trans('quiz.description') }}';
        var quizRemovePair = '{{ trans('quiz.removePair') }}';
    </script>

    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/default/js/panel/quiz.min.js"></script>
    <script src="/assets/default/js/panel/webinar_content_locale.min.js"></script>
@endpush
