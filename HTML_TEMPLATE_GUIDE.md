# HTML Template System - Complete Guide

## ⚠️ Important: PDF Placeholder Limitation

**PDF files cannot have their text content directly edited** because PDFs are binary files with complex internal structures. Simple `str_replace()` does not work on PDF content.

## ✅ Solution: Use HTML Templates

Instead of PDF templates with placeholders, use **HTML templates** which are converted to PDF after filling in the data.

---

## How It Works

1. **Create HTML template** with placeholders like `{{{full_name}}}`
2. **Store HTML template** in database (`html_template` field) or upload as `.html` file
3. **System replaces placeholders** with actual user data
4. **Generate PDF** from the filled HTML using DomPDF

---

## Creating HTML Templates

### Sample Barangay Certificate Template

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Barangay Certificate</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 40px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 24px;
        }
        .header p {
            margin: 3px 0;
            font-size: 14px;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 30px 0;
            text-decoration: underline;
        }
        .content {
            text-align: justify;
            font-size: 14px;
            margin: 20px 0;
        }
        .signature {
            margin-top: 60px;
        }
        .signature-line {
            display: inline-block;
            width: 200px;
            border-bottom: 1px solid #000;
            text-align: center;
        }
        .footer {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPUBLIC OF THE PHILIPPINES</h1>
        <p>Province of {{{province}}}</p>
        <p>Municipality of {{{municipality}}}</p>
        <p><strong>BARANGAY {{{barangay}}}</strong></p>
        <p>OFFICE OF THE BARANGAY CAPTAIN</p>
    </div>

    <div class="title">
        BARANGAY CERTIFICATE
    </div>

    <div class="content">
        <p><strong>TO WHOM IT MAY CONCERN:</strong></p>
        
        <p style="text-indent: 40px;">
            This is to certify that <strong>{{{full_name}}}</strong>, 
            {{{age}}} years old, {{{civil_status}}}, Filipino Citizen, 
            and a resident of {{{address}}}, Barangay {{{barangay}}}, 
            {{{municipality}}}, {{{province}}}, is personally known to me 
            to be of good moral character and a law-abiding citizen.
        </p>

        <p style="text-indent: 40px;">
            This certification is being issued upon the request of the 
            above-named person for <strong>{{{purpose}}}</strong> and for 
            whatever legal purpose it may serve.
        </p>

        <p style="text-indent: 40px;">
            Issued this <strong>{{{day_issued}}}</strong> day of 
            <strong>{{{month_issued}}}</strong>, <strong>{{{year_issued}}}</strong> 
            at Barangay {{{barangay}}}, {{{municipality}}}, {{{province}}}.
        </p>
    </div>

    <div class="signature">
        <p>Certified by:</p>
        <br><br>
        <p>
            <strong>{{{captain_name}}}</strong><br>
            Barangay Captain
        </p>
    </div>

    <div class="footer">
        <p>Document No.: {{{transaction_id}}}</p>
        <p>Date Issued: {{{date_issued}}}</p>
    </div>
</body>
</html>
```

---

## API Usage

### Method 1: Store HTML Template in Database (Recommended)

```http
PUT /api/documents/update/{id}
Content-Type: application/json
Authorization: Bearer {token}

{
  "html_template": "<!DOCTYPE html>...your HTML here...",
  "template_fields": [
    {"name": "full_name", "label": "Full Name", "type": "text", "required": true},
    {"name": "address", "label": "Address", "type": "textarea", "required": true},
    {"name": "purpose", "label": "Purpose", "type": "text", "required": true},
    {"name": "date_issued", "label": "Date Issued", "type": "date", "required": false}
  ]
}
```

### Method 2: Upload HTML File

```http
POST /api/documents/{id}/template/upload
Content-Type: multipart/form-data
Authorization: Bearer {token}

template: (select .html file)
```

### Create Request with Data

```http
POST /api/request-documents/create
Content-Type: application/json
Authorization: Bearer {token}

{
  "document": 1,
  "information": {
    "full_name": "Juan Dela Cruz",
    "age": "35",
    "civil_status": "Married",
    "address": "123 Main Street",
    "barangay": "Santol",
    "municipality": "Quezon City",
    "province": "Metro Manila",
    "purpose": "Employment",
    "day_issued": "12",
    "month_issued": "October",
    "year_issued": "2025",
    "date_issued": "2025-10-12",
    "captain_name": "Maria Santos",
    "transaction_id": "TXN_DOC_1234567"
  }
}
```

### Generate Filled PDF

```http
POST /api/request-documents/{id}/generate-filled-document
Authorization: Bearer {token}

Response:
{
  "message": "Document generated successfully",
  "file_path": "filled_documents/TXN_DOC_1234567_1697123456.pdf",
  "file_url": "http://yourapp.com/storage/filled_documents/TXN_DOC_1234567_1697123456.pdf"
}
```

---

## Template Placeholders

Use triple curly braces for placeholders:

- `{{{full_name}}}` - Will be replaced with actual full name
- `{{{address}}}` - Will be replaced with actual address
- `{{{purpose}}}` - Will be replaced with actual purpose

**Rules:**
- Placeholder names must match the keys in the `information` JSON
- Use only letters, numbers, and underscores in placeholder names
- Placeholders are case-sensitive

---

## Styling Tips

### 1. **Use inline CSS or `<style>` tags**
DomPDF supports CSS but has limitations. Avoid complex CSS.

### 2. **Common Styles**
```css
/* Page size */
@page {
    size: A4;
    margin: 20mm;
}

/* Fonts */
body {
    font-family: 'Arial', sans-serif;
    font-size: 12pt;
}

/* Headers */
h1 { font-size: 18pt; }
h2 { font-size: 16pt; }

/* Text alignment */
.center { text-align: center; }
.justify { text-align: justify; }

/* Signatures */
.signature {
    margin-top: 50px;
    page-break-inside: avoid;
}
```

### 3. **Images**
```html
<!-- Use base64 or public URLs -->
<img src="data:image/png;base64,iVBORw0KG..." alt="Logo">
<img src="http://yoursite.com/images/logo.png" alt="Logo">
```

---

## Priority Order

When generating PDFs, the system checks in this order:

1. **`html_template`** (from database) - HIGHEST PRIORITY
2. **`.html` file** (uploaded template)
3. **`.pdf` file** (fallback, limited functionality)

---

## Converting Existing PDF to HTML

If you have existing PDF templates:

1. **Manual Conversion** (Recommended):
   - Open PDF in a viewer
   - Copy the text structure
   - Create HTML template with same layout
   - Add CSS for styling

2. **Use Online Converters**:
   - pdf2htmlEX
   - Adobe Acrobat (Export to HTML)
   - Online tools (may lose formatting)

3. **Redesign from Scratch**:
   - Recommended for best results
   - Full control over layout and styling

---

## Complete Example Workflow

### Step 1: Create Document with HTML Template

```bash
PUT /api/documents/update/1
{
  "document_name": "Barangay Certificate",
  "html_template": "<html>...template here...</html>",
  "template_fields": [
    {"name": "full_name", "label": "Full Name", "type": "text", "required": true},
    {"name": "address", "label": "Address", "type": "textarea", "required": true}
  ]
}
```

### Step 2: User Submits Request

```bash
POST /api/request-documents/create
{
  "document": 1,
  "information": {
    "full_name": "Juan Dela Cruz",
    "address": "123 Main St."
  }
}
```

### Step 3: Generate Filled PDF

```bash
POST /api/request-documents/3/generate-filled-document
```

### Step 4: Download PDF

```bash
GET /storage/filled_documents/TXN_DOC_1234567_1697123456.pdf
```

---

## Troubleshooting

### Issue: Placeholders not replaced

**Solution**: Make sure:
- Placeholder format is `{{{field_name}}}` (triple braces)
- Field name in HTML matches key in `information` JSON
- HTML template is properly stored

### Issue: PDF looks different from HTML

**Solution**:
- DomPDF has CSS limitations
- Use simple layouts
- Test with `loadHTML()->stream()` to preview

### Issue: Images not showing

**Solution**:
- Use absolute URLs or base64
- Check image file permissions
- Ensure images are accessible

---

## Benefits of HTML Templates

✅ **Easy to edit** - Just edit HTML text
✅ **Full control** - CSS styling support
✅ **Dynamic content** - Placeholder replacement works perfectly
✅ **No coordinates** - Layout is automatic
✅ **Version control** - Easy to track changes
✅ **Preview in browser** - Test before generating PDF

---

## Next Steps

1. Convert your existing PDF templates to HTML
2. Store HTML templates in the database
3. Test placeholder replacement
4. Generate filled PDFs

For questions, refer to the DYNAMIC_TEMPLATE_GUIDE.md file.
