<?php

function generate_student_about()
{
	$apiKey = "r16yfDJHkNGDcCwsHPFLIHu14teGjFFihCznYGh3";

	$json_data = file_get_contents('php://input');
	$request = json_decode($json_data, TRUE);

	if (!$request || empty($request['id'])) {
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Invalid request or missing generation hook']);
		exit;
	}


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
       				s.student_name
					FROM " . DB_STUDENT_ASSESSMENTS . " a
					JOIN ".DB_STUDENTS." s ON s.student_id=a.student_id
					JOIN " . DB_PROGRAM_TOPICS . " t ON a.topic_id=t.topic_id
					JOIN " . DB_PROGRAMS . " p ON p.program_id=t.program_id
					WHERE a.student_id=:id ORDER BY a.date DESC LIMIT 50
		", [
		':id' => $request['id'],
	]);
	if (dbrows($res)) {
		while ($rows = dbarray($res)) {
			if (!isset($student_name)) {
				$student_name = $rows['student_name'];
			}
			$reports[] = [
				"title"   => "Daily Lesson Assessment: {$rows['program_subject']}",
				"snippet" => "Topic {$rows['topic_cat']}{$rows['topic_unit']}: {$rows['topic_name']}\nObservation: {$rows['observation']}\nAssessment: {$rows['assessment']}",
				"date"    => $rows['date'],
			];
		}
	}
	// 2. Load Assessment Reports (Documents)
	// Expecting $request['reports'] to be an array of ['title' => '...', 'snippet' => '...']
	// 2. CRITICAL FIX: Don't overwrite $reports!
	// If you want to allow additional reports from the request, merge them instead:
	if (isset($request['additional_reports'])) {
		$reports = array_merge($reports, $request['additional_reports']);
	}

	// 3. Prompt Loading Logic
	$_prompt = "Please perform a Deep Longitudinal Analysis on the attached ".count($reports)." assessment reports for ".($student_name ?? "this student").".
	
	Primary Objective: > Create a comprehensive profile that identifies if the student is meeting the benchmarks for the Malaysian education curriculum.
	
	Specific Focus Areas:

	Academic Velocity: Is the student improving, plateauing, or declining in core subjects?
	
	Subject Mastery: Pinpoint specific modules (e.g., 'Calculus' in Math or 'Inorganic Chemistry' in Science) where intervention is needed.
	
	Holistic Traits: Based on teacher comments, summarize their 'soft skills' like resilience, participation, and peer collaboration.
	
	Please format the final output with clear headers and use the provided Matrix data to validate your text-based findings.";

	// 4. COHERE V2 CHAT API PAYLOAD
	$data = [
		"model"       => "command-r7b-12-2024",
		"messages"    => [
			[
				"role"    => "system",
				"content" => "You are an Expert Educational Data Analyst.
                Core Directives:
                - Pattern Recognition: Do not summarize chronologically. Cross-reference all reports to find recurring themes.
                - Evidence-Based: Support every claim with citations from the provided documents.
                - Context Awareness: Interpret data via Malaysian streams (KSSR, KSSM, SMK, SBP) or International (IGCSE).
                - Weighted Analysis: Prioritize recent reports for trajectory; use older ones for baseline.
                - Output: Identify exactly 3 strengths and 3 growth areas with specific subject focus.",
			],
			[
				"role"    => "user",
				"content" => $_prompt,
			],
		],
		"documents"   => $reports, // This is the "Document Embed" part
		"temperature" => 0.2,
		"max_tokens"  => 1500,
	];

	$ch = curl_init("https://api.cohere.com/v2/chat");
	curl_setopt_array($ch, [
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
	$result = json_decode($response, TRUE);
	curl_close($ch);

	// 5. V2 RESPONSE PARSING
	$aiText = $result['message']['content'][0]['text'] ?? '';

	// Note: If you want to see which documents were used,
	// you can also check $result['message']['citations']

	header('Content-Type: application/json');
	echo json_encode([
		"results"         => [$aiText],
		"citations"       => $result['message']['citations'] ?? [],
		"original_prompt" => $_prompt,
	]);
	exit;
}


/**
 * @uses \generate_student_about()
 */
fusion_add_hook('ai_prompt', 'generate_student_about');
