# 📄 Generator Umów Kupna-Sprzedaży (UKS)

Aplikacja webowa umożliwiająca generowanie, podpisywanie online i zarządzanie umowami kupna-sprzedaży (Pojazdy, Ubrania, Elektronika). System generuje pliki PDF z podpisami cyfrowymi i wysyła je automatycznie na e-mail.

## Wymagania systemowe

Aby uruchomić projekt lokalnie, potrzebujesz:
* **XAMPP** (z PHP w wersji 8.0 lub nowszej)
* **Composer** (do instalacji bibliotek)
* **Git**

---

##Instrukcja instalacji

### 1. Pobranie projektu
Otwórz terminal w folderze `htdocs` (np. `C:\xampp\htdocs`) i wpisz:


git clone [https://github.com/1Stazy/uks.git](https://github.com/1Stazy/uks.git) UKS_App
cd UKS_App


### 2. Instalacja bibliotek
Projekt wykorzystuje `mPDF` do generowania dokumentów oraz `PHPMailer` do wysyłki e-maili. Zainstaluj je komendą:




> **Uwaga:** Jeśli wystąpi błąd o braku rozszerzeń, upewnij się, że w pliku `php.ini` (w panelu XAMPP: Config -> PHP (php.ini)) odkomentowane są linie: `extension=gd` oraz `extension=zip`.

### 3. Konfiguracja bazy danych
1. Uruchom panel **XAMPP** i włącz **Apache** oraz **MySQL**.
2. Wejdź na [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Zaimportuj plik SQL znajdujący się w folderze projektu: `sql/schema.sql`.
   * To automatycznie utworzy bazę `uks_db` i wymagane tabele.

### 4. Konfiguracja połączenia i E-maila
Projekt posiada plik wzorcowy. Należy utworzyć własną konfigurację:

1. Zmień nazwę pliku `config.sample.php` na `config.php`.
2. Otwórz `config.php` i uzupełnij dane:


// Baza danych (domyślne dla XAMPP)
define('DB_USER', 'root');
define('DB_PASS', '');

// Konfiguracja SMTP (np. Gmail)
define('SMTP_USER', 'twoj@gmail.com');
define('SMTP_PASS', 'twoje_haslo_aplikacji'); // Hasło aplikacji (nie hasło do konta Google!)


### 5. Podpis Administratora
Aby na umowach pojawiał się podpis kupującego (administratora), wgraj plik PNG ze swoim podpisem do folderu:
`public/img/admin_sign.png`

---

## Dane logowania

Domyślne konto administratora (zdefiniowane w bazie):
* **Login:** Admin
* **Hasło:** Admin
* **Panel:** [http://localhost/UKS_App/login.php](http://localhost/UKS_App/login.php)

---

## Funkcjonalności

* - **3 typy umów:** Auta, Ubrania, Elektronika (z dynamicznymi polami).
* - **Podpis cyfrowy:** Rysowanie podpisu na ekranie (Canvas).
* - **Generowanie PDF:** Automatyczne tworzenie dokumentu prawnego.
* - **Powiadomienia E-mail:** Wysyłka PDF po akceptacji umowy (PHPMailer).
* - **Panel Admina:** Akceptacja/Odrzucanie umów, podgląd PDF, archiwum, rękojmia.
* - **Bezpieczeństwo:** Honeypot, blokada czasowa (anty-bot), bezpieczne hasła.

---

## Autor
Zespół projektowy nr 4
