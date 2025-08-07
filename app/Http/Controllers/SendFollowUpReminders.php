<?php

// app/Console/Commands/SendFollowUpReminders.php
namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Models\FollowUpReminderLog;
use App\Models\User;
use App\Mail\FollowUpReminder;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendFollowUpReminders extends Command
{
    protected $signature = 'followups:send-reminders';
    protected $description = 'Send 3-day follow-up reminders via email and WhatsApp';

    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        parent::__construct();
        $this->whatsAppService = $whatsAppService;
    }

    public function handle()
    {
        $this->info('Starting follow-up reminder process...');

        // Get follow-ups needing reminders
        $followUps = FollowUp::needingReminder()
            ->with(['assignedTo', 'lead', 'customer'])
            ->get();

        $this->info("Found {$followUps->count()} follow-ups needing reminders.");

        $successCount = 0;
        $failureCount = 0;

        foreach ($followUps as $followUp) {
            $emailSent = false;
            $whatsappSent = false;
            $errors = [];

            // Get all users to notify
            $users = $this->getUsersToNotify($followUp);

            foreach ($users as $user) {
                // Send Email
                if ($user->email) {
                    try {
                        Mail::to($user->email)->send(new FollowUpReminder($followUp, $user));
                        $emailSent = true;
                        $this->info("Email sent to {$user->email} for follow-up ID: {$followUp->id}");
                    } catch (\Exception $e) {
                        $errors[] = "Email failed to {$user->email}: " . $e->getMessage();
                        $this->error("Email failed to {$user->email}: " . $e->getMessage());
                    }
                }

                // Send WhatsApp
                if ($user->staff && $user->staff->mobile) {
                    try {
                        $message = $this->buildWhatsAppMessage($followUp, $user);
                        $this->whatsAppService->sendMessage($user->staff->mobile, $message);
                        $whatsappSent = true;
                        $this->info("WhatsApp sent to {$user->staff->mobile} for follow-up ID: {$followUp->id}");
                    } catch (\Exception $e) {
                        $errors[] = "WhatsApp failed to {$user->staff->mobile}: " . $e->getMessage();
                        $this->error("WhatsApp failed: " . $e->getMessage());
                    }
                }
            }

            // Log the reminder
            $status = ($emailSent || $whatsappSent) ? 'sent' : 'failed';
            $reminderType = [];
            if ($emailSent) $reminderType[] = 'email';
            if ($whatsappSent) $reminderType[] = 'whatsapp';
            
            FollowUpReminderLog::create([
                'follow_up_id' => $followUp->id,
                'reminder_type' => empty($reminderType) ? 'both' : implode(',', $reminderType),
                'sent_to' => implode(', ', $users->pluck('email')->filter()->toArray()),
                'sent_date' => now(),
                'status' => $status,
                'error_message' => empty($errors) ? null : implode('; ', $errors)
            ]);

            // Update follow-up
            if ($status === 'sent') {
                $followUp->update([
                    'reminder_sent' => true,
                    'reminder_sent_date' => now()
                ]);
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        $this->info("Follow-up reminder process completed!");
        $this->info("Success: {$successCount}, Failures: {$failureCount}");

        return Command::SUCCESS;
    }

    private function getUsersToNotify($followUp)
    {
        // Get assigned user
        $users = collect();
        
        if ($followUp->assignedTo && $followUp->assignedTo->user) {
            $users->push($followUp->assignedTo->user);
        }

        // Get all admin users
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['super_admin', 'admin']);
        })->where('is_active', 1)->get();

        $users = $users->merge($adminUsers)->unique('id');

        return $users;
    }

    private function buildWhatsAppMessage($followUp, $user)
    {
        $entityName = $followUp->entity_name;
        $scheduledDate = $followUp->scheduled_date->format('d/m/Y h:i A');
        $type = ucwords(str_replace('_', ' ', $followUp->follow_up_type));
        
        $message = "🔔 *Follow-up Reminder*\n\n";
        $message .= "Hi {$user->name},\n\n";
        $message .= "You have a follow-up scheduled:\n";
        $message .= "📅 *Date:* {$scheduledDate}\n";
        $message .= "🏛️ *{$followUp->entity_type}:* {$entityName}\n";
        $message .= "📋 *Type:* {$type}\n";
        $message .= "📌 *Subject:* {$followUp->subject}\n";
        $message .= "🎯 *Priority:* " . ucfirst($followUp->priority) . "\n\n";
        
        if ($followUp->description) {
            $message .= "📝 *Notes:*\n" . substr($followUp->description, 0, 200);
            if (strlen($followUp->description) > 200) {
                $message .= "...";
            }
            $message .= "\n\n";
        }
        
        $message .= "Please ensure to complete this follow-up on time.\n";
        $message .= "- Temple Management CRM";
        
        return $message;
    }
}
