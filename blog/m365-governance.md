---
title: "Microsoft 365 in Behörden: Rechtssicherheit ohne technische Gewissheit"
subtitle: "Zwischen pragmatischem Kompromiss und grundlegenden Governance-Fragen"
author: "Peter Ebenhöch"
date: "2025-11-27"
categories: [Datenschutz, Cloud-Compliance, Digitale Souveränität]
---

## Einleitung: Die unendliche Geschichte geht weiter

Die Debatte um den datenschutzkonformen Einsatz von Microsoft 365 in öffentlichen Verwaltungen hat im November 2025 eine bemerkenswerte Wendung genommen. Während die schweizerischen Datenschutzbeauftragten (Privatim) in einer Resolution vom 18. November 2025 die Nutzung internationaler Cloud-Dienste für sensible Behördendaten faktisch als unzulässig bewerteten , kam der Hessische Beauftragte für Datenschutz und Informationsfreiheit (HBDI) Prof. Dr. Alexander Roßnagel nach intensiven Verhandlungen mit Microsoft zu dem Ergebnis, dass Microsoft 365 datenschutzkonform genutzt werden kann . Gleichzeitig bestätigte der Europäische Datenschutzbeauftragte (EDPS) im Juli 2025, dass die Europäische Kommission die festgestellten Datenschutzverstöße bei ihrer Nutzung von Microsoft 365 behoben hat .

Diese scheinbar widersprüchlichen Entwicklungen werfen fundamentale Fragen auf: Wie kann dieselbe Software von verschiedenen Aufsichtsbehörden so unterschiedlich bewertet werden? Was bedeutet "Rechtssicherheit" ohne technische Überprüfung? Und welche Risiken bleiben trotz verbesserter Verträge bestehen?

## Die Positionen im Überblick

### Schweiz: Faktisches Verbot für sensible Daten

Privatim hält die Auslagerung besonders schützenswerter oder geheimhaltungspflichtiger Personendaten in SaaS-Lösungen internationaler Anbieter durch öffentliche Organe in den meisten Fällen für unzulässig . Die Begründung ist klar strukturiert:

**Mangelnde Verschlüsselung**: Die meisten SaaS-Lösungen bieten keine echte Ende-zu-Ende-Verschlüsselung, die einen Zugriff des Anbieters auf Klartextdaten ausschließen würde .

**Intransparenz**: Global operierende Firmen bieten zu wenig Transparenz, als dass Schweizer Behörden die Einhaltung vertraglicher Pflichten betreffend Datenschutz und Sicherheit überprüfen könnten .

**US Cloud Act**: US-Anbieter können aufgrund des 2018 erlassenen CLOUD Act dazu verpflichtet werden, Daten ihrer Kunden an US-Behörden herauszugeben, ohne die Regeln der internationalen Rechtshilfe einzuhalten – selbst wenn diese Daten in Schweizer Rechenzentren gespeichert sind .

Die Konsequenz ist eindeutig: Die Nutzung internationaler SaaS-Lösungen für besonders schützenswerte oder geheimhaltungspflichtige Personendaten durch öffentliche Organe ist nur dann möglich, wenn die Daten vom verantwortlichen Organ selbst verschlüsselt werden und der Cloud-Anbieter keinen Zugang zum Schlüssel hat .

### Hessen: Pragmatischer Weg zur Compliance

Der hessische Ansatz unterscheidet sich fundamental. Nach intensiven Verhandlungen seit Januar 2025 mit Microsoft in zahlreichen Diskussionsrunden  kam der HBDI zu einem positiven Ergebnis. Die Grundlage bilden mehrere Faktoren:

**Verbessertes Vertragswerk**: Microsoft hat das Data Protection Addendum (DPA) weiterentwickelt und stellt zusätzliche Dokumentationen bereit (Interpretationshilfe, M365-Kit, Taxonomie).

**EU-Datengrenze**: Die im Februar 2025 fertiggestellte EU-Datengrenze soll gewährleisten, dass Kundendaten in der EU und der Europäischen Freihandelszone gespeichert und verarbeitet werden .

**Rechtlicher Rahmen**: Das EU-US Data Privacy Framework bietet eine rechtliche Grundlage für Datenübermittlungen.

**Beschränkte Datenverarbeitung**: Microsoft verarbeitet für eigene Geschäftszwecke ausschließlich aggregierte und pseudonymisierte Log- und Diagnosedaten, keine Inhaltsdaten.

Allerdings gibt es eine bedeutende Einschränkung: Der hessische Datenschutzbeauftragte räumte ein, dass seine Behörde die einzelnen Dienste von Microsoft technisch nicht untersucht habe, mit der Begründung: "Dazu sind wir personell überhaupt nicht in der Lage, aber wir haben die Grundsatzfragen des Datenschutzes zufriedenstellend gelöst" .

### EU-Kommission: Nachbesserung unter Aufsicht

Der Fall der EU-Kommission zeigt, wie aufwendig echte Compliance ist. Der EDPS hatte im März 2024 mehrere Verstöße gegen Regulation (EU) 2018/1725 festgestellt, insbesondere bei Zweckbindung, internationalen Datentransfers und unautorisierten Datenweitergaben . Nach umfangreichen Korrekturen und der Vorlage eines Compliance-Berichts im Dezember 2024 bestätigte der EDPS im Juli 2025, dass die festgestellten Verstöße behoben wurden .

Wichtig ist die Einschränkung: Der EDPS betonte, dass sich die Verfahrenseinstellung ausschließlich auf die geprüften Bestimmungen bezieht und keine umfassende Bestätigung der Gesamtcompliance darstellt .

## Analyse: Vier zentrale Problemfelder

### 1. Technische Verifikation versus vertragliche Zusicherungen

Der hessische Ansatz basiert auf vertraglichen Zusicherungen ohne technische Überprüfung. Dies wirft grundlegende Fragen auf:

**Kann rechtliche Compliance ohne technische Verifikation erreicht werden?** Die Schweizer Position verneint dies implizit, indem sie echte Ende-zu-Ende-Verschlüsselung als technische Notwendigkeit fordert. Der eigene Input-Text formuliert es treffend: "Die Frage aufwirft, ob man lediglich mit rechtlichen Maßnahmen technisch-organisatorische Maßnahmen absichern kann? Ich würde sagen nein."

**Wer trägt das Risiko der Nicht-Überprüfbarkeit?** Wenn Aufsichtsbehörden mangels Ressourcen keine technische Prüfung durchführen können, verlagert sich die Verantwortung vollständig auf die nutzenden Organisationen. Diese müssen dann Compliance-Maßnahmen umsetzen, deren technische Wirksamkeit sie selbst nicht verifizieren können.

**Das Informationsasymmetrie-Problem**: Cloud-Anbieter haben vollständige Kenntnis ihrer technischen Implementierung, während Kunden und Aufsichtsbehörden auf Dokumentation und Zusicherungen angewiesen sind. Diese strukturelle Asymmetrie kann durch Verträge nicht vollständig kompensiert werden.

### 2. Die Rolle als Auftragsverarbeiter vs. Joint Controller

Der Input-Text weist auf ein grundlegendes Problem hin: "Wesentlich besser wäre es, wenn Microsoft überhaupt die Stellung eines Verantwortlichen einnehmen und nicht die eines Joint Controllers und nicht die eines Auftragsdatenbearbeiters würde."

Diese Frage ist zentral: In der aktuellen Konstellation agiert Microsoft als Auftragsverarbeiter für Kundendaten, verarbeitet aber gleichzeitig Telemetriedaten für eigene Zwecke. Diese Doppelrolle schafft Komplexität:

- Als Auftragsverarbeiter ist Microsoft weisungsgebunden
- Für eigene Geschäftszwecke agiert Microsoft als Verantwortlicher
- Die Trennung dieser Rollen ist in der Praxis schwer nachvollziehbar

Eine klare Zuordnung der Verantwortlichkeit würde Haftungsfragen vereinfachen und die Durchsetzung von Betroffenenrechten erleichtern.

### 3. Verfügbarkeit als unterschätztes Risiko

Der Input-Text hebt ein oft übersehenes Problem hervor: "Das wichtigste Problem dabei dürfte weniger der Zugriff auf die Daten sein als, wie immer, die Verfügbarkeit der Daten."

Das Beispiel des Richters am Europäischen Gerichtshof, dessen Leben durch die Sperrung aller amerikanischen Dienste massiv beeinträchtigt wurde, illustriert das fundamentale Risiko der Abhängigkeit. Selbst bei vollständiger Datenschutzkonformität bleibt das Risiko, dass:

- Der Anbieter den Zugang sperrt (bewusst oder durch technische Fehler)
- Rechtliche oder politische Entwicklungen den Zugang einschränken
- Technische Ausfälle kritische Dienste lahmlegen

Dieses Verfügbarkeitsrisiko kann durch Verschlüsselung nicht gemindert werden. Die einzige Lösung ist echte digitale Souveränität: "Digitale Souveränität, und sei es nur in der Form, dass ich die Daten autonom gesichert habe und rasch auf einer anderen Seite wieder herstellen kann, muss sichergestellt werden."

### 4. Der US Cloud Act als ungelöstes Problem

Trotz aller Verbesserungen bleibt das Cloud Act-Problem bestehen. Die contractual safeguards können EU-Daten nicht vollständig vor US-Überwachungsgesetzen schützen . Der Cloud Act kann US-Unternehmen zur Herausgabe von Daten verpflichten, unabhängig vom Speicherort.

Das EU-US Data Privacy Framework bietet zwar einen rechtlichen Rahmen, aber die Schrems-Rechtsprechung hat gezeigt, dass solche Rahmenabkommen unter Umständen als unzureichend bewertet werden können. Die rechtliche Unsicherheit bleibt also bestehen.

## Handlungsempfehlungen: Risikobasierter Ansatz

Für Organisationen, die Microsoft 365 einsetzen oder erwägen, ergibt sich aus der Analyse folgendes differenziertes Bild:

### Hohe Konfidenz: Notwendige Basismaßnahmen

1. **Vertragsgestaltung**: Nutzen Sie das aktuellste DPA und für öffentliche Stellen das DPA-öS für zusätzliche Garantien.

2. **Konfiguration**: Implementieren Sie alle im M365-Kit dokumentierten Datenschutzeinstellungen. Deaktivieren Sie nicht benötigte Funktionen.

3. **Dokumentation**: Führen Sie ein vollständiges Verarbeitungsverzeichnis. Dokumentieren Sie alle Datenflüsse und Zwecke.

4. **Löschkonzept**: Implementieren Sie ein umfassendes Löschkonzept für alle Datenarten entsprechend gesetzlicher Fristen.

5. **Datenschutz-Folgenabschätzung**: Führen Sie für jeden Einsatzzweck eine spezifische DSFA durch, insbesondere bei besonderen Kategorien personenbezogener Daten (Art. 9, 10 DSGVO).

### Mittlere Konfidenz: Risikominimierung

1. **Datenklassifizierung**: Kategorisieren Sie Daten nach Schutzbedarf. Lagern Sie hochsensible Daten (Gesundheitsdaten, Sozialdaten, Verschlusssachen) nicht in Microsoft 365 aus.

2. **Verschlüsselung**: Implementieren Sie, wo möglich, clientseitige Verschlüsselung für besonders schützenswerte Daten. Beachten Sie, dass dies die Funktionalität einschränkt.

3. **EU-Datengrenze**: Aktivieren Sie die EU-Datengrenze in Ihrem Tenant. Beachten Sie, dass Ausnahmen für Support und Störungsbeseitigung bestehen.

4. **Monitoring**: Etablieren Sie kontinuierliches Monitoring der Datenflüsse. Nutzen Sie Audit-Logs und überprüfen Sie regelmäßig Unterauftragsverarbeiter.

5. **Alternative Backup-Strategie**: Implementieren Sie unabhängige Backup-Lösungen außerhalb der Microsoft-Infrastruktur zur Sicherstellung der Verfügbarkeit.

### Niedrige Konfidenz / Offene Fragen

1. **Technische Verifikation**: Die fehlende technische Überprüfung durch Aufsichtsbehörden schafft Unsicherheit. Organisationen können die tatsächliche technische Implementierung nicht vollständig verifizieren. Hier bleibt ein Restrisiko bestehen.

2. **Langfristige rechtliche Stabilität**: Die Bewertung kann sich ändern, wenn:
   - Das EU-US Data Privacy Framework rechtlich angefochten wird
   - Neue Schrems-Urteile ergehen
   - Politische Entwicklungen die Rechtslage ändern

3. **Compliance-Aufwand**: Der im HBDI-Bericht dokumentierte Aufwand ist erheblich. Kleinere Organisationen ohne dedizierte Datenschutzressourcen werden Schwierigkeiten haben, alle Anforderungen umzusetzen und kontinuierlich zu überwachen.

### Strategische Überlegung: Exit-Strategie

Unabhängig von der aktuellen Bewertung sollte jede Organisation eine Exit-Strategie entwickeln:

- **Datenportabilität**: Stellen Sie sicher, dass alle Daten jederzeit vollständig exportiert werden können.
- **Migrationsplan**: Identifizieren Sie alternative Lösungen (z.B. openDesk, Nextcloud, andere europäische Anbieter).
- **Zeitrahmen**: Definieren Sie Kriterien und Zeitrahmen für eine mögliche Migration.
- **Testszenarien**: Testen Sie regelmäßig die Wiederherstellbarkeit aus Backups.

## Fazit: Pragmatismus unter Vorbehalt

Die unterschiedlichen Bewertungen von Privatim, HBDI und EDPS zeigen: Es gibt keine einfache Antwort auf die Frage der datenschutzkonformen Nutzung von Microsoft 365.

**Der hessische Ansatz bietet pragmatische Rechtssicherheit** für Organisationen, die Microsoft 365 unter definierten Bedingungen nutzen möchten. Die umfangreichen Anforderungen im 137-seitigen Bericht zeigen jedoch, dass diese "Rechtssicherheit" mit erheblichem Umsetzungsaufwand erkauft wird.

**Die Schweizer Position markiert den technisch konsequenten Standpunkt**: Ohne Ende-zu-Ende-Verschlüsselung mit kundenkontrolliertem Schlüsselmanagement besteht ein strukturelles Risiko. Dieses kann durch vertragliche Zusicherungen nicht vollständig eliminiert werden.

**Die Realität liegt dazwischen**: Organisationen müssen eine risikobasierte Entscheidung treffen, die folgende Faktoren berücksichtigt:

- Art und Schutzbedarf der verarbeiteten Daten
- Verfügbare Ressourcen für Implementierung und Monitoring
- Bedeutung von Verfügbarkeit und Geschäftskontinuität
- Strategische Bedeutung digitaler Souveränität
- Risikobereitschaft und -tragfähigkeit

**Die fundamentale Governance-Frage bleibt**: Sollten kritische digitale Infrastrukturen öffentlicher Verwaltungen und sensibler privater Bereiche von Anbietern betrieben werden, die ausländischen Rechtssystemen unterliegen? Die technischen und vertraglichen Verbesserungen haben die Situation verbessert, aber das strukturelle Problem nicht gelöst.

Die Diskussion um Microsoft 365 ist daher nicht nur eine juristische oder technische Compliance-Frage. Sie ist eine strategische Entscheidung über die Zukunft digitaler Souveränität in Europa.

---

**Quellen:**

- [Privatim Resolution zur Cloud-Nutzung (18.11.2025)](https://www.privatim.ch/de/publikation-resolution-zur-auslagerung-von-datenbearbeitungen-in-die-cloud/)
- [HBDI Bericht Microsoft 365 (15.11.2025, PDF 137 Seiten)](https://datenschutz.hessen.de/presse/hbdi-microsoft-365-kann-datenschutzkonform-genutzt-werden)
- [EDPS: Closure of enforcement proceedings (11.07.2025)](https://www.edps.europa.eu/press-publications/press-news/press-releases/2025/european-commission-brings-use-microsoft-365-compliance-data-protection-rules-eu-institutions-and-bodies_en)
- [Heise: Schweiz - Cloud-Verbot für Behörden](https://www.heise.de/news/Schweiz-Datenschuetzer-verhaengen-breites-Cloud-Verbot-fuer-Behoerden-11093438.html)
- [Heise: Grünes Licht für M365 in Hessen](https://www.heise.de/news/Gruenes-Licht-fuer-Microsoft-365-in-Hessens-Verwaltung-11079834.html)