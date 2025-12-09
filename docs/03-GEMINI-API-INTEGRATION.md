# Gemini API Integration - Complete Guide

## Table of Contents
1. [Overview](#overview)
2. [API Configuration](#api-configuration)
3. [How Gemini API Works](#how-gemini-api-works)
4. [API Call Implementation](#api-call-implementation)
5. [AI Conversation System](#ai-conversation-system)
6. [Response Parsing](#response-parsing)
7. [ATS Score Analysis](#ats-score-analysis)
8. [Error Handling](#error-handling)

---

## Overview

The application integrates **Google Gemini API** (Generative AI) for:
- ✅ Conversational resume building
- ✅ ATS score analysis
- ✅ Job description matching
- ✅ Resume optimization suggestions
- ✅ Document text extraction (PDF/DOCX)

### API Details
- **Provider**: Google Generative AI
- **Model**: `gemini-2.0-flash-exp`
- **Endpoint**: `https://generativelanguage.googleapis.com/v1beta/models/`
- **Authentication**: API Key (query parameter)
- **Format**: JSON (REST API)

---

## API Configuration

**File**: `config/gemini.php`

### Environment Variables (.env)

```env
GEMINI_API_KEY=your_actual_api_key_here
```

### Configuration Code

```php
<?php
// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;  // Skip comments
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// Define constants
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: $_ENV['GEMINI_API_KEY'] ?? '');
define('GEMINI_MODEL_NAME', 'gemini-2.0-flash-exp');
define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL_NAME . ':generateContent');
```

### Why .env File?
- ✅ Keep API keys secret (not in version control)
- ✅ Easy to change without code modification
- ✅ Different keys for dev/production
- ✅ Security best practice

---

## How Gemini API Works

### Request-Response Flow

```
1. Application sends HTTP POST request
   ↓
2. Request includes:
   - API Key (URL parameter)
   - Prompt (JSON body)
   - Generation config (temperature, tokens)
   ↓
3. Gemini processes prompt using AI model
   ↓
4. Gemini returns JSON response
   ↓
5. Application parses response
   ↓
6. Extract text from JSON structure
```

### API Request Structure

```json
{
  "contents": [
    {
      "parts": [
        {
          "text": "Your prompt here"
        }
      ]
    }
  ],
  "generationConfig": {
    "temperature": 0.4,
    "topK": 32,
    "topP": 1,
    "maxOutputTokens": 4096
  }
}
```

### API Response Structure

```json
{
  "candidates": [
    {
      "content": {
        "parts": [
          {
            "text": "AI generated response here"
          }
        ]
      },
      "finishReason": "STOP"
    }
  ]
}
```

---

## API Call Implementation

**File**: `config/gemini.php` (Function: `callGeminiAPI`)

### Complete Function Code

```php
function callGeminiAPI($prompt, $systemInstruction = '') {
    $apiKey = GEMINI_API_KEY;

    // Validate API key
    if ($apiKey === 'YOUR_GEMINI_API_KEY_HERE' || empty($apiKey)) {
        return [
            'success' => false,
            'error' => 'Gemini API key not configured'
        ];
    }

    // Build URL with API key
    $url = GEMINI_API_ENDPOINT . '?key=' . $apiKey;

    // Combine system instruction with prompt
    $fullPrompt = $prompt;
    if ($systemInstruction) {
        $fullPrompt = $systemInstruction . "\n\n" . $prompt;
    }

    // Prepare request data
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $fullPrompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.4,        // Creativity (0-1, lower = more focused)
            'topK' => 32,                // Token sampling
            'topP' => 1,                 // Nucleus sampling
            'maxOutputTokens' => 4096,   // Max response length
        ]
    ];

    // Encode to JSON
    $jsonData = json_encode($data);
    if ($jsonData === false) {
        return [
            'success' => false,
            'error' => 'Failed to encode request: ' . json_last_error_msg()
        ];
    }

    // Make cURL request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Handle cURL errors
    if ($curlError) {
        return [
            'success' => false,
            'error' => 'CURL error: ' . $curlError
        ];
    }

    // Handle HTTP errors
    if ($httpCode !== 200) {
        $errorMsg = 'API request failed with status: ' . $httpCode;
        $result = json_decode($response, true);
        if (isset($result['error']['message'])) {
            $errorMsg .= ' - ' . $result['error']['message'];
        }
        return [
            'success' => false,
            'error' => $errorMsg,
            'response' => $response
        ];
    }

    // Parse response
    $result = json_decode($response, true);

    // Extract text from response
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return [
            'success' => true,
            'text' => $result['candidates'][0]['content']['parts'][0]['text']
        ];
    }

    // Handle blocked/filtered content
    if (isset($result['candidates'][0]['finishReason']) &&
        $result['candidates'][0]['finishReason'] !== 'STOP') {
        $reason = $result['candidates'][0]['finishReason'];
        return [
            'success' => false,
            'error' => 'Content filtered/blocked: ' . $reason
        ];
    }

    // Unexpected response format
    return [
        'success' => false,
        'error' => 'Unexpected API response format'
    ];
}
```

### Generation Config Explained

| Parameter | Value | Purpose |
|-----------|-------|---------|
| `temperature` | 0.4 | Controls randomness (0=deterministic, 1=creative) |
| `topK` | 32 | Limits token selection to top K options |
| `topP` | 1 | Nucleus sampling threshold |
| `maxOutputTokens` | 4096 | Maximum length of AI response |

### Why These Values?
- **temperature: 0.4** → Balanced between accuracy and creativity
- **topK: 32** → Reasonable variety without too much randomness
- **maxOutputTokens: 4096** → Long enough for detailed resume content

---

## AI Conversation System

**File**: `api/ai-conversation.php`

This endpoint powers the conversational AI resume builder.

### Request Flow

```javascript
// Frontend sends this:
POST api/ai-conversation.php
{
  "message": "I worked as a software engineer at Google",
  "resumeState": { /* current resume data */ },
  "conversationHistory": [ /* previous messages */ ],
  "templateName": "modern"
}
```

### Backend Processing

```php
<?php
header('Content-Type: application/json');
require_once '../config/session.php';
require_once '../config/gemini.php';

requireLogin();

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$resumeState = $input['resumeState'] ?? [];
$conversationHistory = $input['conversationHistory'] ?? [];
$templateName = $input['templateName'] ?? 'modern';

// Build system instruction (tells AI its role)
$systemInstruction = buildSystemInstruction($templateName, $resumeState);

// Build conversation prompt
$conversationContext = buildConversationPrompt($userMessage, $conversationHistory, $resumeState);

// Call Gemini API
$geminiResponse = callGeminiAPI($conversationContext, $systemInstruction);

if ($geminiResponse['success']) {
    $aiMessage = $geminiResponse['text'];

    // Extract structured data from response
    $extractedData = extractStructuredData($aiMessage, $templateName);

    // Return to frontend
    echo json_encode([
        'success' => true,
        'response' => $conversationalResponse,
        'updates' => $extractedData
    ]);
}
?>
```

### System Instruction (AI Personality)

```php
function buildSystemInstruction($templateName, $resumeState) {
    $instruction = "You are an expert resume writing assistant. ";
    $instruction .= "Your role is to:\n";
    $instruction .= "1. Ask relevant questions to gather resume information\n";
    $instruction .= "2. Extract and structure the information provided\n";
    $instruction .= "3. Provide helpful suggestions\n\n";

    // Template-specific guidance
    if ($templateName === 'academic-standard') {
        $instruction .= "Focus on: research interests, publications, grants, teaching.\n\n";
    } else {
        $instruction .= "Focus on: work experience, skills, achievements.\n\n";
    }

    // Response format instructions
    $instruction .= "IMPORTANT: Respond in this EXACT format:\n\n";
    $instruction .= "CONVERSATIONAL_RESPONSE: [Your friendly response]\n\n";
    $instruction .= "DATA_UPDATES:\n```json\n{\"field_name\": \"value\"}\n```\n\n";

    // Include current resume state
    $instruction .= "Current resume data:\n" . json_encode($resumeState, JSON_PRETTY_PRINT);

    return $instruction;
}
```

### Why Two Parts in Response?

The AI returns:
1. **CONVERSATIONAL_RESPONSE**: Human-friendly message for the chat
2. **DATA_UPDATES**: Structured JSON for updating the resume

Example AI Response:
```
CONVERSATIONAL_RESPONSE: Great! I've added your experience at Google. What were your key achievements there?

DATA_UPDATES:
```json
{
  "experience": [
    {
      "jobTitle": "Software Engineer",
      "company": "Google",
      "dates": "2020-2023",
      "description": "Worked on backend systems"
    }
  ]
}
```
```

---

## Response Parsing

**File**: `api/ai-conversation.php` (Function: `extractStructuredData`)

### Parsing Function

```php
function extractStructuredData($aiMessage, $templateName) {
    $updates = [];

    // Extract JSON from DATA_UPDATES section
    if (preg_match('/DATA_UPDATES:\s*```json\s*(\{.*?\})\s*```/s', $aiMessage, $matches)) {
        $jsonData = $matches[1];

        // Parse JSON
        $parsedData = json_decode($jsonData, true);

        if ($parsedData && is_array($parsedData)) {
            $updates = $parsedData;
        }
    }

    return $updates;
}
```

### How Regex Works

```php
preg_match('/DATA_UPDATES:\s*```json\s*(\{.*?\})\s*```/s', $aiMessage, $matches)
```

- `/DATA_UPDATES:/` → Literal text "DATA_UPDATES:"
- `\s*` → Any whitespace
- ` ```json` → JSON code block marker
- `(\{.*?\})` → Capture group: match JSON object
- `\s*```  ` → Closing code block
- `/s` → Dot matches newlines (multiline mode)

### Frontend Update Application

**File**: `js/ai-editor.js`

```javascript
if (data.updates && Object.keys(data.updates).length > 0) {
    applyUpdates(data.updates);  // Update resumeState
    updatePreview();              // Refresh visual preview
}

function applyUpdates(updates) {
    // Personal details
    if (updates.personal_details) {
        Object.assign(resumeState.personal_details, updates.personal_details);
    }

    // Experience (array)
    if (updates.experience) {
        resumeState.experience = updates.experience;
    }

    // Education (array)
    if (updates.education) {
        resumeState.education = updates.education;
    }

    // Skills (string)
    if (updates.skills) {
        resumeState.skills = updates.skills;
    }
}
```

---

## ATS Score Analysis

**File**: `api/analyze-ats-score.php`

### How ATS Analysis Works

```php
<?php
require_once '../config/gemini.php';

// User uploads resume PDF/DOCX
$resumeText = extractTextFromFile($_FILES['resume_file']);
$jobDescription = $_POST['job_description'];

// Build analysis prompt
$prompt = "
Analyze this resume for ATS compatibility.

RESUME:
$resumeText

JOB DESCRIPTION:
$jobDescription

Provide:
1. Overall ATS score (0-100)
2. Keyword matching score
3. Formatting score
4. Experience relevance score
5. List of matched keywords
6. List of missing keywords
7. Specific improvements
8. Strengths

Respond in JSON format.
";

// Call Gemini
$result = callGeminiAPI($prompt);

// Parse AI response
$analysis = json_decode($result['text'], true);

// Save to database
$stmt = $conn->prepare("
    INSERT INTO ats_scores (
        user_id, resume_text, job_description,
        overall_score, keywords_score, formatting_score,
        improvements, strengths, keywords_found, keywords_missing
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("issiiissss",
    $userId,
    $resumeText,
    $jobDescription,
    $analysis['overall_score'],
    $analysis['keywords_score'],
    $analysis['formatting_score'],
    json_encode($analysis['improvements']),
    json_encode($analysis['strengths']),
    json_encode($analysis['keywords_found']),
    json_encode($analysis['keywords_missing'])
);

$stmt->execute();
?>
```

### ATS Scoring Breakdown

| Category | Weight | What It Checks |
|----------|--------|----------------|
| **Keywords** | 30% | Job-relevant terms present |
| **Formatting** | 20% | ATS-parseable structure |
| **Experience** | 25% | Relevant work history |
| **Contact Info** | 10% | Phone, email present |
| **Content Structure** | 15% | Section organization |

### Gemini's Role in ATS

Gemini analyzes:
1. **Keyword extraction** from job description
2. **Semantic matching** (not just exact words)
3. **Context understanding** (experience relevance)
4. **Natural language recommendations**

---

## Error Handling

### Common Errors and Solutions

#### 1. API Key Not Set

```php
if (empty(GEMINI_API_KEY)) {
    return [
        'success' => false,
        'error' => 'API key not configured'
    ];
}
```

#### 2. Rate Limiting

```php
if ($httpCode === 429) {
    return [
        'success' => false,
        'error' => 'Rate limit exceeded. Please try again later.'
    ];
}
```

#### 3. Content Blocked

```php
if ($finishReason === 'SAFETY') {
    return [
        'success' => false,
        'error' => 'Content blocked by safety filters'
    ];
}
```

#### 4. Network Error

```php
if ($curlError) {
    error_log("Gemini API cURL error: " . $curlError);
    return [
        'success' => false,
        'error' => 'Network error'
    ];
}
```

### Frontend Error Display

```javascript
if (!data.success) {
    addMessageToChat('assistant', 'Sorry, I encountered an error. Please try again.');
    console.error('API Error:', data.error);
}
```

---

## Usage Examples

### Example 1: Ask AI to Add Experience

**User Input**:
```
I worked as a Senior Developer at Microsoft from 2018-2022.
I led a team of 5 engineers and improved system performance by 40%.
```

**AI Response**:
```
CONVERSATIONAL_RESPONSE: Excellent! I've added your Microsoft experience. That's impressive leadership and performance improvement. What technologies did you use?

DATA_UPDATES:
```json
{
  "experience": [
    {
      "jobTitle": "Senior Developer",
      "company": "Microsoft",
      "dates": "2018-2022",
      "description": "Led team of 5 engineers. Improved system performance by 40%."
    }
  ]
}
```
```

### Example 2: Check ATS Score

**Input**: Upload resume PDF + Job description

**Gemini Analysis**:
```json
{
  "overall_score": 82,
  "keywords_score": 85,
  "formatting_score": 90,
  "keywords_found": ["Python", "React", "AWS", "Leadership"],
  "keywords_missing": ["Kubernetes", "CI/CD", "Agile"],
  "improvements": [
    "Add Kubernetes experience if applicable",
    "Mention Agile methodology",
    "Include more quantifiable achievements"
  ],
  "strengths": [
    "Strong technical keyword coverage",
    "Clear ATS-friendly formatting",
    "Quantified achievements present"
  ]
}
```

---

## Viva Questions & Answers

**Q: How does Gemini API work?**
A: It's a REST API that accepts text prompts via HTTP POST and returns AI-generated text in JSON format.

**Q: What model are you using?**
A: `gemini-2.0-flash-exp` - Google's fast experimental model for text generation.

**Q: How do you secure the API key?**
A: Stored in `.env` file (not in version control), loaded via `config/gemini.php`.

**Q: How does the AI update the resume?**
A: AI returns structured JSON in DATA_UPDATES section, which we parse and apply to the resume state.

**Q: What is temperature in API config?**
A: Controls randomness (0-1). 0.4 balances accuracy and creativity.

**Q: How does ATS analysis work?**
A: Gemini analyzes resume text against job description, returns scores and suggestions in JSON.

**Q: How do you parse AI responses?**
A: Using regex to extract JSON from DATA_UPDATES code blocks, then `json_decode()`.

**Q: What happens if API fails?**
A: We return error response, log details, and show user-friendly message.
