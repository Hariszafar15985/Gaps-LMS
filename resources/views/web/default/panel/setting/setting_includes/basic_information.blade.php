@push('styles_top')
<link href="/assets/default/vendors/select2/select2.min.css" rel="stylesheet" />
@endpush
<section>
    <h2 class="section-title after-line">{{ trans('financial.account') }}</h2>

    <div class="row mt-20">
        <div class="col-12 col-lg-4">
            <div class="form-group">
                <label class="input-label">{{ trans('public.email') }}</label>
                <input type="text" name="email" value="{{ (!empty($user) and empty($new_user)) ? $user->email : old('email') }}" class="form-control @error('email')  is-invalid @enderror" placeholder=""/>
                @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="form-group">
                <label class="input-label">{{ trans('auth.name') }}</label>
                <input type="text" name="full_name" value="{{ (!empty($user) and empty($new_user)) ? $user->full_name : old('full_name') }}" class="form-control @error('full_name')  is-invalid @enderror" placeholder=""/>
                @error('full_name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            @if(isset($new_user) && $new_user == true)
                <input type="hidden" name="password" value="{{ $password }}" />
                <input type="hidden" name="password_confirmation" value="{{ $password }}" class="form-control" />
            @else
                <div class="form-group">
                    <label class="input-label">{{ trans('auth.password') }}</label>
                    <input type="password" name="password" value="{{ old('password') }}" class="form-control @error('password')  is-invalid @enderror" placeholder=""/>
                    @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="input-label">{{ trans('auth.password_repeat') }}</label>
                    <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}" class="form-control @error('password_confirmation')  is-invalid @enderror" placeholder=""/>
                    @error('password_confirmation')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            @endif

            <div class="form-group">
                <label class="input-label">{{ trans('public.mobile') }}</label>
                <input type="number" name="mobile"
                maxlength = "10"
                oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                value="{{ (!empty($user) and empty($new_user)) ? $user->mobile : old('mobile') }}" class="form-control @error('mobile')  is-invalid @enderror" placeholder=""/>
                @error('mobile')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            @if(isset($user_type) && $user_type == "students" || (isset($user) && $user->isUser()))
            {{-- don't include bio field --}}
            @else
                <div class="form-group">
                    <label class="input-label">{{ trans('panel.bio') }} / {{ trans('panel.job_title') }}</label>
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

                </div>
            @endif

            @if( (isset($user_type) && $user_type === 'students') && (isset($user->role_name) && !$user->isUser()))
                <div class="form-group">
                    <label>Study Schedule</label>
                    <select class="form-control" id="schedule" name="schedule">
                        <option disabled selected>Select Schedule</option>
                        <option value="full_25" @if(!empty($user) and $user->schedule == 'full_25') selected @endif>Full Time (25 hours per week)</option>
                        <option value="part_15" @if(!empty($user) and $user->schedule == 'part_15') selected @endif>Part Time (15 hours per week)</option>
                        <option value="part_8" @if(!empty($user) and $user->schedule == 'part_8') selected @endif>Part Time (8 hours per week)</option>
                        <option value="self" @if(!empty($user) and $user->schedule == 'self') selected @endif>Self Paced</option>
                    </select>
                    @error('schedule')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            @endif

            @if($authUser->isTeacher())
            {{-- <div class="form-group">
                <label>Organization</label>
                <select class="form-control" id="organization" name="organization">
                    <option disabled selected>Select Organization</option>
                    @foreach ($organization as $org)
                    <option value="{{$org->id}}" @if(!empty($user) and $user->organ_id == $org->id) selected @endif>{{$org->full_name}}</option>
                    @endforeach
                </select>
                @error('organization')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div> --}}
            @elseif ($authUser->isOrganization())
                <input type="hidden" name="organization" id="organization" value="{{$authUser->id}}" />
            @elseif ($authUser->isOrganizationManager())
                <input type="hidden" name="organization" id="organization" value="{{$authUser->organ_id}}" />
            @endif

            @if($authUser->isOrganizationPersonnel())
                {{-- @if ($authUser->isOrganizationPersonnelButNotStaff()) --}}
                    @if (
                        (
                            ($user->isOrganizationMember() || $user->isUser())
                            && ($authUser->id !== $user->id)
                        )
                        || ((!empty($new_user) && $new_user) &&  in_array($user_type, ['managers', 'sub_managers', 'students', 'consultants']))
                    )
                        <div class="form-group">
                            <label>Organization Site</label>
                            <select class="form-control site-multiSelect @error('organization_site')  is-invalid @enderror" id="organization_site" required name='{{ $authUser->isOrganizationStaff() ? "organization_site" : " organization_site[]" }}' data-url="{{route('panel.get.organization.site.managers')}}"
                            @if (
                                (isset($user_type) && in_array($user_type, ['managers', 'sub_managers', 'consultants']))
                               && ($authUser->isOrganization() || $authUser->isOrganizationManager())
                                 && (
                                    (!empty($new_user) && $new_user) //new user
                                    || (isset($user->role_name)
                                    && ( $user->isOrganizationMember() )
                                    ) //editing the user
                                )
                            )
                            multiple="multiple"
                            @endif
                            >
                            @if(!isset($organization_sites) || $organization_sites->count() < 1)
                            <option disabled selected>Select Organization Site</option>
                            @else
                                @php $userOrganizationSites = $user->organizationSitesArray(); @endphp
                                @foreach($organization_sites as $organization_site)
                                    @php $organizationSiteId = isset($organization_site->site_id) ? $organization_site->site_id : $organization_site->id; @endphp
                                    <option value="{{$organizationSiteId}}"
                                        {{(empty($new_user) && !empty($user) && !empty($userOrganizationSites) && in_array($organizationSiteId, $userOrganizationSites) ) ? " selected='selected' " : ""}}
                                    >{{$organization_site->name}}</option>
                                @endforeach
                                @endif
                            </select>
                            @error('organization_site')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    @endif
                {{-- @else --}}
                    {{-- <input type="text" name="organization_site[]" id="organization_site" value="{{ $authUser->organization_site }}" /> --}}
                {{-- @endif --}}

                @if(
                    ($authUser->isOrganizationPersonnelButNotStaff() )
                    && (
                        (!empty($new_user) && $new_user && in_array($user_type,['students'])) //new user
                        || (isset($user->role_name) && ($user->isUser())) //edit
                    )
                )
                    <div class="form-group">
                        <label>Organization Manager</label>
                        <select class="form-control @error('manager_id')  is-invalid @enderror" id="manager_id" name="manager_id" required data-url="{{route('panel.get.organization.site.managers')}}">
                            @if(!isset($managers) || $managers->count() < 1)
                                <option disabled selected>Select Organization Manager</option>
                            @else
                                @foreach($managers as $manager)
                                    <option value="{{$manager->id}}"
                                        {{ isset($user->manager_id) ? "selected='selected'" : "" }}
                                        >{{$manager->full_name}}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('manager_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                @elseif($authUser->isOrganizationStaff() && !empty($new_user) && $new_user)
                    <input type="hidden" name="manager_id" id="manager_id" value="{{ $authUser->id }}" />
                @else
                    <input type="hidden" name="manager_id" id="manager_id" value="{{ (isset($user->manager_id) && (int)($user->manager_id) > 0) ? (int)$user->manager_id : "" }}" />
                @endif
                @if( (isset($user_type) && $user_type === 'students') || isset($user->role_name) && $user->isUser())
                    <div class="form-group">
                        <label>Course</label>
                        <x-course-drop-down :webinar="$webinar" :currency="$currency" :selected="old('course')" > </x-course-drop-down>
                        <x-course-preview-button class="btn-sm"/>
                        @error('course')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                @endif
            @endif

            @if ($user->isOrganization() && !isset($user_type))
                {{-- contracts --}}
                <div id="organization_fields" class="conditionalFields organization_fields">
                    <div class="form-group">
                        <label>{{trans('/admin/pages/users.organization_contracts')}}</label>
                        <select class="form-control @error('contract') is-invalid @enderror" id="contract" name="contract[]" multiple>
                            @foreach (config('organization.contracts') as $contract)
                                <option value="{{$contract}}"
                                @if(isset($organizationContracts) && is_array($organizationContracts) && in_array($contract, $organizationContracts))
                                    selected='selected'
                                @endif >{{$contract}}</option>
                            @endforeach
                        </select>
                        @error('contract')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>{{trans('/admin/pages/users.other_contract')}}</label>
                        <input type="text" class="form-control @error('other_contract') is-invalid @enderror" id="other_contract" name="other_contract"
                            value="{{ isset($organizationOtherContract) && strlen(trim($organizationOtherContract)) > 0 ? trim($organizationOtherContract) : ''}}"
                            disabled />
                        @error('other_contract')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                {{-- PO related fields --}}
                <div id="po_fields" class="conditionalFields organization_fields"
                    style="display: @error('po_num_required')
                            block
                        @else
                            @error('po_sequence')
                                block
                            @else
                                block
                            @enderror
                        @enderror">
                    <div class="form-inline" style="padding-bottom:20px;">
                        <label for="po_num_required"  style="font-weight: 600; color: #34395e; font-size: 12px; letter-spacing: .5px;">{{trans('/admin/pages/users.is_po_num_required')}}</label>
                        <input type="checkbox" class="form-control ml-3 @error('po_num_required') is-invalid @enderror" id="po_num_required" name="po_num_required"
                            value="1" {{(isset($organizationData->po_num_required) && $organizationData->po_num_required) ? 'checked="checked"' : ''}} style="height:18px;" />
                        <label class="ml-1" for="po_num_required">{{ trans('panel.yes_no') }}</label>
                        @error('po_num_required')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>{{trans('/admin/pages/users.po_sequence')}}</label>
                        <input type="text" class="form-control @error('po_sequence') is-invalid @enderror" id="po_sequence" name="po_sequence"
                            value="{{isset($organizationData->po_sequence) ? $organizationData->po_sequence : ''}}" />
                        @error('po_sequence')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label class="input-label">{{ trans('auth.language') }}</label>
                <select name="language" class="form-control">
                    <option value="">{{ trans('auth.language') }}</option>
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(!empty($user) and mb_strtolower($user->language) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                    @endforeach
                </select>
                @error('language')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                <label class="cursor-pointer input-label" for="newsletterSwitch">{{ trans('auth.join_newsletter') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="join_newsletter" class="custom-control-input" id="newsletterSwitch" {{ (!empty($user) and $user->newsletter) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="newsletterSwitch"></label>
                </div>
            </div>

            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                <label class="cursor-pointer input-label" for="publicMessagesSwitch">{{ trans('auth.public_messages') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="public_messages" class="custom-control-input" id="publicMessagesSwitch" {{ (!empty($user) and $user->public_message) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="publicMessagesSwitch"></label>
                </div>
            </div>
        </div>
    </div>

</section>

@push('scripts_bottom')
<script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".site-multiSelect").select2();
            $('#contract').change(function() {
                var vals = $(this).val();
                if (vals.indexOf("Other") > -1) {
                    $('#other_contract').removeAttr('disabled');
                    $('#other_contract').attr('required', 'required');
                } else {
                    $('#other_contract').attr('disabled', 'disabled');
                    $('#other_contract').removeAttr('required');
                }
            });
            $('#contract').trigger('change');
        });

    </script>
@endpush
