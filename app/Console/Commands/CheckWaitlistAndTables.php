<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RestaurantWaitlist;
use App\Models\Restaurant;
use Carbon\Carbon;
use App\Notifications\WaitlistNotification;

class CheckWaitlistAndTables extends Command
{
    protected $signature = 'waitlist:check';
    protected $description = 'Check booked tables, free seats and promote waiting users';

    public function handle()
    {
        $now = Carbon::now();
        $expiredBookings = RestaurantWaitlist::where('status', 'booked')
            ->where('expires_at', '<', $now)
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->status = 'cancelled';
            $booking->save();
            $restaurant = Restaurant::find($booking->restaurant_id); 
            if ($booking->user->cm_firebase_token) {
                $this->sendPushNotification(
                    $booking->user->cm_firebase_token,
                    "Booking Cancelled",
                    "Your table booking at {$restaurant->restaurant_name} has been cancelled as you did not arrive."
                );
            }
        }

        $seatedUsers = RestaurantWaitlist::where('status', 'seated')->get();
        foreach ($seatedUsers as $seated) {
            $restaurant = Restaurant::with('tables')->find($seated->restaurant_id);
            $table = $restaurant->tables->where('table_size', '>=', $seated->party_size)->first();
            if (!$table) continue;

            $seatedEndTime = $seated->updated_at->copy()->addMinutes($table->avg_turnover_time);
            if ($now->greaterThanOrEqualTo($seatedEndTime)) {
                $seated->status = 'completed';
                $seated->save();

                if ($seated->user->cm_firebase_token) {
                    $this->sendPushNotification(
                        $seated->user->cm_firebase_token,
                        "Table Free",
                        "Your table is now free at {$restaurant->restaurant_name}. Hope you enjoyed your meal!"
                    );
                }
            }
        }


        // Step 3: Promote waiting users if table free
        $restaurants = Restaurant::with('tables')->get();

        foreach ($restaurants as $restaurant) {
            $tables = $restaurant->tables;

            // Loop through waiting users by position
            $waitingUsers = RestaurantWaitlist::where('restaurant_id', $restaurant->id)
                ->where('status', 'waiting')
                ->orderBy('position')
                ->get();

            foreach ($waitingUsers as $waiting) {
                // Check if table available for this user
                $availableTableCount = 0;
                foreach ($tables as $table) {
                    $bookedCount = RestaurantWaitlist::where('restaurant_id', $restaurant->id)
                        ->whereIn('status', ['booked', 'seated'])
                        ->where('party_size', '<=', $table->table_size)
                        ->where(function ($q) {
                            $q->where('expires_at', '>', now())
                                ->orWhere('status', 'seated');
                        })
                        ->count();
                    $availableTableCount += max(0, $table->table_count - $bookedCount);
                }

                if ($availableTableCount > 0) {
                    $waiting->status = 'booked';
                    $waiting->expires_at = $now->copy()->addMinutes(15);
                    $waiting->save();

                    if ($waiting->user->cm_firebase_token) {
                        $this->sendPushNotification(
                            $waiting->user->cm_firebase_token,
                            "Table Available",
                            "A table is now available for you at {$restaurant->restaurant_name}. Please arrive within 15 minutes."
                        );
                    }
                } else {
                    if ($waiting->user->cm_firebase_token) {
                        $this->sendPushNotification(
                            $waiting->user->cm_firebase_token,
                            "Still Waiting",
                            "Your table is still in waiting at {$restaurant->restaurant_name}. Expected time: " . $waiting->expected_time->toDateTimeString()
                        );
                    }
                }
            }
        }

        $this->info('Waitlist and table check completed successfully!');
    }
}
