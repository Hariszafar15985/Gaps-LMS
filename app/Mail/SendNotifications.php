<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNotifications extends Mailable
{
    use SerializesModels;
    public $notification;
    public $inActiveNotification;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($notification, $inActiveNotification = false)
    {
        $this->notification = $notification;
        $this->inActiveNotification = $inActiveNotification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $notification = $this->notification;

        if (!empty($notification)) {
            $generalSettings = getGeneralSettings();
            if( $this->inActiveNotification == false) {
                return $this->subject($notification['title'])
                    ->from(!empty($generalSettings['site_email']) ? $generalSettings['site_email'] : env('MAIL_FROM_ADDRESS'))
                    ->view('web.default.emails.notification', [
                        'notification' => $notification,
                        'generalSettings' => $generalSettings
                    ]);
            } else {
                return $this->subject($notification['title'])
                ->from(!empty($generalSettings['site_email']) ? $generalSettings['site_email'] : env('MAIL_FROM_ADDRESS'))
                ->view('web.default.emails.inactivity-notification', [
                    'notification' => $notification,
                    'generalSettings' => $generalSettings
                ]);
            }

        }
    }
}
