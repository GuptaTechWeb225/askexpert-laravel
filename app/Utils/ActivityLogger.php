<?php

if (!function_exists('logActivity')) {

    function logActivity(
        string $message,
        $subject = null,
        array $properties = []
    ) {
        $activity = activity();

        if ($subject) {
            $activity->performedOn($subject);
        }

        if (!empty($properties)) {
            $activity->withProperties($properties);
        }

        // 🔥 Auto prefix causer name
        if (auth('admin')->check()) {
            $name = auth('admin')->user()->name ?? 'Admin';
        } elseif (auth('expert')->check()) {
            $u = auth('expert')->user();
            $name = trim(($u->f_name ?? '') . ' ' . ($u->l_name ?? ''));
        } elseif (auth('customer')->check()) {
            $name = auth('customer')->user()->f_name ?? 'User';
        } else {
            $name = 'System';
        }

        $activity->log($name . ' ' . $message);
    }
}
