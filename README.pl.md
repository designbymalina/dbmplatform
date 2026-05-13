# DBM Platform - gotowa baza aplikacji oparta na DBM Framework

DBM Platform to gotowa baza aplikacji webowej oparta na DBM Framework, zaprojektowana do szybkiego uruchamiania CMS, paneli administracyjnych i aplikacji modułowych.

Pozwala szybko rozpocząć projekt bez budowania panelu administracyjnego, systemu użytkowników i podstawowej infrastruktury od zera.

## Dla kogo?

Platforma sprawdzi się gdy:

- chcesz szybko uruchomić projekt
- potrzebujesz panelu administracyjnego
- budujesz CMS, portal lub aplikację webową
- chcesz rozwijać system modułowo

## Dlaczego DBM Platform?

Platforma pozwala rozpocząć projekt szybciej niż budowanie aplikacji od zera, zachowując pełną kontrolę nad architekturą i kodem źródłowym.

W przeciwieństwie do ciężkich systemów CMS:  

- nie narzuca zamkniętej struktury
- może działać bez rozbudowanej infrastruktury
- wspiera rozwój modułowy
- wykorzystuje lekki runtime DBM Framework  

## Co zawiera platforma?

### Podstawowe funkcje

- logowanie i rejestracja użytkowników
- panel administracyjny
- zarządzanie modułami
- system budowy stron
- routing i middleware
- system szablonów
- filesystem i upload plików

## Wersje platformy

### CMS Lite

Minimalna wersja oparta na plikach i szablonach.

### Base (CMS Lite + Admin)

Rozszerzenie o:

- panel administracyjny
- użytkowników
- moduły
- zarządzanie aplikacją

Platforma wspiera instalację dodatkowych modułów.  

## Podgląd platformy

DBM Platform to modułowe środowisko aplikacyjne i CMS zbudowane na bazie DBM Framework.

Łączy wysoką wydajność z lekką warstwą administracyjną, wbudowaną obsługą API oraz elastycznymi narzędziami do zarządzania treścią.

Platforma została zaprojektowana z myślą o nowoczesnych aplikacjach internetowych, które wymagają szybkości, skalowalności i pełnej kontroli nad architekturą.

⭐ Jeśli projekt Ci się podoba, zostaw gwiazdkę na GitHubie.

### Panel administracyjny

![DBM Platform Admin](https://dbm.org.pl/images/page/packages/dbm-cmslite-admin.png)

## Instalacja

DBM Platform może działać zarówno jako gotowy system CMS jak i fundament pod własne aplikacje PHP.

**Dostępne są dwa sposoby instalacji:**  

- instalacja manualna - dla hostingu i szybkiego uruchomienia
- instalacja developerska - dla pracy z Git i Composer

**DBM Platform może działać:**  

- jako niezależny runtime
- lub z pełnym wsparciem Composer

### Instalacja manualna

Najprostszy sposób uruchomienia DBM Platform.

Polecany dla:

- hostingu współdzielonego
- prostych wdrożeń
- CMS Lite
- użytkowników bez środowiska developerskiego

#### Kroki instalacji

1. Pobierz archiwum projektu z GitHub
2. Rozpakuj pliki na serwer
3. Skopiuj `.env.example` jako `.env`
4. Ustaw podstawową konfigurację:

```env
APP_URL="https://twoja-domena.pl/"
APP_NAME="DBM Platform"
APP_EMAIL="admin@domain.com"
```

5. Skieruj domenę na katalog `public/`

6. Jeśli serwer wymaga, nadaj prawa zapisu dla katalogów: `data/`, `storage/`, `var/`.

7. Otwórz aplikację w przeglądarce i zakończ konfigurację środowiska.

---

### Instalacja developerska

Instalacja przeznaczona dla programistów pracujących z Git i Composer.

#### Pobranie projektu

```bash
git clone https://github.com/designbymalina/dbmplatform
cd dbmplatform
```

#### Konfiguracja środowiska

```bash
cp .env.example .env
```

#### Uruchomienie lokalne

```bash
php -S localhost:8000 -t public
```

Aplikacja będzie dostępna pod: `http://localhost:8000`

#### Composer (opcjonalnie)

Platforma domyślnie jest niezależna i funkcjonuje bez Composera.

Composer jest zalecany przy większych projektach i dodatkowych pakietach.

Opcjonalnie (nie jest wymagane na starcie):

```bash
composer install
```

Instalacja utworzy autoloading Composer oraz pobierze wszystkie zależności.

Po przejściu na Composer część bibliotek może być zarządzana bezpośrednio przez Composer zamiast katalogu `libraries/`.

### Konfiguracja środowiska

#### Document root

W środowisku produkcyjnym domena powinna wskazywać na katalog: `/public`.

#### Apache / localhost

W środowisku lokalnym może być wymagana konfiguracja `.htaccess` oraz dyrektywy `RewriteBase`.

Jeśli korzystasz z lokalnego środowiska (localhost), skopiuj plik `.htaccess` z katalogu `_Documents/_Server/` do głównego folderu projektu. Następnie w obu plikach - w katalogu głównym oraz `public/.htaccess` - dostosuj dyrektywę **RewriteBase** do ścieżki uruchomieniowej aplikacji.

Na serwerze zdalnym upewnij się, że **open_basedir** nie blokuje dostępu do katalogów aplikacji.

#### Cache

Po zakończeniu konfiguracji zaleca się ustawienie:

```env
CACHE_ENABLED=true
```

**Ważne** Podczas instalacji modułów cache powinien być wyłączony: `CACHE_ENABLED=false`.

#### Prawa zapisu

DBM Platform wymaga praw zapisu dla katalogów: `var/`, `storage/`, `data/`.

## Architektura

DBM Platform działa jako warstwa aplikacyjna nad DBM Framework.

Framework odpowiada za: runtime, routing, middleware, DI, infrastrukturę.

Platforma dostarcza gotowe moduły aplikacyjne i panel administracyjny.

## Struktura

- `bin/` - pliki wykonywalne: interfejs konsolowy (CLI) oraz worker (punkt wejścia: bin/dbm)  
- `bootstrap/` - rdzeń frameworka (Routing, DI, API)  
- `libraries/` - zewnętrzne biblioteki (PSR, PHPMailer, Guzzle)  
- `modules/` - moduły platformy (instalator, system zarządzania treścią, auth, admin)
- `public/` - pliki publiczne (root domeny)  
- `src/` - logika aplikacji: kontrolery, serwisy, modele, usługi  
- `storage/` - przechowuje pliki generowane przez aplikację (cache)  
- `templates/` - szablony widoków  
- `tests/` - testy jednostkowe  
- `translations/` - pliki tłumaczeń (opcjonalny)  
- `var/` - cache i logi (tworzone automatycznie, wymagane prawa do zapisu)  
- `vendor/` - biblioteki zainstalowane przez Composera (tworzone automatycznie)  
- `.env.example` - przykładowa konfiguracja środowiskowa  

## Rozszerzona struktura projektu

- `_Documents/` - dokumentacja, archiwum instalacji modułów  
- `data/` - dane i pliki (wymagane prawa do zapisu)  
- `config/` - pliki configuracji (opcjonalne, np. php.ini)  
- `frontend/` - frontend (opcjonalnie React.js lub Vue.js, Node.js, Webpack)  

## Hybrid Autoloading

DBM Platform posiada własny hybrydowy mechanizm autoloadingu.

System może działać:  

- całkowicie niezależnie od Composer
- z wewnętrznym autoloadingiem PSR-4
- lub z pełnym wsparciem Composer

Pozwala to uruchamiać aplikacje zarówno na prostym hostingu współdzielonym, jak i w pełnym środowisku developerskim.

Dzięki temu DBM Platform może działać jako:

- lekki CMS bez dodatkowych zależności
- klasyczna aplikacja Composer
- lub hybrydowy projekt łączący oba podejścia

## Routing

Klasyczny routing definiujesz w pliku: `bootstrap/web.php`.

Przykład:

```shell
$router->get('/path', [NameController::class, 'methodName'], 'route_name');
```

REST API Routing definiujesz w pliku: `bootstrap/api.php`.

Przykład:  

```shell
$router->get('/api/path', [NameApiController::class, 'methodName'], 'api_route_name');
```

## Silnik szablonów

DBM Framework domyślnie korzysta z lekkiego silnika **DbM View Engine**.

Cechy:

- brak dodatkowego DSL
- składnia oparta bezpośrednio o PHP
- wysoka wydajność
- możliwość rozszerzania przez callbacki i helpery

Szablony znajdują się w katalogu `templates/`.

Silnik może zostać zastąpiony inną implementacją (np. Twig).

## Konsola poleceń

Lekki i szybki CLI do zadań CRON i DEV. Zapewnia prosty sposób uruchamiania zadań w tle lub zadań konserwacyjnych bezpośrednio z wiersza poleceń z lekką i niezależną implementacją. Polecenia konsoli są wykonywane za pośrednictwem pliku: `bin/dbm`.

Dostępne polecenia:  

```bash
php bin/dbm list
php bin/dbm command example (for ExampleCommand)
php bin/dbm worker example (for ExampleWorker)
```

## Stack

- PHP 8.1+
- DBM Framework v6
- PSR-4 / PSR-11 / PSR-12
- Modular architecture
- Middleware pipeline
- Hybrid autoloading
- File-based architecture
- File-based CMS + optional database

## Dokumentacja

### Pierwsze kroki  
[Wprowadzenie i architektura](_Documents/_Docs/pl/01-getting-started/01-introduction.md)  
[Szybki start](_Documents/_Docs/pl/01-getting-started/02-quick-start.md)  

---

### Podstawowe koncepcje  
[Kontrolery i serwisy](_Documents/_Docs/pl/02-core-concepts/01-controllers-and-services.md)  
[Dependency Injection](_Documents/_Docs/pl/02-core-concepts/02-dependency-injection.md)  
[Zmienne środowiskowe (.env)](_Documents/_Docs/pl/02-core-concepts/03-environment-variables.md)  
[Komendy CLI](_Documents/_Docs/pl/02-core-concepts/04-console-commands.md)  
[Tłumaczenia (Localization)](_Documents/_Docs/pl/02-core-concepts/05-localization.md)  

---

### HTTP i Routing  
[Routing Web (web.php)](_Documents/_Docs/pl/03-http-and-routing/01-web-routing.md)  
[Routing API (api.php)](_Documents/_Docs/pl/03-http-and-routing/02-api-routing.md)  
[Request](_Documents/_Docs/pl/03-http-and-routing/03-request.md)  
[Response](_Documents/_Docs/pl/03-http-and-routing/04-response.md)  
[Middleware](_Documents/_Docs/pl/03-http-and-routing/05-middleware.md)  
[API Client](_Documents/_Docs/pl/03-http-and-routing/06-api-client.md)  
[API Endpointy i autoryzacja](_Documents/_Docs/pl/03-http-and-routing/07-api-endpoints-and-authentication.md)  
[Sesje](_Documents/_Docs/pl/03-http-and-routing/08-session.md)  
[Cookies](_Documents/_Docs/pl/03-http-and-routing/09-cookies.md)  

---

### Dane i prezentacja  
[Baza danych](_Documents/_Docs/pl/04-data-and-presentation/01-database.md)  
[Szablony (Templates)](_Documents/_Docs/pl/04-data-and-presentation/02-templates.md)  
[Template Features](_Documents/_Docs/pl/04-data-and-presentation/03-template-feature.md)  
[Template Engine](_Documents/_Docs/pl/04-data-and-presentation/04-template-engine.md)  
[Budowanie pierwszej funkcjonalności](_Documents/_Docs/pl/04-data-and-presentation/05-building-first-feature.md)  

---

### Walidacja  
[System walidacji](_Documents/_Docs/pl/05-validation/01-validation-system.md)  

---

### Infrastruktura  
[Logowanie (Logger)](_Documents/_Docs/pl/06-infrastructure/01-logging.md)  

---

### Moduły  
[Tworzenie modułów](_Documents/_Docs/pl/07-modules/01-creating-modules.md)  

## Wsparcie projektu

Jeśli korzystasz z DBM Platform, rozważ pozostawienie informacji o projekcie w stopce aplikacji.

Pomaga to wspierać rozwój frameworka i ekosystemu DBM.  

## Licencja

DBM Framework udostępniany jest na licencji MIT.

Wybrane elementy DBM Platform, moduły oraz komponenty mogą podlegać osobnym warunkom licencyjnym.

Szczegóły:  

- `/LICENSE`
- `/LICENSE_DBM_PLATFORM.txt`

Copyright (c) Design by Malina
