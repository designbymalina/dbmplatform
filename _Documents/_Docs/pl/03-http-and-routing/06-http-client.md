# Klient HTTP do komunikacji z API

## Przegląd

Framework DBM zapewnia lekką i rozszerzalną abstrakcję klienta HTTP do komunikacji z usługami zewnętrznymi i interfejsami API REST.

Sam framework zawiera jedynie ogólny kontrakt HTTP i domyślną implementację opartą na rozszerzeniu PHP cURL. **Nie** jest zależny od żadnych zewnętrznych bibliotek HTTP.

Aplikacje zbudowane na bazie DBM Framework (na przykład DBM Platform) mogą zastąpić domyślną implementację innym klientem HTTP, takim jak Guzzle, rejestrując inną usługę w kontenerze zależności.

---

## Architektura

Warstwa HTTP składa się z następujących komponentów:

```
HttpClientInterface
        │
CurlHttpClient
        │
HttpResponse
```

Aplikacje mogą udostępniać własną implementację `HttpClientInterface`.

---

## Komponenty

### HttpClientInterface

Wspólny kontrakt używany przez wszystkie implementacje klienta HTTP.

```php
namespace Dbm\Http\Contracts;

interface HttpClientInterface
{
    public function request(string $method, string $url, array $options = []): HttpResponseInterface;

    public function get(string $url, array $options = []): HttpResponseInterface;

    public function post(string $url, array $options = []): HttpResponseInterface;

    public function put(string $url, array $options = []): HttpResponseInterface;

    public function delete(string $url, array $options = []): HttpResponseInterface;
}
```

---

### CurlHttpClient

Domyślny klient HTTP dołączony do DBM Framework.

Funkcje:

- brak zależności zewnętrznych
- oparty na rozszerzeniu PHP cURL
- obsługa żądań JSON
- konfigurowalne nagłówki
- konfigurowalny limit czasu
- obsługa logowania PSR-3

---

### HttpResponseInterface

Wszystkie żądania zwracają wspólny obiekt odpowiedzi implementujący `HttpResponseInterface`.

Dostępne typowe informacje:

- kod stanu HTTP
- treść odpowiedzi
- nagłówki odpowiedzi

---

## Opcje żądania

Klient akceptuje opcjonalną tablicę `$options`.

Przykład:

```php
$options = [
    'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer your-token',
    ],
    'json' => [
        'name' => 'John',
    ],
    'timeout' => 15,
];
```

Obsługiwane opcje:

| Opcja | Opis |
|--------|------|
| headers | Dodatkowe nagłówki HTTP |
| json | Automatycznie kodowane ciało JSON |
| timeout | Limit czasu żądania w sekundach |
| auth | Uwierzytelnienie Basic Auth (`[login, hasło]`) |
| verify | Włącza lub wyłącza weryfikację certyfikatu SSL |
| follow_redirects | Automatyczne podążanie za przekierowaniami HTTP |

---

## Podstawowe użycie

```php
use Dbm\Http\Contracts\HttpClientInterface;

final class ExampleService
{
    public function __construct(
        private readonly HttpClientInterface $http
    ) {
    }

    public function users(): array
    {
        $response = $this->http->get(
            'https://api.example.com/users'
        );

        return json_decode($response->body(), true);
    }
}
```

---

## Żądanie POST

```php
$response = $http->post(
    'https://api.example.com/orders',
    [
        'json' => [
            'product_id' => 10,
            'quantity' => 2,
        ],
    ]
);
```

## Żądanie PUT

```php
$response = $http->put(
    'https://api.example.com/orders/15',
    [
        'json' => [
            'status' => 'completed',
        ],
    ]
);
```

## Żądanie DELETE

```php
$response = $http->delete(
    'https://api.example.com/orders/15'
);
```

---

## Niestandardowe nagłówki

```php
$response = $http->get(
    'https://api.example.com/profile',
    [
        'headers' => [
            'Authorization' => 'Bearer token',
            'Accept' => 'application/json',
        ],
    ]
);
```

---

## Obsługa błędów

Domyślna implementacja nigdy nie zgłasza wyjątków HTTP dla nieudanych kodów statusu.

Zawsze weryfikuj zwrócony kod statusu.

```php
$response = $http->get($url);

if ($response->statusCode() !== 200) {
    // handle error
}
```

Nieoczekiwane błędy w czasie wykonywania (na przykład awarie sieci) powinny być obsługiwane za pomocą `try/catch`.

```php
try {
    $response = $http->get($url);
} catch (\Throwable $e) {
    // log error
}
```

---

## Logowanie

`CurlHttpClient` obsługuje opcjonalne logowanie PSR-3.

Logowanie można włączyć lub wyłączyć poprzez konfigurację aplikacji.

Typowe wpisy w logu obejmują:

- metodę HTTP
- adres URL
- kod odpowiedzi HTTP
- czas wykonania żądania
- błędy transportu

---

# Korzystanie z Guzzle (DBM Platform)

Framework DBM celowo **nie ma zależności** od Guzzle.

Aplikacje mogą rejestrować własną implementację interfejsu HttpClientInterface.

Na przykład Platforma DBM w wersji Admin udostępnia:

```
App\Infrastructure\Http\GuzzleHttpClient
```

który wewnętrznie używa:

```
guzzlehttp/guzzle
```

Implementacja jest rejestrowana poprzez kontener zależności aplikacji i transparentnie zastępuje domyślny `CurlHttpClient`.

Na przykład:

```php
HttpClientProvider::register($container);
```

Ponieważ usługi zależą tylko od `HttpClientInterface`, nie ma potrzeby zmiany kodu aplikacji.

Przykład:

```php
final class CurrencyRateProvider
{
    public function __construct(
        private readonly HttpClientInterface $http
    ) {
    }
}
```

Dostawca działa identycznie niezależnie od tego, czy aplikacja używa:

- CurlHttpClient
- GuzzleHttpClient
- dowolnej niestandardowej implementacji

---

## Filozofia projektowania

Framework DBM zapewnia jedynie abstrakcję HTTP.

**NIE** zawiera:

- Guzzle
- Symfony HTTP Client
- żadnej zewnętrznej biblioteki sieciowej

Dzięki temu framework jest lekki, wolny od zależności i odpowiedni dla każdego typu aplikacji.

Aplikacje zachowują swobodę wyboru implementacji protokołu HTTP, która najlepiej odpowiada ich potrzebom.

### Wybór implementacji (DBM Platform)

Aplikacja może wybierać implementację klienta HTTP za pomocą zmiennej środowiskowej:

```bash
HTTP_CLIENT_DRIVER=auto
```

### Logowanie HTTP

Logowanie komunikacji HTTP można włączyć poprzez zmienną środowiskową:

```bash
HTTP_CLIENT_LOG=true
```

Po jej włączeniu klient zapisuje informacje diagnostyczne zgodnie z PSR-3.

Domyślnie logowanie HTTP jest wyłączone.

---

## Wymagania

### Domyślna implementacja

- PHP
- rozszerzenie cURL

### Implementacje niestandardowe

Może wymagać dodatkowych pakietów Composera w zależności od wybranego klienta HTTP.

---

## Podsumowanie

Framework DBM zapewnia:

- abstrakcję klienta HTTP
- domyślną implementację bez zależności
- ujednolicony interfejs odpowiedzi
- obsługę rejestrowania PSR-3
- łatwą wymianę poprzez wstrzykiwanie zależności
- zgodność z niestandardowymi klientami HTTP, takimi jak Guzzle

---
