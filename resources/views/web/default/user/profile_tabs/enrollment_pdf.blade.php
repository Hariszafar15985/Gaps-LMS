@if (isset($user->userInfo))
    @php
        $userInformation = $user->userInfo->getAttributes();
//print_r($userInformation); exit;
    @endphp
<div class="mt-20 row">
    <div class="col-12">
    {{-- User Details --}}
    @php
        $sectionKeys = [
            "title", "first_name", "middle_name", "sur_name", "dob", "gender",
            "suburb", "state", "post_code","emergency_contact",
            "contact_number" //This is the emergency contact number (facepalm)
        ];
        $headings = [
            "title" => trans('public.title'),
            "first_name" => trans('public.first_name'),
            "middle_name" => trans('public.middle_name'),
            "sur_name" => trans('public.sur_name'),
            "dob" => trans('public.dob'),
            "gender" => trans('public.gender'),
            "suburb" => trans('public.suburb'),
            "state" => trans('public.state'),
            "post_code" => trans('public.post_code'),
            "emergency_contact" => trans('public.emergency_contact'),
            "contact_number" => trans('public.contact_number')
        ];
        $first = true;
    @endphp
    @foreach ($userInformation as $key => $value)
        @if (isset($value) && strlen(trim($value)) > 0)
        @if(in_array($key, $sectionKeys))
        @if ($first)
            <div class="card">
                <div class="card-body">
                    <div class="row mt-20 mb-20">
                        <div class="col">
                            <h3 style="text-align: center;">{{trans('public.enrollment')}}</h3>
                            <h3>{{trans('public.user_details')}}</h3>
        @endif
                            <div class="" style="width: 500px; height: 25px;">
                                <div style="float: left; width: 220px; font-weight: bold;">{{ $headings[$key] }} : </div>
                                    <div class="---" style="float: left;">
                                        @php
                                            if($key == "title") {
                                                $title = [1 => "Mr.", 2 => "Mrs.", 3 => "Miss", 4 => "Dr."];
                                                $value = $title[$value];
                                                unset($title);
                                            } elseif ($key == "gender") {
                                                $gender = [1 => "Male", 2 => "Female", 3 => "Unspecified"];
                                                $value = $gender[$value];
                                                unset($gender);
                                            }
                                        @endphp
                                        {{ ucwords(trim($value)) }}
                                    </div>
                                </div>
                @php
                    $first = false;
                    unset($userInformation[$key]);
                @endphp
            @endif
        @endif
    @endforeach
    @if ($first === false)
                            </div>
                        </div>
                    </div>
                </div>
    @endif

    {{-- Cultural Background --}}
    @php
        /*
            "cultural_identity" => 1
            "birth_country" => "Pakistan"
            "birth_city" => "Lahore"
            "citizenship" => 1
            "other_visa_type" => null
        */

        $sectionKeys = [
            "cultural_identity", "birth_country", "birth_city", "citizenship", "other_visa_type"
        ];
        $headings = [
            "cultural_identity" => trans('public.cultural_identity'),
            "birth_country" => trans('public.birth_country'),
            "birth_city" => trans('public.birth_city'),
            "citizenship" => trans('public.citizenship'),
            "other_visa_type" => trans('public.other_visa_type'),
            ];
        $first = true;
    @endphp
    @foreach ($userInformation as $key => $value)
        @if (isset($value) && strlen(trim($value)) > 0)
        @if(in_array($key, $sectionKeys))
        @if ($first === true)
            <div class="card">
                <div class="card-body">
                    <div class="row mt-20 mb-20">
                        <div class="col">
                            <h3>{{trans('public.cultural_background')}}</h3>
        @endif
                            <div class="" style="width: 500px; height: 25px;">
                                <div style="float: left; width: 220px; font-weight: bold;">{{ $headings[$key] }} : </div>
                                    <div class="---" style="float: left; width: 200px;">
                                        @php
                                            if($key == "cultural_identity") {
                                                $legend = [1 => trans("public.aboriginal"), 2 => trans("public.tsi"),
                                                            3 => trans("public.both_aboriginal_tsi"), 4 => trans("public.neither")];
                                                $value = $legend[$value];
                                                unset($legend);
                                            } elseif ($key == "citizenship") {
                                                $legend = [1 => trans("public.aust_citizen"), 2 => trans("public.perm_resident"),
                                                            3 => trans("public.new_zealand_resident"),
                                                            4 => trans("public.other_visa_type")];
                                                $value = $legend[$value];
                                                unset($legend);
                                            }
                                        @endphp
                                        {{ ucwords(trim($value)) }}
                                    </div>
                                </div>
                            <div class="clearfix"></div>
                @php
                    $first = false;
                    unset($userInformation[$key]);
                @endphp
            @endif
        @endif
    @endforeach
    @if ($first === false)
                            </div>
                        </div>
                    </div>
                </div>
    @endif

    {{-- Special Needs --}}
    @php
        /*
            "does_speak_other_language" => 1
            "other_language" => "Urdu"
            "require_assistance" => 0
            "is_disable" => 0
            "disability" => 2
        */
        $sectionKeys = [
            "does_speak_other_language", "other_language", "require_assistance", "is_disable", "disability"
        ];
        $headings = [
            "does_speak_other_language" => trans('public.does_speak_other_language'),
            "other_language" => trans('public.other_language'),
            "require_assistance" => trans('public.require_assistance'),
            "is_disable" => trans('public.is_disable'),
            "disability" => trans('public.disability'),
            ];
        $first = true;
    @endphp
    @foreach ($userInformation as $key => $value)
        @if (isset($value) && strlen(trim($value)) > 0)
        @if(in_array($key, $sectionKeys))
        @if ($first === true)
            <div class="card">
                <div class="card-body">
                    <div class="row mt-20 mb-20">
                        <div class="col">
                            <h3>{{trans('public.special_needs')}}</h3>
        @endif
                            <div class="" style="width: 500px; height: 25px;">
                                <div style="float: left; width: 220px; font-weight: bold;">{{ $headings[$key] }} : </div>
                                    <div class="---" style="float: left;">
                                        @php
                                            if(in_array($key, ["does_speak_other_language", "require_assistance", "is_disable"])) {
                                                $legend = [0 => trans("public.no"), 1 => trans("public.yes")];
                                                $value = $legend[$value];
                                                unset($legend);
                                            } elseif ($key == "disability") {
                                                $legend = [
                                                    0 => trans("public.none"), 1 => trans("public.disabled_deaf"),
                                                    2 => trans("public.physical"), 3 => trans("public.intellectual"),
                                                    4 => trans("public.vision"), 5 => trans("public.acquired_brain"),
                                                    6 => trans("public.mental_illness"), 7 => trans("public.learning"),
                                                    8 => trans("public.medical_condition"), 9 => trans("public.other"),
                                                ];
                                                $value = $legend[$value];
                                                unset($legend);
                                            }
                                        @endphp
                                        {{ ucwords(trim($value)) }}
                                    </div>
                                </div>
                @php
                    $first = false;
                    unset($userInformation[$key]);
                @endphp
            @endif
        @endif
    @endforeach
    @if ($first === false)
                            </div>
                        </div>
                    </div>
                </div>
    @endif

    {{-- Employment --}}

    @php
        /*
            "employment_type" => 2
        */
        $sectionKeys = [
            "employment_type"
        ];
        $headings = [
            "employment_type" => trans('public.employment_type'),
            ];
        $first = true;
    @endphp
    @foreach ($userInformation as $key => $value)
        @if (isset($value) && strlen(trim($value)) > 0)
        @if(in_array($key, $sectionKeys))
        @if ($first === true)
            <div class="card">
                <div class="card-body">
                    <div class="row mt-20 mb-20">
                        <div class="col">
                            <h3>{{trans('public.employment')}}</h3>
        @endif
                            <div class="" style="width: 500px; height: 25px;">
                                <div style="float: left; width: 220px; font-weight: bold;">{{ $headings[$key] }} : </div>
                                    <div class="---" style="float: left;">
                                        @php
                                            $legend = [
                                                1 => trans("public.full_time_employee"), 2 => trans("public.part_time_employee"),
                                                3 => trans("public.self_employed_not_employing"), 4 => trans("public.self_employed_employing"),
                                                5 => trans("public.employed_unpaid_family_business"), 6 => trans("public.unemployed_seeking_full_time"),
                                                7 => trans("public.unemployed_seeking_part_time"), 8 => trans("public.not_employed_not_seeking")
                                            ];
                                            $value = $legend[$value];
                                            unset($legend);
                                        @endphp
                                        {{ ucwords(trim($value)) }}
                                    </div>
                                </div>
                @php
                    $first = false;
                    unset($userInformation[$key]);
                @endphp
            @endif
        @endif
    @endforeach
    @if ($first === false)
                            </div>
                        </div>
                    </div>
                </div>
    @endif

    {{-- Education --}}

        {{-- "attending_secondary_school" => 1 --}}
        {{--
            "school_level" => 1
            "school_completed_year" => "1997"
            "is_enrolled" => 0
            "enrolled_studies" => null
            "completed_qualification_in_australia" => 0
            "certificate1" => 0
            "certificate1_qualification" => "CCNA"
            "certificate1_year_completed" => "2000"
            "certificate2" => 0
            "certificate2_qualification" => "CCNP"
            "certificate2_year_completed" => "2003"
            "certificate3" => 0
            "certificate3_qualification" => "CCIE"
            "certificate3_year_completed" => "2004"
            "certificate4" => 0
            "certificate4_qualification" => null
            "certificate4_year_completed" => "1900"
            "diploma" => 0
            "diploma_qualification" => "Diploma of Electronics"
            "diploma_year_completed" => "1999"
            "adiploma" => 0
            "adiploma_qualification" => "DAE"
            "adiploma_year_completed" => "2001"
            "bachelor" => 0
            "bachelor_qualification" => null
            "bachelor_year_completed" => "1900"
            "miscellaneous" => 0
            "miscellaneous_qualification" => null
            "miscellaneous_year_completed" => "1900"
         --}}

    @php
        $sectionKeys = [
            "attending_secondary_school",
            "school_level",
            "school_completed_year",
            "is_enrolled",
            "enrolled_studies",
            "completed_qualification_in_australia",
            "certificate1",
            "certificate1_qualification",
            "certificate1_year_completed",
            "certificate2",
            "certificate2_qualification",
            "certificate2_year_completed",
            "certificate3",
            "certificate3_qualification",
            "certificate3_year_completed",
            "certificate4",
            "certificate4_qualification",
            "certificate4_year_completed",
            "diploma",
            "diploma_qualification",
            "diploma_year_completed",
            "adiploma",
            "adiploma_qualification",
            "adiploma_year_completed",
            "bachelor",
            "bachelor_qualification",
            "bachelor_year_completed",
            "miscellaneous",
            "miscellaneous_qualification",
            "miscellaneous_year_completed"
        ];
        $headings = [
            "attending_secondary_school" => trans('panel.ed_attending_school'),
            "school_level" => trans('panel.ed_hs_level'),
            "school_completed_year" => trans('panel.ed_cyear'),
            "is_enrolled" => trans('panel.ed_is_enrolled_studies'),
            "enrolled_studies" => trans('panel.ed_enrolled_studies'),
            "completed_qualification_in_australia" => trans('panel.ed_aus_completed_qualifications'),
            "certificate1_qualification" => trans('panel.ed_certificate'),
            "certificate1_year_completed" => trans('panel.ed_ycompleted'),
            "certificate2_qualification" => trans('panel.ed_certificate'),
            "certificate2_year_completed" => trans('panel.ed_ycompleted'),
            "certificate3_qualification" => trans('panel.ed_certificate'),
            "certificate3_year_completed" => trans('panel.ed_ycompleted'),
            "certificate4_qualification" => trans('panel.ed_certificate'),
            "certificate4_year_completed" => trans('panel.ed_ycompleted'),
            "diploma_qualification" => trans('panel.ed_diploma'),
            "diploma_year_completed" => trans('panel.ed_ycompleted'),
            "adiploma_qualification" => trans('panel.ed_diploma'),
            "adiploma_year_completed" => trans('panel.ed_ycompleted'),
            "bachelor_qualification" => trans('panel.ed_bachelor'),
            "bachelor_year_completed" => trans('panel.ed_ycompleted'),
            "miscellaneous_qualification" => trans('panel.ed_miscellaneous'),
            "miscellaneous_year_completed" => trans('panel.ed_ycompleted')
            ];
        $first = true;
        for($i = 1; $i <= 4; $i++) {
            $certificateName = 'certificate'.$i;
            if(!isset($userInformation[$certificateName.'_qualification'])
            || strlen(trim($userInformation[$certificateName.'_qualification'])) < 1) {
                unset($userInformation[$certificateName]);
            }
            unset($userInformation[$certificateName]); //extra field
        }
        if(!isset($userInformation['diploma_qualification'])
            || strlen(trim($userInformation['diploma_qualification'])) < 1) {
            unset($userInformation[$certificateName]);
        }
        unset($userInformation['diploma']); //extra field
        if(!isset($userInformation['adiploma_qualification'])
            || strlen(trim($userInformation['adiploma_qualification'])) < 1) {
            unset($userInformation[$certificateName]);
        }
        unset($userInformation['adiploma']); //extra field
        if(!isset($userInformation['bachelor_qualification'])
            || strlen(trim($userInformation['bachelor_qualification'])) < 1) {
            unset($userInformation[$certificateName]);
        }
        unset($userInformation['bachelor']); //extra field
        if(!isset($userInformation['miscellaneous_qualification'])
            || strlen(trim($userInformation['miscellaneous_qualification'])) < 1) {
            unset($userInformation[$certificateName]);
        }
        unset($userInformation['miscellaneous']); //extra field
    @endphp
    @foreach ($userInformation as $key => $value)
        @if (isset($value) && strlen(trim($value)) > 0)
        @if(in_array($key, $sectionKeys))
        @if ($first === true)
            <div class="card">
                <div class="card-body">
                    <div class="row mt-20 mb-20">
                        <div class="col">
                            <h3>{{trans('public.education')}}</h3>
        @endif
                                    @php
                                        if (in_array($key, ['attending_secondary_school', 'is_enrolled', 'completed_qualification_in_australia'])) {
                                            $legend = [
                                                0 => trans("public.no"), 1 => trans("public.yes")
                                            ];
                                            $value = $legend[$value];
                                            unset($legend);
                                        } elseif ($key == 'school_level') {
                                            $legend = [
                                                1 => trans("panel.ed_year_12"), 2 => trans("panel.ed_year_11"),
                                                3 => trans("panel.ed_year_10"), 4 => trans("panel.ed_year_9"),
                                                5 => trans("panel.ed_year_8"), 6 => trans("panel.ed_year_never"),
                                            ];
                                            $value = $legend[$value];
                                            unset($legend);
                                        } elseif (strpos($key, 'certificate') !== false) {
                                            if(strpos($key, 'year_completed')) {
                                                continue;
                                            }
                                            $yearKey = str_replace('qualification', 'year_completed', $key);
                                            $year = $userInformation[$yearKey];
                                            $value = $value . " ({$year})";
                                            unset($userInformation[$yearKey]);
                                        } elseif (strpos($key, 'diploma') !== false) {
                                            if(strpos($key, 'year_completed')) {
                                                continue;
                                            }
                                            $yearKey = str_replace('qualification', 'year_completed', $key);
                                            $year = $userInformation[$yearKey];
                                            $value = $value . " ({$year})";
                                            unset($userInformation[$yearKey]);
                                        } elseif (strpos($key, 'bachelor') !== false) {
                                            if(strpos($key, 'year_completed')) {
                                                continue;
                                            }
                                            $yearKey = str_replace('qualification', 'year_completed', $key);
                                            $year = $userInformation[$yearKey];
                                            $value = $value . " ({$year})";
                                            unset($userInformation[$yearKey]);
                                        } elseif (strpos($key, 'miscellaneous') !== false) {
                                            if(strpos($key, 'year_completed')) {
                                                continue;
                                            }
                                            $yearKey = str_replace('qualification', 'year_completed', $key);
                                            $year = $userInformation[$yearKey];
                                            $value = $value . " ({$year})";
                                            unset($userInformation[$yearKey]);
                                        }
                                    @endphp
                            <div class="" style="width: 500px; height: 25px;">
                                <div style="float: left; width: 220px; font-weight: bold;">{{ $headings[$key] }} : </div>
                                    <div class="---" style="float: left;">
                                        {{ ucwords(trim($value)) }}
                                    </div>
                                </div>
                @php
                    $first = false;
                    unset($userInformation[$key]);
                @endphp
            @endif
        @endif
    @endforeach
    @if ($first === false)
                            </div>
                        </div>
                    </div>
                </div>
    @endif

    {{-- Study Reasons --}}

    @php
        /*
            "study_reason" => 1
        */

        $sectionKeys = [
            "study_reason"
            ];
        $headings = [
            "study_reason" => trans('public.study_reasons'),
            ];
        $first = true;
    @endphp
    @foreach ($userInformation as $key => $value)
        @if (isset($value) && strlen(trim($value)) > 0)
        @if(in_array($key, $sectionKeys))
        @if ($first === true)
            <div class="card">
                <div class="card-body">
                    <div class="row mt-20 mb-20">
                        <div class="col">
                            <h3>{{trans('public.employment')}}</h3>
        @endif
                            <div class="" style="width: 500px; height: 25px;">
                                <div style="float: left; width: 220px; font-weight: bold;">{{ $headings[$key] }} : </div>
                                    <div class="---" style="float: left;">
                                        @php
                                            $legend = [
                                                1 => trans("public.get_a_job"), 2 => trans("public.develop_existing_business"),
                                                3 => trans("public.start_own_business"), 4 => trans("public.get_into_another_course"),
                                                5 => trans("public.try_for_different_career"), 6 => trans("public.better_job_or_promotion"),
                                                7 => trans("public.requirement_of_job"), 8 => trans("public.other_reasons"),
                                                9 => trans("public.extra_skill_for_job"), 10 => trans("public.personal_interest_self_development"),
                                                11 => trans("public.get_skill_for_community_work")
                                            ];
                                            $value = $legend[$value];
                                            unset($legend);
                                        @endphp
                                        {{ ucwords(trim($value)) }}
                                    </div>
                                </div>
                @php
                    $first = false;
                    unset($userInformation[$key]);
                @endphp
            @endif
        @endif
    @endforeach
    @if ($first === false)
                            </div>
                        </div>
                    </div>
                </div>
    @endif
    </div>
</div>

@else
    @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'webinar.png',
        'title' => trans('site.no_information_to_display'),
        'hint' => '',
    ])
@endif
