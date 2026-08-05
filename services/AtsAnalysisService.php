<?php

require_once __DIR__ . '/../config/gemini.php';

function performATSAnalysis(string $resumeText, string $jobDescription = ''): array
{
    $result = callGeminiAPI(buildAnalysisPrompt($resumeText, $jobDescription));
    if (!$result['success']) {
        return $result;
    }

    $analysis = decodeGeminiJsonObject($result['text'], [
        'overall_score' => 'number',
        'formatting_score' => 'number',
        'keywords_score' => 'number',
        'content_structure_score' => 'number',
        'contact_info_score' => 'number',
        'experience_format_score' => 'number',
        'technical_score' => 'number',
        'improvements' => 'list',
        'strengths' => 'list',
    ]);
    if ($analysis === null) {
        return ['success' => false, 'error' => 'AI service returned an invalid analysis.'];
    }

    $scoreLimits = [
        'overall_score' => 100,
        'formatting_score' => 25,
        'keywords_score' => 25,
        'content_structure_score' => 20,
        'contact_info_score' => 10,
        'experience_format_score' => 10,
        'technical_score' => 10,
    ];
    foreach ($scoreLimits as $field => $maximum) {
        if (!is_numeric($analysis[$field])
            || (int) $analysis[$field] < 0
            || (int) $analysis[$field] > $maximum) {
            return ['success' => false, 'error' => 'AI service returned invalid scores.'];
        }
        $analysis[$field] = (int) $analysis[$field];
    }

    if (!is_array($analysis['improvements']) || !is_array($analysis['strengths'])) {
        return ['success' => false, 'error' => 'AI service returned invalid recommendations.'];
    }
    foreach ($analysis['strengths'] as $strength) {
        if (!is_string($strength) || strlen($strength) > 1000) {
            return ['success' => false, 'error' => 'AI service returned invalid strengths.'];
        }
    }
    foreach ($analysis['improvements'] as $improvement) {
        if (!is_array($improvement)) {
            return ['success' => false, 'error' => 'AI service returned invalid recommendations.'];
        }
        foreach (['category', 'issue', 'suggestion'] as $field) {
            if (!isset($improvement[$field])
                || !is_string($improvement[$field])
                || strlen($improvement[$field]) > 2000) {
                return ['success' => false, 'error' => 'AI service returned invalid recommendations.'];
            }
        }
    }
    foreach (['keywords_found', 'keywords_missing'] as $field) {
        if (isset($analysis[$field]) && !is_array($analysis[$field])) {
            return ['success' => false, 'error' => 'AI service returned invalid keywords.'];
        }
    }

    return ['success' => true] + $analysis + [
        'keywords_found' => [],
        'keywords_missing' => [],
    ];
}

function buildAnalysisPrompt(string $resumeText, string $jobDescription): string
{
    $hasJobDescription = trim($jobDescription) !== '';
    $prompt = "You are an ATS analyzer. Evaluate the resume and return only valid JSON.\n\n";
    $prompt .= "RESUME TEXT:\n{$resumeText}\n";
    if ($hasJobDescription) {
        $prompt .= "\nJOB DESCRIPTION:\n{$jobDescription}\n";
    }

    $prompt .= <<<'PROMPT'

Score these categories:
- formatting_score: 0-25
- keywords_score: 0-25
- content_structure_score: 0-20
- contact_info_score: 0-10
- experience_format_score: 0-10
- technical_score: 0-10

The overall_score must be 0-100. Return this exact object shape:
{
  "overall_score": 0,
  "formatting_score": 0,
  "keywords_score": 0,
  "content_structure_score": 0,
  "contact_info_score": 0,
  "experience_format_score": 0,
  "technical_score": 0,
  "strengths": ["specific strength"],
  "improvements": [
    {"category": "Formatting", "issue": "specific issue", "suggestion": "actionable fix"}
  ],
  "keywords_found": [],
  "keywords_missing": []
}

Use standard ATS criteria: simple layout, standard headings, readable contact
details, reverse-chronological experience, contextual keywords, measurable
achievements, consistent dates, and clean spelling/punctuation.
PROMPT;

    if ($hasJobDescription) {
        $prompt .= "\nPopulate keywords_found and keywords_missing from the job description.";
    }

    return $prompt;
}
