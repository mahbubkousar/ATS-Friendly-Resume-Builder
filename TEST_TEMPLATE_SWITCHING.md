# Testing Template Switching

## How to Test

1. **Open the editor:**
   - Navigate to `pages/editor.html` in your browser
   - Or click "Use Template" from the homepage

2. **Test template switching:**
   - Look for the "Choose Template" dropdown at the top of the left panel
   - Select different templates from the dropdown:
     - Modern Professional
     - Clean Minimal
     - Technical Developer

3. **What should happen:**

   When you switch templates, you should see these changes in the live preview:

   ### Modern Professional
   - Container: White background with shadow
   - Name: Large bold text (3xl) in gray-800
   - Section titles: Uppercase with gray bottom border
   - Skills: Gray background tags with rounded corners
   - Overall: Traditional professional look

   ### Clean Minimal
   - Container: White background with shadow
   - Name: Extra large light text (4xl) with wide letter spacing
   - Section titles: Small uppercase with extra wide letter spacing
   - Skills: Plain text with bullet separators
   - Overall: Minimalist, lots of white space

   ### Technical Developer
   - Container: White background with LEFT BLUE BORDER (most noticeable!)
   - Name: Large bold text
   - Section titles: Blue bottom border (2px thick)
   - Skills: Light blue background tags
   - Overall: Technical/modern with blue accents

## Expected Visual Changes

The most noticeable differences when switching:

1. **Technical Developer** has a **blue left border** on the entire preview container
2. **Clean Minimal** has much **lighter font weights** and more spacing
3. **Modern Professional** has gray borders and traditional spacing

## Debugging Steps

If template switching doesn't work:

### Step 1: Check Browser Console
Open browser DevTools (F12) → Console tab
- Look for any JavaScript errors
- Check if "Template changed!" toast appears when switching

### Step 2: Check localStorage
In DevTools → Application → Local Storage
- Look for `currentResume` key
- Check if `template` field changes when you switch

### Step 3: Verify dropdown works
- Click the dropdown - does it show 3 options?
- Can you select different options?
- Does the dropdown value change?

### Step 4: Add sample data
Add some content to see the differences better:
- Add your name in "Full Name"
- Add a skill or two
- Add one experience entry

The styling differences will be more obvious with actual content.

## Quick Fix Test

If it's still not working, try:

1. **Clear localStorage:**
   ```javascript
   // Run in browser console:
   localStorage.clear();
   // Then refresh the page
   ```

2. **Hard refresh:**
   - Windows/Linux: Ctrl + Shift + R
   - Mac: Cmd + Shift + R

3. **Check file paths:**
   - Make sure you're accessing via a local server (not file://)
   - Use: `python -m http.server 8000`
   - Then open: `http://localhost:8000`

## Visual Comparison

| Feature | Modern Professional | Clean Minimal | Technical Developer |
|---------|-------------------|---------------|-------------------|
| Container | Plain white | Plain white | **Blue left border** |
| Name size | 3xl (32px) | **4xl (36px)** | 3xl (32px) |
| Name weight | Bold | **Light** | Bold |
| Section titles | Gray border bottom | No border | **Blue border bottom** |
| Skills styling | Gray background | Plain text | **Blue background** |
| Letter spacing | Normal | **Wide** | Normal |

## Success Indicators

✅ Template switching is working if:
- You see "Template changed!" toast notification
- The preview container gets a blue left border in "Technical Developer" mode
- Section titles change thickness and color
- Skill tags change from gray → plain text → blue background
- Name text changes weight between templates

## Still Not Working?

If template switching still doesn't work after following these steps:

1. Check that `editor.js` is loaded correctly (no 404 errors)
2. Verify `main.js` is loaded first (for ResumeSync utilities)
3. Open browser console and type: `console.log(localStorage.getItem('currentResume'))`
4. Try manually in console:
   ```javascript
   let data = JSON.parse(localStorage.getItem('currentResume') || '{}');
   data.template = 'technical_developer';
   localStorage.setItem('currentResume', JSON.stringify(data));
   location.reload();
   ```

This will force the template change and reload the page.
