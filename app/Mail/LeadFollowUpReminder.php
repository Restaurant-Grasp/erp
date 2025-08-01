<?php
namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeadFollowUpReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $lead;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Follow-up Reminder: ' . ($this->lead->company_name ?: $this->lead->contact_person))
            ->view('emails.lead-followup-reminder');
    }
}

// Add to app/Console/Kernel.php in the schedule method:
// $schedule->command('leads:send-followup-reminders')->daily();