<div id="fillBlankQuestionModal" class="@if(!empty($quiz)) fillBlankQuestionModal{{ $quiz->id }} @endif {{ empty($question_edit) ? 'd-none' : ''}}">
    <div class="custom-modal-body">
        <h2 class="section-title after-line">{{ trans('quiz.fillInTheBlank_question') }}</h2>

        <div class="quiz-questions-form" data-action="/panel/quizzes-questions/{{ empty($question_edit) ? 'store' : $question_edit->id.'/update' }}" >
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="ajax[quiz_id]" value="{{ !empty($quiz) ? $quiz->id :'' }}">
            <input type="hidden" name="ajax[type]" value="{{ \App\Models\QuizzesQuestion::$fillInBlank }}">
            <div class="row mt-3">

                @if(!empty(getGeneralSettings('content_translate')))
                    <div class="col-12 col-md-8">
                        <div class="form-group">
                            <label class="input-label">{{ trans('auth.language') }}</label>
                            <select name="ajax[locale]"
                                    class="form-control {{ !empty($question_edit) ? 'js-quiz-question-locale' : '' }}"
                                    data-id="{{ !empty($question_edit) ? $question_edit->id : '' }}"
                            >
                                @foreach($userLanguages as $lang => $language)
                                    <option value="{{ $lang }}" {{ (!empty($question_edit) and !empty($question_edit->locale)) ? (mb_strtolower($question_edit->locale) == mb_strtolower($lang) ? 'selected' : '') : ($locale == $lang ? 'selected' : '') }}>{{ $language }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="ajax[locale]" value="{{ $defaultLocale }}">
                @endif

                <div class="col-12 col-md-4 ml-auto">
                    <div class="form-group">
                        <label class="input-label">{{ trans('quiz.grade') }}</label>
                        <input type="text" name="ajax[grade]" class="form-control" value="{{ !empty($question_edit) ? $question_edit->grade : '1' }}"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label class="input-label">{{ trans('quiz.file_in_blank_question_title') }}</label>
                        <textarea name="ajax[title]" id="" class="summernote form-control" rows="10">{{ !empty($question_edit) ? $question_edit->title : '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label class="input-label">{{ trans('quiz.preview') }}
                            <br />
                            <span class="font-italic">{{trans('quiz.pleasefillBlankFields')}}</span>
                        </label>
                        <div name="fitb_preview" id="" class="preview_div"
                        @if(isset($question_edit->correct) && strlen($question_edit->correct) > 0)
                            @php $answers = json_decode($question_edit->correct); @endphp
                            @if(isset($answers) && count($answers) > 0)
                                @php $count = 0; @endphp
                                @foreach($answers as $answer)
                                    data-answer_{{ $count }}="{{$answer}}"
                                    @php $count++; @endphp
                                @endforeach
                            @endif
                        @endif
                        ></div>
                    </div>
                </div>
            </div>


            <div class="d-flex align-items-center justify-content-end mt-25">
                <button type="button" class="save-question btn btn-sm btn-primary">{{ trans('public.save') }}</button>
                <button type="button" class="close-swl btn btn-sm btn-danger ml-10">{{ trans('public.close') }}</button>
            </div>
        </div>
    </div>
</div>
