<!-- Modal -->
<div class="d-none" id="quizzesModal">
    <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('public.add_quiz') }}</h3>

    <form action="/admin/webinar-quiz/store" method="post">
        <input type="hidden" name="webinar_id" value="{{  !empty($webinar) ? $webinar->id :''  }}">

        <div class="form-group mt-15">
            <label class="input-label d-block">{{ trans('public.select_a_quiz') }}</label>

            <select name="quiz_id" class="form-control quiz-select2" data-placeholder="{{ trans('public.add_quiz') }}">
                <option disabled selected></option>
                @if(!empty($webinar))
                    @foreach($teacherQuizzes as $quiz)
                        <option value="{{ $quiz->id }}">{{ $quiz->title }}</option>
                    @endforeach
                @endif
            </select>
            <div class="invalid-feedback"></div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <label class="input-label">{{ trans('public.chapter') }}</label>
                <select class="custom-select" name="chapter_id">
                    <option value="">{{ trans('admin/main.no_chapter') }}</option>

                    @if(!empty($chapters))
                        @foreach($chapters as $chapter)
                            <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                        @endforeach
                    @endif
                </select>
                <div class="invalid-feedback"></div>
            </div>

            <div class="col-lg-6">
                <label class="input-label">{{ trans('public.lesson') }}</label>
                <select class="custom-select" name="lesson_id">
                    <option class="defaultVal" value="">{{ trans('admin/main.no_lesson') }}</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
            {{-- <div class="form-group"> --}}
        </div>

        <x-drip-feed-quiz ></x-drip-feed-quiz>

        {{-- <div class="form-group">
            <label class="input-label">{{ trans('public.lesson') }}</label>
            <select class="custom-select" name="lesson_id">
                <option class="defaultVal" value="">{{ trans('admin/main.no_lesson') }}</option>
            </select>
            <div class="invalid-feedback"></div>
        </div> --}}

        <div class="mt-3 d-flex align-items-center justify-content-end">
            <button type="button" id="saveQuiz" class="btn btn-primary">{{ trans('public.save') }}</button>
            <button type="button" class="btn btn-danger ml-2 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
