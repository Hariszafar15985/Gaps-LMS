@if(!empty($user->attendance) and $user->attendance->count() > 0)
    @php
        $lastDate = date('Y-m-d', strtotime($attendance['endDate']->check_in_time));
        $currentDate = date('Y-m-d', strtotime($attendance['beginDate']->check_in_time));
    @endphp
    <div class="mt-40">
        <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('public.attendance') }}</h3>
    </div>

    <div class="mt-40" id="AttendanceRecords">
        <table class="table table-striped">
            <thead>
              <tr>
                <th scope="col" class="text-left">{{ trans('public.date') }}</th>
                <th scope="col" class="text-left">{{ trans('public.activity') }}</th>
              </tr>
            </thead>
            <tbody>
        
        @while(strtotime($currentDate) <= strtotime($lastDate))
            <tr>
                <td>{{ date('d F, Y', strtotime($currentDate)) }}</td>
                <td>
                    @php
                        $datePresent = null;
                        //TODO:: To be improved in future
                        $datePresent = $user->attendance->filter(function($record) use($currentDate) {
                            return date('Y-m-d', strtotime($record->check_in_time)) === date('Y-m-d', strtotime($currentDate));
                        });
                    @endphp
                    @if(isset($datePresent) && $datePresent->count()) 
                        {{ trans('public.user_logged_in') }}
                    @else
                        {{ trans('public.no_activity') }}
                    @endif
                </td>
                
            </tr>
            @php
                $nextDateStr = $currentDate . '+1 day';
                $currentDate = date('Y-m-d', strtotime($nextDateStr));
            @endphp
        @endwhile
            </tbody>
        </table>
    </div>
@else

    <div class="mt-40">
        <h3 class="font-16 font-weight-bold text-dark-blue">{{ trans('public.no_attendance_records_for_user') }}</h3>
    </div>

@endif
