<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\PolicyEndReminder;
use Illuminate\Support\Facades\Mail;
use App\Models\Policy;
use Carbon\Carbon;

class SendPolicyEndReminders extends Command
{
    protected $signature = 'policy:end-reminders';
    protected $description = 'Send email reminders for policies ending in 10 days';

    public function handle()
    {
        $policies = Policy::where('end_date', Carbon::now()->addDays(10)->format('Y-m-d'))->get();

        foreach ($policies as $policy) {
            Mail::to('babalu0607@gmail.com')->send(new PolicyEndReminder($policy));
        }

        $this->info('Reminder emails sent successfully!  ');
    }
}
