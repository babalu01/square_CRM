<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Policy;
use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationUser;

use Carbon\Carbon;

class UpdateExpiredPolicies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'policies:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of expired policies to inactive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredPolicies = Policy::where('end_date', '<', Carbon::now())
        ->where('status', 'active')
        ->get();

        foreach ($expiredPolicies as $policy) {
            $policy->status = 'inactive';
            $policy->save();

            $user = User::all();
            $notification = new Notification([
                'workspace_id' => 1,
                'from_id' => 'u_1',
                'type' => 'info',
                'action' => 'policy_expired',
                'type_id' => 1,
                'title' => 'Policy Expired',
                'message' => $policy->policy_number . ' has been expired',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $notification->save();

            $notificationUsers = $user->map(function ($user) use ($notification) {
                return new NotificationUser([
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'read_at' => null,
                ]);
            });

            NotificationUser::insert($notificationUsers->toArray());
        } // Closing brace for foreach
    } // Closing brace for handle method
} // Closing brace for class
