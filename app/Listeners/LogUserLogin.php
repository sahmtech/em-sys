<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class LogUserLogin
{
    public function handle(Login $event)
    {
        $user = $event->user;

        // Ensure super admins are excluded
        if ($user->hasRole('Admin#1')) {
            return; // Skip logging for super admins
        }

        // Get the user's IP address
        $ipAddress = Request::ip();

        // Fetch location data using a geolocation API
        $locationData = Http::get("http://ip-api.com/json/{$ipAddress}")->json();
        // Extract relevant location fields
        $location = [
            'country' => $locationData['country'] ?? 'Unknown',
            'region' => $locationData['regionName'] ?? 'Unknown',
            'city' => $locationData['city'] ?? 'Unknown',
            'latitude' => $locationData['lat'] ?? null,
            'longitude' => $locationData['lon'] ?? null,
        ];

        // Create a login record for this user
        DB::table('login_records')->insert([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'device' => $this->getDevice(),
            'browser' => $this->getBrowser(),
            'os' => $this->getOS(),
            'is_successful' => true,
            'session_id' => session()->getId(),
            'additional_data' => json_encode([
                'location' => $location, // Include location data
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Helper methods to extract device, location, browser, and OS information
    protected function getDevice()
    {
        return request()->header('User-Agent'); // Extract User-Agent for device info
    }

    protected function getBrowser()
    {
        $userAgent = request()->header('User-Agent');
        if (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Opera') !== false) {
            return 'Opera';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    protected function getOS()
    {
        $userAgent = request()->header('User-Agent');
        if (strpos($userAgent, 'Windows NT 10.0') !== false) {
            return 'Windows 10';
        } elseif (strpos($userAgent, 'Windows NT 6.3') !== false) {
            return 'Windows 8.1';
        } elseif (strpos($userAgent, 'Windows NT 6.2') !== false) {
            return 'Windows 8';
        } elseif (strpos($userAgent, 'Windows NT 6.1') !== false) {
            return 'Windows 7';
        } elseif (strpos($userAgent, 'Mac OS X') !== false) {
            return 'Mac OS X';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            return 'Linux';
        } else {
            return 'Unknown';
        }
    }
}
