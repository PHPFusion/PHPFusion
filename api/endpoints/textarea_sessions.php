<?php
function update_textarea_sessions() {
	require_once INCLUDES.'ajax_include.php';
	
    // PHPFusion usually handles sessions, but we check just in case
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // We use fusion_get_settings or direct $_POST
        // depending on your sanitization needs
        $field_id = isset($_POST['field_id']) ? strip_tags($_POST['field_id']) : '';
        $content  = isset($_POST['content']) ? $_POST['content'] : '';

        if (!empty($field_id)) {
            $_SESSION['form_drafts'][$field_id] = $content;

            // Set header for JSON response
            header_content_type('json');
            echo json_encode(['status' => 'success', 'field' => $field_id]);
            exit;
        }
    }
	
	header_content_type('json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

// Ensure the hook is called
update_textarea_sessions();
