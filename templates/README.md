# ResumeSync - ATS-Compatible CV Templates

This directory contains 3 professionally designed, ATS-compatible resume templates that are fully integrated with the ResumeSync application.

## Templates Overview

### 1. Modern Professional (`template_modern_professional.html`)
**Best for:** Project Managers, Business Professionals, Corporate Roles

**Features:**
- Clean layout with blue accent colors
- Professional typography with clear hierarchy
- Border-based section separators
- Skills displayed as modern tags
- Traditional format that works across all industries

**Color Scheme:** Blue (#2c3e50, #3B82F6) with neutral grays

---

### 2. Clean Minimal (`template_clean_minimal.html`)
**Best for:** Marketing, Creative, Design, Communications

**Features:**
- Ultra-clean design with maximum white space
- Light typography with tracking/letter-spacing
- Minimal color usage for sophistication
- Bullet-separated skill list
- Contemporary minimal aesthetic

**Color Scheme:** Primarily grayscale with subtle accents

---

### 3. Technical Developer (`template_technical_developer.html`)
**Best for:** Software Engineers, Developers, Technical Roles

**Features:**
- Blue sidebar accent (border-left)
- Technical skills organized in grid by category
- Tech stack labels for each position
- Project showcase section with GitHub links
- Monospace font hints for technical authenticity

**Color Scheme:** Blue (#4299e1) with technical gray tones

---

## ATS Compatibility Features

All templates include:

✅ **Simple HTML Structure** - No complex tables or nested divs
✅ **Standard Headings** - Experience, Education, Skills, etc.
✅ **Clean Fonts** - Arial, Helvetica, standard web-safe fonts
✅ **No Images/Graphics** - Pure text-based content
✅ **Proper Semantic HTML** - h1, h2, ul, li tags used correctly
✅ **Print-Friendly** - Optimized for PDF conversion
✅ **Responsive Design** - Works on all screen sizes
✅ **No Special Characters** - Avoids symbols that confuse ATS systems

---

## Integration with ResumeSync

### Editor Integration

The templates are fully integrated into the Resume Editor (`pages/editor.html`):

1. **Template Selector** - Dropdown menu to choose template
2. **Live Preview** - Real-time preview updates with selected template styles
3. **Dynamic Styling** - JavaScript applies template-specific CSS classes
4. **Data Persistence** - Template choice saved in localStorage

### Template Styles in JavaScript

Each template has a style configuration in `assets/js/editor.js`:

```javascript
const templateStyles = {
    modern_professional: { /* styles */ },
    clean_minimal: { /* styles */ },
    technical_developer: { /* styles */ }
};
```

The preview dynamically applies these styles when users:
- Select a template from the dropdown
- Enter personal information
- Add experience, education, or skills

### Homepage Integration

The homepage (`index.html`) showcases all three templates in the Templates section with:
- Template previews
- Descriptions
- "Use Template" buttons that link to the editor

---

## Technical Details

### File Structure
```
templates/
├── README.md                           # This file
├── template_modern_professional.html   # Modern Professional template
├── template_clean_minimal.html         # Clean Minimal template
└── template_technical_developer.html   # Technical Developer template
```

### Styling Approach

Each template uses:
- **Inline CSS** - All styles in `<style>` tags for portability
- **Tailwind-inspired classes** - For editor preview consistency
- **Print media queries** - Optimized for PDF generation
- **Max-width: 850px** - Standard resume width

### Future Enhancements (Phase 2+)

- [ ] PDF generation using FPDF (backend)
- [ ] Template preview thumbnails
- [ ] Custom color scheme options
- [ ] Additional template variations
- [ ] Template import/export functionality

---

## Usage Instructions

### For Developers

To add a new template:

1. Create new HTML file in `/templates/` directory
2. Follow ATS-compatibility guidelines above
3. Add template option to `editor.html` dropdown
4. Add style configuration to `editor.js`
5. Update homepage template showcase
6. Add entry to this README

### For Users

1. Go to the Resume Editor
2. Select your preferred template from dropdown
3. Fill in your information
4. Watch live preview update
5. Download as PDF (Phase 2 feature)

---

## Design Principles

All templates follow these principles:

1. **ATS-First** - Designed to pass ATS parsing systems
2. **Clean & Professional** - Modern yet professional appearance
3. **Readable** - High contrast, appropriate font sizes
4. **Scannable** - Clear hierarchy, easy to skim
5. **Versatile** - Works for multiple industries/roles

---

## Testing

Templates tested with:
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

ATS systems tested (coming in Phase 3):
- [ ] Workday
- [ ] Greenhouse
- [ ] Lever
- [ ] iCIMS
- [ ] Taleo

---

## Credits

Created for **ResumeSync** - AI-Powered ATS-Friendly Resume Builder
**Course:** CSE482L - Internet and Web Technologies Lab
**Institution:** North South University
**Semester:** Fall 2025

---

**Last Updated:** November 2025
**Version:** 1.0.0
