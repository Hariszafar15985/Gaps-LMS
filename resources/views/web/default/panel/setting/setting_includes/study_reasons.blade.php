<section>
    <h3 class="section-title after-line mt-35">{{ trans('site.study_reasons') }}</h3>

    <div class="row mt-20">
        <div class="col-12 col-lg-10">

            <label class="cursor-pointer mt-4 d-flex" for="statusSwitch">{{ trans('panel.sr_reason') }}</label>
            <div class="form-group mt-4 align-items-center justify-content-between mb-0 @error('study_reason')  is-invalid @enderror">
                @foreach(config('students.study_reasons') as $k => $sr)
                    <div class="custom-control custom-switch">
                        <input type="radio" name="study_reason" class="custom-control-input @error('study_reason')  is-invalid @enderror" id="study-reason-{{$k}}" value="{{$k}}" {{ ($userInfo && $userInfo->study_reason == $k) ? 'checked' : ''}}>
                        <label class="custom-control-label" for="study-reason-{{$k}}">{{ $sr }}</label>
                    </div>
                @endforeach
            </div>
            @error('study_reason')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>
    </div>
</section>
