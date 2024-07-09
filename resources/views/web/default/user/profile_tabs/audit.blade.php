@if(!empty($auditTrails) and $auditTrails->count() > 0)
    <div class="mt-40">
        <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('public.audit_trail') }}</h3>
    </div>

    <div class="mt-40" id="AttendanceRecords">
        <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col" class="text-left">#</th>
                <th scope="col" class="text-left">{{ trans('public.activity') }}</th>
                <th scope="col" class="text-left">{{ trans('public.date_time') }}</th>
              </tr>
            </thead>
            <tbody>
        @php
            $count = 1;
        @endphp
        @foreach($auditTrails as $auditTrail)
            <tr>
                <td>{{ $count }}</td>
                <td>{!! isset($auditTrail->description) ? trim($auditTrail->description) : "" !!}</td>
                <td>{{ isset($auditTrail->created_at) ? date('d F, Y \a\t h:i a T', strtotime($auditTrail->created_at)) : "" }}</td>
            </tr>
            @php $count++; @endphp
        @endforeach
            </tbody>
        </table>
    </div>
@else

    <div class="mt-40">
        <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('public.no_audit_trail_for_user') }}</h3>
    </div>

@endif
