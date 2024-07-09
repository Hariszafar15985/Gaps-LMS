<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\UserBreak;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserBreaksController extends Controller
{
    public $refererIsProfile = false;
    public $referer = '';
    
    public function __construct()
    {
        $this->referer = request()->headers->get('referer');
        if (strpos($this->referer, 'users') !== false &&  strpos($this->referer, 'profile') !== false) {
            $userBreakTabParam = '?tab=userBreaks'; 
            if (strpos($this->referer, $userBreakTabParam) === false) {
                $this->referer .= $userBreakTabParam;
            }
            $this->refererIsProfile = true;
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        //
        $query = UserBreak::with([
            'user' => function ($query) {
                $query->with(['organization', 'organizationSites']);
            },
            'requestedBy'
        ]);
        
        if ($request->route()->getName() === 'admin.breakRequests.pending') {
            $query = $query->where('status', strtolower(UserBreak::$status['pending']));
        }
        $userBreaks = $query->orderBy('created_at', 'ASC')->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.break_requests'),
            'mainHeading' => trans('admin/main.break_requests'),
            'userBreaks' => $userBreaks,
        ];

        return view('admin.user_breaks.students', $data);

    }

    /**
     * Update the status of the User Break Request.
     *
     * @param  int  $id 
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $message = trans('admin/main.breakRequestUpdateFailed');
        if (!empty($id) && $id > 0) {
            $break = UserBreak::where([
                'status'    => strtolower(UserBreak::$status['pending']),
                'id'        => $id
            ])->first();
            if (!empty($break) && isset($break->id)) {
                if ($request->route()->getName() === 'admin.breakRequest.approve') {
                    $break->status = UserBreak::$status['approved'];
                    $message = trans('admin/main.breakRequestApproved');
                    $auditMessage = "Break Request Approved (by " . auth()->user()->full_name . ")";
                } else if ($request->route()->getName() === 'admin.breakRequest.reject') {
                    $break->status = UserBreak::$status['rejected'];
                    $message = trans('admin/main.breakRequestRejected');
                    $auditMessage = "Break Request Rejected (by " . auth()->user()->full_name . ")";
                }
                if ($break->isDirty('status')) {
                    $break->approved_by = auth()->user()->id;
                    if ($break->save()) {
                        //redirect back with success
                        if ($this->refererIsProfile) {
                            //audit break request status
                            $auditedUser = User::find($break->user_id);
                            $audit = new AuditTrail();
                            $audit->user_id = $auditedUser->id;
                            $audit->organ_id = $auditedUser->organ_id;
                            $audit->role_name = $auditedUser->role_name;
                            $audit->audit_type = AuditTrail::auditType['user_break_status'];
                            $audit->added_by = $break->approved_by;
                            $audit->description = $auditMessage;
                            $ip = null;
                            $ip = getClientIp();
                            $audit->ip = ip2long($ip);
                            $audit->save();
                            return redirect($this->referer)->with('breakSuccess', $message);
                        }
                        return redirect()->back()->with('success', $message);
                    }
                }
            }
        }
        //redirect back with error
        if ($this->refererIsProfile) {
            return redirect($this->referer)->with('breakError', $message);
        }
        return redirect()->back()->with('error', $message);
    }

    /**
     * Destory the specified resource.
     *
     * @param  int  $id
     * @return RedirectResponse|\Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $message = trans('admin/main.breakRequestDeletedFailed');
        if (!empty($id) && $id > 0) {
            $break = UserBreak::find($id);
            if (!empty($break) && isset($break->id)) {
                if ($break->delete()) {
                    //redirect back with success
                    $message = trans('admin/main.breakRequestDeleted');
                    if ($this->refererIsProfile) {
                        return redirect($this->referer)->with('breakSuccess', $message);
                    }
                    return redirect()->back()->with('success', $message);
                }
            }
        }
        //redirect back with error
        if ($this->refererIsProfile) {
            return redirect($this->referer)->with('breakError', $message);
        }
        return redirect()->back()->withErrors('error', $message);
    }

    /**
     * Store a new break request record in the system
     * 
     * Note: Break Request created by admin is stored directly with an approved status
     *
     * @param Request $request
     * @return RedirectResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $message = trans('panel.break_request_added_failure');
        $user_id = $request->get('user_id');
        if(!empty($user_id)) {
            $user = User::select('users.*')->where('users.id', $user_id)
                    ->with('occupiedBreaks')->first();

            if (isset($user) && $user->id > 0) {
                //fetch existing occupied breaks
                $occupiedBreaks = $user->occupiedBreaks;
                //check if submitted date range clashes with existing occupied break ranges
                $clashingDates = false;
                $fromDate = Carbon::createFromFormat('Y-m-d', $request->get('from'));
                $toDate = Carbon::createFromFormat('Y-m-d', $request->get('to'));
                foreach($occupiedBreaks as $break) {
                    $breakFrom = Carbon::createFromFormat('Y-m-d', $break->from);
                    $breakTo = Carbon::createFromFormat('Y-m-d', $break->to);
                    if(
                        (
                            ($fromDate >= $breakFrom && $fromDate <= $breakTo)
                            || ($toDate >= $breakFrom && $toDate <= $breakTo)
                        ) || (
                            ($breakFrom >= $fromDate && $breakFrom <= $toDate)
                            || ($breakTo >= $fromDate && $breakTo <= $toDate)
                        )
                    ) {
                        $clashingDates = true;
                        break;
                    }
                }

                if ($clashingDates) {
                    $message = trans('panel.clashing_dates_please_reselect');
                    if ($this->refererIsProfile) {
                        return redirect($this->referer)->with('breakError', $message);
                    }
                    return redirect()->back()->withInput($request->input())
                        ->with('breakError', $message);
                } else {
                    //Commit record to database
                    $fromDate = Carbon::createFromFormat('Y-m-d', $request->get('from'));
                    $toDate = Carbon::createFromFormat('Y-m-d', $request->get('to'));
                    $userBreak = new UserBreak();
                    $userBreak->from = $fromDate->format('Y-m-d');
                    $userBreak->to = $toDate->format('Y-m-d');
                    $userBreak->user_id = $user->id;
                    $adminUser = auth()->user();
                    $userBreak->requested_by = $adminUser->id;
                    $userBreak->status = strtolower(UserBreak::$status['approved']);
                    $userBreak->type = (in_array($request->get('type'), UserBreak::$breakTypes)) ? strtolower($request->get('type')) : strtolower(UserBreak::$breakTypes['other']);
                    
                    if ($userBreak->save()) {
                        $message = trans('panel.break_request_added_success');
                        if ($this->refererIsProfile) {
                            return redirect($this->referer)->with('breakSuccess', $message);
                        }
                        return redirect()->back()->with('success', $message);
                    }
                }
            }
        }
        if ($this->refererIsProfile) {
            return redirect($this->referer)->with('breakError', $message);
        }
        return redirect()->back()->with('BreakError', $message);
    }
}
