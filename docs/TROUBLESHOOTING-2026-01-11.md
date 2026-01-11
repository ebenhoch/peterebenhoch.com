# Kontaktformular Troubleshooting - 11. Januar 2026

## Zusammenfassung

Das Kontaktformular wurde heute fast vollständig implementiert. **Das Hauptproblem ist jetzt nur noch der SMTP-E-Mail-Versand**, nicht mehr die Server-Infrastruktur.

---

## ✅ Was funktioniert

1. **Frontend** - Formular in `contact.qmd` ist fertig und gerendert
2. **PHP-FPM** - Läuft und führt das Script aus
3. **Caddy** - Kommuniziert erfolgreich mit PHP-FPM
4. **PHP-Script** - Wird ausgeführt und gibt korrekte Fehlerme ldungen zurück
5. **Dependencies** - PHPMailer und dotenv sind installiert
6. **Validierung** - Formular-Validierung funktioniert

## ❌ Was NICHT funktioniert

**SMTP-E-Mail-Versand** - Das PHP-Script kann keine E-Mails über Mailbox.org versenden.

**Fehler:**
```
HTTP 500 - "Failed to send your message. Please try again later or contact us directly at pe@peterebenhoch.com"
```

---

## 🔍 Root Cause Analysis

### Hauptproblem: SMTP-Authentifizierung schlägt fehl

**Mögliche Ursachen (in Reihenfolge der Wahrscheinlichkeit):**

1. **Falsche SMTP-Credentials in `.env`**
   - Falscher Username oder Passwort
   - Bei Mailbox.org: Evtl. muss ein App-spezifisches Passwort erstellt werden
   - `SMTP_FROM_EMAIL` muss vom Mailbox.org Account autorisiert sein

2. **Port-Blockierung**
   - Port 465 (SSL/TLS) scheint blockiert zu sein (Timeout beim Testen)
   - Port 587 (STARTTLS) funktioniert, aber Authentifizierung schlägt fehl

3. **TLS/SSL-Zertifikat-Probleme**
   - Server hat veraltete CA-Zertifikate
   - TLS-Version wird von Mailbox.org nicht akzeptiert

4. **Mailbox.org Security Settings**
   - SMTP-Zugang muss in Mailbox.org Account aktiviert sein
   - Evtl. IP-basierte Einschränkungen

---

## 🛠️ Durchgeführte Schritte (Chronologie)

### Phase 1: Frontend & Backend Setup ✅
- Quarto-Formular erstellt und gerendert
- PHP-Script mit PHPMailer erstellt
- `composer.json` und Dependencies

### Phase 2: Server-Konfiguration (Caddy) 🟡
**Problem:** Anfangs viele Caddy-Konfigurations-Fehler

**Gelöste Probleme:**
1. ❌ **HTTP 502** - PHP-FPM Socket-Syntax falsch (`unix:` statt `unix//`)
2. ❌ **HTTP 502** - Caddy User nicht in `www-data` Gruppe
3. ❌ **HTTP 405** - POST-Methode nicht erlaubt (Caddy-Config-Problem)

**Finale Caddy-Config (funktioniert!):**
```caddy
www.peterebenhoch.com, peterebenhoch.com {
    root * /var/www/peterebenhoch.com
    
    @php {
        path /api/*.php
    }
    
    handle @php {
        php_fastcgi unix//var/run/php/php8.1-fpm.sock
    }
    
    respond /api/.env 404
    file_server
}
```

### Phase 3: PHP & SMTP ❌
**Aktueller Status:** PHP läuft, aber SMTP-Versand schlägt fehl

**Was getestet wurde:**
- Port 465 (SSL/TLS) → Timeout (wahrscheinlich blockiert)
- Port 587 (STARTTLS) → HTTP 500 (Authentifizierung schlägt fehl)
- PHP-Script wurde angepasst für automatische Port-Erkennung
- `.env` Datei wurde konfiguriert

**Fehler-Logs:** Leider keine Details im PHP-FPM Log sichtbar

---

## 📋 Nächste Schritte (Morgen)

### Priorität 1: SMTP-Credentials verifizieren

1. **Mailbox.org Account checken:**
   - Einloggen auf https://mailbox.org
   - Prüfen ob SMTP-Zugang aktiviert ist
   - Evtl. App-spezifisches Passwort erstellen (Einstellungen → Sicherheit)
   - Prüfen ob IP-Whitelist existiert

2. **`.env` Datei auf Server nochmal prüfen:**
   ```bash
   cat /var/www/peterebenhoch.com/api/.env
   ```
   Validieren:
   - `SMTP_USERNAME` = vollständige E-Mail-Adresse
   - `SMTP_PASSWORD` = korrektes Passwort (oder App-Passwort)
   - `SMTP_FROM_EMAIL` = E-Mail die vom Mailbox.org Account gesendet werden darf

3. **Debug-Logging aktivieren:**
   ```bash
   sudo nano /var/www/peterebenhoch.com/api/submit-contact.php
   ```
   
   Im `catch` Block (Zeile ~159) hinzufügen:
   ```php
   } catch (Exception $e) {
       error_log("Contact form error: " . $mail->ErrorInfo);
       error_log("Full exception: " . $e->getMessage());
       
       // Write to separate debug file
       file_put_contents('/tmp/phpmailer-debug.log', 
           date('Y-m-d H:i:s') . "\n" .
           "Error: " . $mail->ErrorInfo . "\n" .
           "Exception: " . $e->getMessage() . "\n" .
           "Trace: " . $e->getTraceAsString() . "\n\n", 
           FILE_APPEND
       );
   ```
   
   Dann testen und Log lesen:
   ```bash
   cat /tmp/phpmailer-debug.log
   ```

### Priorität 2: Alternative SMTP-Provider testen

Falls Mailbox.org nicht funktioniert, **Brevo SMTP** testen (einfacher & zuverlässiger):

1. **Brevo Account:**
   - Kostenlos: https://app.brevo.com
   - SMTP & API → SMTP Key erstellen
   
2. **Credentials in `.env`:**
   ```
   SMTP_HOST=smtp-relay.brevo.com
   SMTP_PORT=587
   SMTP_USERNAME=deine-brevo-email@example.com
   SMTP_PASSWORD=dein-brevo-smtp-key
   ```

3. **Vorteil:** Brevo ist spezialisiert auf transaktionale E-Mails und hat bessere Error-Messages

### Priorität 3: TLS/SSL-Zertifikate aktualisieren

Falls Zertifikat-Problem:
```bash
sudo apt update
sudo apt install ca-certificates
sudo update-ca-certificates
```

### Priorität 4: Manueller SMTP-Test

Test ob SMTP überhaupt vom Server erreichbar ist:
```bash
telnet smtp.mailbox.org 587
# Erwartete Ausgabe: "220 smtp.mailbox.org..."

# Dann testen:
EHLO localhost
# Sollte Liste von Features zeigen, inkl. STARTTLS
```

---

## 🤔 Caddy vs. Nginx/Apache

### Meine Empfehlung: **CADDY BEHALTEN** ✅

**Begründung:**
- Caddy funktioniert jetzt einwandfrei!
- Das SMTP-Problem hat **NICHTS mit Caddy zu tun**
- Caddy ist moderner und einfacher als Nginx/Apache
- Automatisches HTTPS ist super
- Ein Wechsel zu Nginx/Apache würde das SMTP-Problem NICHT lösen

**Caddy ist NICHT das Problem!** Das Problem liegt bei:
1. SMTP-Authentifizierung (Credentials)
2. Oder Mailbox.org Server-Konfiguration
3. Oder Firewall/Port-Blockierung

### Nur wechseln wenn:
- Du Nginx/Apache schon kennst und dich wohler fühlst
- Du spezielle Nginx/Apache Features brauchst

**Aufwand Wechsel:** 2-3 Stunden (nicht empfohlen jetzt)

---

## 📝 Quick-Fix Checklist für Morgen

### Schritt 1: Mailbox.org Credentials (5 Min)
- [ ] Mailbox.org Login
- [ ] SMTP-Zugang aktiviert?
- [ ] App-Passwort erstellen
- [ ] In `.env` eintragen

### Schritt 2: Debug-Logging (5 Min)
- [ ] PHP-Script erweitern (siehe oben)
- [ ] Formular testen
- [ ] `/tmp/phpmailer-debug.log` lesen

### Schritt 3: Alternative testen (15 Min)
- [ ] Brevo Account erstellen
- [ ] SMTP Key generieren
- [ ] In `.env` eintragen
- [ ] Testen

### Schritt 4: Wenn alles funktioniert (5 Min)
- [ ] Debug-Logging wieder entfernen
- [ ] Production `.env` finalisieren
- [ ] Formular live testen
- [ ] Erste Test-E-Mail empfangen! 🎉

---

## 🎯 Erwartete Lösung

**Wahrscheinlichkeit >90%:** 

Ein falsches oder fehlendes Detail in den Mailbox.org SMTP-Credentials. Sobald die korrekten Credentials (evtl. mit App-Passwort) in der `.env` stehen, wird es sofort funktionieren.

**Zeitaufwand morgen:** 15-30 Minuten

---

## 📞 Fallback-Option

Falls Mailbox.org weiterhin Probleme macht:

**Brevo SMTP** ist die schnellste Alternative:
- 5 Minuten Setup
- 300 E-Mails/Tag kostenlos
- Bessere Error-Messages
- Spezialisiert auf transaktionale E-Mails

---

## 🔧 Aktuelle Server-Konfiguration

### Dateien auf Server (alle korrekt!)

```
/var/www/peterebenhoch.com/
├── api/
│   ├── submit-contact.php ✅ (mit Port 465/587 Auto-Detection)
│   ├── composer.json ✅
│   ├── .env ✅ (Credentials prüfen!)
│   ├── .env.example ✅
│   └── vendor/ ✅ (PHPMailer, dotenv installiert)
├── contact.html ✅ (gerendert mit Formular)
└── ... (weitere Seiten)
```

### Services (alle laufen!)

- ✅ **Caddy** - Webserver läuft
- ✅ **PHP-FPM 8.1** - Läuft, User `caddy` in Gruppe `www-data`
- ✅ **PHP-Extensions** - Alle nötigen installiert

### Berechtigungen (alle korrekt!)

```bash
/var/www/peterebenhoch.com/api/
- Owner: www-data:www-data
- PHP-Files: 644 (lesbar)
- .env: 600 (nur Owner kann lesen)
- vendor/: 755 (lesbar & ausführbar)
```

---

## 💡 Lessons Learned

1. **Caddy PHP-FPM Syntax:** `unix//path/to/socket` (mit Doppel-Slash!)
2. **Permissions:** Caddy User muss in `www-data` Gruppe sein
3. **POST erlauben:** `handle @php` Block nutzen, nicht nur `php_fastcgi` Direktive
4. **Debug:** PHP-Logs sind nicht immer vollständig - eigene Log-Datei schreiben!

---

## ✅ Erfolge heute

Trotz aller Probleme:
- ✨ Kontaktformular Frontend komplett fertig
- ✨ PHP-Backend läuft und validiert korrekt
- ✨ Caddy + PHP-FPM kommunizieren perfekt
- ✨ Alle Dependencies installiert
- ✨ Security (Rate Limiting, .env Protection) implementiert

**Nur noch 1 Problem:** SMTP-Authentifizierung! 🎯

---

*Erstellt: 11. Januar 2026, 02:00 Uhr*
*Status: Ready für morgen!*
