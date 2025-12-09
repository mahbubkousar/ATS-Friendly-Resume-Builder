# ResumeSync - Use Case Documentation

## Table of Contents
1. [Use Case Diagram](#use-case-diagram)
2. [Actors Description](#actors-description)
3. [Use Case Tables](#use-case-tables)
4. [Use Case Relationships](#use-case-relationships)

---

## Use Case Diagram

```
                                    ResumeSync System
    ┌─────────────────────────────────────────────────────────────────────────┐
    │                                                                           │
    │    ┌──────────────────────┐        ┌──────────────────────┐            │
    │    │  Register Account    │        │  Login to System     │            │
    │    └──────────────────────┘        └──────────────────────┘            │
    │             │                                 │                          │
    │             │                                 │                          │
┌───────┐        │         ┌───────────────────────┴──────────────┐          │
│ Guest │────────┘         │                                       │          │
│ User  │                  │                                       │          │
└───────┘                  │                                       │          │
                           │                                       │          │
                           │                                       │          │
                    ┌──────▼──────┐                                │          │
                    │             │                                │          │
                    │  Registered │                                │          │
                    │    User     │                                │          │
                    │             │                                │          │
                    └──────┬──────┘                                │          │
                           │                                       │          │
    ┌──────────────────────┼───────────────────────────────────────┘          │
    │                      │                                                  │
    │  ┌───────────────────▼──────────────────┐                              │
    │  │     Create Resume                    │                              │
    │  │  ┌────────────────────────────────┐  │                              │
    │  │  │ Select Template                │  │                              │
    │  │  └────────────────────────────────┘  │                              │
    │  │  ┌────────────────────────────────┐  │                              │
    │  │  │ Use Manual Editor              │  │◄──────────include────────┐  │
    │  │  └────────────────────────────────┘  │                          │  │
    │  │  ┌────────────────────────────────┐  │                          │  │
    │  │  │ Use AI Editor                  │  │◄──────────include────┐   │  │
    │  │  └────────────────────────────────┘  │                      │   │  │
    │  └──────────────────────────────────────┘                      │   │  │
    │                      │                                          │   │  │
    │  ┌───────────────────▼──────────────────┐                      │   │  │
    │  │     Edit Resume                      │                      │   │  │
    │  │  ┌────────────────────────────────┐  │                      │   │  │
    │  │  │ Update Personal Details        │  │                      │   │  │
    │  │  └────────────────────────────────┘  │                      │   │  │
    │  │  ┌────────────────────────────────┐  │                      │   │  │
    │  │  │ Add/Edit Experience            │  │                      │   │  │
    │  │  └────────────────────────────────┘  │                      │   │  │
    │  │  ┌────────────────────────────────┐  │                      │   │  │
    │  │  │ Add/Edit Education             │  │                      │   │  │
    │  │  └────────────────────────────────┘  │                      │   │  │
    │  └──────────────────────────────────────┘                      │   │  │
    │                      │                                          │   │  │
    │  ┌───────────────────▼──────────────────┐                      │   │  │
    │  │     Save Resume                      │                      │   │  │
    │  └──────────────────────────────────────┘                      │   │  │
    │                      │                                          │   │  │
    │  ┌───────────────────▼──────────────────┐                      │   │  │
    │  │     Download Resume as PDF           │                      │   │  │
    │  └──────────────────────────────────────┘                      │   │  │
    │                      │                                          │   │  │
    │  ┌───────────────────▼──────────────────┐                      │   │  │
    │  │     View Resume Preview              │                      │   │  │
    │  └──────────────────────────────────────┘                      │   │  │
    │                                                                 │   │  │
    │  ┌────────────────────────────────────────┐                    │   │  │
    │  │     Delete Resume                      │                    │   │  │
    │  └────────────────────────────────────────┘                    │   │  │
    │                                                                 │   │  │
    │  ┌────────────────────────────────────────┐                    │   │  │
    │  │     Share Resume                       │                    │   │  │
    │  │  ┌──────────────────────────────────┐  │                    │   │  │
    │  │  │ Generate Share Link              │  │                    │   │  │
    │  │  └──────────────────────────────────┘  │                    │   │  │
    │  │  ┌──────────────────────────────────┐  │                    │   │  │
    │  │  │ Toggle Public/Private            │  │                    │   │  │
    │  │  └──────────────────────────────────┘  │                    │   │  │
    │  └────────────────────────────────────────┘                    │   │  │
    │                                                                 │   │  │
    │  ┌────────────────────────────────────────┐                    │   │  │
    │  │     Check ATS Score                    │────────────────────┘   │  │
    │  │  ┌──────────────────────────────────┐  │                        │  │
    │  │  │ Upload Resume File               │  │                        │  │
    │  │  └──────────────────────────────────┘  │                        │  │
    │  │  ┌──────────────────────────────────┐  │                        │  │
    │  │  │ Enter Job Description            │  │                        │  │
    │  │  └──────────────────────────────────┘  │                        │  │
    │  │  ┌──────────────────────────────────┐  │                        │  │
    │  │  │ View Analysis Results            │  │                        │  │
    │  │  └──────────────────────────────────┘  │                        │  │
    │  └────────────────────────────────────────┘                        │  │
    │                                                                     │  │
    │  ┌────────────────────────────────────────┐                        │  │
    │  │     AI Resume Generation               │────────────────────────┘  │
    │  │  ┌──────────────────────────────────┐  │                           │
    │  │  │ Chat with AI Assistant           │  │                           │
    │  │  └──────────────────────────────────┘  │                           │
    │  │  ┌──────────────────────────────────┐  │                           │
    │  │  │ Auto-fill Resume Fields          │  │                           │
    │  │  └──────────────────────────────────┘  │                           │
    │  └────────────────────────────────────────┘                           │
    │                                                                        │
    │  ┌────────────────────────────────────────┐                           │
    │  │     Track Job Applications             │                           │
    │  │  ┌──────────────────────────────────┐  │                           │
    │  │  │ Add Job Application              │  │                           │
    │  │  └──────────────────────────────────┘  │                           │
    │  │  ┌──────────────────────────────────┐  │                           │
    │  │  │ Update Application Status        │  │                           │
    │  │  └──────────────────────────────────┘  │                           │
    │  │  ┌──────────────────────────────────┐  │                           │
    │  │  │ View Application Timeline        │  │                           │
    │  │  └──────────────────────────────────┘  │                           │
    │  └────────────────────────────────────────┘                           │
    │                                                                        │
    │  ┌────────────────────────────────────────┐                           │
    │  │     Manage User Profile                │                           │
    │  │  ┌──────────────────────────────────┐  │                           │
    │  │  │ Update Personal Information      │  │                           │
    │  │  └──────────────────────────────────┘  │                           │
    │  │  ┌──────────────────────────────────┐  │                           │
    │  │  │ Manage Experience History        │  │                           │
    │  │  └──────────────────────────────────┘  │                           │
    │  │  ┌──────────────────────────────────┐  │                           │
    │  │  │ Manage Education History         │  │                           │
    │  │  └──────────────────────────────────┘  │                           │
    │  └────────────────────────────────────────┘                           │
    │                                                                        │
    │  ┌────────────────────────────────────────┐                           │
    │  │     Logout                             │                           │
    │  └────────────────────────────────────────┘                           │
    │                                                                        │
    └────────────────────────────────────────────────────────────────────────┘
                                        │
                                        │
                                        │ <<uses>>
                                        │
                                        ▼
                             ┌──────────────────────┐
                             │   Google Gemini AI   │
                             │   (External System)  │
                             └──────────────────────┘
```

---

## Actors Description

### 1. Guest User
**Description**: An unregistered visitor to the system

**Capabilities**:
- View landing page
- Register for new account
- Login to existing account
- Access public ATS score checker (limited)

**Goals**:
- Explore the system features
- Create an account to access full features

---

### 2. Registered User
**Description**: An authenticated user with full system access

**Capabilities**:
- All guest capabilities
- Create and manage multiple resumes
- Use AI-powered resume builder
- Check ATS scores with detailed analysis
- Track job applications
- Download resumes as PDF
- Share resumes via link
- Manage personal profile

**Goals**:
- Create ATS-optimized resumes
- Get hired by tracking applications
- Improve resume quality with AI assistance

---

### 3. Google Gemini AI (External System)
**Description**: External AI service for natural language processing

**Interactions**:
- Receives prompts from the system
- Generates resume content
- Analyzes resume ATS compatibility
- Provides improvement suggestions

---

## Use Case Tables

### Use Case 1: Register Account

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-001 |
| **Use Case Title** | Register Account |
| **Actor** | Guest User |
| **Description** | User creates a new account to access the system |
| **Preconditions** | - User is not logged in<br>- User has valid email address<br>- Email is not already registered |
| **Trigger** | User clicks "Sign Up" or "Register" button |
| **Main Flow** | 1. System displays registration form<br>2. User enters full name, email, password, and phone<br>3. User clicks "Register" button<br>4. System validates input data<br>5. System checks if email already exists<br>6. System hashes password using bcrypt<br>7. System creates new user record in database<br>8. System creates login session<br>9. System redirects user to dashboard<br>10. System displays welcome message |
| **Alternate Flow 1** | **Email Already Exists**<br>5a. System finds existing email<br>5b. System displays error "Email already registered"<br>5c. User returns to step 2 |
| **Alternate Flow 2** | **Invalid Input**<br>4a. System detects invalid/missing data<br>4b. System displays specific error messages<br>4c. User corrects input and returns to step 3 |
| **Alternate Flow 3** | **Weak Password**<br>4a. System detects password < 8 characters<br>4b. System displays "Password must be at least 8 characters"<br>4c. User enters stronger password and returns to step 3 |
| **Post-conditions** | - User account is created in database<br>- User is logged in with active session<br>- User is redirected to dashboard |
| **Business Rules** | - Email must be unique<br>- Password minimum 8 characters<br>- Password stored as bcrypt hash (never plain text)<br>- Session created immediately after registration |
| **Special Requirements** | - Registration must complete within 5 seconds<br>- Password hashing uses bcrypt with cost factor 10<br>- HTTPS required for data transmission |

---

### Use Case 2: Login to System

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-002 |
| **Use Case Title** | Login to System |
| **Actor** | Guest User |
| **Description** | User authenticates with credentials to access the system |
| **Preconditions** | - User has registered account<br>- User is not currently logged in |
| **Trigger** | User navigates to login page or clicks "Login" button |
| **Main Flow** | 1. System displays login form<br>2. User enters email and password<br>3. User optionally checks "Remember me"<br>4. User clicks "Sign In" button<br>5. System validates input<br>6. System queries database for user by email<br>7. System verifies password using password_verify()<br>8. System creates session with user_id<br>9. If "Remember me" checked, system sets cookie<br>10. System redirects to dashboard<br>11. System displays user's resumes |
| **Alternate Flow 1** | **Invalid Credentials**<br>7a. Password verification fails OR email not found<br>7b. System displays "Invalid email or password"<br>7c. User returns to step 2 |
| **Alternate Flow 2** | **Empty Fields**<br>5a. System detects missing email or password<br>5b. System displays "Please enter both email and password"<br>5c. User returns to step 2 |
| **Alternate Flow 3** | **Account Suspended**<br>7a. System detects account_status = 'suspended'<br>7b. System displays "Account suspended. Contact support"<br>7c. Login flow terminates |
| **Post-conditions** | - User is authenticated<br>- Session created with user data<br>- User redirected to dashboard<br>- Optional cookie set for 30 days |
| **Business Rules** | - Failed login attempts don't reveal if email exists<br>- Session expires after 24 hours of inactivity<br>- Remember me cookie valid for 30 days |
| **Special Requirements** | - Login must complete within 3 seconds<br>- Password never sent or stored in plain text<br>- HTTPS required |

---

### Use Case 3: Create Resume

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-003 |
| **Use Case Title** | Create Resume |
| **Actor** | Registered User |
| **Description** | User creates a new resume from scratch |
| **Preconditions** | - User is logged in<br>- User is on dashboard |
| **Trigger** | User clicks "Create New Resume" button |
| **Main Flow** | 1. System displays template selection modal<br>2. User selects template (Modern/Professional/Academic)<br>3. System loads editor page for selected template<br>4. System displays empty resume form<br>5. System shows real-time preview in iframe<br>6. User enters resume title<br>7. User fills personal details (name, email, phone, etc.)<br>8. System updates preview in real-time<br>9. User adds experience entries<br>10. User adds education entries<br>11. User adds skills<br>12. System auto-saves every 30 seconds<br>13. User clicks "Save Resume" button<br>14. System validates and saves to database<br>15. System displays success notification<br>16. System assigns resume_id to new resume |
| **Alternate Flow 1** | **User Exits Without Saving**<br>13a. User navigates away or closes tab<br>13b. Last auto-saved version is retained<br>13c. User can resume editing later |
| **Alternate Flow 2** | **Template Change**<br>3a. User clicks "Change Template"<br>3b. System shows template selection again<br>3c. System migrates data to new template<br>3d. Flow continues from step 4 |
| **Alternate Flow 3** | **Validation Error**<br>14a. System detects missing required field (resume title)<br>14b. System displays "Resume title is required"<br>14c. User enters title and returns to step 13 |
| **Post-conditions** | - New resume record created in database<br>- Resume_id assigned<br>- Resume status set to 'draft'<br>- User can continue editing or download |
| **Business Rules** | - Each user can have unlimited resumes<br>- Resume title must be unique per user<br>- Auto-save preserves work every 30 seconds<br>- Real-time preview always reflects current state |
| **Special Requirements** | - Preview updates within 100ms of input<br>- Auto-save is silent (no notification)<br>- Support for undo/redo operations |

---

### Use Case 4: Edit Resume

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-004 |
| **Use Case Title** | Edit Resume |
| **Actor** | Registered User |
| **Description** | User modifies an existing resume |
| **Preconditions** | - User is logged in<br>- User has at least one existing resume<br>- User has permission to edit the resume |
| **Trigger** | User clicks "Edit" button on a resume card in dashboard |
| **Main Flow** | 1. System retrieves resume data from database<br>2. System loads appropriate editor based on template<br>3. System populates form fields with existing data<br>4. System renders preview with current content<br>5. User modifies any fields (personal details, experience, etc.)<br>6. System updates preview in real-time<br>7. System auto-saves changes every 30 seconds<br>8. User clicks "Save Resume" button<br>9. System validates changes<br>10. System updates resume record in database<br>11. System updates `updated_at` timestamp<br>12. System displays "Resume updated successfully"<br>13. User returns to dashboard or continues editing |
| **Alternate Flow 1** | **Add New Experience Entry**<br>5a. User clicks "Add Experience"<br>5b. System displays new empty experience form<br>5c. User fills experience details<br>5d. System adds entry to resume state<br>5e. System updates preview<br>5f. Flow continues from step 7 |
| **Alternate Flow 2** | **Delete Section**<br>5a. User clicks delete icon on experience/education entry<br>5b. System confirms "Are you sure?"<br>5c. User confirms<br>5d. System removes entry from state<br>5e. System updates preview<br>5f. Flow continues from step 7 |
| **Alternate Flow 3** | **Resume Not Found**<br>1a. System cannot find resume_id for current user<br>1b. System displays "Resume not found"<br>1c. System redirects to dashboard |
| **Post-conditions** | - Resume record updated in database<br>- Updated_at timestamp refreshed<br>- Changes visible in dashboard<br>- Previous version overwritten |
| **Business Rules** | - Only resume owner can edit<br>- All changes are saved to same resume_id<br>- Version history not maintained (latest version only)<br>- Template cannot be changed after creation |
| **Special Requirements** | - Load time < 2 seconds for resumes up to 10 pages<br>- Support concurrent editing from multiple tabs<br>- Graceful handling of connection loss |

---

### Use Case 5: Download Resume as PDF

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-005 |
| **Use Case Title** | Download Resume as PDF |
| **Actor** | Registered User |
| **Description** | User exports resume to PDF format for job applications |
| **Preconditions** | - User is logged in<br>- User has created/opened a resume<br>- Resume has content to export |
| **Trigger** | User clicks "Download PDF" button in editor |
| **Main Flow** | 1. System calls JavaScript downloadPDF() function<br>2. System accesses iframe window containing resume<br>3. System triggers browser print dialog (window.print())<br>4. Browser applies @media print CSS styles<br>5. Browser renders resume for printing<br>6. User selects "Save as PDF" option<br>7. User chooses save location and filename<br>8. Browser generates PDF file<br>9. PDF downloads to user's computer<br>10. System records download in database (optional)<br>11. System displays success notification |
| **Alternate Flow 1** | **User Cancels Print Dialog**<br>6a. User clicks "Cancel" in print dialog<br>6b. Dialog closes<br>6c. No PDF generated<br>6d. User returns to editor |
| **Alternate Flow 2** | **Browser Doesn't Support Print to PDF**<br>8a. System detects no PDF option available<br>8b. System displays "Please install PDF printer"<br>8c. User installs virtual PDF printer<br>8d. Flow returns to step 1 |
| **Alternate Flow 3** | **Print Error**<br>3a. JavaScript error occurs during print<br>3b. System catches exception<br>3c. System displays "Error opening print dialog"<br>3d. System logs error for debugging |
| **Post-conditions** | - PDF file saved to user's computer<br>- Download count incremented (optional)<br>- Resume remains in editor for further editing<br>- No changes to resume record |
| **Business Rules** | - PDF uses A4 page size (210mm × 297mm)<br>- Margins: 0.75in top/bottom, 1in left/right<br>- All fonts embedded for compatibility<br>- Colors preserved for color printing |
| **Special Requirements** | - Page breaks respect section boundaries<br>- No orphaned lines (min 3 lines per page break)<br>- Professional print quality (300 DPI equivalent)<br>- File size typically 50-200 KB |

---

### Use Case 6: Check ATS Score

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-006 |
| **Use Case Title** | Check ATS Score |
| **Actor** | Registered User |
| **Description** | User uploads resume to receive ATS compatibility analysis |
| **Preconditions** | - User is logged in<br>- User navigates to ATS Score Checker page<br>- User has resume file (PDF/DOCX) |
| **Trigger** | User accesses "ATS Score Checker" from navigation menu |
| **Main Flow** | 1. System displays ATS checker interface<br>2. User uploads resume file (PDF or DOCX)<br>3. User enters/pastes job description (optional)<br>4. User clicks "Analyze Resume" button<br>5. System displays loading indicator<br>6. System extracts text from uploaded file via Gemini API<br>7. System sends resume text + job description to Gemini<br>8. Gemini AI analyzes ATS compatibility<br>9. Gemini returns scores and suggestions<br>10. System parses AI response (JSON)<br>11. System saves analysis to ats_scores table<br>12. System displays results page with:<br>    - Overall score (0-100)<br>    - Category scores (keywords, formatting, etc.)<br>    - Matched keywords<br>    - Missing keywords<br>    - Improvement suggestions<br>    - Strengths identified<br>13. User reviews results<br>14. User optionally downloads detailed report |
| **Alternate Flow 1** | **Invalid File Type**<br>2a. User uploads unsupported file (e.g., .txt, .jpg)<br>2b. System displays "Only PDF and DOCX supported"<br>2c. User uploads correct file type and returns to step 3 |
| **Alternate Flow 2** | **File Too Large**<br>2a. User uploads file > 10MB<br>2b. System displays "File size must be under 10MB"<br>2c. User compresses or uses different file<br>2d. Flow returns to step 2 |
| **Alternate Flow 3** | **API Error**<br>7a. Gemini API returns error (rate limit, timeout, etc.)<br>7b. System catches error<br>7c. System displays "Analysis failed. Please try again"<br>7d. System logs error details<br>7e. User can retry from step 4 |
| **Alternate Flow 4** | **No Job Description**<br>3a. User skips job description<br>3b. System performs general ATS analysis<br>3c. Keyword matching section shows "N/A"<br>3d. Flow continues from step 4 |
| **Post-conditions** | - Analysis results saved to database<br>- User has scores and recommendations<br>- Results accessible from history<br>- User can improve resume based on feedback |
| **Business Rules** | - Each analysis creates new record<br>- History maintained for user reference<br>- Score range: 0-100 (higher is better)<br>- Keywords are case-insensitive matching |
| **Special Requirements** | - Analysis completes within 30 seconds<br>- Supports PDF up to version 1.7<br>- Supports DOCX (Office 2007+)<br>- Gemini API timeout: 60 seconds |

---

### Use Case 7: AI Resume Generation

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-007 |
| **Use Case Title** | AI Resume Generation |
| **Actor** | Registered User |
| **Description** | User creates resume through conversational AI interface |
| **Preconditions** | - User is logged in<br>- User navigates to AI Editor<br>- User selects resume template<br>- Gemini API is accessible |
| **Trigger** | User clicks "Create with AI" button or accesses AI Editor |
| **Main Flow** | 1. System displays AI chat interface<br>2. System sends initial AI greeting message<br>3. User types message about their background<br>4. User sends message<br>5. System sends message to ai-conversation API<br>6. API builds system instruction based on template<br>7. API includes conversation history and current resume state<br>8. API calls Gemini with full context<br>9. Gemini processes request and generates response<br>10. Gemini returns conversational text + structured data<br>11. API parses response using regex<br>12. API extracts JSON data from DATA_UPDATES section<br>13. API returns to frontend:<br>    - Conversational response (for chat)<br>    - Structured updates (for resume)<br>14. System displays AI message in chat<br>15. System applies updates to resumeState<br>16. System updates preview in real-time<br>17. User continues conversation to add more details<br>18. User clicks "Save Resume" when complete |
| **Alternate Flow 1** | **API Connection Error**<br>8a. Gemini API unreachable<br>8b. System displays "AI temporarily unavailable"<br>8c. User can retry or switch to manual editor |
| **Alternate Flow 2** | **Invalid AI Response**<br>12a. Response doesn't match expected format<br>12b. System logs parsing error<br>12c. System shows generic AI response<br>12d. Updates not applied to resume<br>12e. User can rephrase and try again |
| **Alternate Flow 3** | **Quick Action Button**<br>3a. User clicks "Add Experience" quick action<br>3b. System inserts template text in input<br>3c. User modifies and sends<br>3d. Flow continues from step 5 |
| **Alternate Flow 4** | **Reset Conversation**<br>17a. User clicks "New Chat" button<br>17b. System confirms "Start over? Resume data will be kept"<br>17c. User confirms<br>17d. System clears conversation history<br>17e. Resume state preserved<br>17f. Flow returns to step 2 |
| **Post-conditions** | - Resume fields populated with AI-generated content<br>- Conversation history maintained during session<br>- Resume saved to database<br>- User can continue editing manually |
| **Business Rules** | - AI responses context-aware (knows previous messages)<br>- Template type affects AI guidance<br>- AI can update multiple fields per response<br>- User can override any AI suggestion |
| **Special Requirements** | - AI response time < 10 seconds<br>- Conversation history limited to last 20 messages<br>- Temperature set to 0.4 (balanced creativity)<br>- Max output tokens: 4096 |

---

### Use Case 8: Track Job Application

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-008 |
| **Use Case Title** | Track Job Application |
| **Actor** | Registered User |
| **Description** | User records and monitors job application progress |
| **Preconditions** | - User is logged in<br>- User has applied for a job<br>- User is on dashboard or application tracker page |
| **Trigger** | User clicks "Add Application" button |
| **Main Flow** | 1. System displays job application form modal<br>2. User enters company name<br>3. User enters job title<br>4. User enters job location<br>5. User selects job type (Full-time/Part-time/etc.)<br>6. User selects application date<br>7. User sets initial status (Applied/In Review/etc.)<br>8. User optionally enters job description<br>9. User optionally adds notes<br>10. User optionally sets priority (Low/Medium/High)<br>11. User clicks "Add Application"<br>12. System validates required fields<br>13. System saves to job_applications table<br>14. System creates initial timeline entry<br>15. System displays application in dashboard list<br>16. System closes modal<br>17. System displays success notification |
| **Alternate Flow 1** | **Update Application Status**<br>1a. User clicks on existing application<br>1b. System displays application details<br>1c. User changes status dropdown<br>1d. System prompts for update notes<br>1e. User enters notes<br>1f. User confirms<br>1g. System updates status in database<br>1h. System creates timeline event<br>1i. System updates UI |
| **Alternate Flow 2** | **Add Timeline Event**<br>1a. User views application details<br>1b. User clicks "Add Event"<br>1c. System displays event form<br>1d. User selects event type (interview/follow-up/etc.)<br>1e. User enters event title and date<br>1f. User adds description<br>1g. System saves to application_timeline<br>1h. System displays event in timeline |
| **Alternate Flow 3** | **Missing Required Field**<br>12a. System detects missing company or job title<br>12b. System highlights missing fields<br>12c. System displays error message<br>12d. User fills required fields<br>12e. Flow returns to step 11 |
| **Alternate Flow 4** | **Delete Application**<br>1a. User clicks delete icon on application<br>1b. System confirms "Delete this application?"<br>1c. User confirms<br>1d. System deletes from database (cascades to timeline)<br>1e. System removes from UI<br>1f. System displays "Application deleted" |
| **Post-conditions** | - Application record created in database<br>- Timeline initialized<br>- Application visible in dashboard<br>- User can track progress over time |
| **Business Rules** | - Each application linked to one user<br>- Timeline events ordered by date<br>- Status changes create automatic timeline entries<br>- Applications can be filtered by status/priority |
| **Special Requirements** | - Dashboard shows count of applications by status<br>- Upcoming interviews highlighted<br>- Overdue follow-ups flagged<br>- Export applications to PDF report |

---

### Use Case 9: Share Resume

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-009 |
| **Use Case Title** | Share Resume |
| **Actor** | Registered User |
| **Description** | User generates shareable link to resume for recruiters/employers |
| **Preconditions** | - User is logged in<br>- User has created a resume<br>- Resume has content to share |
| **Trigger** | User clicks "Share" button on resume card or in editor |
| **Main Flow** | 1. System displays share options modal<br>2. User clicks "Generate Share Link"<br>3. System generates unique share token<br>4. System creates record in resume_shares table<br>5. System builds public URL with token<br>6. System displays shareable link<br>7. User clicks "Copy Link" button<br>8. System copies link to clipboard<br>9. System displays "Link copied!"<br>10. User shares link via email/LinkedIn/etc.<br>11. Recipient opens link in browser<br>12. System validates token<br>13. System retrieves resume data<br>14. System displays public resume view<br>15. System increments view count<br>16. System records view in resume_views table |
| **Alternate Flow 1** | **Toggle Public/Private**<br>2a. User clicks "Make Public" toggle<br>2b. System updates is_public flag in database<br>2c. System displays "Resume is now public"<br>2d. Resume accessible without token via profile URL<br>2e. User can toggle back to private anytime |
| **Alternate Flow 2** | **Invalid/Expired Token**<br>12a. System finds no matching token<br>12b. OR System finds expired share link<br>12c. System displays "Link not found or expired"<br>12d. System suggests contacting resume owner |
| **Alternate Flow 3** | **Password Protected Share**<br>3a. User enables "Password Protection"<br>3b. User sets password<br>3c. System hashes password<br>3d. System saves to resume_shares<br>3e. When recipient opens link:<br>3f. System prompts for password<br>3g. System verifies password<br>3h. If correct, shows resume<br>3i. If incorrect, shows error |
| **Alternate Flow 4** | **Set Expiry Date**<br>3a. User sets expiration date<br>3b. System saves to resume_shares.expiry_date<br>3c. After expiry:<br>3d. System marks is_active = false<br>3e. Link no longer accessible |
| **Post-conditions** | - Share link generated and active<br>- Resume accessible via public URL<br>- View tracking enabled<br>- Link can be revoked anytime |
| **Business Rules** | - Share tokens are unique and random (64 chars)<br>- Multiple share links can exist per resume<br>- Owner can revoke access anytime<br>- Views tracked by IP and timestamp |
| **Special Requirements** | - Public view is read-only (no editing)<br>- No authentication required for public view<br>- Download button available on public view<br>- Analytics available to resume owner |

---

### Use Case 10: Manage User Profile

| **Element** | **Description** |
|-------------|-----------------|
| **Use Case ID** | UC-010 |
| **Use Case Title** | Manage User Profile |
| **Actor** | Registered User |
| **Description** | User updates personal information and master experience/education history |
| **Preconditions** | - User is logged in<br>- User navigates to Profile page |
| **Trigger** | User clicks "Profile" in navigation menu |
| **Main Flow** | 1. System displays profile management page<br>2. System loads user data from database<br>3. System displays three sections:<br>   - Personal Information<br>   - Experience History<br>   - Education History<br>4. User edits personal details (name, phone, address)<br>5. User clicks "Save Changes"<br>6. System updates users table<br>7. System displays "Profile updated"<br>8. User navigates to Experience section<br>9. User clicks "Add Experience"<br>10. System displays experience form<br>11. User enters job details<br>12. System saves to user_experience table<br>13. This experience now available for all resumes<br>14. User repeats for Education section |
| **Alternate Flow 1** | **Delete Experience Entry**<br>9a. User clicks delete icon on experience<br>9b. System confirms "Delete from profile?"<br>9c. User confirms<br>9d. System deletes from user_experience<br>9e. Entry removed from UI |
| **Alternate Flow 2** | **Reorder Entries**<br>9a. User drags experience entry up/down<br>9b. System updates display_order field<br>9c. Order reflected in all resumes using this data |
| **Alternate Flow 3** | **Validation Error**<br>6a. System detects invalid phone format<br>6b. System displays "Invalid phone number"<br>6c. User corrects and returns to step 5 |
| **Post-conditions** | - User profile updated in database<br>- Changes reflected across all resumes<br>- Master experience/education list maintained<br>- Reusable data for future resumes |
| **Business Rules** | - Profile data separate from resume data<br>- Experience/education can be reused across resumes<br>- Deleting from profile doesn't affect existing resumes<br>- Display order maintained per user preference |
| **Special Requirements** | - Profile photo upload (optional)<br>- Phone number format validation<br>- Address autocomplete (optional)<br>- Data export option |

---

## Use Case Relationships

### Include Relationships

```
Create Resume <<include>> Select Template
Create Resume <<include>> Use Manual Editor OR Use AI Editor
Edit Resume <<include>> Update Personal Details
Edit Resume <<include>> Add/Edit Experience
Edit Resume <<include>> Add/Edit Education
Share Resume <<include>> Generate Share Link
Check ATS Score <<include>> Upload Resume File
Check ATS Score <<include>> View Analysis Results
AI Resume Generation <<include>> Chat with AI Assistant
Track Job Application <<include>> Update Application Status
```

### Extend Relationships

```
Create Resume <<extend>> Download Resume as PDF
Create Resume <<extend>> Share Resume
Edit Resume <<extend>> Download Resume as PDF
Check ATS Score <<extend>> Download Analysis Report
Share Resume <<extend>> Set Password Protection
Share Resume <<extend>> Set Expiry Date
Track Job Application <<extend>> View Application Timeline
```

### Generalization Relationships

```
Guest User ──generalization──> User (abstract)
Registered User ──generalization──> User (abstract)
```

---

## System Boundary

The ResumeSync system includes:

**Inside Boundary**:
- User authentication (register, login, logout)
- Resume creation and management
- AI-powered resume generation
- ATS score analysis
- Job application tracking
- Resume sharing and export
- User profile management
- Real-time preview

**Outside Boundary** (External Systems):
- Google Gemini AI API
- Email service (for notifications)
- PDF rendering (browser native)
- File storage (local server)

---

## Use Case Priority Matrix

| Use Case | Priority | Complexity | Frequency |
|----------|----------|------------|-----------|
| Register Account | High | Low | Once per user |
| Login to System | High | Low | Multiple daily |
| Create Resume | Critical | High | Weekly |
| Edit Resume | Critical | High | Daily |
| Download Resume as PDF | Critical | Medium | Daily |
| Check ATS Score | High | High | Weekly |
| AI Resume Generation | High | Very High | Weekly |
| Track Job Application | Medium | Medium | Daily |
| Share Resume | Medium | Low | Weekly |
| Manage User Profile | Low | Low | Monthly |

---

## Non-Functional Requirements

### Performance
- Login/Registration: < 3 seconds
- Resume load: < 2 seconds
- Real-time preview update: < 100ms
- ATS analysis: < 30 seconds
- AI response: < 10 seconds

### Security
- All passwords hashed with bcrypt
- SQL injection prevention via prepared statements
- XSS protection via output escaping
- HTTPS for all data transmission
- Session timeout after 24 hours

### Usability
- Responsive design (mobile, tablet, desktop)
- Intuitive navigation
- Inline help text
- Error messages clear and actionable
- Undo/redo support in editor

### Reliability
- 99% uptime target
- Auto-save every 30 seconds
- Graceful error handling
- Data backup daily

---

## End of Use Case Documentation

This comprehensive documentation covers all major use cases in the ResumeSync system, including actors, flows, and relationships.
