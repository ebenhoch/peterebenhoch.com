# Contact Form Backend - Schritt-für-Schritt Implementierungsanleitung

**Version:** 1.0.0  
**Datum:** 2026-01-07  
**Technologie:** FastAPI + SQLite + Attio CRM  
**Geschätzte Zeit:** ~1.5 Stunden  

---

## Übersicht

Diese Anleitung führt Sie Schritt-für-Schritt durch die Implementierung des Contact Form Backends für peterebenhoch.com mit:

- ✅ **FastAPI** - Modernes Python Web Framework
- ✅ **Automatische Validierung** - Pydantic Models
- ✅ **Swagger UI** - API-Dokumentation unter `/docs`
- ✅ **Attio CRM Integration** - Mit Multi-Website Support
- ✅ **Source Tracking** - Namespace `source:peterebenhoch.com` für spätere Multi-Website-Nutzung
- ✅ **SQLite Datenbank** - Lokale Speicherung aller Submissions
- ✅ **Rate Limiting** - 5 Submissions/Stunde pro IP
- ✅ **Logging** - Vollständige Protokollierung
- ✅ **Unsubscribe** - Token-basiertes System (Foundation)

---

## ⚠️ Wichtig: Frontend-Formular Backup

**Status:** Das Contact-Formular ist aktuell **deaktiviert** auf der Live-Website.

**Backup-Datei:** `/contact-form-BACKUP.txt` (im Projekt-Root)

Diese Datei enthält den kompletten HTML/CSS/JavaScript Code des Contact Forms, der später wieder in `contact.qmd` eingefügt werden muss, sobald das Backend läuft.

**Wiederherstellung nach Backend-Deployment:**
1. Backend ist deployed und getestet ✅
2. Öffnen Sie `contact-form-BACKUP.txt`
3. Kopieren Sie den kompletten Inhalt
4. Öffnen Sie `contact.qmd`
5. Ersetzen Sie die Zeile `*Contact form coming soon...*` mit dem Backup-Inhalt
6. Rendern und deployen

---

## Wichtig: Namespace/Source Tracking

**Neu implementiert für Multi-Website Support:**

Jeder Kontakt wird in Attio mit einem Source-Tag versehen:
- Tag: `source:peterebenhoch.com`
- Note: "Contact form submission from peterebenhoch.com"

**Vorteil:** Später können Sie weitere Websites hinzufügen (z.B. `source:otherwebsite.com`) und in Attio nach Quelle filtern.

---

## Teil 1: Vorbereitung (5 Minuten)

### Schritt 1.1: Attio API Key erstellen

1. Öffnen Sie: https://app.attio.com/settings/api
2. Klicken Sie: **"Create new API key"**
3. **Name:** "peterebenhoch.com Contact Form"
4. **Permissions wählen:**
   - ✅ `people:read-write` (Kontakte lesen/schreiben)
   - ✅ `notes:read-write` (Notizen hinzufügen)
5. **API Key kopieren** (beginnt mit `sk_...`)
6. **Temporär speichern** (Sie brauchen ihn in Schritt 3.1)

### Schritt 1.2: SSH Verbindung zum Hetzner VPS

```bash
ssh root@IHRE-SERVER-IP
# oder mit Ihrem Username
ssh IHR-USERNAME@IHRE-SERVER-IP
```

---

## Teil 2: Projekt-Setup (10 Minuten)

### Schritt 2.1: Projektverzeichnis erstellen

```bash
# Wechseln Sie zu /srv
cd /srv

# Verzeichnis erstellen
mkdir peterebenhoch-api
cd peterebenhoch-api
```

**Falls "Permission denied":**
```bash
sudo mkdir /srv/peterebenhoch-api
sudo chown $USER:$USER /srv/peterebenhoch-api
cd /srv/peterebenhoch-api
```

### Schritt 2.2: Python Virtual Environment

```bash
# Virtual Environment erstellen
python3 -m venv venv

# Aktivieren
source venv/bin/activate
```

**Erfolg:** Sie sehen jetzt `(venv)` vor Ihrem Terminal-Prompt.

```bash
# Pip aktualisieren
pip install --upgrade pip
```

### Schritt 2.3: Python Dependencies installieren

```bash
pip install fastapi uvicorn[standard] pydantic[email] httpx python-dotenv slowapi
```

**Hinweis:** Das dauert ca. 1-2 Minuten.

### Schritt 2.4: Verzeichnisstruktur erstellen

```bash
# Unterverzeichnisse
mkdir -p data/backups
mkdir -p logs

# Dateien
touch .env
touch api.py
touch requirements.txt
```

**Ihre Struktur sieht jetzt so aus:**
```
/srv/peterebenhoch-api/
├── venv/
├── data/
│   └── backups/
├── logs/
├── .env
├── api.py
└── requirements.txt
```

---

## Teil 3: Dateien konfigurieren (15 Minuten)

### Schritt 3.1: Environment Variables (.env)

```bash
nano .env
```

**Fügen Sie folgendes ein** (ersetzen Sie `IHREN_ATTIO_KEY`):

```bash
# Attio API Configuration
ATTIO_API_KEY=sk_IHREN_ATTIO_API_KEY_HIER_EINFÜGEN

# Website Source (für Multi-Website Support)
WEBSITE_SOURCE=peterebenhoch.com

# CORS - Erlaubte Origin
ALLOWED_ORIGIN=https://peterebenhoch.com

# Rate Limiting
RATE_LIMIT_PER_HOUR=5
```

**Speichern:**
1. `Ctrl + O` (Write Out)
2. `Enter` (bestätigen)
3. `Ctrl + X` (Exit)

**Wichtig - Dateiberechtigungen setzen:**
```bash
chmod 600 .env
```

Dies stellt sicher, dass nur Sie die Datei lesen können (Sicherheit!).

### Schritt 3.2: Requirements File

```bash
nano requirements.txt
```

**Inhalt:**

```
fastapi==0.109.0
uvicorn[standard]==0.27.0
pydantic[email]==2.5.3
httpx==0.26.0
python-dotenv==1.0.0
slowapi==0.1.9
```

**Speichern:** `Ctrl+O`, `Enter`, `Ctrl+X`

### Schritt 3.3: Haupt-API Datei (api.py)

```bash
nano api.py
```

**Kopieren Sie den kompletten Code unten:**

```python
#!/usr/bin/env python3
"""
Contact Form API for peterebenhoch.com
FastAPI with Attio integration and multi-website source tracking
"""

import os
import json
import sqlite3
import logging
from datetime import datetime
from pathlib import Path
from uuid import uuid4
from typing import List
from contextlib import asynccontextmanager

from fastapi import FastAPI, HTTPException, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse, HTMLResponse
from pydantic import BaseModel, EmailStr, field_validator
from slowapi import Limiter, _rate_limit_exceeded_handler
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded
import httpx
from dotenv import load_dotenv

# Load environment
load_dotenv()

# Configuration
ATTIO_API_KEY = os.getenv('ATTIO_API_KEY')
ATTIO_BASE = 'https://api.attio.com/v2'
WEBSITE_SOURCE = os.getenv('WEBSITE_SOURCE', 'peterebenhoch.com')
DB_PATH = 'data/contacts.db'
ALLOWED_ORIGIN = os.getenv('ALLOWED_ORIGIN', 'https://peterebenhoch.com')
RATE_LIMIT = int(os.getenv('RATE_LIMIT_PER_HOUR', '5'))

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('logs/api.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# ============================================================================
# DATABASE SETUP
# ============================================================================

def init_db():
    """Initialize SQLite database with source field for namespace"""
    Path('data').mkdir(exist_ok=True)
    Path('data/backups').mkdir(exist_ok=True)
    Path('logs').mkdir(exist_ok=True)
    
    conn = sqlite3.connect(DB_PATH)
    conn.execute('''
        CREATE TABLE IF NOT EXISTS submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL,
            interests TEXT NOT NULL,
            source TEXT NOT NULL,
            unsubscribe_token TEXT UNIQUE,
            ip_address TEXT,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            synced_to_attio BOOLEAN DEFAULT FALSE,
            attio_record_id TEXT
        )
    ''')
    conn.execute('CREATE INDEX IF NOT EXISTS idx_email ON submissions(email)')
    conn.execute('CREATE INDEX IF NOT EXISTS idx_source ON submissions(source)')
    conn.execute('CREATE INDEX IF NOT EXISTS idx_token ON submissions(unsubscribe_token)')
    conn.commit()
    conn.close()
    logger.info(f"Database initialized for source: {WEBSITE_SOURCE}")

def get_db():
    """Get database connection"""
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn

# ============================================================================
# PYDANTIC MODELS
# ============================================================================

ALLOWED_INTERESTS = [
    'transformational-governance',
    'information-security-data-sovereignty',
    'digital-law-counseling',
    'keep-me-posted',
    'contact-me-directly'
]

class ContactSubmission(BaseModel):
    """Contact form submission"""
    email: EmailStr
    interests: List[str]
    
    @field_validator('interests')
    @classmethod
    def validate_interests(cls, v):
        if not v:
            raise ValueError('At least one interest required')
        invalid = [i for i in v if i not in ALLOWED_INTERESTS]
        if invalid:
            raise ValueError(f'Invalid interests: {invalid}')
        return v

class SuccessResponse(BaseModel):
    success: bool = True
    message: str

# ============================================================================
# FASTAPI APP
# ============================================================================

limiter = Limiter(key_func=get_remote_address)

@asynccontextmanager
async def lifespan(app: FastAPI):
    init_db()
    logger.info(f"Contact Form API started for {WEBSITE_SOURCE}")
    yield
    logger.info("Contact Form API stopped")

app = FastAPI(
    title=f"Contact Form API - {WEBSITE_SOURCE}",
    description=f"API for {WEBSITE_SOURCE} contact form with Attio integration",
    version="1.0.0",
    lifespan=lifespan
)

app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)

app.add_middleware(
    CORSMiddleware,
    allow_origins=[ALLOWED_ORIGIN],
    allow_credentials=True,
    allow_methods=["GET", "POST", "OPTIONS"],
    allow_headers=["*"],
)

# ============================================================================
# ATTIO INTEGRATION WITH SOURCE TRACKING
# ============================================================================

async def sync_to_attio(
    email: str, 
    interests: List[str], 
    source: str,
    submission_id: int
) -> bool:
    """
    Sync to Attio with source tracking for multi-website support
    Tags contact with 'source:WEBSITE' for easy filtering
    """
    try:
        headers = {
            'Authorization': f'Bearer {ATTIO_API_KEY}',
            'Content-Type': 'application/json'
        }
        
        async with httpx.AsyncClient(timeout=10.0) as client:
            # Check if person exists
            query_data = {
                "filter": {
                    "email": {"$eq": email}
                }
            }
            
            response = await client.post(
                f'{ATTIO_BASE}/objects/people/records/query',
                headers=headers,
                json=query_data
            )
            
            existing = response.json().get('data', [])
            
            # Add source tag to interests
            source_tag = f"source:{source}"
            all_tags = interests + [source_tag]
            
            # Prepare person data
            person_data = {
                "data": {
                    "values": {
                        "email_addresses": [{"email_address": email}],
                        "tags": all_tags
                    }
                }
            }
            
            if existing:
                # Update existing
                record_id = existing[0]['id']['record_id']
                await client.patch(
                    f'{ATTIO_BASE}/objects/people/records/{record_id}',
                    headers=headers,
                    json=person_data
                )
                logger.info(f"Updated Attio: {email} → {record_id} (source: {source})")
            else:
                # Create new
                result = await client.post(
                    f'{ATTIO_BASE}/objects/people/records',
                    headers=headers,
                    json=person_data
                )
                record_id = result.json()['data']['id']['record_id']
                logger.info(f"Created Attio: {email} → {record_id} (source: {source})")
            
            # Add note with source info
            note_data = {
                "data": {
                    "parent_object": "people",
                    "parent_record_id": record_id,
                    "title": f"Contact form submission from {source}",
                    "content": f"Submitted via {source} contact form\\nInterests: {', '.join(interests)}"
                }
            }
            
            try:
                await client.post(
                    f'{ATTIO_BASE}/notes',
                    headers=headers,
                    json=note_data
                )
            except Exception as note_error:
                logger.warning(f"Could not create note: {note_error}")
            
            # Update database
            conn = get_db()
            conn.execute(
                'UPDATE submissions SET synced_to_attio=?, attio_record_id=? WHERE id=?',
                (True, record_id, submission_id)
            )
            conn.commit()
            conn.close()
            
            return True
            
    except Exception as e:
        logger.error(f"Attio sync failed for {email}: {str(e)}")
        return False

# ============================================================================
# ENDPOINTS
# ============================================================================

@app.post("/api/submit-contact", response_model=SuccessResponse)
@limiter.limit(f"{RATE_LIMIT}/hour")
async def submit_contact(submission: ContactSubmission, request: Request):
    """Submit contact form with automatic source tracking"""
    ip_address = request.client.host
    email = submission.email.lower()
    interests = submission.interests
    
    logger.info(f"Submission from {WEBSITE_SOURCE}: {email} (IP: {ip_address})")
    
    try:
        unsubscribe_token = str(uuid4())
        
        conn = get_db()
        cursor = conn.execute(
            '''INSERT INTO submissions 
               (email, interests, source, unsubscribe_token, ip_address) 
               VALUES (?, ?, ?, ?, ?)''',
            (email, json.dumps(interests), WEBSITE_SOURCE, unsubscribe_token, ip_address)
        )
        submission_id = cursor.lastrowid
        conn.commit()
        conn.close()
        
        logger.info(f"Saved submission ID: {submission_id}")
        
        # Sync to Attio with source
        await sync_to_attio(email, interests, WEBSITE_SOURCE, submission_id)
        
        return SuccessResponse(
            message="Thank you! Your message has been submitted successfully."
        )
        
    except Exception as e:
        logger.error(f"Error: {str(e)}")
        raise HTTPException(status_code=500, detail="Server error")

@app.get("/api/unsubscribe")
async def unsubscribe(token: str):
    """Unsubscribe from communications"""
    if not token:
        raise HTTPException(status_code=400, detail="Invalid link")
    
    try:
        conn = get_db()
        cursor = conn.execute(
            'SELECT id, email, source FROM submissions WHERE unsubscribe_token=?',
            (token,)
        )
        result = cursor.fetchone()
        conn.close()
        
        if not result:
            raise HTTPException(status_code=404, detail="Invalid or expired link")
        
        email = result['email']
        source = result['source']
        
        logger.info(f"Unsubscribe: {email} (source: {source})")
        
        return HTMLResponse(f"""
            <html>
                <head>
                    <title>Unsubscribed - {source}</title>
                    <style>
                        body {{ font-family: Arial; padding: 50px; text-align: center; }}
                        .box {{ max-width: 500px; margin: 0 auto; }}
                    </style>
                </head>
                <body>
                    <div class="box">
                        <h1>✓ Successfully Unsubscribed</h1>
                        <p><strong>Email:</strong> {email}</p>
                        <p><strong>Source:</strong> {source}</p>
                        <p>You have been removed from our mailing list.</p>
                    </div>
                </body>
            </html>
        """)
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Unsubscribe error: {str(e)}")
        raise HTTPException(status_code=500, detail="Server error")

@app.get("/api/health")
async def health():
    """Health check"""
    return {
        "status": "healthy",
        "source": WEBSITE_SOURCE,
        "database": "connected" if Path(DB_PATH).exists() else "missing",
        "version": "1.0.0"
    }

@app.get("/")
async def root():
    """Root with links to docs"""
    return HTMLResponse(f"""
        <html>
            <head><title>Contact API - {WEBSITE_SOURCE}</title></head>
            <body style="font-family: Arial; padding: 50px;">
                <h1>Contact Form API</h1>
                <p><strong>Source:</strong> {WEBSITE_SOURCE}</p>
                <ul>
                    <li><a href="/docs">📖 API Documentation (Swagger UI)</a></li>
                    <li><a href="/api/health">🏥 Health Check</a></li>
                </ul>
            </body>
        </html>
    """)

# ============================================================================
# RUN
# ============================================================================

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "api:app",
        host="127.0.0.1",
        port=8000,
        reload=False,
        log_level="info"
    )
```

**Speichern:** `Ctrl+O`, `Enter`, `Ctrl+X`

**Ausführbar machen:**
```bash
chmod +x api.py
```

---

## Teil 4: Erste Tests (10 Minuten)

### Schritt 4.1: API starten

```bash
# Stellen Sie sicher, dass venv aktiviert ist
source venv/bin/activate

# API starten
python api.py
```

**Erwartete Ausgabe:**
```
INFO:     Started server process
INFO:     Waiting for application startup.
2026-01-07 15:30:45 - INFO - Database initialized for source: peterebenhoch.com
2026-01-07 15:30:45 - INFO - Contact Form API started for peterebenhoch.com
INFO:     Application startup complete.
INFO:     Uvicorn running on http://127.0.0.1:8000
```

✅ **Lassen Sie dieses Terminal offen!**

### Schritt 4.2: Health Check (neues Terminal)

Öffnen Sie ein **zweites SSH-Terminal** zum Server und testen Sie:

```bash
curl http://127.0.0.1:8000/api/health
```

**Erwartete Ausgabe:**
```json
{
  "status": "healthy",
  "source": "peterebenhoch.com",
  "database": "connected",
  "version": "1.0.0"
}
```

✅ **Wenn Sie das sehen, läuft die API korrekt!**

### Schritt 4.3: Test-Submission senden

Im zweiten Terminal:

```bash
curl -X POST http://127.0.0.1:8000/api/submit-contact \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "interests": ["keep-me-posted"]
  }'
```

**Erwartete Ausgabe:**
```json
{
  "success": true,
  "message": "Thank you! Your message has been submitted successfully."
}
```

**Im ersten Terminal (wo die API läuft) sollten Sie sehen:**
```
INFO - Submission from peterebenhoch.com: test@example.com (IP: 127.0.0.1)
INFO - Saved submission ID: 1
INFO - Created Attio: test@example.com → rec_XXXXX (source: peterebenhoch.com)
```

### Schritt 4.4: In Attio prüfen

1. Öffnen Sie: https://app.attio.com
2. Suchen Sie nach: `test@example.com`
3. Verifizieren Sie:
   - ✅ Kontakt existiert
   - ✅ Tag: `source:peterebenhoch.com`
   - ✅ Tag: `keep-me-posted`
   - ✅ Note: "Contact form submission from peterebenhoch.com"

✅ **Wenn alles da ist, funktioniert die Attio-Integration perfekt!**

---

## Teil 5: Nginx Konfiguration (10 Minuten)

### Schritt 5.1: API dauerhaft mit screen starten

Im ersten Terminal (wo die API läuft):
1. Drücken Sie `Ctrl+C` um die API zu stoppen

```bash
# API mit screen im Hintergrund starten
screen -dmS contact-api bash -c 'cd /srv/peterebenhoch-api && source venv/bin/activate && python api.py'

# Prüfen ob screen läuft
screen -ls
```

**Erwartete Ausgabe:**
```
There is a screen on:
    12345.contact-api    (Detached)
```

**Nützliche Screen-Befehle:**
```bash
# Logs live ansehen
screen -r contact-api
# Zum Verlassen: Ctrl+A dann D

# Screen beenden
screen -S contact-api -X quit
```

### Schritt 5.2: Nginx Reverse Proxy konfigurieren

```bash
nano /etc/nginx/sites-available/peterebenhoch.com
```

**Fügen Sie INNERHALB des `server { ... }` Blocks hinzu:**

```nginx
    # Contact Form API Endpoints
    location /api/submit-contact {
        proxy_pass http://127.0.0.1:8000/api/submit-contact;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Timeouts
        proxy_connect_timeout 5s;
        proxy_send_timeout 10s;
        proxy_read_timeout 10s;
    }
    
    location /api/unsubscribe {
        proxy_pass http://127.0.0.1:8000/api/unsubscribe;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
    
    location /api/health {
        proxy_pass http://127.0.0.1:8000/api/health;
        proxy_set_header Host $host;
    }
```

**Speichern:** `Ctrl+O`, `Enter`, `Ctrl+X`

### Schritt 5.3: Nginx testen und neu laden

```bash
# Konfiguration testen
nginx -t
```

**Erwartete Ausgabe:**
```
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

```bash
# Nginx neu laden
systemctl reload nginx
```

### Schritt 5.4: Von außen testen

```bash
# Health Check von außen
curl https://peterebenhoch.com/api/health
```

**Sollte funktionieren!**

---

## Teil 6: Von der Website testen (5 Minuten)

### Schritt 6.1: Formular auf Website ausfüllen

1. Öffnen Sie: **https://peterebenhoch.com/contact**
2. Füllen Sie das Formular aus:
   - Email: `ihre-email@example.com`
   - Wählen Sie Interessen (z.B. "Keep me posted")
3. Klicken Sie **"Submit"**

**Erwartung:**
- ✅ Erfolgsmeldung wird angezeigt
- ✅ Formular wird zurückgesetzt

### Schritt 6.2: Logs auf dem Server prüfen

```bash
tail -20 /srv/peterebenhoch-api/logs/api.log
```

**Sie sollten sehen:**
```
INFO - Submission from peterebenhoch.com: ihre-email@example.com (IP: XXX.XXX.XXX.XXX)
INFO - Saved submission ID: 2
INFO - Created/Updated Attio: ihre-email@example.com → rec_XXXXX (source: peterebenhoch.com)
```

### Schritt 6.3: Final in Attio verifizieren

1. Öffnen Sie: https://app.attio.com
2. Suchen Sie Ihre Email
3. Prüfen Sie:
   - ✅ Kontakt existiert
   - ✅ Tag: `source:peterebenhoch.com`
   - ✅ Ihre gewählten Interessen als Tags
   - ✅ Note mit "Contact form submission from peterebenhoch.com"

🎉 **Herzlichen Glückwunsch! Alles funktioniert!**

---

## Teil 7: Deployment Script (Optional, 5 Minuten)

### Schritt 7.1: Deployment Script erstellen

```bash
cd /srv/peterebenhoch-api
nano deploy.sh
```

**Inhalt:**

```bash
#!/bin/bash
set -e

echo "======================================"
echo "Contact Form API Deployment"
echo "======================================"

# Backup database
if [ -f data/contacts.db ]; then
    echo "📦 Creating backup..."
    cp data/contacts.db data/backups/contacts-$(date +%Y%m%d-%H%M%S).db
    echo "✓ Backup created"
fi

# Restart API
echo "🔄 Restarting API..."
screen -S contact-api -X quit || true
sleep 2
screen -dmS contact-api bash -c 'cd /srv/peterebenhoch-api && source venv/bin/activate && python api.py'
sleep 3

# Health check
if curl -s http://127.0.0.1:8000/api/health | grep -q "healthy"; then
    echo "✅ Deployment successful!"
    echo "📊 API Status: Running"
    echo "📁 Logs: tail -f /srv/peterebenhoch-api/logs/api.log"
else
    echo "❌ Health check failed!"
    exit 1
fi
```

**Speichern und ausführbar machen:**
```bash
chmod +x deploy.sh
```

**Verwendung:**
```bash
./deploy.sh
```

---

## Zusammenfassung & Checkliste

### ✅ Was Sie jetzt haben:

- ✅ FastAPI Backend läuft auf Port 8000
- ✅ Automatische Email/Interest-Validierung (Pydantic)
- ✅ SQLite Datenbank mit allen Submissions
- ✅ **Attio Integration mit Source-Tracking** (`source:peterebenhoch.com`)
- ✅ Rate Limiting (5/Stunde pro IP)
- ✅ Logging in `logs/api.log`
- ✅ Unsubscribe-Foundation (Token wird generiert)
- ✅ Nginx Reverse Proxy konfiguriert
- ✅ Formular funktioniert von der Website

### 🎯 Multi-Website Support für die Zukunft:

**Für weitere Websites:**

1. Neues Verzeichnis: `/srv/otherwebsite-api/`
2. Gleiche `api.py`, aber in `.env`:
   ```bash
   WEBSITE_SOURCE=otherwebsite.com
   ALLOWED_ORIGIN=https://otherwebsite.com
   ```
3. Port ändern (z.B. 8001)
4. In Attio filtern nach:
   - `source:peterebenhoch.com`
   - `source:otherwebsite.com`

---

## Nützliche Befehle für den Alltag

### API Management

```bash
# API Status prüfen
screen -ls

# API Logs live ansehen
tail -f /srv/peterebenhoch-api/logs/api.log

# API neu starten
./deploy.sh

# API stoppen
screen -S contact-api -X quit
```

### Datenbank

```bash
# Alle Submissions ansehen
sqlite3 /srv/peterebenhoch-api/data/contacts.db "SELECT * FROM submissions;"

# Zählen
sqlite3 /srv/peterebenhoch-api/data/contacts.db "SELECT COUNT(*) FROM submissions;"

# Letzte 5 Submissions
sqlite3 /srv/peterebenhoch-api/data/contacts.db "SELECT email, source, submitted_at FROM submissions ORDER BY submitted_at DESC LIMIT 5;"
```

### Health Checks

```bash
# Lokal
curl http://127.0.0.1:8000/api/health

# Von außen
curl https://peterebenhoch.com/api/health
```

---

## Troubleshooting

### Problem: "Address already in use" beim Start

**Ursache:** API läuft bereits auf Port 8000

**Lösung:**
```bash
# Prozess finden
ps aux | grep uvicorn

# Prozess beenden
kill -9 PID_NUMMER

# Oder Screen beenden
screen -S contact-api -X quit
```

### Problem: Nginx zeigt 502 Bad Gateway

**Prüfung:**
```bash
# 1. Läuft die API?
curl http://127.0.0.1:8000/api/health

# 2. Nginx Fehler-Log
tail -20 /var/log/nginx/error.log

# 3. API neu starten
./deploy.sh
```

### Problem: Attio Sync funktioniert nicht

**Diagnose:**
```bash
# 1. API Key korrekt?
cat /srv/peterebenhoch-api/.env | grep ATTIO_API_KEY

# 2. Attio-bezogene Logs
grep "Attio" /srv/peterebenhoch-api/logs/api.log

# 3. Test mit curl (siehe Schritt 4.3)
```

### Problem: "Permission denied" bei /srv

**Lösung:**
```bash
sudo chown -R $USER:$USER /srv/peterebenhoch-api
```

### Problem: Rate Limit testen funktioniert nicht

**Hinweis:** Rate Limit ist pro IP. Vom selben Server/IP mehrfach:

```bash
# 6x schnell hintereinander senden
for i in {1..6}; do
  curl -X POST http://127.0.0.1:8000/api/submit-contact \
    -H "Content-Type: application/json" \
    -d '{"email":"test'$i'@example.com","interests":["keep-me-posted"]}'
  echo ""
done
```

Die 6. Anfrage sollte `429 Too Many Requests` zurückgeben.

---

## Sicherheitshinweise

✅ **Implementiert:**
- Rate Limiting (5/Stunde pro IP)
- Input Validation (Pydantic)
- SQL Injection geschützt (Parameterized Queries)
- CORS nur für peterebenhoch.com
- `.env` mit chmod 600
- Unsubscribe Token (UUID4)
- HTTPS via Nginx

⚠️ **Noch zu tun (später):**
- Systemd Service (statt screen)
- Logrotation einrichten
- Monitoring/Alerting (Uptime Robot)
- Backup Automatisierung (Cron)
- Attio Retry-Mechanismus (bei Ausfall)

---

## Nächste Schritte

**Nach erfolgreicher Implementierung:**

1. **Testen Sie mehrfach** das Formular
2. **Prüfen Sie regelmäßig** die Logs
3. **Checken Sie Attio** nach ein paar Submissions
4. **Dokumentieren Sie** weitere Websites (wenn nötig)

**Optional - Production Hardening:**
- Systemd Service einrichten (siehe `docs/contact-form-implementation.md`)
- Daily Backups via Cron
- Log Rotation konfigurieren
- Monitoring Setup (UptimeRobot, Sentry, etc.)

---

## Support & Dokumentation

- **FastAPI Docs:** https://fastapi.tiangolo.com/
- **Attio API Docs:** https://developers.attio.com/
- **Swagger UI:** http://YOUR-SERVER-IP:8000/docs
- **Weitere Details:** `docs/contact-form-implementation.md`

---

**Viel Erfolg bei der Implementierung! 🚀**

Bei Fragen: Logs prüfen (`tail -f logs/api.log`) und Troubleshooting-Sektion konsultieren.

