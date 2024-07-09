<section>
    <style>
        .custom-control.custom-switch{
            width: 490px;
        }
    </style>
    <h3 class="section-title after-line mt-35">{{ trans('site.employment') }}</h3>
    <div class="row mt-20">
        <div class="col-12 col-lg-8">
            <label class="cursor-pointer mt-4" for="statusSwitch">{{ trans('panel.e_type') }}</label>
            <div class="form-group align-items-center justify-content-between @error('employment_type')  is-invalid @enderror">
                @foreach(config('students.employment_types') as $k => $e)
                    <div class="col-md-3">
                        <div class="custom-control custom-switch">
                            <input type="radio" name="employment_type" class="custom-control-input @error('employment_type')  is-invalid @enderror" id="e-type-{{$k}}" value="{{$k}}" {{ ($userInfo && $userInfo->employment_type == $k) ? 'checked' : ''}}>
                            <label class="custom-control-label" for="e-type-{{$k}}">{{ $e }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('employment_type')
            <div class="invalid-feedback mb-2">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>
</section>
