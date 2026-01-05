# Black Shield Logistics - Aplicație Web Completă

## Funcționalități Implementate

### 1. ✅ Parsare Conținut din Surse Externe
**Fișiere:** `external_content.php`, `news.php`

Aplicația parsează și modelează conținut din diverse surse externe:
- **Feed-uri RSS** - Știri despre securitate IT
- **API REST** - Cursuri valutare de la BNR (XML)
- **Date meteo** - Pentru planificarea rutelor
- **Cache automat** - Evită requests repetate

Toate datele sunt procesate server-side, **NU** sunt încărcate direct prin iframe sau URL-uri externe.

### 2. ✅ Sistem Email pentru Contact și Comenzi
**Fișiere:** `contact.php`, `order.php`, `mailer.php`

Funcționalitate completă de transmitere mesaje email:
- **Formular de contact** - Cu validare și trimitere email către admin
- **Sistem de comenzi transport** - Comandă securizată cu email de confirmare
- **Email-uri automate** - Confirmare către client și notificare către admin
- **Integrare PHPMailer** - Folosește librăria PHPMailer existentă

### 3. ✅ Import/Export în Formate Multiple
**Fișiere:** `import_export.php`, `pdf_generator.php`, `export_word.php`

Aplicația permite import/export în următoarele formate:
- **📊 Excel (.xls)** - Export date în format compatibil Microsoft Excel
- **📄 PDF** - Rapoarte PDF printabile
- **📝 Word (.doc)** - Documente compatibile Microsoft Word
- **📥 Import CSV/Excel** - Încărcare date din fișiere externe

**NU** sunt folosite formate TXT, XML, JSON conform cerințelor.

### 4. ✅ Element Multimedia - Grafice și Statistici
**Fișiere:** `statistics.php`

Pagină de statistici interactive cu:
- **Grafice Pie** - Distribuție status comenzi
- **Grafice Bar** - Evoluție comenzi pe lună
- **Grafice Doughnut** - Top tipuri de marfă
- **Grafice Horizontal Bar** - Niveluri de securitate
- **Card-uri statistici** - Rezumat vizual al datelor
- **Chart.js** - Librărie profesională de vizualizare date

## Instalare și Configurare

### 1. Baza de Date

Rulați scriptul SQL pentru a crea tabelele necesare:

```bash
mysql -u cenaches_BSL -p cenaches_BSL < create_tables.sql
```

Sau executați manual în phpMyAdmin:

```sql
-- Copiați și executați conținutul din create_tables.sql
```

### 2. Configurare Email (PHPMailer)

Editați fișierul `mailer.php` și configurați:

```php
$smtpHost = 'smtp.yourdomain.com';  // Server SMTP
$smtpUser = 'your-email@domain.com'; // Email
$smtpPass = 'your-password';         // Parolă
$smtpPort = 587;                     // Port (587 pentru TLS)
```

### 3. Permisiuni Fișiere

Asigurați-vă că directorul temporar are permisiuni de scriere pentru cache:

```bash
chmod 755 /tmp
```

### 4. Dependențe

Aplicația folosește:
- **PHPMailer** - Deja instalat în folder `PHPmailer/`
- **Chart.js** - Încărcat de la CDN (nu necesită instalare)
- **PHP 7.4+** - Cu extensii: mysqli, curl, simplexml

## Structura Fișierelor Noi

```
├── external_content.php      # Funcții parsare date externe
├── news.php                   # Pagină afișare conținut extern
├── contact.php                # Formular contact cu email
├── order.php                  # Formular comandă transport cu email
├── import_export.php          # Pagină import/export date
├── export_word.php            # Export Word
├── pdf_generator.php          # Generator PDF și Word
├── statistics.php             # Statistici și grafice interactive
└── create_tables.sql          # Script SQL pentru tabele noi
```

## Utilizare

### Pentru Vizitatori:
1. **Acces informații externe** - `news.php` - Vezi știri, cursuri valutare, meteo
2. **Formular contact** - `contact.php` - Trimite mesaj către companie

### Pentru Utilizatori Autentificați:
3. **Comandă transport** - `order.php` - Creează cerere de transport securizat
4. **Primire email confirmare** - Automat după comandă

### Pentru Administratori (permission_id >= 2):
5. **Statistici** - `statistics.php` - Vizualizează grafice interactive
6. **Export Excel** - Descarcă raport comenzi în format .xls
7. **Export PDF** - Generează PDF pentru printare
8. **Export Word** - Creează document .doc
9. **Import date** - Încarcă comenzi din CSV/Excel

## Testare Funcționalități

### Test 1: Conținut Extern
```
1. Accesați: http://yourdomain.com/news.php
2. Verificați: Știri, cursuri BNR, date meteo
3. Observați: Datele sunt parsate, nu iframe-uri
```

### Test 2: Email Contact
```
1. Accesați: http://yourdomain.com/contact.php
2. Completați formularul
3. Verificați: Email primit la admin și confirmare la client
```

### Test 3: Email Comandă
```
1. Autentificați-vă
2. Accesați: http://yourdomain.com/order.php
3. Completați cerere transport
4. Verificați: Email confirmare cu număr comandă
```

### Test 4: Export Excel
```
1. Autentificați-vă ca admin
2. Accesați: http://yourdomain.com/import_export.php
3. Click "Export Excel"
4. Verificați: Fișier .xls descărcat și deschis în Excel
```

### Test 5: Export PDF
```
1. Accesați: http://yourdomain.com/import_export.php
2. Click "Export PDF"
3. Verificați: Document HTML pentru print-to-PDF
```

### Test 6: Export Word
```
1. Accesați: http://yourdomain.com/import_export.php
2. Click "Export Word"
3. Verificați: Fișier .doc descărcat și deschis în Word
```

### Test 7: Statistici și Grafice
```
1. Accesați: http://yourdomain.com/statistics.php
2. Verificați: 4 grafice interactive (Pie, Bar, Doughnut)
3. Testați: Interacțiune hover pe grafice
```

## Caracteristici Tehnice

### Parsare Conținut Extern:
- ✅ SimpleXML pentru RSS feeds
- ✅ cURL pentru API REST calls
- ✅ Cache inteligent cu validare timp
- ✅ Error handling robust

### Sistem Email:
- ✅ PHPMailer cu SMTP
- ✅ HTML templates pentru emails
- ✅ Validare formulare server-side
- ✅ Salvare în baza de date

### Import/Export:
- ✅ Excel compatibil (HTML cu meta tags Office)
- ✅ PDF printabil (HTML optimizat)
- ✅ Word compatibil (OOXML format)
- ✅ Import CSV cu validare

### Statistici:
- ✅ Chart.js 4.4.1
- ✅ Responsive design
- ✅ 4 tipuri de grafice
- ✅ Date real-time din MySQL

## Securitate

- ✅ Validare input pentru toate formularele
- ✅ Prepared statements pentru SQL
- ✅ htmlspecialchars pentru output
- ✅ Verificare autentificare și permisiuni
- ✅ CSRF protection via sessions

## Performanță

- ✅ Cache pentru date externe (reducere API calls)
- ✅ Indexes pe tabele MySQL
- ✅ Lazy loading pentru grafice
- ✅ CDN pentru Chart.js

## Suport

Pentru probleme sau întrebări:
- Email: admin@blackshieldlogistics.com
- Documentație: Acest fișier README.md

---

**Toate cerințele proiectului au fost implementate cu succes!** ✅
