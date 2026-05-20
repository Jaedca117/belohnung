# Belohnungsbarometer

Ein einfaches PHP-Projekt für ein Belohnungsbarometer mit zwei Kinderprofilen.

## Start lokal

```bash
php -S localhost:8000
```

Danach im Browser öffnen: <http://localhost:8000>

## Einrichtung

1. `index.php` und zwei Bilder (z. B. `kind1.jpg`, `kind2.jpg`) in denselben Ordner legen.
2. In `index.php` im Array `$children` Namen und Dateinamen der Fotos anpassen.
3. Seite im Browser öffnen.

## Hinweise

- Der Stand wird pro Browser lokal gespeichert (`localStorage`).
- Keine Datenbank erforderlich.
