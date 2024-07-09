@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@push("styles_top")
    <style>
        .can_set_visibility {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>
                {{ trans('/admin/main.edit') }} {{ trans('admin/main.user') }}
                @if(isset($user) && isset($user->role_name))
                    @if($user->isUser())
                        {{ " / " . trans('admin/main.student') }}
                    @else
                        {{ " / " . trans('admin/main.' . $user->role_name) }}
                    @endif
                @endif
            </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/admin/">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item active"><a href="/admin/users">{{ trans('admin/main.users') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('/admin/main.edit') }}</div>
            </div>
        </div>

        @if(!empty(session()->has('msg')))
            <div class="alert alert-success my-25">
                {{ session()->get('msg') }}
            </div>
        @endif


        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <ul class="nav nav-pills" id="myTab3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link @if(empty($becomeInstructor)) active @endif" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">{{ trans('admin/main.main_general') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="images-tab" data-toggle="tab" href="#images" role="tab" aria-controls="images" aria-selected="true">{{ trans('auth.images') }}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="financial-tab" data-toggle="tab" href="#financial" role="tab" aria-controls="financial" aria-selected="true">{{ trans('admin/main.financial') }}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="occupations-tab" data-toggle="tab" href="#occupations" role="tab" aria-controls="occupations" aria-selected="true">{{ trans('site.occupations') }}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="badges-tab" data-toggle="tab" href="#badges" role="tab" aria-controls="badges" aria-selected="true">{{ trans('admin/main.badges') }}</a>
                                </li>

                                @if(!empty($becomeInstructor))
                                    <li class="nav-item">
                                        <a class="nav-link @if(!empty($becomeInstructor)) active @endif" id="become_instructor-tab" data-toggle="tab" href="#become_instructor" role="tab" aria-controls="become_instructor" aria-selected="true">{{ trans('admin/main.become_instructor_info') }}</a>
                                    </li>
                                @endif
                            </ul>

                            <div class="tab-content" id="myTabContent2">

                                <div class="tab-pane mt-3 fade @if(empty($becomeInstructor)) active show @endif" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form action="/admin/users/{{ $user->id .'/update' }}" method="Post">
                                                {{ csrf_field() }}

                                                <div class="form-group">
                                                    <label>{{ trans('/admin/main.full_name') }}</label>
                                                    <input type="text" name="full_name"
                                                           class="form-control  @error('full_name') is-invalid @enderror"
                                                           value="{{ !empty($user) ? $user->full_name : old('full_name') }}"
                                                           placeholder="{{ trans('admin/main.create_field_full_name_placeholder') }}"/>
                                                    @error('full_name')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('/admin/main.role_name') }}</label>
                                                    <select class="form-control @error('role_id') is-invalid @enderror" id="roleId" name="role_id">
                                                        <option disabled {{ empty($user) ? 'selected' : '' }}>{{ trans('admin/main.select_role') }}</option>
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role->id }}" {{ (!empty($user) and $user->role_id == $role->id) ? 'selected' :''}}>{{ $role->caption }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('role_id')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>

                                                {{-- Organization and Site .. Meer --}}
                                                <div id="organization_fields">
                                                    @if(($user->isOrganizationPersonnel() || $user->isUser()) && !$user->isOrganization())
                                                        <div class="form-group">
                                                            <label>Organization</label>
                                                            <select class="form-control @error('organization') is-invalid @enderror" required id="organization" name="organization">
                                                                {{-- fetch live form the db --}}
                                                                @if($user['organization'])
                                                                    <option value="{{ $user['organization']['id'] }}" selected>{{ $user['organization']['full_name'] }}</option>
                                                                @endif
                                                            </select>
                                                            @error('organization')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                            @enderror
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Organization Site</label>
                                                            {{-- <select class="form-control @error('organization_site') is-invalid @enderror" required id="organization_site" name="organization_site[]" {{ ($user->isOrganizationManager() || $user->isOrganizationSubManager()) ? 'multiple' : ''}}> --}}
                                                            <select class="form-control @error('organization_site') is-invalid @enderror" required id="organization_site" name="organization_site[]" multiple >
                                                                {{-- fetch live form the db --}}
                                                                @if(isset($user->organization) && isset($user->organizationSites))
                                                                    @foreach($user->organizationSites as $site)
                                                                    {{ print_r($site) }}
                                                                        <option value="{{ $site['id'] }}" selected>{{ $site['name'] }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            @error('organization_site')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                            @enderror
                                                        </div>

                                                        @if($user->isOrganizationStaff())
                                                            <div class="form-group" style="display: none;">
                                                                <label>Backup Consultant for Students</label>
                                                                <select class="form-control @error('backup_consultant') is-invalid @enderror" id="backup_consultant" name="backup_consultant">
                                                                    {{-- fetch live form the db --}}
                                                                </select>
                                                                @error('backup_consultant')
                                                                <div class="invalid-feedback">
                                                                    {{ $message }}
                                                                </div>
                                                                @enderror
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>


                                                @if($user->isUser())
                                                    <div class="form-group">
                                                        <label>Consultant</label>
                                                        <select class="form-control @error('manager') is-invalid @enderror" required id="manager" name="manager">
                                                            {{-- fetch live form the db --}}
                                                            @if(isset($user['manager']))
                                                                <option value="{{ $user['manager']['id'] }}" selected>{{ $user['manager']['full_name'] }}</option>
                                                            @endif
                                                        </select>
                                                        @error('manager')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Study Schedule</label>
                                                        <select class="form-control @error('schedule') is-invalid @enderror" id="schedule" name="schedule">
                                                            <option disabled {{ empty($user) ? 'selected' : '' }}>Select Schedule</option>
                                                            <option value="full_25" {{ (!empty($user) and $user->schedule == "full_25") ? 'selected' :''}}>Full Time (25 hours per week)</option>
                                                            <option value="part_15" {{ (!empty($user) and $user->schedule == "part_15") ? 'selected' :''}}>Part Time (15 hours per week)</option>
                                                            <option value="part_8" {{ (!empty($user) and $user->schedule == "part_8") ? 'selected' :''}}>Part Time (8 hours per week)</option>
                                                            <option value="self" {{ (!empty($user) and $user->schedule == "self") ? 'selected' :''}}>Self Paced</option>
                                                        </select>
                                                        @error('schedule')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                        @enderror
                                                    </div>

                                                    {{-- Course should only be shown at the time of student creation.
                                                        If enrolment is missed at the time of registration, it should be done through the Student enrolment page. --}}
                                                    {{-- <div class="form-group">
                                                        <label>Course</label>
                                                        <select class="form-control @error('course') is-invalid @enderror" id="course" name="course">
                                                            <option disabled selected>Select Course</option>
                                                            @foreach ($webinar as $course)
                                                            <option value="{{$course->id}}" {{ old('course') === $course->id ? 'selected' :''}}>{{$course->slug}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('course')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                        @enderror
                                                    </div> --}}
                                                @endif

                                                <div id="organization_fields" class="conditionalFields organization_fields"
                                                    style="display: @error('contract')
                                                            block
                                                        @else
                                                            @error('other_contract')
                                                                block
                                                            @else
                                                                none
                                                            @enderror
                                                        @enderror">
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


                                                <div class="form-group">
                                                    <label for="username">{{ trans('admin/main.email') }}:</label>
                                                    <input name="email" type="text" id="username" value="{{ $user->email }}" class="form-control @error('email') is-invalid @enderror">
                                                    @error('email')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="username">{{ trans('admin/main.mobile') }}:</label>
                                                    <input name="mobile" type="text" value="{{ $user->mobile }}" class="form-control @error('mobile') is-invalid @enderror">
                                                    @error('mobile')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.password') }}</label>
                                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"/>
                                                    @error('password')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                                @if(!$user->isUser())
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.bio') }}</label>
                                                    <textarea name="bio" rows="3" class="form-control @error('bio') is-invalid @enderror">{{ $user->bio }}</textarea>
                                                    @error('bio')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                                @endif
                                                <div class="form-group">
                                                    <label>{{ trans('site.about') }}</label>
                                                    <textarea name="about" rows="6" class="form-control">{{ $user->about }}</textarea>
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('/admin/main.status') }}</label>
                                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                                        <option disabled {{ empty($user) ? 'selected' : '' }}>{{ trans('admin/main.select_status') }}</option>

                                                        @foreach (\App\User::$statuses as $status)
                                                            <option value="{{ $status }}" {{ !empty($user) && $user->status === $status ? 'selected' :''}}>{{  $status }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('status')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>

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

                                                <div class="form-group custom-switches-stacked mt-2">
                                                    <label class="custom-switch pl-0">
                                                        <input type="hidden" name="ban" value="0">
                                                        <input type="checkbox" name="ban" id="banSwitch" value="1" {{ (!empty($user) and $user->ban) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                        <span class="custom-switch-indicator"></span>
                                                        <label class="custom-switch-description mb-0 cursor-pointer" for="banSwitch">{{ trans('admin/main.ban') }}</label>
                                                    </label>
                                                </div>

                                                <div class="row {{ (($user->ban) or (old('ban') == 'on')) ? '' : 'd-none' }}" id="banSection">
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-group">
                                                            <label class="input-label">{{ trans('public.from') }}</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="dateInputGroupPrepend">
                                                                        <i class="fa fa-calendar-alt"></i>
                                                                    </span>
                                                                </div>
                                                                <input type="text" name="ban_start_at" class="form-control datepicker @error('ban_start_at') is-invalid @enderror" value="{{ !empty($user->ban_start_at) ? dateTimeFormat($user->ban_start_at,'Y/m/d') :'' }}"/>
                                                                @error('ban_start_at')
                                                                <div class="invalid-feedback">
                                                                    {{ $message }}
                                                                </div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-group">
                                                            <label class="input-label">{{ trans('public.to') }}</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="dateInputGroupPrepend">
                                                                        <i class="fa fa-calendar-alt"></i>
                                                                    </span>
                                                                </div>
                                                                <input type="text" name="ban_end_at" class="form-control datepicker @error('ban_end_at') is-invalid @enderror" value="{{ !empty($user->ban_end_at) ? dateTimeFormat($user->ban_end_at,'Y/m/d') :'' }}"/>
                                                                @error('ban_end_at')
                                                                <div class="invalid-feedback">
                                                                    {{ $message }}
                                                                </div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group custom-switches-stacked">
                                                    <label class="custom-switch pl-0">
                                                        <input type="hidden" name="verified" value="0">
                                                        <input type="checkbox" name="verified" id="verified" value="1" {{ (!empty($user) and $user->verified) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                        <span class="custom-switch-indicator"></span>
                                                        <label class="custom-switch-description mb-0 cursor-pointer" for="verified">{{ trans('admin/main.enable_blue_badge') }}</label>
                                                    </label>
                                                </div>

                                                <div class="form-group custom-switches-stacked mt-2">
                                                    <label class="custom-switch pl-0">
                                                        <input type="hidden" name="affiliate" value="0">
                                                        <input type="checkbox" name="affiliate" id="affiliateSwitch" value="1" {{ (!empty($user) and $user->affiliate) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                        <span class="custom-switch-indicator"></span>
                                                        <label class="custom-switch-description mb-0 cursor-pointer" for="affiliateSwitch">{{ trans('panel.affiliate') }}</label>
                                                    </label>
                                                </div>
                                                @if ( $user->isTeacher() )
                                                <div class="form-group">
                                                    <input type="checkbox" {{ $user->set_class_visiblity == true ? "checked" : "" }} class="can_set_visibility @error('can_set_visibility') is-invalid @enderror" id="can_set_visibility" name="can_set_visibility" />
                                                    <label for="can_set_visibility" class="can_set_visibility"> {{ trans("public.can_set_visibillity") }} </label>
                                                    @error('can_set_visibility')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                                @endif  
                                                <div class=" mt-4">
                                                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane mt-3 fade" id="images" role="tabpanel" aria-labelledby="images-tab">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form action="/admin/users/{{ $user->id .'/updateImage' }}" method="Post">
                                                {{ csrf_field() }}

                                                <div class="form-group mt-15">
                                                    <label class="input-label">{{ trans('admin/main.avatar') }}</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="input-group-text admin-file-manager" data-input="avatar" data-preview="holder">
                                                                <i class="fa fa-chevron-up"></i>
                                                            </button>
                                                        </div>
                                                        <input type="text" name="avatar" id="avatar" value="{{ !empty($user->avatar) ? $user->getAvatar() : old('image_cover') }}" class="form-control"/>
                                                    </div>

                                                </div>

                                                <div class="form-group mt-15">
                                                    <label class="input-label">{{ trans('admin/main.cover_image') }}</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="input-group-text admin-file-manager" data-input="cover_img" data-preview="holder">
                                                                <i class="fa fa-chevron-up"></i>
                                                            </button>
                                                        </div>
                                                        <input type="text" name="cover_img" id="cover_img" value="{{ !empty($user->cover_img) ? $user->cover_img : old('image_cover') }}" class="form-control"/>
                                                        <div class="input-group-append">
                                                            <button type="button" class="input-group-text admin-file-view" data-input="cover_img">
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class=" mt-4">
                                                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane mt-3 fade" id="financial" role="tabpanel" aria-labelledby="financial-tab">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form action="/admin/users/{{ $user->id .'/financialUpdate' }}" method="Post">
                                                {{ csrf_field() }}

                                                <div class="form-group">
                                                    <label>{{ trans('financial.account_type') }}</label>
                                                    <input type="text" name="account_type"
                                                           class="form-control "
                                                           value="{{ !empty($user) ? $user->account_type : old('account_type') }}"
                                                           placeholder="{{ trans('financial.account_type') }}"/>
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('financial.iban') }}</label>
                                                    <input type="text" name="iban"
                                                           class="form-control "
                                                           value="{{ !empty($user) ? $user->iban : old('iban') }}"
                                                           placeholder="{{ trans('financial.iban') }}"/>
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('financial.account_id') }}</label>
                                                    <input type="text" name="account_id"
                                                           class="form-control "
                                                           value="{{ !empty($user) ? $user->account_id : old('account_id') }}"
                                                           placeholder="{{ trans('financial.account_id') }}"/>
                                                </div>

                                                <div class="form-group mt-15">
                                                    <label class="input-label">{{ trans('financial.identity_scan') }}</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="input-group-text admin-file-manager" data-input="identity_scan" data-preview="holder">
                                                                <i class="fa fa-chevron-up"></i>
                                                            </button>
                                                        </div>
                                                        <input type="text" name="identity_scan" id="identity_scan" value="{{ !empty($user->identity_scan) ? $user->identity_scan : old('identity_scan') }}" class="form-control"/>
                                                        <div class="input-group-append">
                                                            <button type="button" class="input-group-text admin-file-view" data-input="identity_scan">
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('financial.address') }}</label>
                                                    <input type="text" name="address"
                                                           class="form-control "
                                                           value="{{ !empty($user) ? $user->address : old('address') }}"
                                                           placeholder="{{ trans('financial.address') }}"/>
                                                </div>

                                                @if(!$user->isUser())
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.user_commission') }} (%)</label>
                                                        <input type="text" name="commission"
                                                               class="form-control "
                                                               value="{{ !empty($user) ? $user->commission : old('commission') }}"
                                                               placeholder="{{ trans('admin/main.user_commission_placeholder') }}"/>
                                                    </div>
                                                @endif

                                                <div class="form-group mb-0 d-flex">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="financial_approval" class="custom-control-input" id="verifySwitch" {{ (($user->financial_approval) or (old('financial_approval') == 'on')) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="verifySwitch"></label>
                                                    </div>
                                                    <label for="verifySwitch">{{ trans('admin/main.financial_approval') }}</label>
                                                </div>

                                                <div class=" mt-4">
                                                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane mt-3 fade" id="occupations" role="tabpanel" aria-labelledby="occupations-tab">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form action="/admin/users/{{ $user->id .'/occupationsUpdate' }}" method="Post">
                                                {{ csrf_field() }}

                                                @foreach($categories as $category)
                                                    @if(!empty($category->subCategories) and count($category->subCategories))
                                                        @foreach($category->subCategories as $subCategory)
                                                            <div class="checkbox-button mr-15 mt-10">
                                                                <input type="checkbox" name="occupations[]" id="checkbox{{ $subCategory->id }}" value="{{ $subCategory->id }}" @if(!empty($occupations) and in_array($subCategory->id,$occupations)) checked="checked" @endif>
                                                                <label for="checkbox{{ $subCategory->id }}">{{ $subCategory->title }}</label>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="checkbox-button mr-15 mt-10">
                                                            <input type="checkbox" name="occupations[]" id="checkbox{{ $category->id }}" value="{{ $category->id }}" @if(!empty($occupations) and in_array($category->id,$occupations)) checked="checked" @endif>
                                                            <label for="checkbox{{ $category->id }}">{{ $category->title }}</label>
                                                        </div>
                                                    @endif
                                                @endforeach

                                                <div class=" mt-4">
                                                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane mt-3 fade" id="badges" role="tabpanel" aria-labelledby="financial-tab">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form action="/admin/users/{{ $user->id .'/badgesUpdate' }}" method="Post">
                                                {{ csrf_field() }}

                                                <div class="form-group">
                                                    <select name="badge_id" class="form-control @error('badge_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select_badge') }}</option>

                                                        @foreach($badges as $badge)
                                                            <option value="{{ $badge->id }}">{{ $badge->title }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('badge_id')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>

                                                <div class=" mt-4">
                                                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                                </div>
                                            </form>

                                        </div>

                                        <div class="col-12">
                                            <div class="mt-5">
                                                <h5>{{ trans('admin/main.custom_badges') }}</h5>

                                                <div class="table-responsive mt-5">
                                                    <table class="table table-striped table-md">
                                                        <tr>
                                                            <th>{{ trans('admin/main.title') }}</th>
                                                            <th>{{ trans('admin/main.image') }}</th>
                                                            <th>{{ trans('admin/main.condition') }}</th>
                                                            <th>{{ trans('admin/main.description') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.created_at') }}</th>
                                                            <th>{{ trans('admin/main.actions') }}</th>
                                                        </tr>

                                                        @if(!empty($user->customBadges))
                                                            @foreach($user->customBadges as $customBadge)

                                                                @php
                                                                    $condition = json_decode($customBadge->badge->condition);
                                                                @endphp

                                                                <tr>
                                                                    <td>{{ $customBadge->badge->title }}</td>
                                                                    <td>
                                                                        <img src="{{ $customBadge->badge->image }}" width="24"/>
                                                                    </td>
                                                                    <td>{{ $condition->from }} to {{ $condition->to }}</td>
                                                                    <td width="25%">
                                                                        <p>{{ $customBadge->badge->description  }}</p>
                                                                    </td>
                                                                    <td class="text-center">{{ dateTimeFormat($customBadge->badge->created_at,'d M Y') }}</td>
                                                                    <td>
                                                                        @can('admin_users_edit')
                                                                            @include('admin.includes.delete_button',['url' => '/admin/users/'.$user->id.'/deleteBadge/'.$customBadge->id , 'btnClass' => 'btn-sm'])
                                                                        @endcan
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    </table>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="mt-5">
                                                <h5>{{ trans('admin/main.auto_badges') }}</h5>

                                                <div class="table-responsive mt-5">
                                                    <table class="table table-striped table-md">
                                                        <tr>
                                                            <th>{{ trans('admin/main.title') }}</th>
                                                            <th>{{ trans('admin/main.image') }}</th>
                                                            <th>{{ trans('admin/main.condition') }}</th>
                                                            <th>{{ trans('admin/main.description') }}</th>
                                                            <th>{{ trans('admin/main.created_at') }}</th>
                                                        </tr>

                                                        @if(!empty($userBadges))
                                                            @foreach($userBadges as $badge)
                                                                @php
                                                                    $badgeCondition = json_decode($badge->condition);
                                                                @endphp

                                                                <tr>
                                                                    <td>{{ $badge->title }}</td>
                                                                    <td>
                                                                        <img src="{{ $badge->image }}" width="24"/>
                                                                    </td>
                                                                    <td>{{ $badgeCondition->from }} to {{ $badgeCondition->to }}</td>
                                                                    <td width="25%">
                                                                        <p>{{ $badge->description  }}</p>
                                                                    </td>
                                                                    <td>{{ dateTimeFormat($badge->created_at,'d M Y') }}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($becomeInstructor))
                                    <div class="tab-pane mt-3 fade active show" id="become_instructor" role="tabpanel" aria-labelledby="become_instructor-tab">
                                        <div class="row">
                                            <div class="col-12">
                                                <table class="table">
                                                    <tr>
                                                        <td class="text-left">{{ trans('site.extra_information') }}</td>
                                                        <td class="text-center">{{ trans('public.certificate_and_documents') }}</td>
                                                    </tr>

                                                    <tr>
                                                        <td width="80%" class="text-left">{{ $becomeInstructor->description }}</td>
                                                        <td class="text-center">
                                                            @if(!empty($becomeInstructor->certificate))
                                                                <a href="{{ (strpos($becomeInstructor->certificate,'http') != false) ? $becomeInstructor->certificate : url($becomeInstructor->certificate) }}" target="_blank" class="btn btn-sm btn-success">{{ trans('admin/main.show') }}</a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>


                                                @include('admin.includes.delete_button',[
                                                                 'url' => '/admin/users/become_instructors/'. $becomeInstructor->id .'/reject',
                                                                 'btnClass' => 'mt-3 btn btn-danger',
                                                                 'btnText' => trans('admin/main.reject_request'),
                                                                 'hideDefaultClass' => true
                                                                 ])

                                                <a href="/admin/users/{{ $user->id }}/acceptRequestToInstructor" class="btn btn-success ml-1 mt-3">{{ trans('admin/main.accept_request') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>

        $(document).ready(function() {

            $('#roleId').change(function() {
                if(this.value == 1) {
                    $("#student_fields").css("display", "block");
                } else {
                    $("#student_fields").css("display", "none");
                }

                if(this.value == 3) {
                    $(".organization_fields").css("display", "block");
                } else {
                    $(".organization_fields").css("display", "none");
                }
            });
            $('#roleId').trigger('change');

            $('#contract').select2({
                placeholder: "{{ trans('admin/main.select_contracts') }}"
            });

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


            // start of organization fetch live ajax
            organization_id = {{ isset($user['organization']['id']) ? $user['organization']['id'] : 0 }};
            organization_site_id = {{ isset($user['organization_site']) ? $user['organization_site'] : 0 }};

            old_organization_id = organization_id;
            old_organization_site_id = organization_site_id;
            user_role_name = "{{ isset($user['role_name']) ? $user['role_name'] : "" }}";

            $('#organization').select2({
                placeholder: '{{ trans("admin/main.select_organization") }}',
                allowClear: false,
                // minimumInputLength: 2,
                tags: null,
                ajax: {
                    url: "{{ route('admin.organizations.query') }}",
                    dataType: 'json',
                    type: "GET",
                    quietMillis: 50,
                    delay: 200,
                    data: function (params) {
                        return {
                            term: params.term,
                            user_type: 'organization'
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function(obj, index) {
                                return {
                                    id: obj.id,
                                    text: obj.full_name + ' (' + obj.email + ')',
                                }
                            })
                        };
                    }
                }
            }).on("change.select2", function(e) {
                organization_id = $('#organization').val();
                $('#organization_site').val(null).trigger('change');
                checkBackupConsultant();
            }); // end of organization ajax fetch

            // start of organization site fetch live ajax
            $('#organization_site').select2({
                placeholder: '{{ trans("admin/main.select_organization_site") }}',
                allowClear: false,
                // minimumInputLength: 2,
                tags: null,
                ajax: {
                    url: "{{ route('admin.organizations.query') }}",
                    dataType: 'json',
                    type: "GET",
                    quietMillis: 50,
                    delay: 200,
                    data: function (params) {
                        return {
                            term: params.term,
                            user_type: "organization_site",
                            organization_id: organization_id
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function(obj, index) {
                                return {
                                    id: obj.id,
                                    text: obj.name,
                                }
                            })
                        };
                    }
                }
            }).on("change.select2", function(e) {
                organization_site_id = $('#organization_site').val();
                $('#manager').val(null).trigger('change');
                checkBackupConsultant();
            });; // end of organization site ajax fetch

            // start of site consultant fetch live ajax
            $('#manager').select2({
                placeholder: '{{ trans("admin/main.select_consultant") }}',
                allowClear: false,
                // minimumInputLength: 2,
                tags: null,
                ajax: {
                    url: "{{ route('admin.organizations.query') }}",
                    dataType: 'json',
                    type: "GET",
                    quietMillis: 50,
                    delay: 200,
                    data: function (params) {
                        return {
                            term: params.term,
                            user_type: "organization_staff",
                            organization_id: organization_id,
                            organization_site_id: organization_site_id
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function(obj, index) {
                                return {
                                    id: obj.id,
                                    text: obj.full_name + ' (' + obj.email + ')',
                                }
                            })
                        };
                    }
                }
            }); // end of site consultant ajax fetch

            // start of backup consultant fetch live ajax
            $('#backup_consultant').select2({
                placeholder: '{{ trans("admin/main.select_backup_consultant") }}',
                allowClear: false,
                minimumInputLength: 2,
                tags: null,
                ajax: {
                    url: "{{ route('admin.organizations.query') }}",
                    dataType: 'json',
                    type: "GET",
                    quietMillis: 50,
                    delay: 200,
                    data: function (params) {
                        return {
                            term: params.term,
                            user_type: "organization_staff",
                            organization_id: old_organization_id,
                            organization_site_id: old_organization_site_id
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function(obj, index) {
                                return {
                                    id: obj.id,
                                    text: obj.full_name + ' (' + obj.email + ')',
                                }
                            })
                        };
                    }
                }
            }); // end of backup consultant ajax fetch


            function checkBackupConsultant() {
                if(user_role_name == "organization_staff") {
                    $('#backup_consultant').val(null).trigger('change');
                    if(old_organization_id != organization_id || old_organization_site_id != organization_site_id) {
                        $('#backup_consultant').parent().show();
                        $('#backup_consultant').prop('required', true);
                    } else {
                        $('#backup_consultant').parent().hide();
                        $('#backup_consultant').prop('required', false);
                    }
                } else {
                    $('#backup_consultant').parent().hide();
                    $('#backup_consultant').prop('required', false);
                }
            }


        });
    </script>
    <script src="/assets/default/js/admin/user_edit.min.js"></script>
@endpush
