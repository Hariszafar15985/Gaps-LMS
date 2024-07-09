<section>
    <h3 class="section-title after-line mt-35">{{ trans('site.participant_details') }}</h3>

    <div class="row mt-20">
        <div class="col-12 col-lg-6">
            <div>
                <label class="cursor-pointer mt-4">{{ trans('panel.p_title') }}</label>
                <div class="form-group d-flex align-items-center justify-content-between mb-0 @error('title')  is-invalid @enderror">
                    @foreach(config('students.titles') as $k => $t)
                        <div class="custom-control custom-switch">
                            <input type="radio" name="title" class="custom-control-input @error('title')  is-invalid @enderror" id="title-{{$k}}" value="{{$k}}" {{ ($userInfo && $userInfo->title == $k) ? 'checked' : ''}}>
                            <label class="custom-control-label" for="title-{{$k}}">{{ $t }}</label>
                        </div>
                    @endforeach
                </div>
                @error('title')
                <div class="invalid-feedback mb-2">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.p_fname') }}</label>
                <input type="text" name="first_name" value="{{ old('first_name', ($userInfo->first_name) ?? '') }}" class="form-control @error('first_name')  is-invalid @enderror" required maxlength="30" placeholder=""/>
                @error('first_name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.p_mname') }}</label>
                <input type="text" name="middle_name" value="{{ old('middle_name', ($userInfo->middle_name) ?? '') }}" class="form-control @error('middle_name')  is-invalid @enderror" maxlength="30" placeholder=""/>
                @error('middle_name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.p_sname') }}</label>
                <input type="text" name="sur_name" value="{{ old('sur_name', ($userInfo->sur_name) ?? '') }}" class="form-control @error('sur_name')  is-invalid @enderror" required maxlength="30" placeholder=""/>
                @error('sur_name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label for="dob" class="input-label">{{ trans('public.dob') }}</label>
                <input type="text" placeholder="DD/MM/YYYY" pattern="\d{2}\/\d{2}\/\d{4}" name="dob" id="dob" value="{{ old('dob', (!empty($userInfo->dob) ? (\Carbon\Carbon::createFromFormat('Y-m-d', $userInfo->dob)->format('d/m/Y')) : '')) }}" class="form-control @error('dob')  is-invalid @enderror" required />
                @error('dob')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div>
                <label class="cursor-pointer mt-4">{{ trans('panel.p_gender') }}</label>
                <div class="form-group d-flex align-items-center justify-content-between mb-0 @error('gender')  is-invalid @enderror">
                    @foreach(config('students.genders') as $k => $g)
                        <div class="custom-control custom-switch">
                            <input type="radio" name="gender" class="custom-control-input @error('gender')  is-invalid @enderror" id="gender-{{$k}}" value="{{$k}}" {{ ($userInfo && $userInfo->gender == $k) ? 'checked' : ''}}>
                            <label class="custom-control-label" for="gender-{{$k}}">{{ $g }}</label>
                        </div>
                    @endforeach
                </div>
                @error('gender')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.p_address') }}</label>
                <input type="text" name="address" value="{{ old('address', ($user->address) ?? '') }}" class="form-control @error('address')  is-invalid @enderror" required placeholder=""/>
                @error('address')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.p_suburb') }}</label>
                <input type="text" name="suburb" value="{{ old('suburb', ($userInfo->suburb) ?? '') }}" class="form-control @error('suburb')  is-invalid @enderror" maxlength="30" placeholder=""/>
                @error('suburb')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.p_state') }}</label>
                <input type="text" name="state" value="{{ old('state', ($userInfo->state) ?? '') }}" class="form-control @error('state')  is-invalid @enderror" maxlength="30" required placeholder=""/>
                @error('state')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.p_postcode') }}</label>
                <input type="text" name="post_code" value="{{ old('post_code', ($userInfo->post_code) ?? '') }}" class="form-control @error('post_code')  is-invalid @enderror" maxlength="10" required placeholder=""/>
                @error('post_code')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.p_econtact') }}</label>
                <input type="text" name="emergency_contact" value="{{ old('emergency_contact', ($userInfo->emergency_contact) ?? '') }}" class="form-control @error('emergency_contact')  is-invalid @enderror" maxlength="50" required placeholder=""/>
                @error('emergency_contact')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <label class="input-label">{{ trans('panel.p_contact_no') }}</label>
                <input type="text" name="contact_number" value="{{ old('contact_number', ($userInfo->contact_number) ?? '') }}" class="form-control @error('contact_number')  is-invalid @enderror" maxlength="15" required placeholder=""/>
                @error('contact_number')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                <label class="cursor-pointer" for="emp-copy">{{ trans('panel.p_emp_copy') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="send_result_to_employer" class="custom-control-input" id="emp-copy" value="1" {{ ($userInfo && $userInfo->send_result_to_employer == 1) ? 'checked' : ''}}>
                    <label class="custom-control-label" for="emp-copy">{{ trans('panel.yes_no') }}</label>
                </div>
            </div>
            {{--<div class="form-group">
                <label class="input-label">{{ trans('panel.bio') }}</label>
                <textarea name="about" rows="9" class="form-control @error('about')  is-invalid @enderror">{!! (!empty($user) and empty($new_user)) ? $user->about : old('about')  !!}</textarea>
                @error('about')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="form-group">
                <label class="input-label">{{ trans('panel.job_title') }}</label>
                <textarea name="bio" rows="3" class="form-control @error('bio') is-invalid @enderror">{{ $user->bio }}</textarea>
                @error('bio')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

                <div class="mt-15">
                     <p class="font-12 text-gray">- {{ trans('panel.bio_hint_1') }}</p>
                     <p class="font-12 text-gray">- {{ trans('panel.bio_hint_2') }}</p>
                </div>

            </div>--}}

        </div>
    </div>
</section>

@push('scripts_bottom')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    $('#dob').datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "c-150:c",
        dateFormat: "dd/mm/yy",
        maxDate: "-1"
    });
    </script>
@endpush
@push('styles_bottom')
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
@endpush
