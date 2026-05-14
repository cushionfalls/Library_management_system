<?php
/**
 * Membership Expiry Check Cron Job
 * Should be run daily at midnight.
 * Command: php cron/membership_expiry_check.php
 */

require_once __DIR__ . '/../classes/Membership.php';

echo "Starting Membership Expiry Check...\n";

try {
    $membership = new Membership();
    $notified = $membership->notifyExpiringMemberships(3);
    
    echo "Successfully sent notifications to $notified users whose membership expires in 3 days.\n";
    
    // Optional: Log to file
    $logFile = __DIR__ . '/../logs/cron_membership.log';
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0777, true);
    }
    file_put_contents($logFile, date('[Y-m-d H:i:s]') . " Notified $notified users.\n", FILE_APPEND);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Cron Error (Membership Expiry): " . $e->getMessage());
}

echo "Check Complete.\n";
