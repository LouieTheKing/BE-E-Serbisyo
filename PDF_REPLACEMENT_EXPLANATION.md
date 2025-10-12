# Why PDF Placeholder Replacement Doesn't Work

## The Problem

You uploaded a PDF with `{{{full_name}}}` placeholders and tried to replace them, but the generated PDF still shows the placeholders instead of the actual data.

## Why This Happens

**PDF files are not plain text files.** They are binary files with a complex internal structure that includes:
- Binary formatting codes
- Font definitions
- Graphics and layout information
- Compressed streams
- Object references

When you try to do `str_replace('{{{full_name}}}', 'Juan Dela Cruz', $pdfBinaryContent)`, you're trying to replace text in binary data, which:
1. May not find the text because it's encoded/compressed
2. Can corrupt the PDF structure
3. Will not properly render the replacement text

## The Solution: Use HTML Templates Instead

### ✅ What Works

**HTML templates** → Replace placeholders → **Convert to PDF**

```
HTML Template with {{{full_name}}}
    ↓
Replace {{{full_name}}} with "Juan Dela Cruz"  
    ↓
Convert filled HTML to PDF using DomPDF
    ↓
Final PDF with actual data
```

## Implementation

I've updated your system to support **3 template types**:

### 1. **HTML Template in Database** (BEST - Recommended)
- Store HTML directly in `documents.html_template` column
- Easy to update without file uploads
- Fast placeholder replacement
- Full CSS styling support

**Example:**
```sql
UPDATE documents 
SET html_template = '<!DOCTYPE html><html>...{{{full_name}}}...</html>'
WHERE id = 1;
```

### 2. **HTML Template File**
- Upload `.html` file instead of `.pdf`
- Works exactly like database templates
- Good for complex templates

**Example:**
```http
POST /api/documents/1/template/upload
Content-Type: multipart/form-data

template: barangay_cert.html (file)
```

### 3. **PDF Template** (FALLBACK - Limited)
- Extracts text from PDF
- Replaces placeholders in extracted text
- Generates new PDF from text
- ⚠️ **Loses all formatting, images, layout**
- Only use as last resort

## How to Convert Your PDF to HTML

### Option 1: Recreate as HTML (Recommended)

1. Look at your PDF design
2. Create equivalent HTML with placeholders
3. Use CSS for styling
4. Store in database or upload as `.html`

**Sample HTML Template:**
```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial; padding: 40px; }
        .header { text-align: center; }
        .title { font-size: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BARANGAY CERTIFICATE</h1>
    </div>
    <p>This is to certify that <strong>{{{full_name}}}</strong>...</p>
    <p>Address: {{{address}}}</p>
    <p>Purpose: {{{purpose}}}</p>
</body>
</html>
```

### Option 2: Use PDF to HTML Converter

Tools like:
- Adobe Acrobat (Export to HTML)
- pdf2htmlEX
- Online converters

⚠️ May need manual cleanup

## Updated Workflow

### OLD (Doesn't Work):
```
PDF Template → Upload → Try to replace placeholders → ❌ Doesn't work
```

### NEW (Works):
```
HTML Template → Store in DB or Upload → Replace placeholders → Generate PDF → ✅ Works!
```

## Quick Start Guide

### Step 1: Create HTML Template

```html
<!DOCTYPE html>
<html>
<body>
    <h1>Certificate</h1>
    <p>Name: {{{full_name}}}</p>
    <p>Address: {{{address}}}</p>
</body>
</html>
```

### Step 2: Save Template

**Method A: Database (Recommended)**
```http
PUT /api/documents/update/1
{
  "html_template": "<!DOCTYPE html>...",
  "template_fields": [
    {"name": "full_name", "label": "Full Name", "type": "text", "required": true},
    {"name": "address", "label": "Address", "type": "text", "required": true}
  ]
}
```

**Method B: Upload File**
```http
POST /api/documents/1/template/upload
(Upload .html file)
```

### Step 3: Create Request with Data

```http
POST /api/request-documents/create
{
  "document": 1,
  "information": {
    "full_name": "Juan Dela Cruz",
    "address": "123 Main Street"
  }
}
```

### Step 4: Generate Filled PDF

```http
POST /api/request-documents/3/generate-filled-document
```

## What Changed in Your System

### New Database Column
```sql
ALTER TABLE documents ADD COLUMN html_template TEXT NULL;
```

### Updated Service
- `PdfGeneratorService` now supports:
  - HTML templates from database
  - HTML file templates
  - PDF fallback (limited)

### Priority Order
1. Check for `html_template` in database → Use it
2. Check for `.html` file → Use it
3. Check for `.pdf` file → Extract text and generate (limited)

## Benefits

✅ **Placeholders actually work**
✅ **Easy to update** - just edit HTML
✅ **Full styling control** with CSS
✅ **No coordinate-based positioning**
✅ **Preview in browser** before generating PDF
✅ **Version control friendly**

## Migration Path

If you already have PDF templates:

1. **Keep the PDF** for reference
2. **Create HTML version** with same content
3. **Add placeholders** like `{{{field_name}}}`
4. **Store HTML** in `html_template` column
5. **Test** by generating filled document
6. **Update frontend** to use new system

## See Also

- `HTML_TEMPLATE_GUIDE.md` - Complete HTML template creation guide
- `DYNAMIC_TEMPLATE_GUIDE.md` - Original dynamic template documentation

## Summary

**Problem**: Can't replace text in PDF files  
**Cause**: PDFs are binary, not plain text  
**Solution**: Use HTML templates instead  
**Result**: Perfect placeholder replacement + PDF generation
