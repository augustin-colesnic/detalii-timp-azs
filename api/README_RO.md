PHP + SQLite pentru mesajul implicit (soluție pentru shared hosting)

Această mapă conține un backend minim PHP + SQLite care păstrează un "mesaj implicit" setat de administrator. Aplicația client (front-end) va cere acest mesaj la pornire dacă utilizatorul nu are un suprascris local în localStorage.

Fișiere principale:
- `config.php` - șablon de configurație (setați `admin_token` și `db_path`). Păstrați acest fișier în siguranță (ideal în `cgi-bin` sau în afara `public_html`).
- `init_db.php` - rulați o singură dată în browser pentru a crea baza de date SQLite și rândul inițial.
- `api.php` - endpoint HTTP: `GET action=get` (citire publică), `POST action=set` (actualizare admin, necesită `X-Admin-Token`).
- `.htaccess` și `data/.htaccess` - reguli Apache pentru a bloca accesul direct la fișiere sensibile.

Pași de instalare specifici pentru shared hosting (folosind folderul protejat `cgi-bin`)

1) Determinați calea absolută a `cgi-bin` (dacă nu o știți deja)
- Dacă aveți acces la panoul de hosting (cPanel etc.) verificați `/home/<username>/cgi-bin` sau similar.
- Alternativ, încărcați temporar `api/tmp_whereami.php` și deschideți-l în browser; acesta va afișa ceva de genul:
  __DIR__: /home/inspicio/azstulcea.ro/timp-tulcea/api
  __FILE__: /home/inspicio/azstulcea.ro/timp-tulcea/api/tmp_whereami.php

2) Plasați `config.php` și (opțional) baza de date în `cgi-bin`
- Creați un fișier `/home/<user>/cgi-bin/config.php` (în cazul dvs. `/home/inspicio/azstulcea.ro/cgi-bin/config.php`) cu conținut similar:

```php
<?php
return [
  // exemplu: puneți baza de date în cgi-bin/data
  'db_path' => '/home/inspicio/azstulcea.ro/cgi-bin/data/default_message.sqlite',
  'admin_token' => 'PUNE_AICI_UN_TOKEN_LUNG_SI_RANDOM',
];
```

- Notă: dacă preferați să păstrați DB în altă parte, actualizați `db_path` corespunzător.

3) Inițializați baza de date (doar o dată)
- Accesați în browser (o singură dată):
  https://www.azstulcea.ro/timp-tulcea/api/init_db.php
- Ar trebui să vedeți un mesaj de tipul:
  DB initialized at /home/inspicio/azstulcea.ro/cgi-bin/data/default_message.sqlite
- După ce inițializarea reușește, ștergeți imediat `init_db.php` (sau mutați-l în afara webroot-ului).

4) Permisiuni fișiere
- Dacă aveți SSH, setați permisiuni restrictive:

```bash
chmod 600 /home/inspicio/azstulcea.ro/cgi-bin/config.php
chmod 600 /home/inspicio/azstulcea.ro/cgi-bin/data/default_message.sqlite
chmod 700 /home/inspicio/azstulcea.ro/cgi-bin/data
```

- Dacă nu aveți SSH, folosiți File Manager din cPanel / interfața hostului și setați permisiunile astfel încât fișierele să fie accesibile doar proprietarului.

5) Teste rapide (smoke tests)
- GET default (ar trebui să returneze JSON cu mesajul inițial):

```pwsh
curl "https://www.azstulcea.ro/timp-tulcea/api/api.php?action=get"
```

- POST pentru a schimba mesajul (înlocuiți token-ul):

```pwsh
curl -X POST "https://www.azstulcea.ro/timp-tulcea/api/api.php?action=set" `
  -H "Content-Type: application/json" `
  -H "X-Admin-Token: PUNE_AICI_TOKENUL_TAU" `
  -d '{"message":"Salutare de la admin!"}'
```

6) Securitate și bune practici
- Nu includeți `admin_token` în codul client-side (JS/browser). Token-ul trebuie păstrat doar pe server sau în instrumente administrative server-side.
- După implementare, ștergeți fișierele temporare (`tmp_whereami.php`, `init_db.php`) din `api/` și orice `whereami.php` pus în `cgi-bin`.
- Restricționați CORS în `api/api.php` la domeniul dvs. în producție, înlocuind blocul permissiv de Access-Control-Allow-Origin cu:

```php
header('Access-Control-Allow-Origin: https://www.azstulcea.ro');
```



## Probleme comune și depanare
- Dacă `api.php` returnează `config_not_found`, verificați că ați copiat `config.php` în calea corectă și că `api.php` poate citi acel fișier.
- Dacă vedeți erori legate de permisiuni la deschiderea SQLite, reveniți temporar la permisiuni mai permisive (0640 sau 0644) și testați, apoi încercați din nou 0600/640 pentru a găsi combinația care funcționează cu configurația PHP a hostului.

### Exemplu de flux
- Administratorul editează mesajul folosind curl sau o pagină `admin.php` server-side.
- Clienții la pornire: front-end (în `main.js`) cere `GET /api/api.php?action=get`. Dacă utilizatorul are `localStorage` cu un text personalizat, front-end îl folosește pe acela; altfel folosește mesajul de la server.

