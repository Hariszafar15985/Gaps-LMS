<?php
    $currentYear = (int) date('Y', time());
    $beginYear = 1900;
?>
<section>

    <h3 class="section-title after-line mt-35">{{ trans('site.education') }}</h3>
    <div class="row mt-20">
        <div class="col-12 col-lg-10">

            <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                <label class="cursor-pointer" for="emp-copy">{{ trans('panel.ed_attending_school') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="attending_secondary_school" class="custom-control-input" id="attending-school" value="1" {{ ($userInfo && $userInfo->attending_secondary_school) ? 'checked' : ''}}>
                    <label class="custom-control-label" for="attending-school">{{ trans('panel.yes_no') }}</label>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label class="cursor-pointer mt-4" for="statusSwitch">{{ trans('panel.ed_hs_level') }}</label>
                    <div class="form-group align-items-center justify-content-between mb-0 @error('school_level')  is-invalid @enderror">
                        @foreach(config('students.school_levels') as $k => $e)
                            <div class="custom-control custom-switch">
                                <input type="radio" name="school_level" class="custom-control-input @error('school_level')  is-invalid @enderror" id="school-level-{{$k}}" value="{{$k}}" {{ ($userInfo && $userInfo->school_level == $k) ? 'checked' : ''}}>
                                <label class="custom-control-label" for="school-level-{{$k}}">{{ $e }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('school_level')
                        <div class="invalid-feedback mb-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="form-group mt-4">
                <label class="input-label">{{ trans('panel.ed_cyear') }}</label>
                {{-- <input type="text" name="school_completed_year" value="{{ old('school_completed_year', ($userInfo->school_completed_year) ?? '') }}" class="form-control @error('school_completed_year')  is-invalid @enderror" placeholder=""/> --}}
                <select name="school_completed_year" id="school_completed_year" class="form-control @error('school_completed_year')  is-invalid @enderror">
                    
                    @php $oldVal = old('school_completed_year', ($userInfo->school_completed_year) ?? ''); @endphp
                    @for($year = $beginYear; $year <= $currentYear; $year++)
                        <option value="{{$year}}" {{ ($year == $oldVal) ? " selected='selected' " : "" }}>{{$year}}</option>
                    @endfor
                </select>
                @error('school_completed_year')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="form-group mt-4 d-flex align-items-center justify-content-between">
                <label class="cursor-pointer" for="emp-copy">{{ trans('panel.ed_enrolled_ostudies') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="is_enrolled" class="custom-control-input" id="is-enrolled" value="1" {{ ($userInfo && $userInfo->is_enrolled == 1) ? 'checked' : ''}}>
                    <label class="custom-control-label" for="is-enrolled">{{ trans('panel.yes_no') }}</label>
                </div>
            </div>

            <div class="form-group">
                <label class="input-label">{{ trans('panel.ed_ostudy') }}</label>
                <input type="text" name="enrolled_studies" value="{{ old('enrolled_studies', (isset($userInfo->enrolled_studies) ? $userInfo->enrolled_studies : "") ?? '') }}" class="form-control @error('enrolled_studies')  is-invalid @enderror" placeholder=""/>
                @error('enrolled_studies')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="col-6 col-lg-6">
                <label class="input-label">{{ trans('panel.ed_aus_cstudies') }}</label>
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_c1') }} ({{ trans('panel.ed_squalification') }}</label>
                    <input type="text" name="certificate1_qualification" value="{{ old('certificate1_qualification', (isset($userInfo->certificate1_qualification) ? $userInfo->certificate1_qualification : "") ?? '') }}" class="form-control @error('certificate1_qualification')  is-invalid @enderror" placeholder=""/>
                    @error('certificate1_qualification')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_c1') }} ({{ trans('panel.ed_ycompleted') }})</label>
                    {{-- <input type="text" name="certificate1_year_completed" value="{{ old('school_completed_year', (isset($userInfo->certificate1_year_completed) ? $userInfo->certificate1_year_completed : "") ?? '') }}" class="form-control @error('certificate1_year_completed')  is-invalid @enderror" placeholder=""/> --}}
                    <select name="certificate1_year_completed" id="certificate1_year_completed" class="form-control @error('certificate1_year_completed')  is-invalid @enderror">
                    
                        @php $oldVal = old('certificate1_year_completed', ($userInfo->certificate1_year_completed) ?? ''); @endphp
                        @for($year = $beginYear; $year <= $currentYear; $year++)
                            <option value="{{$year}}" {{ ($year == $oldVal) ? " selected='selected' " : "" }}>{{$year}}</option>
                        @endfor
                    </select>
                    @error('certificate1_year_completed')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_c2') }} ({{ trans('panel.ed_squalification') }}</label>
                    <input type="text" name="certificate2_qualification" value="{{ old('certificate2_qualification', (isset($userInfo->certificate2_qualification) ? $userInfo->certificate2_qualification : "") ?? '') }}" class="form-control @error('certificate2_qualification')  is-invalid @enderror" placeholder=""/>
                    @error('certificate2_qualification')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_c2') }} ({{ trans('panel.ed_ycompleted') }})</label>
                    {{-- <input type="text" name="certificate2_year_completed" value="{{ old('certificate2_year_completed', (isset($userInfo->certificate2_year_completed) ? $userInfo->certificate2_year_completed : "") ?? '') }}" class="form-control @error('certificate2_year_completed')  is-invalid @enderror" placeholder=""/> --}}
                    <select name="certificate2_year_completed" id="certificate2_year_completed" class="form-control @error('certificate2_year_completed')  is-invalid @enderror">
                    
                        @php $oldVal = old('certificate2_year_completed', ($userInfo->certificate2_year_completed) ?? ''); @endphp
                        @for($year = $beginYear; $year <= $currentYear; $year++)
                            <option value="{{$year}}" {{ ($year == $oldVal) ? " selected='selected' " : "" }}>{{$year}}</option>
                        @endfor
                    </select>
                    @error('certificate2_year_completed')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_c3') }} ({{ trans('panel.ed_squalification') }}</label>
                    <input type="text" name="certificate3_qualification" value="{{ old('certificate3_qualification', (isset($userInfo->certificate3_qualification) ? $userInfo->certificate3_qualification : "") ?? '') }}" class="form-control @error('certificate3_qualification')  is-invalid @enderror" placeholder=""/>
                    @error('certificate3_qualification')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_c3') }} ({{ trans('panel.ed_ycompleted') }})</label>
                    {{-- <input type="text" name="certificate3_year_completed" value="{{ old('certificate3_year_completed', (isset($userInfo->certificate3_year_completed) ? $userInfo->certificate3_year_completed : "") ?? '') }}" class="form-control @error('certificate3_year_completed')  is-invalid @enderror" placeholder=""/> --}}
                    <select name="certificate3_year_completed" id="certificate3_year_completed" class="form-control @error('certificate3_year_completed')  is-invalid @enderror">
                    
                        @php $oldVal = old('certificate3_year_completed', ($userInfo->certificate3_year_completed) ?? ''); @endphp
                        @for($year = $beginYear; $year <= $currentYear; $year++)
                            <option value="{{$year}}" {{ ($year == $oldVal) ? " selected='selected' " : "" }}>{{$year}}</option>
                        @endfor
                    </select>
                    @error('certificate3_year_completed')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_c4') }} ({{ trans('panel.ed_squalification') }}</label>
                    <input type="text" name="certificate4_qualification" value="{{ old('certificate4_qualification', (isset($userInfo->certificate4_qualification) ? $userInfo->certificate4_qualification : "") ?? '') }}" class="form-control @error('certificate4_qualification')  is-invalid @enderror" placeholder=""/>
                    @error('certificate4_qualification')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_c4') }} ({{ trans('panel.ed_ycompleted') }})</label>
                    {{-- <input type="text" name="certificate4_year_completed" value="{{ old('certificate4_year_completed', (isset($userInfo->certificate4_year_completed) ? $userInfo->certificate4_year_completed :"") ?? '') }}" class="form-control @error('certificate4_year_completed')  is-invalid @enderror" placeholder=""/> --}}
                    <select name="certificate4_year_completed" id="certificate4_year_completed" class="form-control @error('certificate4_year_completed')  is-invalid @enderror">
                    
                        @php $oldVal = old('certificate4_year_completed', ($userInfo->certificate4_year_completed) ?? ''); @endphp
                        @for($year = $beginYear; $year <= $currentYear; $year++)
                            <option value="{{$year}}" {{ ($year == $oldVal) ? " selected='selected' " : "" }}>{{$year}}</option>
                        @endfor
                    </select>
                    @error('certificate4_year_completed')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_diploma') }} ({{ trans('panel.ed_squalification') }}</label>
                    <input type="text" name="diploma_qualification" value="{{ old('diploma_qualification', (isset($userInfo->diploma_qualification) ? $userInfo->diploma_qualification : "") ?? '') }}" class="form-control @error('diploma_qualification')  is-invalid @enderror" placeholder=""/>
                    @error('diploma_qualification')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_diploma') }} ({{ trans('panel.ed_ycompleted') }})</label>
                    {{-- <input type="text" name="diploma_year_completed" value="{{ old('diploma_year_completed', (isset($userInfo->diploma_year_completed) ? $userInfo->diploma_year_completed : "") ?? '') }}" class="form-control @error('diploma_year_completed')  is-invalid @enderror" placeholder=""/> --}}
                    <select name="diploma_year_completed" id="diploma_year_completed" class="form-control @error('diploma_year_completed')  is-invalid @enderror">
                    
                        @php $oldVal = old('diploma_year_completed', ($userInfo->diploma_year_completed) ?? ''); @endphp
                        @for($year = $beginYear; $year <= $currentYear; $year++)
                            <option value="{{$year}}" {{ ($year == $oldVal) ? " selected='selected' " : "" }}>{{$year}}</option>
                        @endfor
                    </select>
                    @error('diploma_year_completed')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_adiploma') }} ({{ trans('panel.ed_squalification') }}</label>
                    <input type="text" name="adiploma_qualification" value="{{ old('adiploma_qualification', (isset($userInfo->adiploma_qualification) ? $userInfo->adiploma_qualification : "") ?? '') }}" class="form-control @error('adiploma_qualification')  is-invalid @enderror" placeholder=""/>
                    @error('adiploma_qualification')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_adiploma') }} ({{ trans('panel.ed_ycompleted') }})</label>
                    {{-- <input type="text" name="adiploma_year_completed" value="{{ old('adiploma_year_completed', (isset($userInfo->adiploma_year_completed) ? $userInfo->adiploma_year_completed : "") ?? '') }}" class="form-control @error('adiploma_year_completed')  is-invalid @enderror" placeholder=""/> --}}
                    <select name="adiploma_year_completed" id="adiploma_year_completed" class="form-control @error('adiploma_year_completed')  is-invalid @enderror">
                    
                        @php $oldVal = old('adiploma_year_completed', ($userInfo->adiploma_year_completed) ?? ''); @endphp
                        @for($year = $beginYear; $year <= $currentYear; $year++)
                            <option value="{{$year}}" {{ ($year == $oldVal) ? " selected='selected' " : "" }}>{{$year}}</option>
                        @endfor
                    </select>
                    @error('adiploma_year_completed')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_bachelor') }} ({{ trans('panel.ed_squalification') }}</label>
                    <input type="text" name="bachelor_qualification" value="{{ old('bachelor_qualification', (isset($userInfo->bachelor_qualification) ? $userInfo->bachelor_qualification : "") ?? '') }}" class="form-control @error('bachelor_qualification')  is-invalid @enderror" placeholder=""/>
                    @error('bachelor_qualification')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_bachelor') }} ({{ trans('panel.ed_ycompleted') }})</label>
                    {{-- <input type="text" name="bachelor_year_completed" value="{{ old('bachelor_year_completed', (isset($userInfo->bachelor_year_completed) ? $userInfo->bachelor_year_completed : "") ?? '') }}" class="form-control @error('bachelor_year_completed')  is-invalid @enderror" placeholder=""/> --}}
                    <select name="bachelor_year_completed" id="bachelor_year_completed" class="form-control @error('bachelor_year_completed')  is-invalid @enderror">
                    
                        @php $oldVal = old('bachelor_year_completed', ($userInfo->bachelor_year_completed) ?? ''); @endphp
                        @for($year = $beginYear; $year <= $currentYear; $year++)
                            <option value="{{$year}}" {{ ($year == $oldVal) ? " selected='selected' " : "" }}>{{$year}}</option>
                        @endfor
                    </select>
                    @error('bachelor_year_completed')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_miscellaneous') }} ({{ trans('panel.ed_squalification') }}</label>
                    <input type="text" name="miscellaneous_qualification" value="{{ old('miscellaneous_qualification', (isset($userInfo->miscellaneous_qualification) ? $userInfo->miscellaneous_qualification : "") ?? '') }}" class="form-control @error('miscellaneous_qualification')  is-invalid @enderror" placeholder=""/>
                    @error('miscellaneous_qualification')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="col-6 col-lg-6">
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.ed_miscellaneous') }} ({{ trans('panel.ed_ycompleted') }})</label>
                    {{-- <input type="text" name="miscellaneous_year_completed" value="{{ old('miscellaneous_year_completed', (isset($userInfo->miscellaneous_year_completed) ? $userInfo->miscellaneous_year_completed : "") ?? '') }}" class="form-control @error('miscellaneous_year_completed')  is-invalid @enderror" placeholder=""/> --}}
                    <select name="miscellaneous_year_completed" id="miscellaneous_year_completed" class="form-control @error('miscellaneous_year_completed')  is-invalid @enderror">
                    
                        @php $oldVal = old('miscellaneous_year_completed', ($userInfo->miscellaneous_year_completed) ?? ''); @endphp
                        @for($year = $beginYear; $year <= $currentYear; $year++)
                            <option value="{{$year}}" {{ ($year == $oldVal) ? " selected='selected' " : "" }}>{{$year}}</option>
                        @endfor
                    </select>
                    @error('miscellaneous_year_completed')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>

        </div>
    </div>
</section>
