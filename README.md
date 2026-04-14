# Kategorie Post & Bild Exporter

WordPress-Plugin zum Exportieren aller Beiträge einer Kategorie als **CSV** oder **Excel (XLSX)** sowie zum Herunterladen aller **Headerbilder als ZIP** in bester Qualität.

Unterstützt die **Einsatzdetails** des [Feuerwehr Wolfurt Themes](https://github.com/BattloXX/Feuerwehr-Wolfurt-Theme) und exportiert diese automatisch als zusätzliche Spalten.

## Features

- Kategorie-Auswahl über Dropdown im WordPress-Backend
- Optionaler **Zeitraum-Filter** (Von / Bis)
- **CSV-Export** – UTF-8 mit BOM, Semikolon-getrennt, direkt in Excel öffenbar
- **Excel-Export (XLSX)** – natives Format, keine externe PHP-Bibliothek nötig
- **Bilder-ZIP** – alle Headerbilder (Featured Images) in Originalqualität
- Spalte `Headerbild_Dateiname` im Export stimmt exakt mit dem Dateinamen in der ZIP überein → eindeutige Zuordnung
- Fallback für offloaded Media (z. B. WP Offload Media)
- **Einsatzdetails** (Feuerwehr Wolfurt Theme): Einsatz-Nr., Alarmzeit, Einsatzort, Einsatzart, Mannschaft, Dauer, beteiligte Organisationen, Fahrzeuge
- Sicherheit: Nonce-Prüfung + `manage_options` Capability

## Export-Spalten

| Spalte | Beschreibung |
|---|---|
| `ID` | WordPress Post-ID |
| `Titel` | Beitragstitel |
| `Datum` | Veröffentlichungsdatum |
| `Autor` | Anzeigename des Autors |
| `URL` | Permalink des Beitrags |
| `Auszug` | Manueller Auszug oder automatisch gekürzt (30 Wörter) |
| `Kategorien` | Alle zugewiesenen Kategorien, kommagetrennt |
| `Headerbild_URL` | Vollständige URL des Featured Image |
| `Headerbild_Dateiname` | Dateiname in der ZIP: `{Post-ID}_{Originaldateiname}` |
| `Einsatz_Nr` | Einsatznummer (Feuerwehr Wolfurt Theme) |
| `Alarmzeit` | Alarmierungszeitpunkt |
| `Einsatzort` | Ort des Einsatzes |
| `Einsatzart` | z. B. Brandeinsatz, Technische Hilfeleistung |
| `Mannschaft` | Anzahl eingesetzter Kräfte |
| `Dauer_Min` | Einsatzdauer in Minuten |
| `Beteiligte_Organisationen` | Weitere beteiligte Organisationen (Freitext) |
| `Fahrzeuge` | Eingesetzte Fahrzeuge, kommagetrennt (z. B. `KDOF, RLF, TMB`) |

> Beiträge ohne Einsatzdetails (andere Kategorien, anderes Theme) haben in den Einsatz-Spalten einfach leere Werte – der Export funktioniert für alle Kategorien.

## Installation

### Über WordPress-Backend (empfohlen)

1. [`kategorie-exporter-v1.2.0.zip`](https://github.com/BattloXX/WP-Category-Export/releases/latest) herunterladen
2. WordPress-Backend → **Plugins → Installieren → Plugin hochladen**
3. ZIP auswählen → **Jetzt installieren** → **Plugin aktivieren**

### Manuell per FTP/SFTP

1. Repository klonen oder ZIP entpacken
2. Ordner `kategorie-exporter/` nach `wp-content/plugins/` hochladen
3. Im Backend unter **Plugins** aktivieren

## Verwendung

**WordPress-Backend → Werkzeuge → Kategorie Exporter**

1. Kategorie aus dem Dropdown wählen (Anzahl der Beiträge in Klammern)
2. Optional: Zeitraum eingeben (Von / Bis)
3. Gewünschte Aktion wählen:
   - **Als CSV exportieren**
   - **Als Excel (XLSX) exportieren**
   - **Bilder als ZIP herunterladen**

> Beiträge ohne Headerbild erscheinen im CSV/XLSX-Export, jedoch nicht in der ZIP-Datei.

## Voraussetzungen

- WordPress 5.0+
- PHP 7.4+
- PHP-Erweiterung `zip` (ZipArchive) – auf praktisch allen Hosting-Paketen verfügbar

## Changelog

### v1.2.0
- Einsatzdetails des Feuerwehr Wolfurt Themes werden beim Export mitexportiert (Einsatz-Nr., Alarmzeit, Einsatzort, Einsatzart, Mannschaft, Dauer, beteiligte Organisationen, Fahrzeuge)

### v1.1.1
- Security Fixes

### v1.1.0
- Optionaler Zeitraum-Filter (Von / Bis)

### v1.0.0
- Erstveröffentlichung

## Lizenz

GPL-2.0+
