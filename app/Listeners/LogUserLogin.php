<?php

namespace App\Listeners;

use App\Models\LoginRecord;
use Illuminate\Auth\Events\Login;

class LogUserLogin
{
    public function handle(Login $event)
    {
        $user = $event->user;
        // // Ensure super admins are excluded
        // if ($event->user->hasRole('super-admin')) {
        //     return; // Skip logging for super admins
        // }

        // Create a login record for this user
        LoginRecord::create([
            'user_id' => $user->id,
            'ip_address' => Request::ip(),
            'device' => $this->getDevice(),
            'browser' => Request::header('User-Agent'),
            'os' => $this->getOS(),
            'is_successful' => true,
            'session_id' => session()->getId(),
            'additional_data' => json_encode(['some_data' => 'value']), // You can add additional data if necessary
        ]);
    }

    // Helper methods to extract device, location, browser, and OS information
    protected function getDevice()
    {
        return request()->header('User-Agent'); // Extract User-Agent for device info
    }

    protected function getLocation($ip)
    {
        // Example IP geolocation integration (use a real service here)
        return 'City, Region'; // Replace with actual location lookup logic
    }

    protected function getBrowser()
    {
        return 'Chrome'; // Simplified, replace with actual browser extraction
    }

    protected function getOS()
    {
        return 'Windows 10'; // Simplified, replace with actual OS extraction
    }
}
