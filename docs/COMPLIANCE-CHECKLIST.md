# Compliance-Checkliste: Kontaktformular DSGVO

**Website:** peterebenhoch.com  
**Erstellt:** 2026-01-11  
**Status:** ⚠️ In Bearbeitung

---

## Übersicht

Dieses Dokument listet alle notwendigen Compliance-Maßnahmen für das Kontaktformular auf, um DSGVO-konform zu sein.

---

## 📋 Checkliste

### 🔴 PFLICHT (DSGVO)

#### 1. Datenschutz-Hinweis am Formular
- [ ] **Status:** Nicht implementiert
- **Anforderung:** Art. 13 DSGVO - Informationspflicht
- **Was:** Kurzer Hinweis direkt am Formular mit:
  - Zweck der Datenverarbeitung
  - Speicherdauer
  - Link zur vollständigen Datenschutzerklärung
- **Zeitaufwand:** 30 Minuten
- **Priorität:** 🔴 HOCH

**Beispiel-Text:**
```
Ihre E-Mail-Adresse wird ausschließlich zur Beantwortung 
Ihrer Anfrage verwendet. Bei Auswahl von "Keep me posted" 
erhalten Sie gelegentlich Updates. Ihre Daten werden für 
maximal 2 Jahre gespeichert. Weitere Informationen in unserer 
Datenschutzerklärung.
```

---

#### 2. Einwilligungs-Checkbox
- [ ] **Status:** Nicht implementiert
- **Anforderung:** Art. 6 Abs. 1 lit. a DSGVO
- **Was:** Checkbox mit Bestätigung der Datenschutzerklärung
- **Wichtig:** Muss `required` sein, darf nicht vorangekreuzt sein
- **Zeitaufwand:** 15 Minuten
- **Priorität:** 🔴 HOCH

**Beispiel-Code:**
```html
<div class="form-check mb-3">
  <input class="form-check-input" type="checkbox" 
         id="privacy-consent" required>
  <label class="form-check-label" for="privacy-consent">
    Ich habe die <a href="/privacy.html">Datenschutzerklärung</a> 
    gelesen und stimme der Verarbeitung meiner Daten zu. *
  </label>
</div>
```

---

#### 3. Datenschutzerklärung erstellen/aktualisieren
- [ ] **Status:** Zu prüfen (existiert privacy.html?)
- **Anforderung:** Art. 13 DSGVO
- **Was:** Vollständige Datenschutzerklärung mit:
  - Verantwortlicher: Peter Ebenhoch + Kontaktdaten
  - Zweck der Verarbeitung (Kontaktaufnahme, Newsletter)
  - Rechtsgrundlage (Art. 6 Abs. 1 lit. a/b DSGVO)
  - Empfänger: Brevo (SMTP), Hetzner (Hosting)
  - Speicherdauer: z.B. 2 Jahre
  - Löschkonzept
  - Rechte der Betroffenen (Auskunft, Berichtigung, Löschung, Widerruf)
  - Beschwerderecht bei Datenschutzbehörde
- **Zeitaufwand:** 2-3 Stunden
- **Priorität:** 🔴 HOCH

**Generator:** https://datenschutz-generator.de/ (kostenlos für kleine Websites)

---

#### 4. Unsubscribe-Option implementieren
- [ ] **Status:** Nicht implementiert
- **Anforderung:** Art. 7 Abs. 3 DSGVO - Widerrufsrecht
- **Was:** Möglichkeit zum Widerruf der Einwilligung für "Keep me posted"
- **Optionen:**
  - **Option A:** Token-basierter Unsubscribe-Link in E-Mails (empfohlen)
  - **Option B:** Manuelle Abmeldung via E-Mail an Admin
- **Zeitaufwand:** 3-4 Stunden (Token-basiert) oder 1 Stunde (manuell)
- **Priorität:** 🟡 MITTEL (erst nach PostgreSQL-Integration)

**Anforderung:**
- Widerruf muss genauso einfach sein wie Einwilligung
- Keine Begründung erforderlich
- Bestätigung des Widerrufs an User

---

#### 5. Speicherdauer definieren & dokumentieren
- [ ] **Status:** Nicht definiert
- **Anforderung:** Art. 13 Abs. 2 lit. a DSGVO
- **Was:** Festlegen, wie lange E-Mail-Adressen gespeichert werden
- **Empfehlung:** 
  - Kontaktanfragen: 2 Jahre nach letztem Kontakt
  - Newsletter-Anmeldungen: Bis zum Widerruf oder 5 Jahre Inaktivität
- **Dokumentieren in:** Datenschutzerklärung
- **Zeitaufwand:** 30 Minuten (Entscheidung + Dokumentation)
- **Priorität:** 🔴 HOCH

---

#### 6. Löschkonzept implementieren
- [ ] **Status:** Nicht implementiert
- **Anforderung:** Art. 17 DSGVO - Recht auf Löschung
- **Was:** Automatische Löschung nach Ablauf der Speicherdauer
- **Implementierung:** Cronjob auf Server
- **Zeitaufwand:** 1-2 Stunden
- **Priorität:** 🟡 MITTEL (nach PostgreSQL-Integration)

**Beispiel Cronjob:**
```bash
# Täglich um 3 Uhr alte Einträge löschen
0 3 * * * /usr/bin/php /var/www/peterebenhoch.com/api/cleanup.php
```

**cleanup.php:**
```php
<?php
// Einträge älter als 2 Jahre löschen
$query = "DELETE FROM contact_submissions 
          WHERE submitted_at < NOW() - INTERVAL '2 years'";
pg_query($dbconn, $query);
```

---

### 🟡 EMPFOHLEN

#### 7. Auftragsverarbeitungsvertrag (AVV) mit Brevo
- [ ] **Status:** Zu prüfen
- **Was:** Vertrag mit Brevo als Auftragsverarbeiter
- **Hinweis:** Brevo bietet in der Regel Standard-AVVs an
- **Wo:** Im Brevo-Account unter "Einstellungen" → "DSGVO"
- **Priorität:** 🟡 MITTEL

---

#### 8. Impressum erstellen/aktualisieren
- [ ] **Status:** Zu prüfen (existiert?)
- **Was:** Vollständiges Impressum mit:
  - Name, Adresse
  - Kontaktdaten (E-Mail, Telefon)
  - Ggf. Registereintrag, USt-ID
- **Priorität:** 🟡 MITTEL

---

#### 9. Double-Opt-In für Newsletter
- [ ] **Status:** Nicht implementiert
- **Was:** Bestätigungs-E-Mail nach Formular-Submission
- **Vorteile:**
  - Schutz vor Spam/Missbrauch
  - Beweis der Einwilligung
  - Best Practice
- **Zeitaufwand:** 2-3 Stunden
- **Priorität:** 🟢 NIEDRIG (Nice-to-have)

---

### 🟢 NICE-TO-HAVE

#### 10. Privacy Dashboard für User
- [ ] **Status:** Nicht geplant
- **Was:** User können ihre eigenen Daten einsehen/löschen
- **Zeitaufwand:** 5-10 Stunden
- **Priorität:** 🟢 NIEDRIG

---

## 📊 Status-Übersicht

| Aufgabe | Status | Priorität | Zeitaufwand |
|---------|--------|-----------|-------------|
| 1. Datenschutz-Hinweis am Formular | ❌ Offen | 🔴 HOCH | 30 Min |
| 2. Einwilligungs-Checkbox | ❌ Offen | 🔴 HOCH | 15 Min |
| 3. Datenschutzerklärung | ⚠️ Zu prüfen | 🔴 HOCH | 2-3 Std |
| 4. Unsubscribe-Option | ❌ Offen | 🟡 MITTEL | 3-4 Std |
| 5. Speicherdauer definieren | ❌ Offen | 🔴 HOCH | 30 Min |
| 6. Löschkonzept | ❌ Offen | 🟡 MITTEL | 1-2 Std |
| 7. AVV mit Brevo | ⚠️ Zu prüfen | 🟡 MITTEL | 30 Min |
| 8. Impressum | ⚠️ Zu prüfen | 🟡 MITTEL | 1 Std |
| 9. Double-Opt-In | ❌ Offen | 🟢 NIEDRIG | 2-3 Std |
| 10. Privacy Dashboard | ❌ Offen | 🟢 NIEDRIG | 5-10 Std |

**Minimaler Zeitaufwand (nur Pflicht):** 4-6 Stunden  
**Empfohlener Zeitaufwand (inkl. Empfehlungen):** 7-9 Stunden  
**Vollständiger Zeitaufwand (alles):** 16-26 Stunden

---

## 🚨 Dringende Nächste Schritte

### Phase 1: Schnelle Fixes (1-2 Stunden)
1. **Datenschutz-Hinweis am Formular hinzufügen** (30 Min)
2. **Einwilligungs-Checkbox hinzufügen** (15 Min)
3. **Speicherdauer definieren** (30 Min)

### Phase 2: Dokumentation (2-3 Stunden)
4. **Datenschutzerklärung erstellen/aktualisieren** (2-3 Std)
5. **Impressum prüfen/erstellen** (1 Std)

### Phase 3: Technische Integration (4-6 Stunden)
6. **PostgreSQL-Integration** (erforderlich für Unsubscribe)
7. **Unsubscribe-Option implementieren** (3-4 Std)
8. **Löschkonzept/Cronjob** (1-2 Std)

---

## 📖 Rechtliche Grundlagen

### DSGVO-Artikel (relevant für Kontaktformular)

- **Art. 6 Abs. 1:** Rechtmäßigkeit der Verarbeitung
  - lit. a: Einwilligung
  - lit. b: Vertragserfüllung/Vertragsanbahnung
  
- **Art. 7:** Bedingungen für Einwilligung
  - Abs. 3: Widerruf muss möglich sein
  
- **Art. 13:** Informationspflicht bei Erhebung
  - Verantwortlicher, Zweck, Rechtsgrundlage, Empfänger, Speicherdauer, Rechte
  
- **Art. 15-18:** Rechte der betroffenen Person
  - Auskunft, Berichtigung, Löschung, Einschränkung
  
- **Art. 17:** Recht auf Löschung ("Recht auf Vergessenwerden")

- **Art. 28:** Auftragsverarbeiter (Brevo)

---

## ⚖️ Bußgeldrisiken

**Bei Verstößen gegen DSGVO:**
- ⚠️ **Bis zu 20 Mio. EUR** oder **4% des weltweiten Jahresumsatzes**
- Abmahnungen durch Wettbewerber oder Datenschutz-Aktivisten
- Beschwerden bei Datenschutzbehörde

**Risikoeinschätzung für peterebenhoch.com:**
- 🟢 Niedriges Risiko: Private Website, geringes Datenvolumen
- 🟡 Aber: Keine Ausrede, DSGVO gilt für alle

**Empfehlung:**
- Mindeststandards einhalten (Phase 1 + 2)
- Bei Unsicherheit: Anwalt/Datenschutzbeauftragten konsultieren

---

## 🛠️ Implementierungs-Templates

### Template 1: Datenschutz-Hinweis im Formular

Füge in `contact.qmd` vor dem Submit-Button ein:

```html
<!-- Datenschutzhinweis -->
<div class="alert alert-info mb-3">
  <small>
    <strong>📋 Datenschutzhinweis:</strong> Ihre E-Mail-Adresse wird 
    ausschließlich zur Beantwortung Ihrer Anfrage verwendet. Bei Auswahl 
    von "Keep me posted" erhalten Sie gelegentlich Updates zu meinen 
    Projekten (jederzeit widerrufbar). Ihre Daten werden für maximal 
    2 Jahre gespeichert. Weitere Informationen finden Sie in unserer 
    <a href="/privacy.html" target="_blank" class="alert-link">
      Datenschutzerklärung
    </a>.
  </small>
</div>

<!-- Einwilligungs-Checkbox -->
<div class="form-check mb-3">
  <input class="form-check-input" type="checkbox" 
         id="privacy-consent" required>
  <label class="form-check-label" for="privacy-consent">
    Ich habe die 
    <a href="/privacy.html" target="_blank">Datenschutzerklärung</a> 
    gelesen und stimme der Verarbeitung meiner Daten zu. *
  </label>
  <div class="invalid-feedback">
    Bitte bestätigen Sie die Datenschutzerklärung.
  </div>
</div>
```

**JavaScript-Validierung ergänzen:**

```javascript
// In contact.qmd im <script> Bereich
const privacyConsent = document.getElementById('privacy-consent');

form.addEventListener('submit', async function(event) {
  // ... bestehender Code ...
  
  // Privacy-Checkbox validieren
  if (!privacyConsent.checked) {
    form.classList.add('was-validated');
    return;
  }
  
  // ... rest des Codes ...
});
```

---

### Template 2: Minimal-Datenschutzerklärung

Erstelle `privacy.qmd`:

```markdown
---
title: "Datenschutzerklärung"
---

## Verantwortlicher

Peter Ebenhoch  
[Adresse hier einfügen]  
E-Mail: pe@peterebenhoch.com

## Kontaktformular

### Zweck der Datenverarbeitung
Die über das Kontaktformular erhobenen Daten (E-Mail-Adresse, 
Interessensgebiete) werden ausschließlich verwendet, um Ihre Anfrage 
zu beantworten und Sie ggf. über Updates zu informieren, falls Sie 
"Keep me posted" ausgewählt haben.

### Rechtsgrundlage
- Art. 6 Abs. 1 lit. b DSGVO (Vertragsanbahnung)
- Art. 6 Abs. 1 lit. a DSGVO (Einwilligung für Newsletter)

### Empfänger der Daten
- Brevo (SMTP-Provider für E-Mail-Versand)
- Hetzner (Server-Hosting in Deutschland)

### Speicherdauer
Ihre Daten werden für maximal 2 Jahre nach dem letzten Kontakt 
gespeichert und danach automatisch gelöscht.

### Ihre Rechte
Sie haben das Recht auf:
- Auskunft über Ihre gespeicherten Daten (Art. 15 DSGVO)
- Berichtigung unrichtiger Daten (Art. 16 DSGVO)
- Löschung Ihrer Daten (Art. 17 DSGVO)
- Widerruf Ihrer Einwilligung (Art. 7 Abs. 3 DSGVO)

Zur Ausübung dieser Rechte kontaktieren Sie uns unter 
pe@peterebenhoch.com.

### Beschwerderecht
Sie haben das Recht, sich bei einer Datenschutz-Aufsichtsbehörde 
zu beschweren.
```

---

## 📞 Hilfe & Ressourcen

- **DSGVO-Generator:** https://datenschutz-generator.de/
- **Brevo DSGVO-Infos:** https://help.brevo.com/hc/de/articles/360001005290
- **Bundesdatenschutzbeauftragter:** https://www.bfdi.bund.de/
- **Anwälte:** Bei Unsicherheit rechtliche Beratung einholen

---

**Dokumentation erstellt:** 2026-01-11  
**Letzte Aktualisierung:** 2026-01-11  
**Nächste Review:** Nach Implementierung von Phase 1
