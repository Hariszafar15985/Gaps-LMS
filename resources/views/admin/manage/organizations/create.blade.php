@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.user_new_organization_page_title') }}</h1>
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

                                        {{-- name --}}
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

                                        {{-- email or phone --}}
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

                                        {{-- password --}}
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

                                        {{-- role name --}}
                                        <div class="form-group" style="display: none;">
                                            <label>{{ trans('/admin/main.role_name') }}</label>
                                            <select class="form-control select2 @error('role_id') is-invalid @enderror" id="roleId" name="role_id" hidden>
                                                <option disabled selected>{{ trans('admin/main.select_role') }}</option>
                                                @foreach ($roles as $role)
                                                    @if($role->name == 'organization')
                                                        <option value="{{ $role->id }}" selected>{{ $role->name }} - {{ $role->caption }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('role_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        {{-- group --}}
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

                                        {{-- status --}}
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

                                        {{-- contracts --}}
                                        <div id="organization_fields" class="conditionalFields organization_fields" 
                                            style="display: @error('contract') 
                                                    block 
                                                @else 
                                                    @error('other_contract') 
                                                        block 
                                                    @else 
                                                        block
                                                    @enderror 
                                                @enderror">
                                            <div class="form-group">
                                                <label>{{trans('/admin/pages/users.organization_contracts')}}</label>
                                                <select class="form-control @error('contract') is-invalid @enderror" id="contract" name="contract[]" multiple>
                                                    @foreach (config('organization.contracts') as $contract)
                                                        <option value="{{$contract}}" {{ in_array($contract, (old('contract') ? old('contract') : []) ) ? 'selected' :''}} >{{$contract}}</option>
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
                                                <input type="text" class="form-control @error('po_sequence') is-invalid @enderror" id="po_sequence" name="po_sequence" disabled />
                                                @error('po_sequence')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
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
            // $('#status').select2({
            //     placeholder: '{{ trans('admin/main.select_status') }}',
            // });
            
            $('#contract').select2({
                placeholder: '{{ trans('admin/main.select_contracts') }}',
                allowClear: true
            });

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

            $('#contract').change(function() {
                var vals = $(this).val();
                if (vals.indexOf("Other") > -1) {
                    $('#other_contract').removeAttr('disabled');
                    $('#other_contract').attr('required', 'required');
                } else {
                    $('#other_contract').attr('disabled', 'disabled');
                    $('#other_contract').removeAttr('required');
                }
            })
        });
    </script>
@endpush

