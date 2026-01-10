# Contact Form API

Backend für das Kontaktformular auf peterebenhoch.com

## Installation auf Hetzner Server

### 1. Dateien hochladen

```bash
# Verbindung zum Server
ssh user@deine-server-ip

# Verzeichnis erstellen (falls nicht vorhanden)
sudo mkdir -p /var/www/peterebenhoch.com/api
sudo chown www-data:www-data /var/www/peterebenhoch.com/api

# Von deinem lokalen Mac aus:
cd "/Volumes/Mac mini External/Next-Storage-Hetzner/5 - Sourcecode/peterebenhoch.com"
scp -r api/* user@deine-server-ip:/tmp/api-upload/

# Auf dem Server:
sudo mv /tmp/api-upload/* /var/www/peterebenhoch.com/api/
sudo chown -R www-data:www-data /var/www/peterebenhoch.com/api
```

### 2. Composer installieren (falls nicht vorhanden)

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"
```

### 3. Dependencies installieren

```bash
cd /var/www/peterebenhoch.com/api
composer install --no-dev --optimize-autoloader
```

### 4. Environment-Konfiguration

```bash
# .env Datei erstellen
cp .env.example .env
nano .env

# Fülle deine Mailbox.org Credentials ein:
# SMTP_USERNAME=deine-email@mailbox.org
# SMTP_PASSWORD=dein-passwort
```

### 5. Berechtigungen setzen

```bash
sudo chown -R www-data:www-data /var/www/peterebenhoch.com/api
sudo chmod 600 /var/www/peterebenhoch.com/api/.env
sudo chmod 644 /var/www/peterebenhoch.com/api/*.php
```

### 6. PHP-Syntax testen

```bash
php -l /var/www/peterebenhoch.com/api/submit-contact.php
```

Erwartete Ausgabe: `No syntax errors detected`

### 7. Test-Request senden

```bash
curl -X POST https://peterebenhoch.com/api/submit-contact.php \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "interests": ["keep-me-posted"]
  }'
```

## Mailbox.org SMTP Einstellungen

- **Host:** smtp.mailbox.org
- **Port:** 587 (STARTTLS)
- **Username:** Deine vollständige E-Mail-Adresse
- **Password:** Dein Mailbox.org Passwort
- **Verschlüsselung:** STARTTLS

## Troubleshooting

### Fehler: "Class 'PHPMailer' not found"
→ Composer dependencies nicht installiert: `composer install`

### Fehler: "Failed to send email"
→ Prüfe SMTP-Credentials in `.env`
→ Prüfe Logs: `sudo tail -f /var/log/nginx/error.log`

### Fehler: "Too many requests"
→ Rate Limiting aktiv (5 Anfragen/Stunde pro IP)
→ Sessions löschen oder MAX_REQUESTS_PER_HOUR erhöhen

## Sicherheit

- `.env` Datei ist in `.gitignore` und wird nicht ins Repository committed
- Rate Limiting verhindert Spam (max. 5 Anfragen/Stunde)
- Input-Validierung und Sanitization aktiv
- CORS auf peterebenhoch.com beschränkt
