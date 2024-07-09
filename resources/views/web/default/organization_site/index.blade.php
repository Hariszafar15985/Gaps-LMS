@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')

@endpush

@section('content')
{{-- <section class="mt-35">
    <h2 class="section-title">{{ trans('panel.filter_sub_managers') }}</h2>
    @include('web.default.panel.manage.filters')
</section> --}}
    <section class="mt-25">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('admin/main.manage_organization_sites') }}</h2>

            {{-- <form action="" method="get">
                <div class="d-flex align-items-center flex-row-reverse flex-md-row justify-content-start justify-content-md-center mt-20 mt-md-0">
                    <label class="cursor-pointer mb-0 mr-10 font-weight-500 font-14 text-gray" for="conductedSwitch">{{ trans('panel.only_not_conducted_webinars') }}</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="not_conducted" @if(request()->get('not_conducted','') == 'on') checked @endif class="custom-control-input" id="conductedSwitch">
                        <label class="custom-control-label" for="conductedSwitch"></label>
                    </div>
                </div>
            </form> --}}
        </div>

        @if(!empty($organizationSites) and !$organizationSites->isEmpty())
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col" class="text-left">#</th>
                        <th scope="col" class="text-left">{{trans('admin/main.organization_site_name')}}</th>
                        <th scope="col" class="text-left">{{trans('admin/main.organization')}}</th>
                        <th scope="col" class="text-left">{{trans('admin/main.actions')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($organizationSites as $organizationSite)
                        <tr>
                            <td>{{$organizationSite->id}}</td>
                            <td>{{$organizationSite->name}}</td>
                            <td>{{$organizationSite->organization->full_name}}</td>
                            <td>
                                <div class="btn-group dropdown table-actions">
                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i data-feather="more-vertical" height="20"></i>
                                    </button>
                                    <div class="dropdown-menu ">
                                        <a href="{{route('panel.get.edit.organizationSite', ['id' => $organizationSite->id])}}" class="webinar-actions d-block mt-10">{{ trans('public.edit') }}</a>
                                        @if(auth()->user()->isAdmin())
                                        <a href="{{ route('panel.delete.organizationSite', ['id' => $organizationSite->id]) }}" class="webinar-actions d-block mt-10 text-danger delete-action">{{ trans('public.delete') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                @endforeach

                </tbody>
            </table>

            <div class="my-30">
                {{ $organizationSites->links('vendor.pagination.panel') }}
            </div>

        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'webinar.png',
                'title' => trans('public.no_organization_sites_defined'),
                'hint' =>  trans('public.define_organization_site_first') ,
                'btn' => ['url' => $authUser->isAdmin() ? route("admin.get.new.organizationSite") : route("panel.get.new.organizationSite"),'text' => trans('public.create_organization_site') ]
            ])
        @endif

    </section>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script>
        var undefinedActiveSessionLang = '{{ trans('webinars.undefined_active_session') }}';
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
    </script>

    <script src="/assets/default/js/panel/make_next_session.min.js"></script>
@endpush
