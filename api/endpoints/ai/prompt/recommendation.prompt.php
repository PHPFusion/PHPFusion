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
function recommendation_prompt($request)
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
	$prompt .= "1. NO FALSE OPTIMISM: If Mastery is < 40%, do not praise effort. State that the student has failed to grasp the core concepts of the current module.\n";
	$prompt .= "2. REALITY CHECK: A negative Progression Level is a critical delay. Use phrases like 'lagging behind the national standard' or 'urgent pace correction required'.\n";
	$prompt .= "3. OBSERVATION PRIORITY: The <teacher_observations> are final. If the teacher notes 'lack of focus', ignore high scores and address the behavioral hurdle.\n";
	$prompt .= "4. QUALITY VS QUANTITY: If Milestone Rate is high but Mastery is low, criticize the student for rushing through content without understanding.\n\n";
	
	$prompt .= "### MANDATORY IMPROVEMENT METHODS\n";
	$prompt .= "5. TARGETED REMEDIATION: If Mastery is low, prescribe specific revision of foundational sub-topics before moving forward.\n";
	$prompt .= "6. PACING STRATEGY: If the Progression Level is behind, recommend a 'recovery schedule' or increased weekly hours to catch up.\n";
	$prompt .= "7. ACTIONABLE STEPS: Every report must conclude with a specific method to improve (e.g., 'Must focus on topical drills for Fractions', 'Requires closer supervision on independent tasks').\n\n";
        
    $prompt .= "### OUTPUT INSTRUCTIONS\n";
    $prompt .= "You do not need to repeat the matrix scores in your response. They are visible in the assessment card\n";
    $prompt .= "No headings, labels or bold titles in your response.\n";
    $prompt .= "Generate 3 variations separated ONLY by '---'.";

    return $prompt;
}

/**
 * @uses \recommendation_prompt()
 */
fusion_add_hook('ai_prompt', 'recommendation_prompt');
