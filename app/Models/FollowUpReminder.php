<?php

namespace App\Mail;

use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FollowUpReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $followUp;
    public $user;

    public function __construct(FollowUp $followUp, User $user)
    {
        $this->followUp = $followUp;
        $this->user = $user;
    }

    public function build()
    {
        $subject = 'Follow-up Reminder: ' . $this->followUp->subject;
        
        return $this->subject($subject)
            ->view('emails.followup-reminder');
    }
}
