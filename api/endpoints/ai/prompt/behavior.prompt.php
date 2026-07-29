<?php
/*
 * student is excellent when it comes to listening the subject being taught. when asked, student is very like to answer question. student also very keen to learn and will start automatically.

however, the student is very bad at understanding what is written. student can do basic science but cannot understand what is prism and colors. student also do not know why clouds formed and how fish breath underwater.
 */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


/**
 * Generates a structured behavioral prompt for an AI model to draft a
 * student progress report.
 *
 * This method constructs a persona-driven prompt that synthesizes quantitative
 * matrix data and qualitative teacher notes into a cohesive, professional,
 * and empathetic narrative.
 */
//
// function behavior_prompt($request)
// {
//     $year = $request['year'] ? $request['year'].', ' : '';
//     $subject = $request['subject'] ? $request['subject'].' subject' : '';
//
// 	$prompt = "### ROLE\n";
// 	$prompt .= "You are an empathetic, professional educator. Write an assessment progress report for {$request['student']}.\n\n";
//
// 	$prompt .= "### CONTEXT\n";
// 	$prompt .= "- Subject/Topics Covered: Malaysian {$year}{$subject} syllabus - {$request['topic']}\n";
// 	$prompt .= "- Assessment Scale: 0 to 10 (10 is excellent)\n\n";
//
// 	$prompt .= "### INPUT DATA\n";
// 	$prompt .= "<matrix_data>\n{$request['matrix']}\n</matrix_data>\n";
//
// 	if (!empty($request['text'])) {
// 		$prompt .= "<teacher_observations>\n{$request['text']}\n</teacher_observations>\n\n";
// 	}
//
//     $prompt .= "### WRITING REQUIREMENTS\n";
//     $prompt .= "1. STRUCTURE: A single cohesive narrative paragraph (no bullets).\n";
//     $prompt .= "2. MANDATORY OPENER: You MUST begin with a specific, warm, positive observation regarding the student's engagement or attitude.\n";
//     $prompt .= "3. DATA INTEGRATION: Seamlessly blend the <matrix_data> scores into the narrative. For scores < 6, use phrases like 'working towards' or 'is developing'.\n";
//     $prompt .= "4. MANDATORY CLOSER: You MUST end with a supportive, forward-looking statement that encourages future growth.\n";
//     $prompt .= "5. TONE: Professional yet accessible to parents (avoid overly academic jargon).\n\n";
//
//     $prompt .= "### OUTPUT INSTRUCTIONS\n";
//     $prompt .= "Generate 3 distinct variations of this report. \n";
//     $prompt .= "Vary the vocabulary and focus in each while keeping the Opener/Closer rules.\n";
//     $prompt .= "SEPARATE each variation ONLY with the delimiter '---'.\n";
//     $prompt .= "DO NOT include labels like 'Variation 1' or introductory text.";
//
// 	return $prompt;
// }
function behavior_prompt($request)
{
    $year = $request['year'] ? $request['year'].', ' : '';
    $subject = $request['subject'] ? $request['subject'].' subject' : '';

    $prompt = "### ROLE\n";
    $prompt .= "You are a professional educator providing a factual, grounded progress report for {$request['student']}. Use Third Person only.\n\n";

    $prompt .= "### CONTEXT\n";
    $prompt .= "- Subject/Topics Covered: Malaysian {$year}{$subject} syllabus - {$request['topic']}\n";
    $prompt .= "- Assessment Scale: 0 to 10 (10 is excellent)\n\n";

    $prompt .= "### INPUT DATA\n";
    $prompt .= "<matrix_data>\n{$request['matrix']}\n</matrix_data>\n";

    if (!empty($request['text'])) {
        $prompt .= "<teacher_observations>\n{$request['text']}\n</teacher_observations>\n\n";
    }

    $prompt .= "### STERNNESS & TRUTH-TELLING RULES\n";
    $prompt .= "1. NO FALSE OPTIMISM: If a student has a score < 4, do NOT say they have a 'growing interest' or 'enthusiasm'. This is a contradiction.\n";
    $prompt .= "2. REALITY CHECK: A score of 3/10 is a failure of effort. Use phrases like 'struggling to remain engaged' or 'not meeting the expected level of commitment'.\n";
    $prompt .= "3. OBSERVATION PRIORITY: The <teacher_observations> are the truth. If the teacher says 'needs to buck up', the report must sound serious, not 'gentle'.\n";
    $prompt .= "4. DISCIPLINE VS ATTITUDE: A 5/10 in self-discipline is still a 'Warning'. Describe it as 'requiring significant improvement' rather than 'having potential'.\n\n";
    
    $prompt .= "### OUTPUT INSTRUCTIONS\n";
    $prompt .= "You do not need to repeat the matrix scores in your response. They are visible in the assessment card.\n";
    $prompt .= "No headings, labels or bold titles in your response.\n";
    $prompt .= "Generate 3 variations separated ONLY by '---'.";

    return $prompt;
}

/**
 * @uses \behavior_prompt()
 */
fusion_add_hook('ai_prompt', 'behavior_prompt');
