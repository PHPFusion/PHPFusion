<?php

function check_site_rate() {
    // 1. Handle Heartbeat (AJAX)
    if (server('REQUEST_METHOD') === 'POST' && post('action') === 'heartbeat') {

        dbquery("UPDATE ".DB_SESSIONS_RATE." 
                 SET hit_count = hit_count + 1, 
                     last_activity = NOW() 
                 WHERE session_id = :session_id AND hit_count = 1", [
            ':session_id' => SESSION_ID
        ]);
        // Fetch the duration so we can verify it's working
        $res = dbquery("SELECT TIMESTAMPDIFF(SECOND, start_time, NOW()) as seconds 
                    FROM ".DB_SESSIONS_RATE." 
                    WHERE session_id = :session_id", [
            ':session_id' => SESSION_ID
        ]);
        $data = dbarray($res);
        $time_on_site = $data['seconds'] ?? 0;

        $response = [
            'status' => 'success',
            'duration_seconds' => $time_on_site,
            'msg' => 'Session is now active (Non-Bounce)'
        ];

        if (post('get_notifications') && iMEMBER) {
            $response['notifications'] = \PHPFusion\Notifications\Notifications::getUnread();
        }

        echo json_encode($response);
        exit;

    }

    // 2. Handle Page Entry (Normal Page Load)
    if (defined('SESSION_ID') && SESSION_ID !== '') {

        // --- SANITIZATION START ---
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");

        // Filter host to prevent HTTP Host Header Injection
        $host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);

        // Sanitize the URI (removes potential script tags or illegal characters)
        $uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);

        // Final clean URL for the database
        $current_url = htmlspecialchars($protocol . "://" . $host . $uri, ENT_QUOTES, 'UTF-8');
        // --- SANITIZATION END ---

        // Check if session already exists in DB
        $check_sql = dbquery("SELECT session_id FROM ".DB_SESSIONS_RATE." WHERE session_id = :session_id", [
            ':session_id' => SESSION_ID
        ]);

        if (dbrows($check_sql) == 0) {

            dbquery("INSERT INTO ".DB_SESSIONS_RATE." 
                     (session_id, last_url, hit_count, start_time, last_activity) 
                     VALUES (:session_id, :url, 1, NOW(), NOW())", [
                ':session_id' => SESSION_ID,
                ':url'        => $current_url
            ]);

        } else {

            dbquery("UPDATE ".DB_SESSIONS_RATE." 
                     SET hit_count = hit_count + 1, 
                         last_url = :url, 
                         last_activity = NOW() 
                     WHERE session_id = :session_id", [
                ':session_id' => SESSION_ID,
                ':url'        => $current_url
            ]);

        }
    }
}

fusion_add_hook('fusion_filters', 'check_site_rate');
