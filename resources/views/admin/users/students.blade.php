@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.students') }} {{ trans('admin/main.list') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a>{{ trans('admin/main.students') }}</a></div>
                <div class="breadcrumb-item"><a href="#">{{ trans('admin/main.users_list') }}</a></div>
            </div>
        </div>
    </section>

    <div class="section-body">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ trans('admin/main.total_students') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalStudents }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-briefcase"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ trans('admin/main.organizations_students') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalOrganizationsStudents }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-info-circle"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ trans('admin/main.inactive_students') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $inactiveStudents }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-ban"></i></div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ trans('admin/main.ban_students') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $banStudents }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="card">
            <div class="card-body">
                <form method="get" class="mb-0">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.search') }}</label>
                                <input name="full_name" type="text" class="form-control" value="{{ request()->get('full_name') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                <div class="input-group">
                                    <input type="date" id="from" class="text-center form-control" name="from" value="{{ request()->get('from') }}" placeholder="Start Date">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                <div class="input-group">
                                    <input type="date" id="to" class="text-center form-control" name="to" value="{{ request()->get('to') }}" placeholder="End Date">
                                </div>
                            </div>
                        </div>


                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.filters') }}</label>
                                <select name="sort" data-plugin-selectTwo class="form-control populate">
                                    <option value="">{{ trans('admin/main.filter_type') }}</option>
                                    <option value="purchased_classes_asc" @if(request()->get('sort') == 'purchased_classes_asc') selected @endif>{{ trans('admin/main.purchased_classes_ascending') }}</option>
                                    <option value="purchased_classes_desc" @if(request()->get('sort') == 'purchased_classes_desc') selected @endif>{{ trans('admin/main.purchased_classes_descending') }}</option>

                                    <option value="purchased_classes_amount_asc" @if(request()->get('sort') == 'purchased_classes_amount_asc') selected @endif>{{ trans('admin/main.purchased_classes_amount_ascending') }}</option>
                                    <option value="purchased_classes_amount_desc" @if(request()->get('sort') == 'purchased_classes_amount_desc') selected @endif>{{ trans('admin/main.purchased_classes_amount_descending') }}</option>


                                    <option value="purchased_appointments_asc" @if(request()->get('sort') == 'purchased_appointments_asc') selected @endif>{{ trans('admin/main.purchased_appointments_ascending') }}</option>
                                    <option value="purchased_appointments_desc" @if(request()->get('sort') == 'purchased_appointments_desc') selected @endif>{{ trans('admin/main.purchased_appointments_descending') }}</option>

                                    <option value="purchased_appointments_amount_asc" @if(request()->get('sort') == 'purchased_appointments_amount_asc') selected @endif>{{ trans('admin/main.purchased_appointments_amount_ascending') }}</option>
                                    <option value="purchased_appointments_amount_desc" @if(request()->get('sort') == 'purchased_appointments_amount_desc') selected @endif>{{ trans('admin/main.purchased_appointments_amount_descending') }}</option>

                                    <option value="register_asc" @if(request()->get('sort') == 'register_asc') selected @endif>{{ trans('admin/main.register_date_ascending') }}</option>
                                    <option value="register_desc" @if(request()->get('sort') == 'register_desc') selected @endif>{{ trans('admin/main.register_date_descending') }}</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.organization') }}</label>
                                <select name="organization_id" data-plugin-selectTwo class="form-control populate">
                                    <option value="">{{ trans('admin/main.select_organization') }}</option>
                                    @foreach($organizations as $organization)
                                        <option value="{{ $organization->id }}" @if(request()->get('organization_id') == $organization->id) selected @endif>{{ $organization->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.users_group') }}</label>
                                <select name="group_id" data-plugin-selectTwo class="form-control populate">
                                    <option value="">{{ trans('admin/main.select_users_group') }}</option>
                                    @foreach($userGroups as $userGroup)
                                        <option value="{{ $userGroup->id }}" @if(request()->get('group_id') == $userGroup->id) selected @endif>{{ $userGroup->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.status') }}</label>
                                <select name="status" data-plugin-selectTwo class="form-control populate">
                                    <option value="">{{ trans('admin/main.all_status') }}</option>
                                    <option value="active_verified" @if(request()->get('status') == 'active_verified') selected @endif>{{ trans('admin/main.active_verified') }}</option>
                                    <option value="active_notVerified" @if(request()->get('status') == 'active_notVerified') selected @endif>{{ trans('admin/main.active_not_verified') }}</option>
                                    <option value="inactive" @if(request()->get('status') == 'inactive') selected @endif>{{ trans('admin/main.inactive') }}</option>
                                    <option value="ban" @if(request()->get('status') == 'ban') selected @endif>{{ trans('admin/main.banned') }}</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-3">
                            <div class="form-group mt-1">
                                <label class="input-label mb-4"> </label>
                                <input type="submit" class="text-center btn btn-primary w-100" value="{{ trans('admin/main.show_results') }}">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="card">
        <div class="card-header">
            @can('admin_users_export_excel')
                <a href="/admin/students/excel?{{ http_build_query(request()->all()) }}" class="btn btn-primary">{{ trans('admin/main.export_xls') }}</a>
            @endcan
            <div class="h-10"></div>
        </div>

        <div class="card-body">
            <div class="table-responsive text-center">
                <table class="table table-striped font-14">
                    <tr>
                        <th>ID</th>
                        <th>{{ trans('admin/main.name') }}</th>
                        <th>{{ trans('admin/main.courses') }}</th>
                        <th>{{ trans('admin/main.progress') }}</th>

                        <th>{{ trans('admin/main.student_progress') }}</th>
                        <th>{{ trans('admin/main.expected_progress') }}</th>

                        <th>{{ trans('admin/main.appointments') }}</th>
                        <th>{{ trans('admin/main.amount') }}</th>
                        <th>{{ trans('admin/main.organization_site') }}</th>
                        <th>{{ trans('admin/main.referred_by') }}</th>
                        <th>{{ trans('admin/main.register_date') }}</th>
                        <th>{{ trans('admin/main.status') }}</th>
                        <th width="120">{{ trans('admin/main.actions') }}</th>
                    </tr>

                    @foreach($users as $user)

                        <tr>
                            <td>{{ $user->id }}</td>
                            <td class="text-left">
                                <div class="d-flex align-items-center">
                                    <figure class="avatar mr-2">
                                        <img src="{{ $user->getAvatar() }}" alt="{{ $user->full_name }}">
                                    </figure>
                                    <div class="media-body ml-1">
                                        <div class="mt-0 mb-1 font-weight-bold">{{ $user->full_name }}</div>

                                        @if($user->mobile)
                                            <div class="text-primary text-small font-600-bold">{{ $user->mobile }}</div>
                                        @endif

                                        @if($user->email)
                                            <div class="text-primary text-small font-600-bold">{{ $user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="media-body">
                                    <div class="text-primary mt-0 mb-1 font-weight-bold">{{ $user->classesPurchasedsCount }}</div>
                                    {{-- <div class="text-small font-600-bold">{{ $currency }}{{ number_format($user->classesPurchasedsSum, 2, ".", "")+0 }}</div> --}}
                                </div>
                            </td>
                            @php
                                /* $behindProgress = false;
                                if (isset($user->classesPurchased) && count($user->classesPurchased)) {
                                    foreach ($user->classesPurchased as $classPurchased) {
                                        if ($classPurchased->webinar->getProgress($user->id) < $classPurchased->webinar->getExpectedProgress($user->id)) {
                                            $behindProgress = true;
                                        }
                                    }
                                } */
                                $classesPurchased = $user->getPurchasedCourses();
                                if (isset($classesPurchased) && count($classesPurchased)) {
                                    $behindProgress = $user->isBehindProgress();
                                    $studentProgress = 0;
                                    $expectedProgress = 0;
                                    foreach ($classesPurchased as $classPurchased) {
                                        $studentProgress = $studentProgress + $classPurchased->webinar->getProgress($user->id);
                                        $expectedProgress = $expectedProgress + $classPurchased->webinar->getExpectedProgress($user->id);
                                    }

                                    $studentProgress = round($studentProgress / count($classesPurchased), 2);
                                    $expectedProgress = round($expectedProgress / count($classesPurchased), 2);
                                }
                            @endphp
                            <td>
                                <div class="media-body">
                                    @if (isset($user->classesPurchasedsCount) && $user->classesPurchasedsCount)
                                        <div class="{{(isset($behindProgress) && $behindProgress) ? "text-danger" : "text-primary"}} mt-0 mb-1 font-weight-bold">
                                            {{ (isset($behindProgress) && $behindProgress) ? trans("admin/main.behind_progress") : trans("admin/main.on_schedule") }}
                                        </div>
                                    @else
                                        <div class="text-primary mt-0 mb-1 font-weight-bold">
                                            -
                                        </div>
                                    @endif
                                    {{-- <div class="text-small font-600-bold">{{ $currency }}{{ number_format($user->classesPurchasedsSum, 2, ".", "")+0 }}</div> --}}
                                </div>
                            </td>

                            <td>
                                <div class="media-body">
                                    {{ isset($studentProgress) ? $studentProgress . "%" : 0 . "%" }}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{ isset($expectedProgress) ? $expectedProgress . "%" : 0 . "%" }}
                                </div>
                            </td>

                            <td>
                                <div class="media-body">
                                    <div class="text-primary mt-0 mb-1 font-weight-bold">{{ $user->meetingsPurchasedsCount }}</div>
                                    {{-- <div class="text-small font-600-bold">{{ $currency }}{{ number_format($user->meetingsPurchasedsSum, 2, ".", "")+0 }}</div> --}}
                                </div>
                            </td>

                            <td>{{ $currency }}{{ (number_format($user->classesPurchasedsSum, 2, ".", "")+0) + (number_format($user->meetingsPurchasedsSum, 2, ".", "")+0) }}</td>

                            <td>
                                @php
                                    $userorganizationSites = $user->organizationSites;
                                    // $organizationSites =  $this->organizationSites;
                                    $organizationSites =  $userorganizationSites;
                                    $organizationSitesArray = [];
                                    foreach($organizationSites as $organizationSite) {
                                        $organizationSitesArray[] = $organizationSite->name;
                                    }
                                @endphp
                                @if (!empty($organizationSitesArray) && is_array($organizationSitesArray))
                                    {{ implode(',', $organizationSitesArray) }}
                                @endif
                                {{-- {{ !empty($user->organization_site) ? $user->organization_site : '' }} --}}
                            </td>

                            <td>
                                @php
                                    $referrer = (!empty($user->manager_id) && !empty($user->manager->full_name)) ? $user->manager->full_name : "";
                                    if (!empty(trim($referrer))) {
                                        $referrer .= "<br />";
                                    }
                                    $referrer .= (!empty($user->organ_id) && !empty($user->organization->full_name)) ?
                                            "<strong>(" . $user->organization->full_name . ")</strong>" : "";
                                    $referrer = trim($referrer);
                                @endphp
                                {!! (!empty($referrer)) ? $referrer : "" !!}
                            </td>
                            <td>{{ dateTimeFormat($user->created_at, 'Y/m/j - H:i') }}</td>

                            <td>
                                @if($user->ban and !empty($user->ban_end_at) and $user->ban_end_at > time())
                                    <div class="mt-0 mb-1 font-weight-bold text-danger">{{ trans('admin/main.ban') }}</div>
                                    <div class="text-small font-600-bold">Until {{ dateTimeFormat($user->ban_end_at, 'Y/m/j') }}</div>
                                @else
                                    <div class="mt-0 mb-1 font-weight-bold {{ ($user->status == 'active') ? 'text-success' : 'text-warning' }}">{{ trans('admin/main.'.$user->status) }}</div>
                                @endif
                            </td>

                            <td class="text-center mb-2" width="120">
                                <a href="/admin/students/{{ $user->id }}/enrol" target="_blank" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="Enrol">
                                    <i class="fas fa-user-plus"></i>
                                </a>
                                <a href="/users/{{ $user->id }}/profile" target="_blank" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="Student Profile">
                                    <i class="fa fa-user-shield"></i>
                                </a>
                                @can('admin_users_impersonate')
                                    <a href="/admin/users/{{ $user->id }}/impersonate" target="_blank" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.login') }}">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </a>
                                @endcan

                                @can('admin_users_edit')
                                    <a href="/admin/users/{{ $user->id }}/edit" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan

                                @can('admin_users_delete')
                                    @include('admin.includes.delete_button',['url' => '/admin/users/'.$user->id.'/delete' , 'btnClass' => ''])
                                @endcan
                            </td>

                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class="card-footer text-center">
            {{ $users->links() }}
        </div>
    </div>


    <section class="card">
        <div class="card-body">
            <div class="section-title ml-0 mt-0 mb-3"><h5>{{trans('admin/main.hints')}}</h5></div>
            <div class="row">
                <div class="col-md-4">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.students_hint_title_1')}}</div>
                        <div class=" text-small font-600-bold">{{trans('admin/main.students_hint_description_1')}}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.students_hint_title_2')}}</div>
                        <div class=" text-small font-600-bold">{{trans('admin/main.students_hint_description_2')}}</div>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.students_hint_title_3')}}</div>
                        <div class="text-small font-600-bold">{{trans('admin/main.students_hint_description_3')}}</div>
                    </div>
                </div>


            </div>
        </div>
    </section>
@endsection
