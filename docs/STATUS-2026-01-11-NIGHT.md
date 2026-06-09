# Status Update - 11. Januar 2026, 02:30 Uhr

## Aktueller Stand

### ✅ Was funktioniert
- **Backend (PHP + Brevo SMTP):** Funktioniert perfekt! ✅
- **curl Test:** E-Mail-Versand funktioniert einwandfrei
- **Server-Infrastruktur:** Caddy + PHP-FPM läuft stabil

### ❌ Was NICHT funktioniert
- **Browser-Formular:** Gibt "Network error" beim Absenden
- **Symptom:** JavaScript fetch() kann API nicht erreichen

### 🔍 Mögliche Ursachen

1. **Wahrscheinlichste:** `contact.html` auf Server ist nicht die aktuelle Version
2. **Möglich:** Browser-Cache zeigt alte Version
3. **Möglich:** CORS-Problem (aber unwahrscheinlich, da Caddy-Config korrekt ist)

---

## 🚀 Morgen: 3 Schritte zum Erfolg (5 Minuten)

### Schritt 1: Neue contact.html hochladen (2 Min)

```bash
# Von deinem Mac Terminal:
scp "/Volumes/Mac mini External/Next-Storage-Hetzner/5 - Sourcecode/peterebenhoch.com/_site/contact.html" root@your-server:/var/www/peterebenhoch.com/
```

### Schritt 2: API-Endpunkt testen (1 Min)

Öffne im Browser: https://peterebenhoch.com/api/submit-contact.php

**Erwartete Ausgabe:**
```json
{"error":"Method not allowed"}
```

✅ Wenn das erscheint → API funktioniert!  
❌ Wenn 404 → PHP-Datei fehlt (nochmal hochladen)

### Schritt 3: Formular testen (2 Min)

1. Öffne: https://peterebenhoch.com/contact.html
2. **Hard-Refresh:** `CMD+SHIFT+R` (Mac) oder `CTRL+F5` (Windows)
3. **Browser Console öffnen:** `F12` → "Network" Tab
4. Formular ausfüllen und absenden
5. **Im Network Tab schauen:**
   - Erscheint ein Request zu `submit-contact.php`?
   - Was ist der Status? (200, 404, 500?)
   - Was ist die Response?

**Falls immer noch "Network error":**

Prüfe im Network Tab die **genaue Fehlermeldung**:
- `CORS error` → Caddy-Config anpassen
- `404 Not Found` → Pfad falsch
- `(failed) net::ERR_FAILED` → CORS oder Firewall

---

## 📋 Alternative: Rate Limit erhöhen (falls nötig)

Falls du viele Tests machen willst:

```bash
# Auf dem Server:
sudo nano /var/www/peterebenhoch.com/api/.env
```

Ändere:
```
MAX_REQUESTS_PER_HOUR=50  # Statt 5
```

Dann kannst du öfter testen ohne blockiert zu werden.

---

## 🎯 Erwartetes Ergebnis morgen

Nach Upload der neuen `contact.html`:
- ✅ Formular sendet Request
- ✅ PHP verarbeitet Request
- ✅ Brevo sendet E-Mail
- ✅ Du erhältst E-Mail bei pe@peterebenhoch.com
- 🎉 **FERTIG!**

**Zeitaufwand: 5 Minuten**

---

## 🔧 Backup-Plan (falls es nicht klappt)

### Debug: Browser Console Fehler analysieren

1. **F12 → Console Tab**
2. Formular absenden
3. **Rote Fehlermeldungen** screenshotten oder kopieren
4. Das zeigt genau was nicht funktioniert

### Häufige Probleme & Lösungen:

| Fehler | Ursache | Lösung |
|--------|---------|--------|
| `CORS policy` | CORS-Header fehlt | Caddy-Config erweitern |
| `404 Not Found` | Pfad falsch | URL im JavaScript prüfen |
| `net::ERR_FAILED` | Allgemeiner Netzwerkfehler | Browser-Cache löschen |
| `Too many requests` | Rate Limit | Sessions löschen oder Limit erhöhen |

---

## 📁 Wichtige Dateien

### Lokal (aktuell):
- ✅ `_site/contact.html` - Muss auf Server hochgeladen werden
- ✅ `api/submit-contact.php` - Ist aktuell auf Server
- ✅ `.env` auf Server - Brevo Credentials korrekt

### Auf Server (zu prüfen):
- ❓ `/var/www/peterebenhoch.com/contact.html` - Wahrscheinlich veraltet
- ✅ `/var/www/peterebenhoch.com/api/submit-contact.php` - Aktuell
- ✅ `/var/www/peterebenhoch.com/api/.env` - Aktuell

---

## 🎓 Was wir heute gelernt haben

1. **Backend funktioniert!** PHP + PHPMailer + Brevo SMTP läuft einwandfrei
2. **curl vs. Browser:** curl funktioniert, aber Browser hat Problem
3. **Wahrscheinlichste Ursache:** Alte HTML-Datei auf Server oder Browser-Cache
4. **Rate Limiting:** Funktioniert wie erwartet (5 Requests/Stunde/IP)

---

## 💡 Schnelltest (wenn du magst)

**Teste im Terminal vom Server aus:**

```bash
# Auf dem Server:
curl -X POST http://localhost/api/submit-contact.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","interests":["keep-me-posted"]}'
```

Wenn das **eine Erfolgsmeldung** zurückgibt → Backend ist 100% OK!  
Dann ist es nur noch ein Frontend-Upload-Problem.

---

*Status: 95% fertig - nur noch Frontend-File-Upload morgen!*  
*Geschätzte Zeit morgen: 5 Minuten* ⏱️

**Gute Nacht! 🌙**
