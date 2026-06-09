# IT-Infrastruktur & Serverwartung
**Server:** Hetzner VPS (peterebenhoch.com / 78.46.199.38)  
**Erstellt:** 2026-01-11  
**Status:** Wartungsaufgaben geplant

---

## Übersicht

Dieses Dokument enthält geplante Wartungs- und Upgrade-Aufgaben für die IT-Infrastruktur von peterebenhoch.com.

---

## 1. Ubuntu Server Upgrade

### Aktueller Status
- **Installiert:** Ubuntu (Version zu prüfen)
- **Ziel:** Ubuntu 24.04 LTS "Noble Numbat"
- **Grund:** Hetzner bietet das Ubuntu-Upgrade an

### Details zu Ubuntu 24.04 LTS
- **Release-Datum:** 25. April 2024
- **Standard Support bis:** Mai 2029
- **Extended Security Maintenance bis:** April 2034
- **Vorteile:**
  - Aktuelle Sicherheitsupdates
  - Langfristiger Support (5 Jahre Standard)
  - Modernere Kernel-Version
  - Verbesserte Performance

### Nächste Schritte
1. Aktuellen Server-Status prüfen: `lsb_release -a`
2. Backup aller wichtigen Daten erstellen
3. Upgrade-Prozess testen (optional: Snapshot erstellen)
4. Ubuntu Upgrade durchführen
5. Alle Services verifizieren (Caddy, PHP-FPM, PostgreSQL)

### Risiken
- ⚠️ Downtime während des Upgrades
- ⚠️ Potenzielle Inkompatibilitäten mit Caddy/PHP
- ⚠️ Backup ist ESSENTIELL

### Zeitaufwand
- Geschätzt: 2-3 Stunden (inkl. Backup & Testing)

---

## 2. PHP Upgrade

### Aktueller Status
- **Installiert:** PHP 8.1
- **Status:** ⚠️ **EOL (End of Life) seit 31. Dezember 2025**
- **Ziel:** PHP 8.5 (latest stable)

### Details zu PHP 8.5
- **Release-Datum:** 20. November 2025
- **Active Support bis:** 31. Dezember 2027
- **Security Support bis:** 31. Dezember 2029
- **Neue Features:**
  - Pipe operator syntax
  - Neue URI extension
  - Performance-Verbesserungen
  - Verbesserte Security Features

### Warum dringend?
**PHP 8.1 ist EOL!** Keine Sicherheitsupdates mehr ab 01.01.2026.

### Betroffene Komponenten
- `/api/submit-contact.php` (Kontaktformular-Backend)
- PHPMailer (kompatibel mit PHP 8.5)
- Caddy PHP-FPM Integration

### Nächste Schritte
1. PHP 8.5 auf Server installieren: `sudo apt install php8.5-fpm`
2. PHP 8.5 Module installieren:
   - `php8.5-cli`
   - `php8.5-curl`
   - `php8.5-mbstring`
   - `php8.5-xml`
   - `php8.5-pgsql` (für PostgreSQL-Integration)
3. Caddy Caddyfile aktualisieren: Socket-Pfad zu `php8.5-fpm.sock`
4. Composer Dependencies neu installieren
5. API testen: `curl https://peterebenhoch.com/api/submit-contact.php`
6. PHP 8.1 deinstallieren (nach erfolgreicher Migration)

### Kompatibilitätsprüfung
- ✅ PHPMailer 6.9: Kompatibel mit PHP 8.5
- ✅ Dotenv 5.6: Kompatibel mit PHP 8.5
- ✅ Unser submit-contact.php: Keine Breaking Changes erwartet

### Zeitaufwand
- Geschätzt: 1-2 Stunden

---

## 3. PostgreSQL Upgrade

### Aktueller Status
- **Installiert:** PostgreSQL (Version zu prüfen)
- **Entdeckt:** PostgreSQL läuft auf Hetzner Server
- **Ziel:** PostgreSQL 18.1 (latest stable)

### Details zu PostgreSQL 18.1
- **Release-Datum:** 13. November 2025
- **Nächste Major Version:** PostgreSQL 19 (geplant September 2026)
- **Verbesserungen:**
  - Performance-Optimierungen
  - Neue Security-Features
  - Verbesserte Replikation
  - Bessere Developer-Tools

### Nächste Schritte
1. Aktuelle PostgreSQL-Version prüfen: `psql --version`
2. **WICHTIG:** Backup aller Datenbanken erstellen: `pg_dumpall > backup.sql`
3. PostgreSQL 18.1 Repository hinzufügen
4. Upgrade durchführen
5. Datenbanken verifizieren
6. Performance-Tests durchführen

### Risiken
- ⚠️ Breaking Changes bei Major-Version-Upgrades
- ⚠️ Potenzielle Downtime
- ⚠️ **Backup ist KRITISCH**

### Zeitaufwand
- Geschätzt: 2-3 Stunden (inkl. Backup & Testing)

---

## 4. Kontaktformular: PostgreSQL-Integration

### Ziel
Kontaktformular-Einträge **zusätzlich** in PostgreSQL speichern (neben E-Mail-Versand).

### Vorteile
- 📊 **Datenanalyse:** Tracking von Anfragen über Zeit
- 🔍 **Suchfunktion:** Durchsuchen alter Anfragen
- 📈 **Statistiken:** Welche Interessen werden am häufigsten ausgewählt?
- 💾 **Backup:** E-Mails können verloren gehen, Datenbank ist persistent
- 🔄 **Automatisierung:** Automatische Follow-ups, CRM-Integration möglich

### Architektur

**Tabellen-Schema:**
```sql
CREATE TABLE contact_submissions (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    interests TEXT[] NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address INET,
    user_agent TEXT,
    email_sent BOOLEAN DEFAULT FALSE,
    email_sent_at TIMESTAMP
);

CREATE INDEX idx_submitted_at ON contact_submissions(submitted_at);
CREATE INDEX idx_email ON contact_submissions(email);
```

**PHP-Integration (submit-contact.php):**
```php
<?php
// Nach erfolgreichem E-Mail-Versand:

try {
    // PostgreSQL-Verbindung
    $dbconn = pg_connect("host=localhost dbname=peterebenhoch_contacts user=www-data password=" . $_ENV['POSTGRES_PASSWORD']);
    
    // Prepared Statement (SQL Injection Protection)
    $query = "INSERT INTO contact_submissions (email, interests, ip_address, user_agent, email_sent, email_sent_at) 
              VALUES ($1, $2, $3, $4, $5, NOW())";
    
    $result = pg_query_params($dbconn, $query, [
        $email,
        '{' . implode(',', $interests) . '}', // PostgreSQL array format
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        true
    ]);
    
    if (!$result) {
        error_log("Database insert failed: " . pg_last_error($dbconn));
    }
    
    pg_close($dbconn);
} catch (Exception $e) {
    // Log but don't fail - email is already sent
    error_log("Database error: " . $e->getMessage());
}
```

### Nächste Schritte
1. PostgreSQL-Datenbank erstellen: `createdb peterebenhoch_contacts`
2. Datenbank-User für PHP erstellen (oder bestehenden `www-data` verwenden)
3. Tabellen-Schema anlegen (siehe oben)
4. PHP `pgsql` Extension installieren: `php8.5-pgsql`
5. `.env` erweitern:
   ```
   POSTGRES_HOST=localhost
   POSTGRES_DB=peterebenhoch_contacts
   POSTGRES_USER=www-data
   POSTGRES_PASSWORD=secure_password_hier
   ```
6. `submit-contact.php` erweitern (Database-Insert nach E-Mail-Versand)
7. Testen mit Testformular-Submission
8. **Optional:** Admin-Dashboard erstellen für Datenbankansicht

### Datenschutz (DSGVO)
- ⚠️ **Rechtsgrundlage:** Einwilligung oder berechtigtes Interesse klären
- 🔒 **Speicherdauer:** Festlegen (z.B. 2 Jahre)
- 🗑️ **Löschkonzept:** Automatische Löschung alter Einträge implementieren
- 📄 **Datenschutzerklärung:** Datenbankspeicherung dokumentieren
- 👤 **Auskunftsrecht:** Möglichkeit für User, ihre Daten abzufragen

### Zeitaufwand
- Geschätzt: 2-3 Stunden (inkl. Testing & Datenschutz-Dokumentation)

---

## Prioritäten

### 🔴 HOCH (Zeitnah)
1. **PHP Upgrade auf 8.5** (PHP 8.1 ist EOL seit 01.01.2026)

### 🟡 MITTEL (Nächste 3 Monate)
2. **Ubuntu Server Upgrade auf 24.04 LTS**
3. **PostgreSQL Upgrade auf 18.1**

### 🟢 NIEDRIG (Erweiterung)
4. **Kontaktformular PostgreSQL-Integration**

### 🔵 COMPLIANCE (DSGVO-Pflicht)
5. **Datenschutz-Hinweise im Kontaktformular anpassen**
6. **Unsubscribe-Option implementieren**

---

## 5. Compliance: Datenschutz & DSGVO

### Übersicht
Das Kontaktformular verarbeitet personenbezogene Daten (E-Mail-Adressen) und muss daher DSGVO-konform gestaltet sein.

### 5.1 Datenschutz-Hinweise im Kontaktformular

#### Aktuelle Situation
- ⚠️ **Fehlend:** Explizite Datenschutz-Hinweise direkt am Formular
- ⚠️ **Fehlend:** Transparenz über Datenverarbeitung

#### Was muss ergänzt werden?

**Pflichtinformationen nach Art. 13 DSGVO:**

1. **Verantwortlicher:** Name und Kontaktdaten
2. **Zweck der Verarbeitung:** Kontaktaufnahme, Newsletter (falls "Keep me posted")
3. **Rechtsgrundlage:** 
   - Art. 6 Abs. 1 lit. b DSGVO (Vertragsanbahnung)
   - Art. 6 Abs. 1 lit. a DSGVO (Einwilligung für Newsletter)
4. **Speicherdauer:** z.B. "2 Jahre nach letztem Kontakt"
5. **Empfänger:** Brevo (SMTP-Provider), eigene Server
6. **Rechte der betroffenen Person:**
   - Auskunft (Art. 15 DSGVO)
   - Berichtigung (Art. 16 DSGVO)
   - Löschung (Art. 17 DSGVO)
   - Widerruf (Art. 7 Abs. 3 DSGVO)
   - Beschwerde bei Aufsichtsbehörde

#### Implementierung im Formular

**Option A: Direkt im Formular (Empfohlen)**

```html
<!-- In contact.qmd vor dem Submit-Button -->
<div class="mb-3">
  <small class="text-muted">
    <strong>Datenschutzhinweis:</strong> Ihre E-Mail-Adresse wird ausschließlich zur Beantwortung Ihrer Anfrage verwendet. 
    Bei Auswahl von "Keep me posted" erhalten Sie gelegentlich Updates zu meinen Projekten. 
    Ihre Daten werden für maximal 2 Jahre gespeichert. 
    Sie können der Verarbeitung jederzeit widersprechen und Ihre Daten löschen lassen. 
    Weitere Informationen finden Sie in unserer 
    <a href="/privacy.html" target="_blank">Datenschutzerklärung</a>.
  </small>
</div>

<div class="form-check mb-3">
  <input class="form-check-input" type="checkbox" id="privacy-consent" required>
  <label class="form-check-label" for="privacy-consent">
    Ich habe die <a href="/privacy.html" target="_blank">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zu. *
  </label>
</div>
```

**Option B: Link zur Datenschutzerklärung**

```html
<p class="small text-muted">
  Mit dem Absenden des Formulars stimmen Sie der Verarbeitung Ihrer Daten gemäß unserer 
  <a href="/privacy.html" target="_blank">Datenschutzerklärung</a> zu.
</p>
```

#### Datenschutzerklärung erstellen/aktualisieren

**Datei:** `privacy.qmd` (zu erstellen)

**Inhalt sollte enthalten:**
- Verantwortlicher (Peter Ebenhoch + Kontaktdaten)
- Kontaktformular-Datenverarbeitung im Detail
- Brevo als Auftragsverarbeiter
- Hosting bei Hetzner (Serverstandort Deutschland)
- Speicherdauer
- Löschkonzept
- Rechte der Betroffenen
- Beschwerderecht bei Datenschutzbehörde

#### Zeitaufwand
- **Formular anpassen:** 30 Minuten
- **Datenschutzerklärung schreiben/aktualisieren:** 2-3 Stunden
- **Rechtliche Prüfung (empfohlen):** Optional, durch Anwalt

---

### 5.2 Unsubscribe-Option implementieren

#### Rechtliche Anforderung
**DSGVO Art. 7 Abs. 3:** Widerruf der Einwilligung muss jederzeit möglich sein und genauso einfach wie die Einwilligung selbst.

#### Anwendungsfälle

1. **"Keep me posted" Newsletter**
   - User hat Checkbox aktiviert
   - Möchte später keine Updates mehr erhalten
   - Benötigt einfache Abmelde-Möglichkeit

2. **"Specific project interest"**
   - User hat spezifisches Interesse angegeben
   - Möchte Updates zu diesem Projekt abbestellen

#### Implementierung

##### Variante A: E-Mail-basierter Unsubscribe (Einfach)

**1. Unsubscribe-Link in jeder E-Mail**

```php
// In submit-contact.php, nach erfolgreichem Versand:

// Eindeutigen Unsubscribe-Token generieren
$unsubscribe_token = bin2hex(random_bytes(32));

// Token in Datenbank speichern (wenn PostgreSQL-Integration läuft)
// INSERT INTO contact_submissions (..., unsubscribe_token) VALUES (..., $unsubscribe_token)

// Auto-Reply E-Mail an User senden
$autoReplyBody = "
Vielen Dank für Ihre Nachricht!

Sie haben sich für folgende Updates angemeldet: " . implode(', ', $interests) . "

Falls Sie diese Updates nicht mehr erhalten möchten, können Sie sich jederzeit abmelden:
https://peterebenhoch.com/unsubscribe.php?token={$unsubscribe_token}

Mit freundlichen Grüßen,
Peter Ebenhoch
";
```

**2. Unsubscribe-Seite erstellen**

**Datei:** `/var/www/peterebenhoch.com/unsubscribe.php`

```php
<?php
require __DIR__ . '/api/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/api');
$dotenv->load();

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['token']) || empty($_GET['token'])) {
    http_response_code(400);
    echo "<h1>Ungültiger Link</h1>";
    exit;
}

$token = $_GET['token'];

// PostgreSQL-Verbindung
try {
    $dbconn = pg_connect("host=localhost dbname=peterebenhoch_contacts user=www-data password=" . $_ENV['POSTGRES_PASSWORD']);
    
    // Token suchen
    $query = "SELECT email FROM contact_submissions WHERE unsubscribe_token = $1 AND unsubscribed_at IS NULL";
    $result = pg_query_params($dbconn, $query, [$token]);
    
    if (pg_num_rows($result) === 0) {
        echo "<h1>Link ist ungültig oder bereits verwendet</h1>";
        exit;
    }
    
    $row = pg_fetch_assoc($result);
    $email = $row['email'];
    
    // Bei POST: Abmeldung durchführen
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $update_query = "UPDATE contact_submissions SET unsubscribed_at = NOW() WHERE unsubscribe_token = $1";
        pg_query_params($dbconn, $update_query, [$token]);
        
        echo "<h1>Erfolgreich abgemeldet</h1>";
        echo "<p>Sie wurden erfolgreich von allen Updates abgemeldet.</p>";
        echo "<p>E-Mail: " . htmlspecialchars($email) . "</p>";
        exit;
    }
    
    // Bestätigungsformular anzeigen
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Abmeldung bestätigen</title>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            button { background: #dc3545; color: white; border: none; padding: 10px 20px; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>Abmeldung bestätigen</h1>
        <p>Möchten Sie sich wirklich von allen Updates abmelden?</p>
        <p>E-Mail: <strong><?= htmlspecialchars($email) ?></strong></p>
        
        <form method="POST">
            <button type="submit">Ja, abmelden</button>
        </form>
        
        <p><small>Sie können diese Seite auch einfach schließen, um angemeldet zu bleiben.</small></p>
    </body>
    </html>
    <?php
    
    pg_close($dbconn);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Unsubscribe error: " . $e->getMessage());
    echo "<h1>Fehler beim Abmelden</h1>";
}
?>
```

**3. Abmeldungen berücksichtigen**

Beim Versenden von Newsletter/Updates prüfen:

```php
// Vor dem Versenden von Follow-up-E-Mails
$query = "SELECT email FROM contact_submissions 
          WHERE email = $1 
          AND unsubscribed_at IS NULL 
          AND 'keep-me-posted' = ANY(interests)";
```

##### Variante B: Admin-Dashboard mit manueller Abmeldung

**Einfachere Alternative ohne Token:**

1. User sendet E-Mail an `pe@peterebenhoch.com` mit Betreff "Abmeldung"
2. Admin loggt sich ins Dashboard ein
3. Sucht E-Mail-Adresse
4. Klickt "Unsubscribe" Button
5. E-Mail wird aus Newsletter-Liste entfernt

**Vorteile:**
- Keine zusätzliche Programmierung nötig
- Persönlicher
- DSGVO-konform (Widerruf ist möglich)

**Nachteile:**
- Nicht automatisiert
- Zeitaufwand für Admin

#### Benötigte Komponenten

1. ✅ PostgreSQL-Datenbank (für Token-Speicherung)
2. ⬜ `unsubscribe.php` Seite
3. ⬜ Auto-Reply-E-Mail mit Unsubscribe-Link
4. ⬜ Datenbank-Spalte `unsubscribe_token` und `unsubscribed_at`
5. ⬜ E-Mail-Versand-Logik, die Unsubscribes berücksichtigt

#### Zeitaufwand
- **Token-basierte Lösung:** 3-4 Stunden
- **Admin-Dashboard-Lösung:** 1 Stunde (+ Dashboard-Entwicklung)

---

### Compliance-Checkliste

#### Pflicht (DSGVO)
- [ ] Datenschutz-Hinweis direkt am Formular
- [ ] Link zur Datenschutzerklärung
- [ ] Datenschutzerklärung erstellen/aktualisieren mit Kontaktformular-Details
- [ ] Einwilligungs-Checkbox (falls Daten über Vertragsanbahnung hinausgehen)
- [ ] Unsubscribe-Option für "Keep me posted" implementieren
- [ ] Speicherdauer festlegen und dokumentieren
- [ ] Löschkonzept implementieren (z.B. Cronjob)

#### Empfohlen
- [ ] Privacy by Design: Minimal nötige Daten sammeln ✅ (nur E-Mail)
- [ ] Verschlüsselung: HTTPS ✅ (bereits implementiert)
- [ ] Auftragsverarbeitungsvertrag (AVV) mit Brevo prüfen
- [ ] Cookie-Banner (falls Tracking/Analytics hinzugefügt wird)
- [ ] Impressum aktualisieren/erstellen

#### Nice-to-Have
- [ ] Double-Opt-In für Newsletter (Best Practice)
- [ ] Privacy Dashboard für User (eigene Daten einsehen/löschen)
- [ ] Datenschutz-Siegel/Zertifizierung

---

## Reihenfolge der Durchführung

**Empfohlene Reihenfolge:**

1. **Datenschutz-Hinweise im Kontaktformular** (0.5h)
   - Grund: DSGVO-Pflicht, schnell umsetzbar
   - Minimales Risiko, hohe Priorität
   
2. **PHP 8.5 Upgrade** (1-2h)
   - Grund: Sicherheitslücken durch EOL-Version
   - Relativ isoliert, geringes Risiko
   
3. **Datenschutzerklärung erstellen/aktualisieren** (2-3h)
   - Grund: DSGVO-Pflicht, sollte zeitnah erfolgen
   - Kann parallel zu technischen Aufgaben erledigt werden
   
4. **Ubuntu 24.04 LTS Upgrade** (2-3h)
   - Grund: Bessere Paket-Unterstützung für PHP 8.5
   - Erfordert Server-Neustart
   
5. **PostgreSQL 18.1 Upgrade** (2-3h)
   - Grund: Vorbereitung für Datenbank-Integration
   - Unabhängig von Kontaktformular
   
6. **PostgreSQL-Integration ins Kontaktformular** (2-3h)
   - Grund: Feature-Erweiterung, benötigt für Unsubscribe
   - Baut auf aktuellem PostgreSQL auf
   
7. **Unsubscribe-Option implementieren** (3-4h)
   - Grund: DSGVO-Pflicht (Widerrufsrecht)
   - Benötigt PostgreSQL-Integration für Token-Verwaltung

**Gesamtaufwand:** 13-20 Stunden

---

## Backup-Strategie

**Vor jedem Upgrade:**

1. **System-Snapshot** (Hetzner Snapshot-Feature)
2. **Datenbank-Backup:**
   ```bash
   pg_dumpall > /backup/postgres_$(date +%Y%m%d).sql
   ```
3. **Website-Backup:**
   ```bash
   rsync -avz /var/www/peterebenhoch.com/ /backup/website_$(date +%Y%m%d)/
   ```
4. **Konfigurationsdateien:**
   ```bash
   cp /etc/caddy/Caddyfile /backup/Caddyfile_$(date +%Y%m%d)
   cp /etc/php/8.1/fpm/php-fpm.conf /backup/php-fpm_$(date +%Y%m%d).conf
   ```

---

## Testing-Checkliste (Nach jedem Upgrade)

- [ ] Website erreichbar: `curl https://peterebenhoch.com`
- [ ] Caddy läuft: `systemctl status caddy`
- [ ] PHP-FPM läuft: `systemctl status php8.5-fpm`
- [ ] PostgreSQL läuft: `systemctl status postgresql`
- [ ] Kontaktformular funktioniert:
  - [ ] Formular-Validierung
  - [ ] E-Mail-Versand (Test-Submission)
  - [ ] E-Mail kommt an bei `pe@peterebenhoch.com`
  - [ ] Rate-Limiting funktioniert
  - [ ] **Compliance-Tests:**
    - [ ] Datenschutz-Hinweis ist sichtbar am Formular
    - [ ] Link zur Datenschutzerklärung funktioniert
    - [ ] Einwilligungs-Checkbox vorhanden und required
    - [ ] Unsubscribe-Link in E-Mails enthalten (falls implementiert)
    - [ ] Unsubscribe-Seite funktioniert (falls implementiert)
- [ ] Alle Seiten laden korrekt
- [ ] HTTPS funktioniert (Let's Encrypt-Zertifikat)
- [ ] Keine Fehler in Logs:
  - [ ] `sudo journalctl -u caddy -n 50`
  - [ ] `sudo tail -50 /var/log/php8.5-fpm.log`
  - [ ] `sudo tail -50 /var/log/postgresql/postgresql-18-main.log`

---

## Kontakt & Verantwortlichkeiten

- **Server-Admin:** Peter Ebenhoch
- **Entwicklung:** Claude (Cursor AI Assistant)
- **Hetzner-Zugangsdaten:** Sicher verwahrt
- **SSH-Zugang:** `root@peterebenhoch.com`

---

**Dokumentation erstellt:** 2026-01-11  
**Letzte Aktualisierung:** 2026-01-11  
**Nächste Review:** Nach Durchführung von Aufgabe 1 (PHP Upgrade)
