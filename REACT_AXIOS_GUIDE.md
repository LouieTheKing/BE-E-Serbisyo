# React + Axios Implementation Guide - Document Template System

## 📋 Complete Step-by-Step Guide

This guide shows you how to integrate the dynamic document template system with your React frontend using Axios.

---

## 🎯 Overview

**What you'll build:**
1. Template upload interface (Admin)
2. Template field configuration (Admin)
3. Dynamic form generation for users
4. Document request creation
5. Filled document generation and download

---

## 📦 Prerequisites

```bash
npm install axios
# or
yarn add axios
```

---

## 🔧 Step 1: Setup Axios Instance

Create `src/api/axios.js`:

```javascript
import axios from 'axios';

// Create axios instance with base configuration
const api = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add request interceptor to include auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized - redirect to login
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

---

## 🔧 Step 2: Create API Service

Create `src/services/documentService.js`:

```javascript
import api from '../api/axios';

const documentService = {
  // ========== DOCUMENT MANAGEMENT ==========
  
  /**
   * Get all documents
   */
  getAllDocuments: async (params = {}) => {
    const response = await api.get('/documents', { params });
    return response.data;
  },

  /**
   * Get single document by ID
   */
  getDocument: async (id) => {
    const response = await api.get(`/documents/${id}`);
    return response.data;
  },

  /**
   * Create new document
   */
  createDocument: async (data) => {
    const response = await api.post('/documents/create', data);
    return response.data;
  },

  /**
   * Update document (including template_fields)
   */
  updateDocument: async (id, data) => {
    const response = await api.put(`/documents/update/${id}`, data);
    return response.data;
  },

  // ========== TEMPLATE MANAGEMENT ==========

  /**
   * Upload template file (PDF, DOCX, or HTML)
   */
  uploadTemplate: async (documentId, file) => {
    const formData = new FormData();
    formData.append('template', file);

    const response = await api.post(
      `/documents/${documentId}/template/upload`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    );
    return response.data;
  },

  /**
   * Extract placeholders from uploaded template
   */
  extractPlaceholders: async (documentId) => {
    const response = await api.get(
      `/documents/${documentId}/template/extract-placeholders`
    );
    return response.data;
  },

  /**
   * Get template file (download)
   */
  getTemplate: async (documentId) => {
    const response = await api.get(
      `/documents/${documentId}/template/get`,
      {
        responseType: 'blob',
      }
    );
    return response.data;
  },

  /**
   * Delete template
   */
  deleteTemplate: async (documentId) => {
    const response = await api.delete(
      `/documents/${documentId}/template/delete`
    );
    return response.data;
  },

  /**
   * Update HTML template (store in database)
   */
  updateHtmlTemplate: async (documentId, htmlTemplate, templateFields) => {
    const response = await api.put(`/documents/update/${documentId}`, {
      html_template: htmlTemplate,
      template_fields: templateFields,
    });
    return response.data;
  },

  // ========== DOCUMENT REQUESTS ==========

  /**
   * Get all document requests
   */
  getRequests: async (params = {}) => {
    const response = await api.get('/request-documents', { params });
    return response.data;
  },

  /**
   * Get single request by ID
   */
  getRequest: async (id) => {
    const response = await api.get(`/request-documents/${id}`);
    return response.data;
  },

  /**
   * Create document request with information
   */
  createRequest: async (data) => {
    const response = await api.post('/request-documents/create', data);
    return response.data;
  },

  /**
   * Generate filled document from request
   */
  generateFilledDocument: async (requestId) => {
    const response = await api.post(
      `/request-documents/${requestId}/generate-filled-document`
    );
    return response.data;
  },

  /**
   * Update request status
   */
  updateRequestStatus: async (requestId, status, remark = null) => {
    const response = await api.put(
      `/request-documents/status/${requestId}`,
      { status, remark }
    );
    return response.data;
  },

  /**
   * Track document by transaction ID
   */
  trackDocument: async (transactionId) => {
    const response = await api.get(`/track-document/${transactionId}`);
    return response.data;
  },

  /**
   * Download filled document
   */
  downloadFilledDocument: async (filePath) => {
    const response = await api.get(
      `/storage/${filePath}`,
      {
        responseType: 'blob',
      }
    );
    return response.data;
  },
};

export default documentService;
```

---

## 🎨 Step 3: Admin - Upload Template Component

Create `src/components/Admin/TemplateUpload.jsx`:

```javascript
import React, { useState } from 'react';
import documentService from '../../services/documentService';

const TemplateUpload = ({ documentId, onUploadSuccess }) => {
  const [file, setFile] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState(null);
  const [result, setResult] = useState(null);

  const handleFileChange = (e) => {
    const selectedFile = e.target.files[0];
    
    // Validate file type
    const allowedTypes = [
      'application/pdf',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/msword',
      'text/html',
    ];
    
    if (selectedFile && !allowedTypes.includes(selectedFile.type)) {
      setError('Please select a PDF, DOCX, or HTML file');
      setFile(null);
      return;
    }
    
    setFile(selectedFile);
    setError(null);
  };

  const handleUpload = async () => {
    if (!file) {
      setError('Please select a file');
      return;
    }

    setUploading(true);
    setError(null);

    try {
      const response = await documentService.uploadTemplate(documentId, file);
      setResult(response);
      
      // Show success message
      alert(`Template uploaded successfully! Found ${response.placeholders_count} placeholders`);
      
      // Call parent callback
      if (onUploadSuccess) {
        onUploadSuccess(response);
      }
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to upload template');
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="template-upload">
      <h3>Upload Template</h3>
      
      <div className="file-input">
        <input
          type="file"
          accept=".pdf,.docx,.doc,.html"
          onChange={handleFileChange}
          disabled={uploading}
        />
      </div>

      {file && (
        <div className="file-info">
          <p>Selected: {file.name}</p>
          <p>Size: {(file.size / 1024).toFixed(2)} KB</p>
        </div>
      )}

      <button
        onClick={handleUpload}
        disabled={!file || uploading}
        className="btn-primary"
      >
        {uploading ? 'Uploading...' : 'Upload Template'}
      </button>

      {error && <div className="error">{error}</div>}

      {result && (
        <div className="result">
          <h4>Upload Successful!</h4>
          <p>Template Type: {result.template_type?.toUpperCase()}</p>
          <p>Placeholders Found: {result.placeholders_count}</p>
          {result.placeholders_found && (
            <div>
              <strong>Placeholders:</strong>
              <ul>
                {result.placeholders_found.map((placeholder) => (
                  <li key={placeholder}>{placeholder}</li>
                ))}
              </ul>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default TemplateUpload;
```

---

## 🎨 Step 4: Admin - Template Field Configuration

Create `src/components/Admin/TemplateFieldsConfig.jsx`:

```javascript
import React, { useState, useEffect } from 'react';
import documentService from '../../services/documentService';

const TemplateFieldsConfig = ({ documentId }) => {
  const [fields, setFields] = useState([]);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);

  // Load existing fields
  useEffect(() => {
    loadDocument();
  }, [documentId]);

  const loadDocument = async () => {
    setLoading(true);
    try {
      const doc = await documentService.getDocument(documentId);
      setFields(doc.template_fields || []);
    } catch (error) {
      console.error('Failed to load document:', error);
    } finally {
      setLoading(false);
    }
  };

  const addField = () => {
    setFields([
      ...fields,
      {
        name: '',
        label: '',
        type: 'text',
        required: false,
      },
    ]);
  };

  const updateField = (index, key, value) => {
    const newFields = [...fields];
    newFields[index][key] = value;
    setFields(newFields);
  };

  const removeField = (index) => {
    setFields(fields.filter((_, i) => i !== index));
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      await documentService.updateDocument(documentId, {
        template_fields: fields,
      });
      alert('Template fields saved successfully!');
    } catch (error) {
      alert('Failed to save: ' + (error.response?.data?.error || error.message));
    } finally {
      setSaving(false);
    }
  };

  const autoExtractFields = async () => {
    try {
      const result = await documentService.extractPlaceholders(documentId);
      
      // Convert extracted placeholders to field objects
      const extractedFields = result.placeholders.map((placeholder) => ({
        name: placeholder,
        label: placeholder
          .split('_')
          .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
          .join(' '),
        type: 'text',
        required: true,
      }));

      setFields(extractedFields);
      alert(`Extracted ${extractedFields.length} fields from template!`);
    } catch (error) {
      alert('Failed to extract fields: ' + error.message);
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div className="template-fields-config">
      <div className="header">
        <h3>Configure Template Fields</h3>
        <div className="actions">
          <button onClick={autoExtractFields} className="btn-secondary">
            Auto-Extract from Template
          </button>
          <button onClick={addField} className="btn-secondary">
            Add Field
          </button>
        </div>
      </div>

      <div className="fields-list">
        {fields.map((field, index) => (
          <div key={index} className="field-row">
            <input
              type="text"
              placeholder="Field Name (e.g., full_name)"
              value={field.name}
              onChange={(e) => updateField(index, 'name', e.target.value)}
            />
            
            <input
              type="text"
              placeholder="Label (e.g., Full Name)"
              value={field.label}
              onChange={(e) => updateField(index, 'label', e.target.value)}
            />
            
            <select
              value={field.type}
              onChange={(e) => updateField(index, 'type', e.target.value)}
            >
              <option value="text">Text</option>
              <option value="textarea">Textarea</option>
              <option value="number">Number</option>
              <option value="date">Date</option>
              <option value="email">Email</option>
            </select>
            
            <label>
              <input
                type="checkbox"
                checked={field.required}
                onChange={(e) =>
                  updateField(index, 'required', e.target.checked)
                }
              />
              Required
            </label>
            
            <button
              onClick={() => removeField(index)}
              className="btn-danger"
            >
              Remove
            </button>
          </div>
        ))}
      </div>

      {fields.length === 0 && (
        <p className="no-fields">
          No fields configured. Click "Add Field" or "Auto-Extract from Template".
        </p>
      )}

      <button
        onClick={handleSave}
        disabled={saving}
        className="btn-primary"
      >
        {saving ? 'Saving...' : 'Save Template Fields'}
      </button>
    </div>
  );
};

export default TemplateFieldsConfig;
```

---

## 🎨 Step 5: User - Dynamic Document Request Form

Create `src/components/User/DocumentRequestForm.jsx`:

```javascript
import React, { useState, useEffect } from 'react';
import documentService from '../../services/documentService';

const DocumentRequestForm = ({ documentId, onSuccess }) => {
  const [document, setDocument] = useState(null);
  const [formData, setFormData] = useState({});
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    loadDocument();
  }, [documentId]);

  const loadDocument = async () => {
    setLoading(true);
    try {
      const doc = await documentService.getDocument(documentId);
      setDocument(doc);
      
      // Initialize form data
      const initialData = {};
      doc.template_fields?.forEach((field) => {
        initialData[field.name] = '';
      });
      setFormData(initialData);
    } catch (error) {
      console.error('Failed to load document:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (fieldName, value) => {
    setFormData({
      ...formData,
      [fieldName]: value,
    });
    
    // Clear error for this field
    if (errors[fieldName]) {
      setErrors({
        ...errors,
        [fieldName]: null,
      });
    }
  };

  const validateForm = () => {
    const newErrors = {};
    
    document.template_fields?.forEach((field) => {
      if (field.required && !formData[field.name]) {
        newErrors[field.name] = `${field.label} is required`;
      }
    });
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setSubmitting(true);

    try {
      const response = await documentService.createRequest({
        document: documentId,
        information: formData,
      });

      alert('Document request submitted successfully!');
      
      if (onSuccess) {
        onSuccess(response);
      }
    } catch (error) {
      const errorMsg = error.response?.data?.errors
        ? Object.values(error.response.data.errors).flat().join(', ')
        : error.response?.data?.error || 'Failed to submit request';
      
      alert('Error: ' + errorMsg);
    } finally {
      setSubmitting(false);
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
            rows={4}
            className={errors[field.name] ? 'error' : ''}
          />
        );

      case 'date':
        return (
          <input
            type="date"
            value={formData[field.name] || ''}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            required={field.required}
            className={errors[field.name] ? 'error' : ''}
          />
        );

      case 'number':
        return (
          <input
            type="number"
            value={formData[field.name] || ''}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            required={field.required}
            className={errors[field.name] ? 'error' : ''}
          />
        );

      case 'email':
        return (
          <input
            type="email"
            value={formData[field.name] || ''}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            required={field.required}
            className={errors[field.name] ? 'error' : ''}
          />
        );

      default:
        return (
          <input
            type="text"
            value={formData[field.name] || ''}
            onChange={(e) => handleInputChange(field.name, e.target.value)}
            required={field.required}
            className={errors[field.name] ? 'error' : ''}
          />
        );
    }
  };

  if (loading) return <div>Loading...</div>;

  if (!document) return <div>Document not found</div>;

  return (
    <form onSubmit={handleSubmit} className="document-request-form">
      <h2>Request: {document.document_name}</h2>
      <p>{document.description}</p>

      <div className="form-fields">
        {document.template_fields?.map((field) => (
          <div key={field.name} className="form-group">
            <label>
              {field.label}
              {field.required && <span className="required">*</span>}
            </label>
            
            {renderField(field)}
            
            {errors[field.name] && (
              <span className="error-message">{errors[field.name]}</span>
            )}
          </div>
        ))}
      </div>

      {!document.template_fields || document.template_fields.length === 0 && (
        <p className="no-fields">
          No fields configured for this document type.
        </p>
      )}

      <button
        type="submit"
        disabled={submitting}
        className="btn-primary"
      >
        {submitting ? 'Submitting...' : 'Submit Request'}
      </button>
    </form>
  );
};

export default DocumentRequestForm;
```

---

## 🎨 Step 6: User - Document Request List & Download

Create `src/components/User/MyRequests.jsx`:

```javascript
import React, { useState, useEffect } from 'react';
import documentService from '../../services/documentService';

const MyRequests = () => {
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [generating, setGenerating] = useState({});

  useEffect(() => {
    loadRequests();
  }, []);

  const loadRequests = async () => {
    setLoading(true);
    try {
      const response = await documentService.getRequests();
      setRequests(response.data || response);
    } catch (error) {
      console.error('Failed to load requests:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleGenerateDocument = async (requestId) => {
    setGenerating({ ...generating, [requestId]: true });

    try {
      const response = await documentService.generateFilledDocument(requestId);
      
      alert('Document generated successfully!');
      
      // Download the file
      if (response.file_url) {
        window.open(response.file_url, '_blank');
      }
    } catch (error) {
      alert('Failed to generate document: ' + error.message);
    } finally {
      setGenerating({ ...generating, [requestId]: false });
    }
  };

  const getStatusBadgeClass = (status) => {
    const statusClasses = {
      pending: 'badge-warning',
      approved: 'badge-info',
      processing: 'badge-info',
      'ready to pickup': 'badge-success',
      released: 'badge-success',
      rejected: 'badge-danger',
    };
    return statusClasses[status] || 'badge-secondary';
  };

  if (loading) return <div>Loading requests...</div>;

  return (
    <div className="my-requests">
      <h2>My Document Requests</h2>

      {requests.length === 0 && (
        <p>You haven't made any document requests yet.</p>
      )}

      <div className="requests-list">
        {requests.map((request) => (
          <div key={request.id} className="request-card">
            <div className="request-header">
              <h3>{request.document_details?.document_name}</h3>
              <span className={`badge ${getStatusBadgeClass(request.status)}`}>
                {request.status}
              </span>
            </div>

            <div className="request-details">
              <p>
                <strong>Transaction ID:</strong> {request.transaction_id}
              </p>
              <p>
                <strong>Submitted:</strong>{' '}
                {new Date(request.created_at).toLocaleDateString()}
              </p>

              {request.information && (
                <div className="information">
                  <strong>Submitted Information:</strong>
                  <ul>
                    {Object.entries(request.information).map(([key, value]) => (
                      <li key={key}>
                        <strong>{key}:</strong> {value}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>

            <div className="request-actions">
              <button
                onClick={() => handleGenerateDocument(request.id)}
                disabled={generating[request.id]}
                className="btn-primary"
              >
                {generating[request.id]
                  ? 'Generating...'
                  : 'Generate Document'}
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default MyRequests;
```

---

## 🎨 Step 7: Track Document by Transaction ID

Create `src/components/Public/TrackDocument.jsx`:

```javascript
import React, { useState } from 'react';
import documentService from '../../services/documentService';

const TrackDocument = () => {
  const [transactionId, setTransactionId] = useState('');
  const [tracking, setTracking] = useState(false);
  const [result, setResult] = useState(null);
  const [error, setError] = useState(null);

  const handleTrack = async (e) => {
    e.preventDefault();
    
    if (!transactionId) {
      setError('Please enter a transaction ID');
      return;
    }

    setTracking(true);
    setError(null);
    setResult(null);

    try {
      const data = await documentService.trackDocument(transactionId);
      setResult(data);
    } catch (err) {
      setError(err.response?.data?.error || 'Document not found');
    } finally {
      setTracking(false);
    }
  };

  return (
    <div className="track-document">
      <h2>Track Your Document</h2>

      <form onSubmit={handleTrack}>
        <div className="form-group">
          <input
            type="text"
            placeholder="Enter Transaction ID (e.g., TXN_DOC_1234567)"
            value={transactionId}
            onChange={(e) => setTransactionId(e.target.value)}
            className="transaction-input"
          />
        </div>

        <button type="submit" disabled={tracking} className="btn-primary">
          {tracking ? 'Tracking...' : 'Track Document'}
        </button>
      </form>

      {error && <div className="error">{error}</div>}

      {result && (
        <div className="tracking-result">
          <h3>Document Status</h3>
          
          <div className="status-info">
            <p>
              <strong>Transaction ID:</strong> {result.transaction_id}
            </p>
            <p>
              <strong>Document Type:</strong> {result.document_type}
            </p>
            <p>
              <strong>Status:</strong>{' '}
              <span className={`status-${result.status}`}>
                {result.status.toUpperCase()}
              </span>
            </p>
            <p>
              <strong>Submitted:</strong> {result.request_date}
            </p>
            <p>
              <strong>Last Updated:</strong> {result.last_updated}
            </p>
          </div>

          {result.certificate_logs && (
            <div className="activity-logs">
              <h4>Activity History</h4>
              <ul>
                {result.certificate_logs.map((log) => (
                  <li key={log.id}>
                    <strong>{log.logged_at}:</strong> {log.remark}
                    {log.staff_name && <em> by {log.staff_name}</em>}
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default TrackDocument;
```

---

## 📝 Step 8: Environment Configuration

Create `.env`:

```env
REACT_APP_API_URL=http://localhost:8000/api
REACT_APP_STORAGE_URL=http://localhost:8000/storage
```

---

## 🎨 Step 9: Example CSS (Optional)

Create `src/styles/DocumentForms.css`:

```css
/* Form Styles */
.document-request-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-group input.error,
.form-group textarea.error {
  border-color: #dc3545;
}

.error-message {
  color: #dc3545;
  font-size: 14px;
  margin-top: 5px;
  display: block;
}

.required {
  color: #dc3545;
  margin-left: 3px;
}

/* Button Styles */
.btn-primary {
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-primary:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

/* Request Card Styles */
.request-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.badge {
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}

.badge-warning {
  background-color: #ffc107;
  color: #000;
}

.badge-success {
  background-color: #28a745;
  color: #fff;
}

.badge-danger {
  background-color: #dc3545;
  color: #fff;
}
```

---

## 🚀 Complete Usage Example

### Admin Workflow:

```javascript
// 1. Create Document
const newDoc = await documentService.createDocument({
  document_name: 'Barangay Certificate',
  description: 'Certificate for residents',
  status: 'active',
});

// 2. Upload Template
const file = document.getElementById('template-file').files[0];
const uploadResult = await documentService.uploadTemplate(newDoc.document.id, file);

// 3. Configure Fields (auto-extracted or manual)
await documentService.updateDocument(newDoc.document.id, {
  template_fields: uploadResult.placeholders_found.map(p => ({
    name: p,
    label: p.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
    type: 'text',
    required: true,
  })),
});
```

### User Workflow:

```javascript
// 1. Submit Request
const request = await documentService.createRequest({
  document: 1,
  information: {
    full_name: 'Juan Dela Cruz',
    address: '123 Main Street',
    purpose: 'Employment',
  },
});

// 2. Generate Filled Document
const generated = await documentService.generateFilledDocument(request.request_document.id);

// 3. Download
window.open(generated.file_url, '_blank');
```

---

## ✅ Testing Checklist

- [ ] Template upload works (PDF/DOCX/HTML)
- [ ] Placeholders are extracted correctly
- [ ] Template fields can be configured
- [ ] Form renders dynamically based on template fields
- [ ] Request submission works
- [ ] Document generation works
- [ ] File download works
- [ ] Error handling displays properly
- [ ] Loading states show correctly

---

## 🐛 Common Issues & Solutions

### Issue: CORS Error

**Solution:** Add CORS middleware in Laravel:

```php
// config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],
'allowed_origins' => ['http://localhost:3000'],
```

### Issue: File Upload Fails

**Solution:** Check FormData and headers:

```javascript
// Don't manually set Content-Type for multipart/form-data
// Axios will set it automatically with correct boundary
const config = {
  headers: {
    'Content-Type': 'multipart/form-data', // ❌ Remove this
  },
};
```

### Issue: Blob Download Fails

**Solution:** Ensure responseType is set:

```javascript
await api.get('/storage/file.pdf', {
  responseType: 'blob', // ✅ Required for file downloads
});
```

---

## 📚 Related Documentation

- Backend API: `DYNAMIC_TEMPLATE_GUIDE.md`
- DOCX Templates: `DOCX_TEMPLATE_GUIDE.md`
- HTML Templates: `HTML_TEMPLATE_GUIDE.md`

---

**You're all set! Start building your React frontend! 🚀**
