@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@push("styles_top")
    <style>
        .can_set_visibility {
            cursor: pointer;
            display: none;
        }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{!empty($user) ?trans('/admin/main.edit'): trans('admin/main.new') }} {{ trans('admin/main.user') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/admin/">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item"><a>{{ trans('admin/main.users') }}</a>
                </div>
                <div class="breadcrumb-item">{{!empty($user) ?trans('/admin/main.edit'): trans('admin/main.new') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 ">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-6 col-lg-6">
                                    <form action="/admin/users/store" method="Post">
                                        {{ csrf_field() }}

                                        <div class="form-group">
                                            <label>{{ trans('/admin/main.full_name') }}</label>
                                            <input type="text" name="full_name"
                                                   class="form-control  @error('full_name') is-invalid @enderror"
                                                   value="{{ old('full_name') }}"
                                                   placeholder="{{ trans('admin/main.create_field_full_name_placeholder') }}"/>
                                            @error('full_name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="username">{{ trans('auth.email_or_mobile') }}:</label>
                                            <input name="username" type="text" class="form-control @error('email') is-invalid @enderror @error('mobile') is-invalid @enderror" id="username" value="{{ old('email') }}" aria-describedby="emailHelp">
                                            @error('email')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                            @error('mobile')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label">{{ trans('admin/main.password') }}</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span type="button" class="input-group-text">
                                                        <i class="fa fa-lock"></i>
                                                    </span>
                                                </div>
                                                <input type="password" name="password"
                                                       class="form-control @error('password') is-invalid @enderror"/>
                                                @error('password')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>{{ trans('/admin/main.role_name') }}</label>
                                            <select class="form-control select2 @error('role_id') is-invalid @enderror" id="roleId" name="role_id">
                                                <option disabled selected>{{ trans('admin/main.select_role') }}</option>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}" {{ old('role_id') === $role->id ? 'selected' :''}}>{{ $role->name }} - {{ $role->caption }}</option>
                                                @endforeach
                                            </select>
                                            @error('role_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group" id="groupSelect">
                                            <label class="input-label d-block">{{ trans('admin/main.group') }}</label>
                                            <select name="group_id" class="form-control select2 @error('group_id') is-invalid @enderror">
                                                <option value="" selected disabled></option>

                                                @foreach($userGroups as $userGroup)
                                                    <option value="{{ $userGroup->id }}" @if(!empty($notification) and !empty($notification->group) and $notification->group->id == $userGroup->id) selected @endif>{{ $userGroup->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">@error('group_id') {{ $message }} @enderror</div>
                                        </div>

                                        <div id="organizationFields" class="organizationFields">
                                            <div class="form-group">
                                                <label>Organization</label>
                                                <select class="form-control select2 @error('organization') is-invalid @enderror" id="organization" name="organization"
                                                data-url="{{ route('admin.get.organization.sites') }}"
                                                >
                                                    <option value="" disabled selected>Select Organization</option>
                                                    @foreach ($organization as $org)
                                                    <option value="{{$org->id}}" {{ old('organization') === $org->id ? 'selected' :''}}>{{$org->full_name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('organization')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label>Organization Site</label>
                                                <select class="form-control select2 @error('organization_site') is-invalid @enderror" id="organization_site" name="organization_site[]"
                                                    data-url="{{ route('admin.get.organization.site.managers') }}" multiple="multiple"
                                                >
                                                    {{-- <option value="" disabled selected></option> --}}
                                                </select>
                                                @error('organization_site')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>{{ trans('/admin/main.status') }}</label>
                                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                                <option disabled selected>{{ trans('admin/main.select_status') }}</option>
                                                @foreach (\App\User::$statuses as $status)
                                                    <option
                                                        value="{{ $status }}" {{ old('status') === $status ? 'selected' :''}}>{{  $status }}</option>
                                                @endforeach
                                            </select>
                                            @error('status')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div id="student_fields" class="conditionalFields" style="display:none">

                                            <div class="form-group">
                                                <label>Study Schedule</label>
                                                <select class="form-control @error('schedule') is-invalid @enderror" id="schedule" name="schedule">
                                                    <option disabled selected>Select Schedule</option>
                                                    <option value="full_25">Full Time (25 hours per week)</option>
                                                    <option value="part_15">Part Time (15 hours per week)</option>
                                                    <option value="part_8">Part Time (8 hours per week)</option>
                                                    <option value="self">Self Paced</option>
                                                </select>
                                                @error('schedule')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label>Organization Manager</label>
                                                <select class="form-control @error('manager')  is-invalid @enderror" id="manager" name="manager" required data-url="{{route('panel.get.organization.site.managers')}}">
                                                    <option value="-1" disabled selected>Select Site Manager</option>
                                                </select>
                                                @error('manager')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label>Course</label>
                                                <x-course-drop-down :webinar="$webinar" :currency="$currency" :selected="old('course')" > </x-course-drop-down>
                                                <x-course-preview-button />
                                                @error('course')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                        </div>

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
                                                        <option value="{{$contract}}" {{ old('contract') === $contract ? 'selected' :''}} >{{$contract}}</option>
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
                                                <input type="text" class="form-control @error('other_contract') is-invalid @enderror" id="other_contract" name="other_contract" disabled />
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
                                                <input type="checkbox" class="form-control ml-3 @error('po_num_required') is-invalid @enderror" id="po_num_required" name="po_num_required" value="1" style="height:18px;" />
                                                <label class="ml-1" for="po_num_required">{{ trans('panel.yes_no') }}</label>
                                                @error('po_num_required')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>{{trans('/admin/pages/users.po_sequence')}}</label>
                                                <input type="text" class="form-control @error('po_sequence') is-invalid @enderror" id="po_sequence" name="po_sequence" />
                                                @error('po_sequence')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                        </div>

                                        <div class="form-group">
                                            <input type="checkbox" class="can_set_visibility @error('can_set_visibility') is-invalid @enderror" id="can_set_visibility" name="can_set_visibility" />
                                            <label for="can_set_visibility" class="can_set_visibility"> {{ trans("public.can_set_visibillity") }} </label>
                                            @error('can_set_visibility')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="text-right mt-4">
                                            <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                        </div>
                                    </form>
                                </div>
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
                if(this.value == {{ App\Models\Role::getUserRoleId() }}) {
                    $("#student_fields").css("display", "block");
                    $("#student_fields select, #student_fields input, #student_fields textarea").attr('disabled', false);
                } else {
                    $("#student_fields").css("display", "none");
                    $("#student_fields select, #student_fields input, #student_fields textarea").attr('disabled', true);
                }

                if(this.value == {{ App\Models\Role::getOrganizationRoleId() }}) {
                    $("#organizationFields").hide();
                    $(".organization_fields").css("display", "block");
                    $(".organization_fields select, .organization_fields input, .organization_fields textarea").attr('disabled', false);
                } else {
                    $("#organizationFields").show();
                    $(".organization_fields").css("display", "none");
                    $(".organization_fields select, .organization_fields input, .organization_fields textarea").attr('disabled', true);
                }

                if ( this.value == {{ App\Models\Role::getTeacherRoleId() }} ) {
                    $(".can_set_visibility").show();
                } else {
                    $(".can_set_visibility").hide();
                }

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

            $('#organization_site').change( function(event){
                event.preventDefault();
                let requestUrl = $(this).data('url');
                let data = {};
                data['organization_site_id'] = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: requestUrl,
                    dataType: 'json',
                    data: data,
                    success: function (data) {
                        if ( Object.keys(data).length > 0 && data.success) {
                            let managersElem = $('#manager');
                            if (Object.keys(data['managers']).length > 0) {
                                let managers = data['managers'];
                                managersElem.html("");
                                for (id in managers) {
                                    let option = document.createElement('option');
                                    option.setAttribute('value', managers[id]);
                                    option.text = `${id}`;
                                    managersElem.append(option);
                                }
                            } else {
                                managersElem.html("");
                                let option = document.createElement('option');
                                option.text = `{{ trans('panel.no_managers_in_selected_site') }}`;
                                managersElem.append(option);
                            }
                            if (typeof data.heading !== 'undefined' && data.heading.length > 0) {
                                $('#resultsModal .modal-title').html(data.heading);
                            }
                            if (typeof data.html !== 'undefined' && data.html.length > 0) {
                                $('#resultsModal .modal-body').html(data.html);
                            }
                        }
                    },error:function(data){
                        console.log(data);
                    }
                });
            });

            $('select#organization').change( function(event){
                event.preventDefault();
                let requestUrl = $(this).data('url');
                let data = {};
                data['organization'] = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: requestUrl,
                    dataType: 'json',
                    data: data,
                    success: function (data) {
                        if ( Object.keys(data).length > 0 && data.success) {
                            let siteElem = $('#organization_site');
                            if (Object.keys(data['site']).length > 0) {
                                let site = data['site'];
                                siteElem.html("");
                                for (id in site) {
                                    let option = document.createElement('option');
                                    option.setAttribute('value', site[id]);
                                    option.text = `${id}`;
                                    siteElem.append(option);
                                }
                                $('#organization_site').trigger('change');
                            } else {
                                siteElem.html("");
                                let option = document.createElement('option');
                                option.text = `{{ trans('panel.no_site_in_selected_organization') }}`;
                                siteElem.append(option);
                            }
                            if (typeof data.heading !== 'undefined' && data.heading.length > 0) {
                                $('#resultsModal .modal-title').html(data.heading);
                            }
                            if (typeof data.html !== 'undefined' && data.html.length > 0) {
                                $('#resultsModal .modal-body').html(data.html);
                            }
                        }
                    },error:function(data){
                        console.log(data);
                    }
                });
            });

            $('#roleId').trigger('change');
        });
    </script>
@endpush

