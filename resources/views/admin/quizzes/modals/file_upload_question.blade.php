<div id="fileUploadQuestionModal"
    class="@if (!empty($quiz)) fileUploadQuestionModal{{ $quiz->id }} @endif {{ empty($question_edit) ? 'd-none' : '' }}">
    <div class="custom-modal-body">
        <h2 class="section-title after-line">{{ trans('quiz.fileUpload_question') }}</h2>
        <div class="quiz-questions-form" data-action="{{ getAdminPanelUrl() }}/quizzes-questions/{{ empty($question_edit) ? 'store' : $question_edit->id.'/update' }}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="quiz_id" value="{{ !empty($quiz) ? $quiz->id : '' }}">
            <input type="hidden" name="type" value="{{ \App\Models\QuizzesQuestion::$fileUpload }}">
            <div class="row mt-3">

                @if (!empty(getGeneralSettings('content_translate')))
                    <div class="col-12">
                        <div class="form-group">
                            <label class="input-label">{{ trans('auth.language') }}</label>
                            <select name="locale"
                                class="form-control {{ !empty($question_edit) ? 'js-quiz-question-locale' : '' }}"
                                data-id="{{ !empty($question_edit) ? $question_edit->id : '' }}">
                                @foreach ($userLanguages as $lang => $language)
                                    <option value="{{ $lang }}"
                                        {{ (!empty($question_edit) and !empty($question_edit->locale)) ? (mb_strtolower($question_edit->locale) == mb_strtolower($lang) ? 'selected' : '') : ($locale == $lang ? 'selected' : '') }}>
                                        {{ $language }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="locale" value="{{ $defaultLocale }}">
                @endif

                <div class="col-12 col-md-8">
                    <div class="form-group">
                        <label class="input-label">{{ trans('quiz.question_title') }}</label>
                        <input type="text" name="title" class="js-ajax-title form-control"
                            value="{{ !empty($question_edit) ? $question_edit->title : '' }}" />
                        <span class="invalid-feedback"></span>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label class="input-label">{{ trans('quiz.grade') }}</label>
                        <input type="text" name="grade" class="js-ajax-grade form-control"
                            value="{{ !empty($question_edit) ? $question_edit->grade : '1' }}" />
                        <span class="invalid-feedback"></span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end mt-3">
                <button type="button" class="save-question btn btn-sm btn-primary">{{ trans('public.save') }}</button>
                <button type="button"
                    class="close-swl btn btn-sm btn-danger ml-2">{{ trans('public.close') }}</button>
            </div>
        </div>
    </div>
</div>
