<div id="matchingListTextQuestionModal"
    class="@if (!empty($quiz)) matchingListTextQuestionModal{{ $quiz->id }} @endif {{ empty($question_edit) ? 'd-none' : '' }}
    @if (!empty($question_edit->id) && $question_edit->id > 0) question_{{ $question_edit->id }} @endif ">
    <div class="custom-modal-body">
        <h2 class="section-title after-line">{{ trans('quiz.matchingListText_question') }}</h2>
        <div class="quiz-questions-form" data-action="/admin/quizzes-questions/{{ empty($question_edit) ? 'store' : $question_edit->id . '/update' }}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="quiz_id" value="{{ !empty($quiz) ? $quiz->id : '' }}">
            <input type="hidden" name="type" value="{{ \App\Models\QuizzesQuestion::$matchingListText }}">
            <div class="row mt-3">

                @if (!empty(getGeneralSettings('content_translate')))
                    <div class="col-12 col-md-8">
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

                <div class="col-12 col-md-4 ml-auto">
                    <div class="form-group">
                        <label class="input-label">{{ trans('quiz.grade') }}</label>
                        <input type="text" name="grade" class="form-control"
                            value="{{ !empty($question_edit) ? $question_edit->grade : '1' }}" />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label class="input-label">{{ trans('quiz.question_title') }}</label>
                        <input name="title" id="" class="form-control"
                            value="{{ !empty($question_edit) ? $question_edit->title : '' }}" />
                    </div>
                </div>
            </div>
            <hr class="py-3" />

            <div class="row">
                <div class="col-12">
                    <h3>{{ trans('quiz.matching_pairs') }}</h3>
                    <div class="row">
                        <div class="col-3 ml-auto">
                            <button type="button" id="add_text_pair"
                                @if (!empty($question_edit->id) && $question_edit->id > 0) onclick="addPair('question_{{ $question_edit->id }}', 'text');" @endif
                                class="add_pair btn btn-sm btn-primary">{{ trans('quiz.add_another_pair') }}</button>
                        </div>
                    </div>
                    <div class="row matchingPairs mt-30">
                        @if (isset($question_edit->correct) && strlen($question_edit->correct) > 0)
                            @php $answers = json_decode($question_edit->correct, true); @endphp
                            @if (isset($answers) && count($answers) > 0)
                                @php $count = 0; @endphp
                                @foreach ($answers as $key => $answer)
                                    <div class="col-3 {{ isset($key) && $key > 0 ? 'pair_container' : '' }}">
                                        <div id="pair_{{ $key }}"
                                            class="row matchingPair {{ $key === 0 ? 'permanentPair' : '' }}">
                                            <div class="col-12">
                                                @if ($key > 0)
                                                    <button type="button"
                                                        class="removePair-btn btn btn-sm btn-background-transparent text-danger position-absolute"
                                                        title="Remove Pair"
                                                        onclick="removePair({{ $key }}, 'question_{{ $question_edit->id }}');"
                                                        style="right:0; height:1em; font-size:1.5em"><span
                                                            aria-hidden="true">&times;</span></button>
                                                @endif
                                                {{-- Text --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('quiz.text') }}</label>
                                                    <input type="text" name="answers[{{ $key }}][text]"
                                                        class="form-control" required
                                                        value="{{ !empty($answer['text']) ? $answer['text'] : '' }}" />
                                                </div>
                                                {{-- Description --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('quiz.description') }}</label>
                                                    <textarea type="text" name="answers[{{ $key }}][description]" class="form-control" required>{{ !empty($answer['description']) ? $answer['description'] : '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php $count++; @endphp
                                @endforeach
                            @endif
                        @else
                            <div class="col-3">
                                <div id="pair_0" class="row matchingPair permanentPair">
                                    <div class="col-12">
                                        {{-- Text --}}
                                        <div class="form-group">
                                            <label class="input-label">{{ trans('quiz.text') }}</label>
                                            <input type="text" name="answers[0][text]" class="form-control" required
                                                value="" />
                                        </div>
                                        {{-- Description --}}
                                        <div class="form-group">
                                            <label class="input-label">{{ trans('quiz.description') }}</label>
                                            <textarea type="text" name="answers[0][description]" class="form-control" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <input type="hidden" name='pairIdentifier' value="{{ isset($count) && $count > 0 ? $count - 1 : 0 }}" />


            <div class="d-flex align-items-center justify-content-end mt-3">
                <button type="button" class="save-question btn btn-sm btn-primary">{{ trans('public.save') }}</button>
                <button type="button" class="close-swl btn btn-sm btn-danger ml-2">{{ trans('public.close') }}</button>
            </div>
        </div>
    </div>
</div>
