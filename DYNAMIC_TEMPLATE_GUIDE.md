# Dynamic Document Template System - Implementation Guide

## Overview
This system allows you to create dynamic PDF documents using templates with placeholders that are filled with user input data. No hardcoded coordinates required - just use placeholder syntax in your templates.

## How It Works

### 1. **Template Placeholders**
Use triple curly braces in your PDF templates:
```
{{{full_name}}}
{{{address}}}
{{{date_of_birth}}}
{{{purpose}}}
```

### 2. **Database Schema**

#### Documents Table (New Column)
- `template_fields` (JSON) - Defines what fields are needed for the template

Example:
```json
[
  {
    "name": "full_name",
    "label": "Full Name",
    "type": "text",
    "required": true
  },
  {
    "name": "address",
    "label": "Complete Address",
    "type": "textarea",
    "required": true
  },
  {
    "name": "date_of_birth",
    "label": "Date of Birth",
    "type": "date",
    "required": true
  },
  {
    "name": "purpose",
    "label": "Purpose",
    "type": "text",
    "required": false
  }
]
```

#### Request Documents Table (New Column)
- `information` (JSON) - Stores the user's input data

Example:
```json
{
  "full_name": "Juan Dela Cruz",
  "address": "123 Main St., Barangay Santol",
  "date_of_birth": "1990-01-15",
  "purpose": "Employment"
}
```

## API Endpoints

### 1. **Upload Template** (Existing - Updated)
```http
POST /api/documents/{id}/template/upload
Content-Type: multipart/form-data

{
  "template": <PDF file with placeholders>
}
```

### 2. **Extract Placeholders from Template** (New)
```http
GET /api/documents/{id}/template/extract-placeholders

Response:
{
  "message": "Placeholders extracted successfully",
  "placeholders": ["full_name", "address", "date_of_birth", "purpose"],
  "count": 4
}
```

### 3. **Update Document with Template Fields** (Updated)
```http
PUT /api/documents/update/{id}
Content-Type: application/json

{
  "document_name": "Barangay Certificate",
  "description": "Certificate for residents",
  "template_fields": [
    {
      "name": "full_name",
      "label": "Full Name",
      "type": "text",
      "required": true
    },
    {
      "name": "address",
      "label": "Complete Address",
      "type": "textarea",
      "required": true
    },
    {
      "name": "date_of_birth",
      "label": "Date of Birth",
      "type": "date",
      "required": true
    }
  ]
}
```

### 4. **Create Request Document with Information** (Updated)
```http
POST /api/request-documents/create
Content-Type: application/json

{
  "document": 1,
  "information": {
    "full_name": "Juan Dela Cruz",
    "address": "123 Main St., Barangay Santol",
    "date_of_birth": "1990-01-15",
    "purpose": "Employment"
  },
  "requirements": [
    {
      "requirement_id": 1,
      "file": <uploaded file>
    }
  ]
}
```

### 5. **Generate Filled Document** (New)
```http
POST /api/request-documents/{id}/generate-filled-document

Response:
{
  "message": "Document generated successfully",
  "file_path": "filled_documents/TXN_DOC_0001234_1697123456.pdf",
  "file_url": "http://yourapp.com/storage/filled_documents/TXN_DOC_0001234_1697123456.pdf"
}
```

## Frontend Implementation Example

### Step 1: Upload Template
```javascript
// Upload PDF template with placeholders
const uploadTemplate = async (documentId, pdfFile) => {
  const formData = new FormData();
  formData.append('template', pdfFile);
  
  const response = await fetch(`/api/documents/${documentId}/template/upload`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  
  return await response.json();
};
```

### Step 2: Extract Placeholders (Optional)
```javascript
// Get placeholders from the template
const extractPlaceholders = async (documentId) => {
  const response = await fetch(`/api/documents/${documentId}/template/extract-placeholders`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  console.log(data.placeholders); // ["full_name", "address", ...]
  return data;
};
```

### Step 3: Define Template Fields
```javascript
// Define what fields the user needs to fill
const defineTemplateFields = async (documentId) => {
  const templateFields = [
    { name: 'full_name', label: 'Full Name', type: 'text', required: true },
    { name: 'address', label: 'Complete Address', type: 'textarea', required: true },
    { name: 'date_of_birth', label: 'Date of Birth', type: 'date', required: true },
    { name: 'purpose', label: 'Purpose', type: 'text', required: false }
  ];
  
  const response = await fetch(`/api/documents/update/${documentId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ template_fields: templateFields })
  });
  
  return await response.json();
};
```

### Step 4: Create Request with User Data
```javascript
// User submits the form with their information
const createDocumentRequest = async (documentId, userInfo) => {
  const requestData = {
    document: documentId,
    information: {
      full_name: userInfo.fullName,
      address: userInfo.address,
      date_of_birth: userInfo.dateOfBirth,
      purpose: userInfo.purpose
    }
  };
  
  const response = await fetch('/api/request-documents/create', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(requestData)
  });
  
  return await response.json();
};
```

### Step 5: Generate Filled Document
```javascript
// Generate the filled PDF
const generateDocument = async (requestDocumentId) => {
  const response = await fetch(`/api/request-documents/${requestDocumentId}/generate-filled-document`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  console.log(data.file_url); // Download URL
  return data;
};
```

## React Component Example

```jsx
import React, { useState, useEffect } from 'react';

function DocumentRequestForm({ documentId }) {
  const [document, setDocument] = useState(null);
  const [formData, setFormData] = useState({});
  
  useEffect(() => {
    // Fetch document details including template_fields
    fetch(`/api/documents/${documentId}`)
      .then(res => res.json())
      .then(data => {
        setDocument(data);
        // Initialize form data
        const initialData = {};
        data.template_fields?.forEach(field => {
          initialData[field.name] = '';
        });
        setFormData(initialData);
      });
  }, [documentId]);
  
  const handleInputChange = (fieldName, value) => {
    setFormData(prev => ({
      ...prev,
      [fieldName]: value
    }));
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Create request
    const response = await fetch('/api/request-documents/create', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        document: documentId,
        information: formData
      })
    });
    
    const result = await response.json();
    
    // Optionally generate the document immediately
    if (result.request_document?.id) {
      await fetch(`/api/request-documents/${result.request_document.id}/generate-filled-document`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` }
      });
    }
  };
  
  const renderField = (field) => {
    switch (field.type) {
      case 'textarea':
        return (
          <textarea
            value={formData[field.name] || ''}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            required={field.required}
          />
        );
      case 'date':
        return (
          <input
            type="date"
            value={formData[field.name] || ''}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            required={field.required}
          />
        );
      default:
        return (
          <input
            type={field.type}
            value={formData[field.name] || ''}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            required={field.required}
          />
        );
    }
  };
  
  if (!document) return <div>Loading...</div>;
  
  return (
    <form onSubmit={handleSubmit}>
      <h2>Request: {document.document_name}</h2>
      
      {document.template_fields?.map(field => (
        <div key={field.name}>
          <label>
            {field.label}
            {field.required && <span>*</span>}
          </label>
          {renderField(field)}
        </div>
      ))}
      
      <button type="submit">Submit Request</button>
    </form>
  );
}
```

## Workflow

1. **Admin uploads a PDF template** with placeholders like `{{{full_name}}}`
2. **Admin defines template_fields** to specify what data is needed
3. **Frontend dynamically renders form** based on `template_fields`
4. **User fills the form** with their information
5. **System creates request** with `information` JSON
6. **System generates filled PDF** by replacing placeholders with actual data
7. **User downloads/receives** the completed document

## Benefits

✅ **No coordinate-based positioning** - just text replacement
✅ **Easy template updates** - upload new template, same placeholders work
✅ **Type-safe data** - JSON validation ensures correct data types
✅ **Flexible fields** - different documents can have different fields
✅ **Frontend-friendly** - template_fields define the UI automatically
✅ **Validation built-in** - required fields are enforced

## Field Types Supported

- `text` - Single line text input
- `textarea` - Multi-line text input
- `number` - Numeric input
- `date` - Date picker
- `email` - Email input with validation

## Notes

- Placeholders must use triple curly braces: `{{{field_name}}}`
- Field names should match between template_fields and template placeholders
- The PDF template file itself contains the placeholders
- Information is stored as JSON for flexibility
- System validates required fields before generating documents
