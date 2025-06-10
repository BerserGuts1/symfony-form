
# Smartiveapp Form

Projekt umożliwia wygenerowanie miniatury obrazu z pliku i zapisanie jej w wybranym miejscu — na FTP albo lokalnie. Użytkownik podaje źródłowy plik, nazwę docelową i sposób zapisu. Całość działa przez prostą komendę CLI:

```bash
php bin/console thumbnail:generate <source> <filename> <storage>
```

Pod spodem:

Plik jest sprawdzany i walidowany (np. rozszerzenie, istnienie), obraz jest skalowany przez ImageResizerInterface, wynik trafia do jednego z dwóch typów Storage: lokalnego albo FTP (oba mają obsługę konfliktów nazw)

## Jak uruchomić aplikację

1. Zainstaluj zależności:

```bash
composer install
```

2. Utwórz plik .env w katalogu głównym projektu i dodaj konfigurację FTP:

```bash
FTP_HOST=localhost
FTP_USER=user
FTP_PASSWORD=pass
FTP_DIR=/uploads
```

## Co robi aplikacja
Aplikacja udostępnia komendę:
```bash
php bin/console thumbnail:generate <source> <filename> <storage>
```

- source – ścieżka do pliku graficznego (np. assets/image.jpg)

- filename – nazwa pliku wyjściowego (np. output.jpg)

- storage – local lub ftp

---
## Działanie:

Plik źródłowy jest weryfikowany, miniatura jest generowana przez pakiet Intervention Image.\
Wynik zapisywany jest lokalnie lub na FTP. \
Jeśli plik już istnieje, generowana jest nowa, unikalna nazwa (np. plik-1.jpg)

## Dlaczego Intervention Image?
Używam pakietu intervention/image jako implementacji ImageResizerInterface. Jest to sprawdzona i popularna biblioteka do manipulacji obrazami w PHP. Oferuje:

- prosty i czytelny API (np. resize()->save())

- wsparcie dla GD i Imagick (działa w różnych środowiskach)

- możliwość manipulacji formatami, jakością i orientacją

- aktywne wsparcie i dobra dokumentacja   

## Testy:

Projekt zawiera:

- testy komendy GenerateThumbnailCommand z użyciem CommandTester

- testy LocalStorage (zapis do tymczasowego katalogu)

- test integracyjny FtpStorage (rzeczywiste połączenie FTP)

- walidacje rozszerzeń, nazw, typu storage

## Wymagania
- PHP 8.1 lub nowszy

- Composer

- PHPUnit

- (do testu FTP) działający serwer FTP z dostępem zapisu

