@extends('admin.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
        </div>

        <div class="section-body">
            @if($errors->any())
            <h4 style="color: #880000">*{{$errors->first()}}</h4>
            @endif


            <div class="row">
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="card mt-4">
                        <div class="card-body">
                            <form action="{{ $formAction }}"
                                  method="Post">
                                {{ csrf_field() }}
                                @if (isset($organizationSite->id) && (int)$organizationSite->id > 0)
                                    <input type="hidden" id="id" name="id" value="{{(int)$organizationSite->id}}" />
                                @endif
                                
                                <div class="form-group">
                                    <label>{{ trans('/admin/main.organization_site_name') }}</label>
                                    <input type="text" class="form-control" name="name" id="name" 
                                        value="{{isset($organizationSite->name) ? $organizationSite->name : ''}}" required />
                                </div>
                                
                                @if ($authUser->isAdmin())
                                    <div class="form-group">
                                        <label>{{ trans('/admin/main.organization') }}</label>
                                        <select class="form-control @error('category_id') is-invalid @enderror" required id="organization" name="organ_id">
                                            {{-- fetch live form database --}}
                                            
                                            {{-- @if (isset($organizations) && $organizations->count() )
                                                <option {{ !empty($organizationSite->organ_id) ? '' : 'selected' }} disabled>{{ trans('admin/main.choose_organization') }}</option>

                                                @foreach($organizations as $organization)
                                                    <option value="{{ $organization->id }}" @if(!empty($organizationSite->organ_id) and $organizationSite->organ_id == $organization->id) selected="selected" @endif>{{ $organization->full_name }}</option>
                                                @endforeach
                                            @else
                                                <option disabled readonly>{{ trans('public.define_organization_first') }}</option>
                                            @endif --}}
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="organ_id" id="organ_id" value="{{ $authUser->id }}" />
                                @endif

                                <div class="text-right mt-4">
                                    <button 
                                    @if (($authUser->isAdmin() && isset($organizations) && $organizations->count()) || $authUser->role_name === \App\Models\Role::$organization)
                                        class="btn btn-primary"
                                    @else
                                        class="btn btn-muted disabled" disabled
                                    @endif
                                    >{{ trans('admin/main.submit') }}</button>
                                </div>
                            </form>
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
            $('#organization').select2({
                placeholder: '{{ trans("admin/main.select_organization") }}',
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
            }); // end of organization ajax fetch
        });
    </script>
@endpush
