# Contact Form Implementation Plan

## Overview
This document outlines the complete implementation plan for the contact form on peterebenhoch.com, including frontend (completed) and backend (pending) components.

## Current Status: Frontend Completed ✓

### Form Features Implemented
- Email input field with validation
- Two-column layout for interests:
  - **Left Column (Areas of Interest):**
    - Transformational Governance
    - Information Security & Data Sovereignty
    - Digital Law Counseling
  - **Right Column (Contact Options):**
    - Keep me posted about website updates (low frequency) - *pre-selected by default*
    - Contact me directly
- Custom checkbox styling with improved contrast (dark borders, Stanford red when checked)
- Privacy notice: "Personal data is only processed for the purpose selected. You can unsubscribe any time."
- Stanford red submit button (#8C1515) with hover effect
- Loading spinner during submission
- Success/error message display

### Frontend Validation
- Client-side email validation (HTML5 + JavaScript)
- At least one interest must be selected
- Visual feedback for validation errors
- Bootstrap form validation classes

### Form Submission Flow
Currently configured to POST to `/api/submit-contact` endpoint (not yet implemented)

**Form Data Structure:**
```json
{
  "email": "user@example.com",
  "interests": [
    "transformational-governance",
    "information-security-data-sovereignty",
    "digital-law-counseling",
    "keep-me-posted",
    "contact-me-directly"
  ]
}
```

---

## Pending: Backend Implementation

### 1. Backend API Endpoint

**Endpoint:** `POST /api/submit-contact`

**Technology Options:**
- **Python (Flask/FastAPI)** - Recommended for Hetzner Linux VPS
- **PHP** - Alternative if Apache/Nginx with PHP already configured
- **Node.js/Express** - If JavaScript/TypeScript ecosystem preferred

**API Requirements:**
- Accept JSON payload with `email` and `interests[]` fields
- Server-side validation:
  - Valid email format
  - At least one interest selected
  - Sanitize all input to prevent injection attacks
- Return JSON response:
  - Success: `{"success": true, "message": "..."}`
  - Error: `{"success": false, "error": "..."}`

**Security Measures:**
- CSRF protection (token-based or SameSite cookies)
- Rate limiting (max 5 submissions per IP per hour)
- Input sanitization and validation
- HTTPS only (already configured for peterebenhoch.com)
- Honeypot field for bot prevention (optional)

### 2. Database Schema

**Option A: SQLite** (Simple, file-based)
```sql
CREATE TABLE contact_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    interests TEXT NOT NULL, -- JSON array stored as text
    ip_address TEXT,
    user_agent TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    synced_to_attio BOOLEAN DEFAULT FALSE,
    attio_record_id TEXT
);

CREATE INDEX idx_email ON contact_submissions(email);
CREATE INDEX idx_submitted_at ON contact_submissions(submitted_at);
```

**Option B: PostgreSQL** (More robust, if already running on VPS)
```sql
CREATE TABLE contact_submissions (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    interests JSONB NOT NULL,
    ip_address INET,
    user_agent TEXT,
    submitted_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    synced_to_attio BOOLEAN DEFAULT FALSE,
    attio_record_id TEXT
);

CREATE INDEX idx_email ON contact_submissions(email);
CREATE INDEX idx_submitted_at ON contact_submissions(submitted_at);
CREATE INDEX idx_interests ON contact_submissions USING GIN(interests);
```

**Option C: Supabase** (Managed database, easier setup)
- No schema management needed on VPS
- Built-in REST API
- Real-time subscriptions available
- Free tier available

### 3. Attio CRM Integration

**API Documentation:** https://developers.attio.com/

**Integration Steps:**

1. **Authentication:**
   - Obtain Attio API key from Attio dashboard
   - Store securely in environment variables
   - Use Bearer token authentication

2. **API Endpoints:**
   - Create/Update People: `POST /v2/objects/people/records`
   - Add Notes: `POST /v2/notes`
   - Create List Entry: `POST /v2/lists/{list_id}/entries`

3. **Data Mapping:**
   ```
   Form Field → Attio Field
   ---------------------------
   email → email_addresses (primary)
   interests → tags or custom attribute
   submitted_at → note timestamp
   ```

4. **Integration Flow:**
   ```
   1. Receive form submission
   2. Validate and sanitize data
   3. Store in local database
   4. Check if contact exists in Attio (by email)
   5. If exists: Update record with new interests
   6. If not exists: Create new person record
   7. Add note with submission details
   8. Update local database with attio_record_id
   9. Return success to frontend
   ```

5. **Error Handling:**
   - If Attio API fails, still store in local database
   - Flag record as `synced_to_attio = FALSE`
   - Implement retry mechanism (cron job or queue)
   - Log errors for manual review

### 4. Implementation Checklist

**Backend API (Python/Flask Example):**
- [ ] Set up Python virtual environment on Hetzner VPS
- [ ] Install dependencies: `flask`, `flask-cors`, `python-dotenv`, `requests`
- [ ] Create `/api/submit-contact` endpoint
- [ ] Implement request validation
- [ ] Add rate limiting (flask-limiter)
- [ ] Add CSRF protection
- [ ] Set up environment variables for API keys

**Database:**
- [ ] Choose database solution (SQLite/PostgreSQL/Supabase)
- [ ] Create database schema
- [ ] Write ORM/query functions for CRUD operations
- [ ] Set up database backups

**Attio Integration:**
- [ ] Create Attio account and obtain API key
- [ ] Test Attio API endpoints with Postman/curl
- [ ] Implement person creation/update logic
- [ ] Implement note creation for submissions
- [ ] Add retry mechanism for failed syncs
- [ ] Create monitoring/logging for sync status

**Deployment:**
- [ ] Configure reverse proxy (Nginx/Apache) for `/api/*` routes
- [ ] Set up systemd service for Flask/FastAPI app
- [ ] Configure CORS headers for peterebenhoch.com
- [ ] Test form submission end-to-end
- [ ] Set up monitoring and error alerts

**Testing:**
- [ ] Unit tests for validation logic
- [ ] Integration tests for database operations
- [ ] End-to-end tests for form submission
- [ ] Test Attio API integration
- [ ] Load testing for rate limiting

### 5. Example Backend Code Structure

```
/srv/peterebenhoch-api/
├── app.py                 # Main Flask app
├── requirements.txt       # Python dependencies
├── .env                   # Environment variables (not in git)
├── config.py             # Configuration management
├── models/
│   └── submission.py     # Database models
├── services/
│   ├── validation.py     # Input validation
│   ├── attio.py          # Attio API integration
│   └── database.py       # Database operations
├── routes/
│   └── contact.py        # Contact form endpoint
└── tests/
    ├── test_validation.py
    ├── test_attio.py
    └── test_integration.py
```

### 6. Environment Variables Required

```bash
# .env file
FLASK_ENV=production
FLASK_SECRET_KEY=<random-secret-key>
DATABASE_URL=sqlite:///contact_submissions.db
ATTIO_API_KEY=<your-attio-api-key>
ALLOWED_ORIGINS=https://peterebenhoch.com
RATE_LIMIT_PER_HOUR=5
```

### 7. Nginx Configuration Example

```nginx
# /etc/nginx/sites-available/peterebenhoch.com

location /api/ {
    proxy_pass http://127.0.0.1:5000/api/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    
    # CORS headers
    add_header Access-Control-Allow-Origin "https://peterebenhoch.com" always;
    add_header Access-Control-Allow-Methods "POST, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Content-Type" always;
    
    if ($request_method = 'OPTIONS') {
        return 204;
    }
}
```

---

## Future Enhancements

### Email Notifications
- Send confirmation email to user after submission
- Send notification email to admin (pe@peterebenhoch.com)
- Use service like SendGrid, Mailgun, or Amazon SES

### Admin Dashboard
- View all submissions
- Filter by date, interests, sync status
- Manual sync to Attio for failed submissions
- Export to CSV

### Analytics
- Track conversion rates
- Popular interest combinations
- Submission trends over time

### Unsubscribe Mechanism
- Generate unique unsubscribe token per email
- Create `/unsubscribe?token=xxx` endpoint
- Update Attio record when user unsubscribes

---

## Related Documents
- [Feature Planning](feature-planning.md) - Overall feature roadmap
- [Contact Form Design Proposal](contact-form-design-proposal.md) - Initial design concepts

---

*Last updated: 2026-01-07*

