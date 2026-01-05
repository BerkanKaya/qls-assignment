# QLS Shipping Label Builder

Mini webtool om een shipment aan te maken via de QLS API en vervolgens een PDF te downloaden met:
- **Packing slip** (pakbon)
- **QLS label PDF**
Alles wordt samengevoegd tot **1 A4-PDF**.

## Stack
- Laravel (PHP)
- Blade + Tailwind (Vite) + Alpine.js
- Redis cache (product combinations)
- PDF: DomPDF (packing slip) + FPDI (merge)

## Architectuur (kort)
- **Controller**: dun, alleen request → services → response/download
- **ProductService**: haalt **product_combinations** op via `/companies/{companyId}/products` en bewaart ze **10 minuten in Redis cache**
- **ShipmentService**: maakt shipment aan via `/v2/companies/{companyId}/shipments`
- **LabelService**: download label (QLS retourneert altijd JSON + base64 in `data`) en decodeert naar PDF-bytes
- **PackingSlipService**: genereert pakbon PDF vanuit Blade view
- **PDFMergerService**: merge’t pakbon + label naar één PDF
- **DTO’s + Factory**: vertaalt form input naar payload/DTO’s zodat de services schoon blijven

### PDF layout & multi-page edge case
De pakbon wordt gegenereerd met DomPDF. Bij uitzonderlijk veel orderregels kan DomPDF automatisch meerdere pagina’s maken.
Omdat deze tool/assignment bewust een **single-page A4 output** nastreeft, heb ik dit edge case expliciet afgevangen:

- Orderregels worden **gegroepeerd** (zelfde SKU/EAN/naam) zodat de pakbon vrijwel altijd binnen één A4 past.
- `PDFMergerService` **forceert single-page input**: als de pakbon of het label toch meerdere pagina’s bevat, stop ik het duidelijke met een foutmelding in plaats van stil content weg te laten.

## Install & Run (lokaal)

### Vereisten
- PHP + Composer
- Node.js + npm
- (optioneel) Docker voor Redis

### 1) Install
```bash
composer install
npm install
```

### 2) Env
Kopieer `.env.example` naar `.env`.  
De enige waarden die je zelf moet invullen/aanpassen zijn de **QLS\_*** variabelen.

```env
APP_NAME="QLS Assignment"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CACHE_STORE=redis
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
FORWARD_REDIS_PORT=6379

QLS_API_BASE_URL=https://api.pakketdienstqls.nl
QLS_API_USERNAME=...
QLS_API_PASSWORD=...
QLS_COMPANY_ID=...
QLS_BRAND_ID=...
QLS_DEFAULT_PRODUCT_COMBINATION_ID=...
QLS_API_TIMEOUT=10
```

### 3) App key
```bash
php artisan key:generate
```

### 4) Frontend
Dev:
```bash
npm run dev
```

### 5) Run
```bash
php artisan serve
```

Submit het formulier → je krijgt direct een **PDF download** met pakbon + label.

## Redis (product combinations cache)

Als je Redis gebruikt:
```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
FORWARD_REDIS_PORT=6379
```

Start Redis (optioneel via Docker):
```bash
docker compose up -d
```
## Tests
```bash
php artisan test
```
