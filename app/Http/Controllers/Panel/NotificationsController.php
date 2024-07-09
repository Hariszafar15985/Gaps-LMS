<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationStatus;
use App\Models\StudentNotificationSetting;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $notifications = Notification::where(function ($query) use ($user) {
            $query->where('notifications.user_id', $user->id)
                ->where('notifications.type', 'single');
        })->orWhere(function ($query) use ($user) {
            if (!$user->isAdmin()) {
                $query->whereNull('notifications.user_id')
                    ->whereNull('notifications.group_id')
                    ->where('notifications.type', 'all_users');
            }
        });

        $userGroup = $user->userGroup()->first();
        if (!empty($userGroup)) {
            $notifications->orWhere(function ($query) use ($userGroup) {
                $query->where('notifications.group_id', $userGroup->group_id)
                    ->where('notifications.type', 'group');
            });
        }

        $notifications->orWhere(function ($query) use ($user) {
            $query->whereNull('notifications.user_id')
                ->whereNull('notifications.group_id')
                ->where(function ($query) use ($user) {
                    if ($user->isUser()) {
                        $query->where('notifications.type', 'students');
                    } elseif ($user->isTeacher()) {
                        $query->where('notifications.type', 'instructors');
                    } elseif ($user->isOrganization()) {
                        $query->where('notifications.type', 'organizations');
                    }
                });
        });

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
    }

    public function saveStatus($id)
    {
        $user = auth()->user();

        $unReadNotifications = $user->getUnReadNotifications();

        if (!empty($unReadNotifications) and !$unReadNotifications->isEmpty()) {
            $notification = $unReadNotifications->where('id', $id)->first();

            if (!empty($notification)) {
                $status = NotificationStatus::where('user_id', $user->id)
                    ->where('notification_id', $notification->id)
                    ->first();

                if (empty($status)) {
                    NotificationStatus::create([
                        'user_id' => $user->id,
                        'notification_id' => $notification->id,
                        'seen_at' => time()
                    ]);
                }
            }
        }

        return response()->json([], 200);
    }

    public function studentNotificationSettings()
    {
        $user = auth()->user();

        if ( $user->isAdmin() || $user->isOrganizationPersonnel() )
        {
            $notificationSettings = StudentNotificationSetting::query()
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhereNull('user_id');
                })->where(function ($query) use ($user) {
                    if ( $user->isOrganization() ) {
                        $query->where('organ_id', $user->id);
                    } else {
                        $query->where('organ_id', $user->organ_id)
                            ->orWhereNull('organ_id');
                    }
                })->first();

            if ( $notificationSettings ) {
                if ( ! empty( $notificationSettings->type ) ) {
                    $settings = json_decode($notificationSettings->type,true);
                } else {
                    StudentNotificationSetting::create([
                        'user_id'  => $user->id,
                        'organ_id' => $user->isOrganization() ? $user->id : $user->organ_id,
                        'type'     => json_encode( StudentNotificationSetting::$default ),
                    ]);
                    $settings = StudentNotificationSetting::$default;
                }
            } else {
                StudentNotificationSetting::create([
                    'user_id'  => $user->id,
                    'organ_id' => $user->isOrganization() ? $user->id : $user->organ_id,
                    'type'     => json_encode( StudentNotificationSetting::$default ),
                ]);
                $settings = StudentNotificationSetting::$default;
            }

            $data = [
                'pageTitle' => trans('panel.student_notifications_settings'),
                'active'    => $notificationSettings ? $notificationSettings->active : 1,
                'settings'  => $settings,
                'default'   => StudentNotificationSetting::$default
            ];

            return view(getTemplate() . '.panel.notifications.student_notifications_settings', $data);
        }

        abort(404);
    }

    public function studentNotificationUpdate(Request $request)
    {
        $user = auth()->user();

        if ( $user->isAdmin() || $user->isOrganizationPersonnel() )
        {
            $inputs  = $request->all();
            $default = StudentNotificationSetting::$default;
            $result  = array_filter($inputs, function($key) use ($default) {
                return in_array($key, $default);
            }, ARRAY_FILTER_USE_KEY);

            $notificationSettings = StudentNotificationSetting::query()
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhereNull('user_id');
                })->where(function ($query) use ($user) {
                    if ( $user->isOrganization() ) {
                        $query->where('organ_id', $user->id);
                    } else {
                        $query->where('organ_id', $user->organ_id)
                            ->orWhereNull('organ_id');
                    }
                })->first();

            if ( $notificationSettings ) {
                if ( ! empty( $result ) ) {
                    $notificationSettings->type = json_encode( array_keys( $result ) );
                    $notificationSettings->active = 1;
                } else {
                    $notificationSettings->active = 0;
                }
                $notificationSettings->update();
            }

            $toastData = [
                'title'  => '',
                'msg'    => trans('panel.student_notifications_settings_msg'),
                'status' => 'success'
            ];

            return back()->with(['toast' => $toastData]);
        }

        abort(404);
    }
}
