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
    
    // 1. Process and deactivate actually expired memberships and remove book access
    $expired = $membership->processExpiredMemberships();
    echo "Processed and expired $expired memberships whose ends_at was in the past.\n";

    // 2. Notify users whose membership is expiring in 3 days
    $notified = $membership->notifyExpiringMemberships(3);
    echo "Successfully sent notifications to $notified users whose membership expires in 3 days.\n";
    
    // Optional: Log to file
    $logFile = __DIR__ . '/../logs/cron_membership.log';
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0777, true);
    }
    $logMsg = date('[Y-m-d H:i:s]') . " Expired: $expired, Notified: $notified.\n";
    file_put_contents($logFile, $logMsg, FILE_APPEND);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Cron Error (Membership Expiry): " . $e->getMessage());
}

echo "Check Complete.\n";
