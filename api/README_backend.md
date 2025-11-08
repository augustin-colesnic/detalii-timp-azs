PHP + SQLite backend for default message

Files created:
- `config.php` - configuration template (admin_token + db_path). Keep this secret and move outside web root if possible.
- `init_db.php` - run once (in browser or CLI) to create DB and initial message.
- `api.php` - endpoint: GET action=get, POST action=set (admin-only).
- `.htaccess` and `data/.htaccess` - basic Apache protections to deny access to config and sqlite files.

Deployment steps (shared hosting, Apache):
1. Upload the `api/` folder into your `timp-tulcea` folder on the host (so it becomes `/timp-tulcea/api/`).
2. Edit `api/config.php` and change `admin_token` to a long random string.
3. Preferred: move `config.php` and the SQLite DB into the host's `cgi-bin` folder (or another directory outside the document root) if available.
	- For many shared hosts the site layout has `cgi-bin` as a sibling of your site folder (e.g. `/home/username/cgi-bin`). If you place `config.php` and the DB there, `api.php` will automatically pick it up.
	- If you don't know the cgi-bin path, check your hosting control panel or ask support. If the cgi-bin is inside `public_html` then it does not help for secrecy and you should keep `.htaccess` protections.
4. If you moved `config.php`/DB, update the `db_path` in the copied `config.php` or leave as-is if you placed the DB in the cgi-bin and `config.php` points to it.
4. Run `api/init_db.php` once (open in browser) to create the SQLite DB. Remove or protect `init_db.php` afterwards.
5. Test GET: https://your-site/timp-tulcea/api.php?action=get
6. To update default (admin): POST JSON to https://your-site/timp-tulcea/api/api.php?action=set with header `X-Admin-Token: <your token>` and body `{ "message": "New default" }`.

Notes & security:
- Do NOT expose `admin_token` in client-side code. Use server-side admin pages or tooling that keep the token secret.
- The `.htaccess` files help protect `config.php` and the SQLite DB, but on some hosts you should also set file permissions and/or put secrets outside web root.
- Consider enabling HTTPS and restricting Access-Control-Allow-Origin in `api.php` to your domain.
