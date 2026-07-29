<?php

use PHPFusion\Infusions\School\Classes\Admin\Controllers\Helpers\Common;


function generate_student_about() {

    define("FUSION_UNCACHED", TRUE);
    require_once INCLUDES . "ajax_include.php";
    header_content_type("json");

    $apiKey = "ytABskM3tJ9cjAZsajYCLbpJNMkZr2igoxYedjF3";

    $json_data = file_get_contents('php://input');
    $request = json_decode($json_data, TRUE);

    if ( ! $request || empty($request['id']) || ! isnum($request['id']) ) {
        header('Content-Type: application/json');
        echo json_encode([ 'error' => 'Invalid request or missing generation hook' ]);
        exit;
    }

    $params[':id'] = $request['id'];
    $condition = "";
    if ( $request['pgid'] && isnum($request['pgid']) ) {
        $condition = "AND p.program_id=:pid";
        $params[':pid'] = $request['pgid'];
    }

    // $request['id'] = 1;
    $student_id = $request['id'];
    $student_name = '';

    // 1. Format Matrix Data (Quantitative)
    $reports = [];
    $res = dbquery("SELECT a.id,
				   	a.date,
				   	a.observation,
				   	a.assessment,
				   	t.topic_cat,
				   	t.topic_unit,
				   	t.topic_year,
				   	t.topic_name,
				   	p.program_subject,
                    s.student_id,
       				s.student_name
					FROM " . DB_STUDENT_ASSESSMENTS . " a
					JOIN " . DB_STUDENTS . " s ON s.student_id=a.student_id
					JOIN " . DB_PROGRAM_TOPICS . " t ON a.topic_id=t.topic_id
					JOIN " . DB_PROGRAMS . " p ON p.program_id=t.program_id
					WHERE a.student_id=:id {$condition} ORDER BY a.date DESC LIMIT 50
		",
                   $params);

    if ( dbrows($res) ) {
        while ( $rows = dbarray($res) ) {
            if ( ! isset($student_name) ) {
                $student_name = $rows['student_name'];
            }
            if ( ! isset($student_id) ) {
                $student_id = $rows['student_id'];
            }

            // FIX: Wrap the content in a format Cohere v2 understands
            $reports[] = [
                "id"   => "rep_" . $rows['id'],
                "data" => [
                    "title"   => "Daily Lesson Assessment: {$rows['program_subject']}",
                    "content" => "Topic {$rows['topic_cat']}{$rows['topic_unit']}: {$rows['topic_name']}\nObservation: {$rows['observation']}\nAssessment: {$rows['assessment']}",
                    "date"    => $rows['date']
                ]
            ];

        }

        // 2. Load Assessment Reports (Documents)
        // Expecting $request['reports'] to be an array of ['title' => '...', 'snippet' => '...']
        // 2. CRITICAL FIX: Don't overwrite $reports!
        // If you want to allow additional reports from the request, merge them instead:
        if ( isset($request['additional_reports']) ) {
            $reports = array_merge($reports, $request['additional_reports']);
        }

        // 3. Prompt Loading Logic
        $_prompt = "Based on all teacher observations provided, identify the specific Strengths and Weaknesses for " . ( $student_name ?? "this student" ) . ".
		STRENGTHS: Identify 3 recurring positive traits or mastered skills mentioned across different subjects.
		WEAKNESSES: Identify 3 recurring challenges, specifically looking for 'careless mistakes', 'social distractions', and 'concept gaps'.
		Constraint: Use a narrative tone. Do not just list the scores. Explain the 'Why' behind each point based on the teacher's comments.";

        // 4. COHERE V2 CHAT API PAYLOAD
        $data = [
            "model"       => "command-r7b-12-2024",
            "messages"    => [
                [
                    "role"    => "system",
                    "content" => "You are a Senior Academic Evaluator.
                    Task: Write a single, insightful paragraph (max 180 words) for a student's profile.
                    Structure:
                    1. Start with Academic Strengths (mentioning specific subjects like English or Math).
                    2. Transition into Behavioral Strengths (attitude/confidence).
                    3. Transition into specific Weaknesses (social distractions like Elvis, time management, and test-taking carelessness).
                    4. End with a specific growth path.
                    Style: No bullet points. No headers. No repetitive scoring. Use high-level vocabulary."
                ],
                [
                    "role"    => "user",
                    "content" => $_prompt,
                ],
            ],
            "documents"   => $reports, // This is the "Document Embed" part
            "temperature" => 0.35,
            "max_tokens"  => 1500,
        ];

        $ch = curl_init("https://api.cohere.com/v2/chat");
        curl_setopt_array($ch,
                          [
                              CURLOPT_RETURNTRANSFER => TRUE,
                              CURLOPT_POST           => TRUE,
                              CURLOPT_HTTPHEADER     => [
                                  "Authorization: Bearer $apiKey",
                                  "Content-Type: application/json",
                                  "accept: application/json",
                              ],
                              CURLOPT_POSTFIELDS     => json_encode($data),
                          ]);

        $response = curl_exec($ch);

        $result = json_decode($response, TRUE, flags: JSON_PRETTY_PRINT);
        // print_P($result);
        curl_close($ch);

        // 5. V2 RESPONSE PARSING
        $result = json_decode($response, TRUE);
        $aiText = $result['message']['content'][0]['text'] ?? '';
        $citations = $result['message']['citations'] ?? [];

        // 2. SERVER-SIDE FORMATTING (Pre-render HTML for the DB)
        $finalHtml = formatCitationsForStorage($aiText, $citations);

        // 1. Determine the Base Key
        $pgId = ( isset($request['pgid']) && isnum($request['pgid']) ) ? $request['pgid'] : NULL;
        $baseKey = $pgId ? "student_pg" . $pgId : "student_overview";
        $expiryDate = date('Y-m-d H:i:s', time() + ( 86400 * 30 )); // 30 days

        $exists = dbcount("(student_id)", DB_STUDENT_INFO, "student_id='" . $student_id . "' AND settings_name='" . $baseKey . "'");

        if ( ! $exists ) {
            // --- SET 1: ROW DOES NOT EXIST ---
            // We populate both settings_value and settings_new_value so the
            // student has immediate content for the first time.
            $data = [
                ':id'     => $student_id,
                ':name'   => $baseKey,
                ':val'    => $finalHtml,
                ':expiry' => $expiryDate,
                ':now'    => DATETIME_NOW
            ];

            dbquery("INSERT INTO " . DB_STUDENT_INFO . " 
                (student_id, settings_name, settings_value, settings_old_value, settings_new_value, settings_expiry, settings_updated) 
                VALUES (:id, :name, :val, '', :val, :expiry, :now)",
                    $data);

        }
        else {
            // --- SET 2: ROW EXISTS ---
            // We DO NOT touch settings_value. We only update settings_new_value
            // for admin approval.
            $data = [
                ':id'   => $student_id,
                ':name' => $baseKey,
                ':val'  => $finalHtml,
                ':now'  => DATETIME_NOW
            ];

            dbquery("UPDATE " . DB_STUDENT_INFO . " 
                    SET settings_new_value = :val, 
                        settings_updated = :now 
                    WHERE student_id = :id AND settings_name = :name",
                    $data);
        }

        // Note: If you want to see which documents were used,
        // you can also check $result['message']['citations']
        header('Content-Type: application/json');
        echo json_encode([
                             "results"         => [ $finalHtml ],
                             "citations"       => $result['message']['citations'] ?? [],
                             "original_prompt" => $_prompt,
                         ]);

    }

    exit;
}

// Helper to handle the logic move
function formatCitationsForStorage($text, $citations) {

    if ( empty($citations) ) {
        return nl2br($text);
    }

    $references = [];
    $uniqueSources = [];

    // 1. Identify unique sources to build our footer
    foreach ( $citations as $cite ) {
        $source = $cite['sources'][0] ?? NULL;
        if ( $source && ! isset($uniqueSources[ $source['id'] ]) ) {
            $uniqueSources[ $source['id'] ] = [
                'title' => $source['document']['title'] ?? 'Reference',
                'id'    => str_replace('rep_', '', $source['id'])
            ];
        }
    }

    // 2. Build the Footer (Markdown links)
    $footer = "\n\n---\n**Sources:**\n";
    $i = 1;
    foreach ( $uniqueSources as $meta ) {
        $footer .= "$i. [{$meta['title']}](?page=assessment&action=view&id={$meta['id']})\n";
        $i++;
    }

    return nl2br($text) . $footer;
}

/**
 * @uses \generate_student_about()
 */
fusion_add_hook('fusion_filters', 'generate_student_about');
