<section>
    <h3 class="section-title after-line mt-35">{{ trans('site.cultural_background') }}</h3>

    <div class="row mt-20">
        <div class="col-12 col-lg-10">
            <div>
                <label class="cursor-pointer mt-4" for="statusSwitch">{{ trans('panel.c_identity') }}</label>
                <div class="form-group d-flex align-items-center justify-content-between mb-0 @error('cultural_identity')  is-invalid @enderror">
                    @foreach(config('students.cultural_identities') as $k => $i)
                        <div class="custom-control custom-switch">
                            <input type="radio" name="cultural_identity" class="custom-control-input @error('cultural_identity')  is-invalid @enderror" id="cidentity-{{$k}}" value="{{$k}}" {{ (isset($userInfo) && $userInfo->cultural_identity == $k) ? 'checked' : ''}}>
                            <label class="custom-control-label" for="cidentity-{{$k}}">{{ $i }}</label>
                        </div>
                    @endforeach
                </div>
                @error('cultural_identity')
                <div class="invalid-feedback mb-2">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.c_birth_country') }}</label>
                <input type="text" name="birth_country" value="{{ old('birth_country', ($userInfo->birth_country) ?? '') }}" class="form-control @error('birth_country')  is-invalid @enderror" maxlength="30" required placeholder=""/>
                @error('birth_country')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.c_birth_city') }}</label>
                <input type="text" name="birth_city" value="{{ old('birth_city', ($userInfo->birth_city) ?? '') }}" class="form-control @error('birth_city')  is-invalid @enderror" maxlength="30" required placeholder=""/>
                @error('birth_city')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div>
                <label class="cursor-pointer mt-4" for="statusSwitch">{{ trans('panel.c_citizenship') }}</label>
                <div class="form-group d-flex align-items-center justify-content-between mb-0 @error('citizenship')  is-invalid @enderror">
                    @foreach(config('students.citizenships') as $k => $c)
                        <div class="custom-control custom-switch">
                            <input type="radio" name="citizenship" class="custom-control-input @error('citizenship')  is-invalid @enderror" id="citizenship-{{$k}}" value="{{$k}}" {{ (isset($userInfo) && $userInfo->citizenship == $k) ? 'checked' : ''}}>
                            <label class="custom-control-label" for="citizenship-{{$k}}">{{ $c }}</label>
                        </div>
                    @endforeach
                </div>
                @error('citizenship')
                <div class="invalid-feedback mb-2">
                    {{ $message }}
                </div>
                @enderror
            </div>

        </div>
    </div>
</section>
