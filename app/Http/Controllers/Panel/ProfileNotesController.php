<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ProfileNotes;
use Illuminate\Http\Request;

class ProfileNotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        /* $user = auth()->user();

        if (!$user->isUser() && !$user->isTeacher()) {

            $notifications = $notifications->leftJoin('notifications_status','notifications.id','=','notifications_status.notification_id')
                ->selectRaw('notifications.*, count(notifications_status.notification_id) AS `count`')
                ->with(['notificationStatus'])
                ->groupBy('notifications.id')
                ->orderBy('count','asc')
                ->orderBy('notifications.created_at','DESC')
                ->paginate(10);
    
            $data = [
                'pageTitle' => trans('panel.notifications'),
                'notifications' => $notifications
            ];
    
            return view(getTemplate() . '.panel.notifications.index', $data);
        } else {
            abort(404);
        } */


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = auth()->user();

        if (!$user->isUser() && !$user->isTeacher()) {
            
            $title = $request->get('title');
            $message = $request->get('message');
            $user_id = $request->get('user_id'); //user against which note is to be added
            $creator_id = $user->id;
            $insertData = [
                'title' => $title,
                'message' => $message,
                'user_id' => $user_id,
                'creator_id' => $creator_id,
            ];
    
            $note = ProfileNotes::create($insertData);
            if (isset($note->id) && $note->id) {
                
            }
    
            return view(getTemplate() . '.panel.notifications.index', $data);
        } else {
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
