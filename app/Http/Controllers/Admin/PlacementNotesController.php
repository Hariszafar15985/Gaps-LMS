<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PlacementNotes;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlacementNotesController extends Controller
{
    public $refererIsProfile = false;
    public $referer = '';
    
    public function __construct()
    {
        $this->referer = request()->headers->get('referer');
        if (strpos($this->referer, 'users') !== false &&  strpos($this->referer, 'profile') !== false) {
            $userBreakTabParam = '?tab=placementNote'; 
            if (strpos($this->referer, $userBreakTabParam) === false) {
                $this->referer .= $userBreakTabParam;
            }
            $this->refererIsProfile = true;
        }
    }
    
    public function store(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser->isAdmin()) {
            abort(403);
        }
        $user_id = $request->get('user_id');
        $message = 'Failed to add Placement Note';
        
        if( $request->get('user_id')){
            $enrolment_notes = new PlacementNotes;
            $enrolment_notes->user_id = $request->get('user_id');
            $enrolment_notes->company_name = $request->comapny_name;
            $enrolment_notes->abn = $request->abn;
            $enrolment_notes->employer_address = $request->employer_address;
            $enrolment_notes->contact_person = $request->contact_person;
            $enrolment_notes->phone = $request->phone;
            $enrolment_notes->employment_type = $request->employment_type;
            $enrolment_notes->pay_rate = $request->pay_rate;
            $enrolment_notes->hours_per_week = $request->hours_per_week;
            if ($enrolment_notes->save()) {
                $student = User::where('id', $enrolment_notes->user_id)->with('manager', 'organization')->first();
                $notificationUser = null;
                if (!empty($student)) {
                    $notificationUser = $student->manager ?? $student->organization ?? null;
                }
                if (!empty($notificationUser)) {
                    $notifyOptions = [
                        '[student.id]' => $student->id,
                        '[student.name]' => $student->full_name,
                        '[consultant.name]' => $notificationUser->full_name,
                        '[placement.company_name]' => $enrolment_notes->company_name,
                        '[placement.abn]' => $enrolment_notes->abn,
                        '[placement.employer_address]' => $enrolment_notes->employer_address,
                        '[placement.contact_person]' => $enrolment_notes->contact_person,
                        '[placement.phone]' => $enrolment_notes->phone,
                        '[placement.employment_type]' => $enrolment_notes->employment_type,
                        '[placement.pay_rate]' =>  $enrolment_notes->pay_rate . 'AUD',
                        '[placement.hours_per_week]' => $enrolment_notes->hours_per_week, 
                    ];
                    sendNotification('placement_notification', $notifyOptions, $notificationUser->id);
                }
                $message = 'Placement Note successfully added';
                return redirect($this->referer)->with('breakSuccess', $message);
            }
        }

        //redirect back with error
        if ($this->refererIsProfile) {
            return redirect($this->referer)->with('breakError', $message);
        }
        return redirect()->back()->with('error', $message);
    }

}
