<?php

/**
 * @param float $temperature
 * @param int   $max_tokens
 * @param int   $response_count
 *
 * @return void
 */
function get_assessment_response($temperature = 0.3, $max_tokens = 600, $response_count = 0)
{
    $apiKey = "ytABskM3tJ9cjAZsajYCLbpJNMkZr2igoxYedjF3";

    $json_data = file_get_contents('php://input');
    $request = json_decode($json_data, true);

    if (!$request || empty($request['generate'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request or missing generation hook']);
        exit;
    }

    // 1. Format the matrix if it exists
    if (isset($request['matrix']) && is_array($request['matrix'])) {
        $request['matrix'] = format_matrix_for_ai($request['matrix']);
    }

    // 2. Prompt Loading Logic
    $promptFile = __DIR__ . '/prompt/' . $request['generate'] . '.prompt.php';
    if (file_exists($promptFile)) {
        include $promptFile;
        $_prompt = fusion_filter_hook('ai_prompt', $request);
        $_prompt = !empty($_prompt) ? $_prompt[0] : $request['text'];
    } else {
        $_prompt = $request['text'];
		echo json_encode(['error' => 'Invalid request or missing prompt generation hook']);
		exit;
    }

    // 3. COHERE V2 CHAT API PAYLOAD
    // Endpoint: v2/chat uses "messages" array and "model"
    $data = [
        "model" => "command-r7b-12-2024", // Recommended for complex reports
        "messages" => [
			[
				"role" => "system",
				"content" => "You are an Expert Educational Data Analyst on Malaysian Education System. Identify recurring patterns in KSSR/KSSM/IGCSE subjects and provide evidence-based strengths/weaknesses."
			],
            [
                "role" => "user",
                "content" => $_prompt
            ]
        ],
        "temperature" => $temperature,
        "max_tokens"  => $max_tokens
    ];

    // URL updated to v2/chat
    $ch = curl_init("https://api.cohere.com/v2/chat");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_POST           => TRUE,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json",
            "accept: application/json"
        ],
        CURLOPT_POSTFIELDS     => json_encode($data),
    ]);

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);
    // print_p($result);

    // 4. V2 RESPONSE PARSING
    // In v2, the text is inside: message -> content -> [0] -> text
    $aiText = $result['message']['content'][0]['text'] ?? '';

    // Split variations if the prompt used the '---' delimiter
    if (str_contains($aiText, '---')) {
        $responses = array_values(array_filter(array_map('trim', explode('---', $aiText))));
    } else {
        $responses = $aiText ? [$aiText] : [];
    }

    header('Content-Type: application/json');
    echo json_encode(["results" => $responses, 'original_prompt'=>$_prompt, 'errors'=>'']);
    exit;
}

function format_matrix_for_ai($matrix_data) {
    if (empty($matrix_data) || !is_array($matrix_data)) return "No specific matrix data provided.";

    $items = [];
    foreach ($matrix_data as $row) {
        $label = $row['label'] ?? 'Criteria';
        $score = (int)($row['value'] ?? 0);

        // Map scores to qualitative descriptors
        if ($score >= 9) $desc = "Exceptional/Mastery";
        elseif ($score >= 7) $desc = "Strong/Proficient";
        elseif ($score >= 5) $desc = "Satisfactory/Developing";
        elseif ($score >= 3) $desc = "Limited/Emerging";
        else $desc = "Needs Significant Support";

        $items[] = "- {$label}: Score {$score}/10 ({$desc})";
    }

    return implode("\n", $items);
}

/**
 * @uses get_assessment_response()
 */
fusion_add_hook('fusion_filters', 'get_assessment_response');
