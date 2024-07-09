<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseCompletionMail extends Mailable
{
    use Queueable, SerializesModels;
    public $mailContent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $mailContent )
    {
        $this->mailContent = $mailContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Student Course Completion')
            ->view('web.default.emails.course-completion');
    }
}
