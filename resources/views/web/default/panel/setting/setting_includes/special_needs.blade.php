<section>
    <h3 class="section-title after-line mt-35">{{ trans('site.special_needs') }}</h3>

    <div class="row mt-20">
        <div class="col-12 col-lg-8">
            <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                <label class="cursor-pointer" for="emp-copy">{{ trans('panel.s_speak_olanguage') }}</label>
                <div class="custom-control custom-switch col-lg-3">
                    <input type="checkbox" name="does_speak_other_language" class="custom-control-input" id="does-speak-other-language" value="1" {{ ($userInfo && $userInfo->does_speak_other_language == 1) ? 'checked' : ''}}>
                    <label class="custom-control-label" for="does-speak-other-language">{{ trans('panel.yes_no') }}</label>
                </div>
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.s_olanguage') }}</label>
                <input type="text" name="other_language" value="{{ old('other_language', (isset($userInfo->other_language) ? $userInfo->other_language : "")) }}" class="form-control @error('other_language')  is-invalid @enderror" placeholder=""/>
                @error('other_language')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                <label class="cursor-pointer" for="emp-copy">{{ trans('panel.s_rassistance') }}</label>
                <div class="custom-control custom-switch col-lg-3">
                    <input type="checkbox" name="require_assistance" class="custom-control-input" id="require-assistance" value="1" {{ ($userInfo && $userInfo->require_assistance == 1) ? 'checked' : ''}}>
                    <label class="custom-control-label" for="require-assistance">{{ trans('panel.yes_no') }}</label>
                </div>
            </div>
            <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                <label class="cursor-pointer" for="emp-copy">{{ trans('panel.s_isdisable') }}</label>
                <div class="custom-control custom-switch col-lg-3">
                    <input type="checkbox" name="is_disable" class="custom-control-input" id="is-disable" value="1" {{ ($userInfo && $userInfo->is_disable == 1) ? 'checked' : ''}}>
                    <label class="custom-control-label" for="is-disable">{{ trans('panel.yes_no') }}</label>
                </div>
            </div>
            <label class="cursor-pointer mt-4" for="statusSwitch">{{ trans('panel.s_disability') }}</label>
            <div class="form-group align-items-center justify-content-between">
                @foreach(config('students.disabilities') as $k => $d)
                    <div class="custom-control custom-switch col-lg-4">
                        <input type="radio" name="disability" class="custom-control-input" id="title-{{$k}}" value="{{$k}}" {{ ($userInfo && $userInfo->disability == $k) ? 'checked' : ''}}>
                        <label class="custom-control-label" for="title-{{$k}}">{{ $d }}</label>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</section>
