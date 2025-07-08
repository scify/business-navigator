# Interactive Landscape Tool

Version 0.56 - July 6, 2025

Build on [Laravel 12](https://laravel.com/docs/12.x/releases) using [Inertia.js 2.x](https://inertiajs.com/) with [Vue.js 3.x](https://vuejs.org/).

**Table of contents**

- [Development environment](#development-environment): For local development via DDEV
- [Production environment](#production-environment): How to deploy on Production
- [Data Import](#data-import): How to import Organisations

## Service Dependencies

### MapBox

Maps rely on [MapBox](https://www.mapbox.com/) and therefore, a valid [MapBox Access Token](https://docs.mapbox.com/help/dive-deeper/access-tokens/) has to be used. 

**Important:** The token will be included in the webpage and will be visible to anyone who makes an effort to look for it. MapBox recommends that you generate a separate access token with URL restrictions to help prevent abuse of billable API endpoints. See [How to use Mapbox securely](https://docs.mapbox.com/help/troubleshooting/how-to-use-mapbox-securely/) for detailed security guidance.

### OpenCage Geocoding

Geocoding services rely on [OpenCage Data](https://opencagedata.com/) and therefore, a valid [OpenCage API Key](https://opencagedata.com/api) must be configured in your `.env` file as `OPENCAGE_API_KEY`.

## Development environment

### Local Requirements

- [DDEV](https://ddev.com/) version *1.24* or newer.
- Access to this repository.

### Installation

#### a. First steps

1. Clone this project.
2. Change to the directory of the project (i.e. `cd interactive-landscape-tool`).
3. The configuration of the DDEV project is included in the repository; therefore, you can start it:

```shell
ddev start &&
ddev composer install
```

#### b. Setup your development environment.

If for some reason the `.env` file has not been created on the root of this project, then please create it yourself by copying the provided example.

```shell
cp .env.example .env
```

Create the application key, required by Laravel, run the migrations, and do the initial seed of the database:

```shell
ddev artisan key:generate &&
ddev artisan migrate:fresh --seed
```

No organisations will be seeded. You should be able to import organisations using the provided CLI tool as described on [Data Import](#data-import).

Finally, install the required front-end dependencies:

```shell
ddev npm i
```

### Development using hot-reload (HMR)

Start Vite to launch the development environment:

```shell
ddev npm run dev
```

You can visit https://interactive-landscape-tool.ddev.site on your browser to enjoy development with hot-reloading for both Laravel and Inertia/Vue.js.

### Restart with a fresh database

Something wrong with the database? Want a fresh start? Do this whenever you feel the need to, and the database will be recreated from scratch (i.e. existing data will be erased).

```shell
ddev artisan migrate:fresh --seed
```

### Log files

For your convenience, the project includes [Laravel Pail](https://github.com/laravel/pail), *a package that allows you to easily dive into your Laravel application's log files directly from the command line*.

```shell
ddev artisan pail
```

### Code Quality Tools

**[Laravel Pint](https://laravel.com/docs/12.x/pint)** — Automatically formats PHP code according to Laravel's coding standards using PHP-CS-Fixer rules:

```shell
ddev pint
```

**[Larastan](https://github.com/larastan/larastan)** — Static analysis tool that extends PHPStan for Laravel projects, configured at level 10 (strictest) to catch type errors, unused variables, and potential bugs:

```shell
ddev exec ./vendor/bin/phpstan analyse
```

**[ESLint](https://eslint.org/)** with **[TypeScript ESLint](https://typescript-eslint.io/)** — Lints JavaScript/TypeScript code in Vue components and scripts, automatically fixing issues where possible:

```shell
ddev npm run lint
```

**[Prettier](https://prettier.io/)** — Code formatter for JavaScript, TypeScript, and Vue files with consistent styling across the project:

```shell
ddev npm run format
```

**[Vue TSC](https://github.com/vuejs/language-tools)** — TypeScript compiler for Vue 3 that type-checks all TypeScript code in Vue components and scripts:

```shell
ddev npm run type-check
```

### Tests

The project uses PHPUnit for testing Laravel backend functionality. Tests are organised into core tests (business logic, models, controllers) and live API tests (external service integrations):

**All tests** — Run all tests including core tests and live API tests:

```shell
ddev php artisan test
```

**Live API tests only** — Test integration with OpenCage geocoding service using real API calls:

```shell
ddev php artisan test --group=live-api
```

**Core tests only (recommended)** — Test application logic, database operations, and API endpoints without external dependencies:

```shell
ddev php artisan test --exclude-group=live-api
```

## Production environment

### Server requirements

- MySQL >= 8.0
- PHP >= 8.2 with required extensions:
  - [intl—](https://www.php.net/manual/en/intl.installation.php) ― for internationalisation
  - [imagick](https://www.php.net/manual/en/book.imagick.php) ― for image processing
  - [pdo](https://www.php.net/manual/en/book.pdo.php) ― for database connectivity
  - Plus all [Laravel 12 required extensions](https://laravel.com/docs/12.x/deployment#server-requirements)

Please note that this app is built for SSR, although this doesn't seem to be a requirement at the moment (hopefully).

### First install on Production:

For production, usually the following steps are enough for the first installation. Please make sure that you have properly configured the `.env` file.

```shell
composer install &&
php artisan key:generate && 
php artisan migrate:fresh --seed &&
npm install &&
npm run build
```

### Consequent deployments:

```shell
artisan down --message="Scheduled maintenance in progress."
composer install &&
npm install &&
npm run build &&
php artisan config:cache
php artisan up
```

Please note that in both cases build assets will be created in two locations:
- `public/build` - Front-end CSS & JS
- `bootstrap/ssr` - SSR

## Data Import

### Requirements

- A valid OpenCage Data API key, properly set on the `OPENCAGE_API_KEY` property of your `.env` file.
- Test geocoding availability at `/test/geocode` route with interactive country selection.

### The Import Process

To import organisation data, use the provided CLI tool which processes Excel files directly.

The command syntax varies depending on your environment:

**Production environment:**
```shell
php artisan app:import-organisations
```

**Local development (using DDEV):**
```shell
ddev artisan app:import-organisations
```

### File Organization Requirements

All import files must be organised in a specific directory structure within `/storage/app/private/organisations/import/`:
1. **Create a dedicated _subfolder_** for each data provider (e.g., `Data_Provider_Name`)
2. **Place the Excel file** (`.xlsx` format) inside this _subfolder_ - the filename itself doesn't matter, only the extension
3. **Include organisation logos** (optional) in the same _subfolder_, as `.png` or `.webp` files with appropriate names

### Logo Naming Convention
**Important:**: Logo files must be named using a specific pattern derived from the organisation name in the Excel file:
- **Naming pattern:** Convert the organisation name to a slug format using underscores
    - Names should be in lower-case
    - Spaces and special characters should be replaced with underscores
- **Supported formats:** `.png`, `.webp`

For example, if the organisation name is "Example Tech Company", the logo should be named `example_tech_company.png` (or `example_tech_company.webp`).

#### Directory Structure Example

```
storage/app/private/organisations/import/
└── Data_Provider_Name/
    ├── organisation_data.xlsx
    ├── example_tech_company.png
    ├── another_organisation.webp
    └── third_company_ltd.png
```

### The Import Process
1. Organise your files according to the structure above
2. Ensure logo filenames match the converted organisation names from your Excel file
3. Run the appropriate artisan command for your environment
4. Follow the interactive prompts to complete the import
5. Consecutive imports are designed to be non-destructive (existing data should be preserved)

**Logo Import Notes:**
- Logo files are copied only if they don't exist or if the file size differs from existing ones
- Missing logos will generate warnings in the import logs but won't stop the import process
- Each logo gets a unique UUID when stored in the media storage

The subfolder structure is mandatory because logos must be co-located with their corresponding Excel data file for proper association during import.

### Geocoding Cache Management

The application maintains a 90-day geocoding cache for performance. You can manage this cache with:

```shell
# Clear expired geocoding cache (90+ days old)
ddev artisan app:clear-expired-geocoding-cache

# Test geocoding service availability
ddev artisan tinker --execute="echo Geocoder::isGeocodingAvailable() ? 'Available' : 'Unavailable';"

# Test geocoding with country bias
ddev artisan tinker --execute="print_r(Geocoder::getCoordinates('Address', 'country_code', 1));"
```
