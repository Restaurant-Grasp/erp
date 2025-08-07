<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadReminderLog;
use App\Mail\LeadFollowUpReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendLeadFollowUpReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:send-followup-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 7-day follow-up reminders for leads';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting lead follow-up reminder process...');

        // Get leads that need 7-day follow-up
        $leads = Lead::needingFollowUp()->get();

        $this->info("Found {$leads->count()} leads needing follow-up reminders.");

        $successCount = 0;
        $failureCount = 0;

        foreach ($leads as $lead) {
            try {
                // Check if assigned staff exists and has email
                if (!$lead->assignedTo || !$lead->assignedTo->email) {
                    $this->warn("Lead {$lead->lead_no} has no assigned staff or staff email. Skipping...");
                    continue;
                }

                // Send email reminder
                Mail::to($lead->assignedTo->email)->send(new LeadFollowUpReminder($lead));

                // Log the reminder
                LeadReminderLog::create([
                    'lead_id' => $lead->id,
                    'reminder_type' => '7_day_followup',
                    'sent_date' => now(),
                    'email_sent_to' => $lead->assignedTo->email,
                    'status' => 'sent'
                ]);

                // Update lead
                $lead->update([
                    'follow_up_scheduled' => true,
                    'reminder_sent_date' => now()
                ]);

                $successCount++;
                $this->info("Reminder sent for lead {$lead->lead_no} to {$lead->assignedTo->email}");

            } catch (\Exception $e) {
                $failureCount++;
                $this->error("Failed to send reminder for lead {$lead->lead_no}: " . $e->getMessage());

                // Log the failure
                LeadReminderLog::create([
                    'lead_id' => $lead->id,
                    'reminder_type' => '7_day_followup',
                    'sent_date' => now(),
                    'email_sent_to' => $lead->assignedTo->email ?? null,
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);

                Log::error('Lead reminder failed', [
                    'lead_id' => $lead->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Lead follow-up reminder process completed!");
        $this->info("Success: {$successCount}, Failures: {$failureCount}");

        return Command::SUCCESS;
    }
}
