@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.user_new_consultant_page_title') }}</h1>
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
                                                    @if($role->name == 'organization_staff')
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

                                        {{-- Organization and Site --}}
                                        <div id="organization_fields">
                                            <div class="form-group">
                                                <label>Organization</label>
                                                <select class="form-control @error('organization') is-invalid @enderror" required id="organization" name="organization">
                                                    {{-- fetch live form the db --}}
                                                </select>
                                                @error('organization')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label>Organization Site</label>
                                                <select class="form-control @error('organization_site') is-invalid @enderror" required id="organization_site" name="organization_site[]"  multiple="multiple">
                                                    {{-- fetch live form the db --}}
                                                </select>
                                                @error('organization_site')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
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
            var organization_id = 0;
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
            }); // end of organization ajax fetch

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
            }); // end of organization site ajax fetch
        });
    </script>
@endpush

