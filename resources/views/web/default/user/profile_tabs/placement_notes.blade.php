@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <style>
        #placementNoteRecords th, #placementNoteRecords td {
            text-align: center;
        }
    </style>
    
@endpush

@if(session()->has('breakSuccess'))
    <div class="container-fluid">
        <div class="row">
            <div class="col col-12 alert alert-success">
                {{ session()->get('breakSuccess') }}
            </div>
        </div>
    </div>
@endif

@if(session()->has('breakError'))
    <div class="container-fluid">
        <div class="row">
            <div class="col col-12 alert alert-warning">
                {{ session()->get('breakError') }}
            </div>
        </div>
    </div>
@endif

<section class="mb-40">
    @if(!empty($user) and !empty($user->placementNotes) and $user->placementNotes->count() > 0)
        <div class="mt-40">
            <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('public.placement_notes') }}</h3>
        </div>

        <div class="mt-40" id="placementNoteRecords">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ trans('public.company_name') }}</th>
                        <th>{{ trans('public.abn') }}</th>
                        <th>{{ trans('public.employer_address') }}</th>
                        <th>{{ trans('public.contact_person') }}</th>
                        <th>{{ trans('public.phone') }}</th>
                        <th>{{ trans('public.employment_type') }}</th>
                        <th>{{ trans('public.pay_rate') }}</th>
                        <th>{{ trans('public.hours_per_week') }}</th>
                        <th>{{ trans('public.date_added') }}</th>
                    </tr>
                </thead>
                <tbody>
            
                    @foreach($user->placementNotes as $placemenNote)
                        <tr>
                            <td>
                                <div class="media-body">
                                    {{ $placemenNote->company_name }}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{ $placemenNote->abn }}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{$placemenNote->employer_address}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{$placemenNote->contact_person}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{$placemenNote->phone}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{$placemenNote->employment_type}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{$placemenNote->pay_rate}} AUD
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{$placemenNote->hours_per_week}}
                                </div>
                            </td>
                            <td>
                                <div class="media-body">
                                    {{ $placemenNote->created_at }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="mt-40">
            <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('public.no_pn_records_for_user') }}</h3>
        </div>

    @endif
</section>

<h3 class="font-16 font-weight-bold text-dark-blue mt-40">{{ trans('public.create_placement_notes') }}</h3>

    @if (session('error'))
        <section class="mt-20">
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert  alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
                </div>
            </div>
        </section>
    @endif
    
    @if(!empty($user) and !empty($user->id) and $user->id > 0)
        <form action="{{ route('admin.placementNotes.save') }}" method="post" id="placementRequestForm">
        
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">

            <div class="row mt-40">
                <div class="col-md-6">
                    <div class="form-group">
                        <h4>{{ trans('public.company_name') }}:</h4>
                        <input type="text" name="comapny_name" class="comapny_name form-control" />
                        @error('type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <h4>{{ trans('public.abn') }}:</h4>
                        <input type="number" name="abn" class="abn form-control" />
                        @error('type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-40">
                <div class="col-md-6">
                    <div class="form-group">
                        <h4>{{ trans('public.employer_address') }}:</h4>
                        <input type="text" name="employer_address" class="addremployer_address form-control" />
                        @error('type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <h4>{{ trans('public.contact_person') }}:</h4>
                        <input type="text" name="contact_person" class="contact_person form-control" />
                        @error('type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-40">
                <div class="col-md-6">
                    <div class="form-group">
                        <h4>{{ trans('public.phone') }}:</h4>
                        <input type="number" name="phone" class="phone form-control" />
                        @error('type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <h4>{{ trans('public.employment_type') }}:</h4>
                        <select class="form-control" id="employment_type" name="employment_type">
                            @foreach (\App\PlacementNotes::$placementType as $pType)
                                <option value="{{ $pType }}">{{ $pType }}</option>
                            @endforeach
                        </select>
                        @error('type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-40">
                <div class="col-md-6">
                    <div class="form-group">
                        <h4>{{ trans('public.pay_rate') }}:</h4>
                        <input type="number" name="pay_rate" step="0.01" class="pay_rate form-control" />
                        @error('type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <h4>{{ trans('public.hours_per_week') }}:</h4>
                        <input type="number" name="hours_per_week" step="0.01" class="hours_per_week form-control" />
                        @error('type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            </div>


        

            <button type="submit" class="btn btn-sm btn-primary mt-30">{{ trans('panel.create_request') }}</button>
            
        </form>

    @else

        @include(getTemplate() . '.includes.no-result',[
        'file_name' => 'meet.png',
        'title' => trans('panel.verify_student_reference'),
        'hint' => '',
        ])

    @endif


@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
@endpush