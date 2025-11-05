# ResumeSync: AI-Powered ATS-Friendly Resume Builder

## Project Overview
ResumeSync is a comprehensive web application designed to help job seekers create, analyze, and optimize ATS-friendly resumes using AI technology.

**Course:** CSE482L - Internet and Web Technologies Lab
**Institution:** North South University
**Group:** Group 1
**Semester:** Fall 2025

## Team Members
- Mahbubur Rahman Khan - 2211804042
- Afifa Imran - 2131823642
- Abdullah Al Mahmud - 1320128642
- Mahfuz Ahmed Nirob - 2011724042

## Milestone 1: UI Implementation ✅

This milestone focuses on creating the complete user interface using HTML, CSS (Tailwind CSS), and JavaScript.

## Project Structure

```
ATS_MA/
├── index.html                 # Homepage
├── pages/
│   ├── login.html            # Login page
│   ├── register.html         # Registration page
│   ├── dashboard.html        # User dashboard
│   └── editor.html           # Resume editor (3-panel layout)
├── assets/
│   ├── css/
│   │   └── style.css         # Custom CSS styles
│   ├── js/
│   │   ├── main.js           # Shared JavaScript functions
│   │   └── editor.js         # Editor-specific functionality
│   └── images/               # Image assets folder (add your images here)
├── 482 (2).pdf               # Project proposal
└── README.md                 # This file
```

## Pages Overview

### 1. Homepage (index.html)
- Hero section with CTA buttons
- Features showcase (6 main features)
- ATS-friendly templates display
- How it works section (4 steps)
- Footer with links

### 2. Login Page (pages/login.html)
- Email and password fields
- Remember me option
- Social login buttons (Google, Facebook)
- Form validation
- Link to registration page

### 3. Registration Page (pages/register.html)
- Full name, email, password fields
- Password confirmation
- Terms and conditions checkbox
- Form validation (8+ character password)
- Social sign-up options

### 4. Dashboard (pages/dashboard.html)
- Statistics cards (Resumes created, Avg ATS score, Applications sent)
- Quick action buttons
- Recent resumes display with preview cards
- Create new resume option
- User menu dropdown

### 5. Resume Editor (pages/editor.html)
**Three-panel layout:**
- **Left Panel:** Form inputs (Personal details, Experience, Education, Skills)
- **Middle Panel:** Live preview of resume
- **Right Panel:** AI analysis panel with ATS score, suggestions, and keyword match

## Technologies Used

### Frontend
- **HTML5:** Structure and semantic markup
- **Tailwind CSS:** Utility-first CSS framework (via CDN)
- **JavaScript (ES6+):** Interactivity and dynamic content
- **Font Awesome:** Icons
- **LocalStorage:** Client-side data persistence

### Features Implemented
✅ Responsive design (mobile, tablet, desktop)
✅ Form validation
✅ Dynamic form fields (add/remove experience, education)
✅ Skills management with tags
✅ Live resume preview
✅ Auto-save functionality
✅ Toast notifications
✅ Mobile menu
✅ User dropdown menu
✅ Image placeholders with fallback

## How to Run

### Option 1: Simple File Opening
1. Open `index.html` directly in your browser
2. Navigate through the pages using the menu

### Option 2: Local Server (Recommended)
Using Python:
```bash
# Python 3
python -m http.server 8000

# Python 2
python -m SimpleHTTPServer 8000
```

Using Node.js (with http-server):
```bash
npx http-server -p 8000
```

Using PHP:
```bash
php -S localhost:8000
```

Then open: `http://localhost:8000`

## Image Placeholders

The project uses placeholder images that will automatically fallback to generated placeholders if images are not found. You can add your own images to the `assets/images/` folder:

**Recommended images to add:**
- `hero-illustration.png` (600x400px) - Hero section
- `template-professional.png` (400x500px) - Professional template preview
- `template-modern.png` (400x500px) - Modern template preview
- `template-executive.png` (400x500px) - Executive template preview
- `resume-preview-1.png` (400x300px) - Dashboard resume preview
- `resume-preview-2.png` (400x300px) - Dashboard resume preview
- `user-avatar.png` (100x100px) - User profile picture

## Key Features

### Resume Editor
- **Dynamic sections:** Add/remove experience and education entries
- **Skills management:** Add skills as tags, remove with one click
- **Live preview:** See changes in real-time
- **Auto-save:** Data persists in localStorage
- **Form validation:** Client-side validation for all inputs

### Data Persistence
All resume data is stored in localStorage, so users won't lose their work when they refresh the page.

### Responsive Design
- Mobile-first approach
- Breakpoints: sm (640px), md (768px), lg (1024px), xl (1280px)
- Hamburger menu for mobile navigation

## JavaScript Utilities

### Available Functions (main.js)
```javascript
// Toast notifications
ResumeSync.showToast(message, type);

// Form validation
ResumeSync.validateEmail(email);
ResumeSync.validatePassword(password);

// Storage helpers
ResumeSync.Storage.set(key, value);
ResumeSync.Storage.get(key);
ResumeSync.Storage.remove(key);

// Utility functions
ResumeSync.debounce(func, wait);
ResumeSync.copyToClipboard(text);
ResumeSync.downloadFile(content, filename);
```

## Next Steps (Future Milestones)

### Phase 2: Backend Integration
- [ ] PHP backend with MySQL database
- [ ] User authentication system
- [ ] Resume CRUD operations
- [ ] Session management

### Phase 3: AI Integration
- [ ] Google Gemini API integration
- [ ] ATS compatibility scoring
- [ ] Keyword analysis
- [ ] Content improvement suggestions

### Phase 4: PDF Generation
- [ ] FPDF library integration
- [ ] Template-based PDF generation
- [ ] Download functionality

### Phase 5: Advanced Features
- [ ] Conversational resume builder
- [ ] MCP/CLI tool
- [ ] Multiple resume versions
- [ ] Analytics dashboard

## Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Known Issues / Notes
- PDF download shows placeholder message (will be implemented in Phase 2)
- AI analysis shows placeholder message (will be implemented in Phase 3)
- Social login buttons are UI-only (will be implemented in Phase 2)
- Login/Register forms accept any input (backend validation in Phase 2)

## CSS Customization

The project uses Tailwind CSS via CDN. Custom styles are in `assets/css/style.css` including:
- Animations (fadeIn, slideIn)
- Custom scrollbar styling
- Toast notifications
- Skill tags
- Loading spinner
- Hover effects

## Development Tips

1. **Testing forms:** All forms have client-side validation but no backend yet
2. **Data persistence:** Check browser console → Application → Local Storage to see saved data
3. **Responsive testing:** Use browser DevTools to test different screen sizes
4. **Icons:** Using Font Awesome 6.4.0 - check docs for more icons
5. **Colors:** Project uses blue (#3B82F6) as primary color

## Credits & Resources
- **Tailwind CSS:** https://tailwindcss.com
- **Font Awesome:** https://fontawesome.com
- **Placeholder images:** https://via.placeholder.com

## License
This is an academic project for CSE482L at North South University.

## Contact
For questions or issues, contact any team member listed above.

---
**Last Updated:** November 2025
**Version:** 1.0.0 (Milestone 1 - UI Complete)
