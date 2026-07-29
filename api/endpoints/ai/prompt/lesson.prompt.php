<?php
/*
 * student is excellent when it comes to listening the subject being taught. when asked, student is very like to answer question. student also very keen to learn and will start automatically.

however, the student is very bad at understanding what is written. student can do basic science but cannot understand what is prism and colors. student also do not know why clouds formed and how fish breath underwater.
 */

/**
 * Generates a structured lesson prompt for an AI model to draft a
 * student progress report.
 *
 * This method constructs a persona-driven prompt that synthesizes quantitative
 * matrix data and qualitative teacher notes into a cohesive, professional,
 * and empathetic narrative.
 */
function lesson_prompt($request)
{
	$year = $request['year'] ? $request['year'].', ' : '';
	$subject = $request['subject'] ? $request['subject'].' subject' : '';

	$prompt = "### ROLE\n";
	$prompt .= "You are an objective academic evaluator for a Malaysian school. Write a progress report focusing on syllabus mastery for {$request['student']}. Use Third Person only.\n\n";

	$prompt .= "### CONTEXT\n";
	$prompt .= "- Subject/Topics Covered: Malaysian {$year}{$subject} syllabus - {$request['topic']}\n";
	$prompt .= "- Assessment Scale: 0 to 10 (10 is excellent)\n\n";


	$prompt .= "### INPUT DATA\n";
	$prompt .= "<matrix_data>\n{$request['matrix']}\n</matrix_data>\n";

	if (!empty($request['text'])) {
		$prompt .= "<teacher_observations>\n{$request['text']}\n</teacher_observations>\n\n";
	}

	$prompt .= "### TONE & TRUTH-TELLING RULES\n";
	$prompt .= "1. NO 'DEVELOPING' FLUFF: A 5/10 across all categories indicates stagnation, not growth. Do not use phrases like 'making steady progress'.\n";
	$prompt .= "2. Answer in the context of the relevant topic of the syllabus in relation to the <matrix_data> scores.\n";
	$prompt .= "3. THE 'MID-POINT' REALITY: Frame 5/10 as 'attaining only the bare minimum' or 'borderline performance'. It is a warning that the student is surviving but not learning.\n";
	$prompt .= "4. OBSERVATION PRIORITY: The <teacher_observations> are the truth. If the teacher says 'needs to buck up', the report must sound serious, not 'gentle'.\n";
	$prompt .= "5. LINGUISTIC STYLE: Professional, parent-friendly, and concise. Use Third Person only.\n";
	$prompt .= "6. CONTRADICTION CHECK: If the teacher notes mention gaps, do not suggest the student is 'on the right track'.\n\n";

	$prompt .= "### WRITING REQUIREMENTS\n";
	$prompt .= "1. OPENER: A brief acknowledgment of the student's attendance or general presence.\n";
	$prompt .= "2. THE ACADEMIC CORE: Merge the scores into a narrative. If a student has a score lower than 6/10 scores in both homework and quizzes, it will suggest a lack of revision and shallow concept mastery.\n";
	$prompt .= "3. MANDATORY PIVOT: Use a phrase like 'However, to excel in the upcoming assessments...(give your suggestions)'\n";
	$prompt .= "4. CLOSER: A collaborative call to action focusing on bridging the remaining knowledge gap.\n\n";

	$prompt .= "### OUTPUT INSTRUCTIONS\n";
	$prompt .= "You do not need to repeat the matrix scores in your response. They are visible in the assessment card.\n";
	$prompt .= "No headings, labels or bold titles in your response.\n";
	$prompt .= "Generate 3 variations separated ONLY by '---'.";

	return $prompt;
}



/**
 * @uses \lesson_prompt()
 */
fusion_add_hook('ai_prompt', 'lesson_prompt');
