<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PolicyEndReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $policy;

    public function __construct($policy)
    {
        $this->policy = $policy;
    }

    public function build()
    {
        return $this->subject('Policy Ending Soon')
                    ->view('mail.policy_end_reminder');
    }
}
