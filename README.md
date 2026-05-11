# Beirão Fusion

A mobile-first recipe-sharing social platform built around Licor Beirão. Users can browse and share cocktail and dessert recipes, like, comment, follow other users, and unlock vouchers through engagement challenges.

**Stack:** PHP 8.2 · MySQL 8 · Bootstrap 5 · Vanilla JS

---

## Running locally (Docker)

The only prerequisite is [Docker Desktop](https://www.docker.com/products/docker-desktop/).

```bash
docker-compose up --build
```

Open **http://localhost:8080** in your browser.

On first launch the seed script runs automatically, creating demo content and copying the recipe images. This takes about 10 seconds.

| Account | Email | Password |
|---------|-------|----------|
| Admin | admin@beiraofusion.pt | admin123 |
| Regular user | joana@example.com | user123 |

Other regular user accounts: `carlos@example.com`, `filipa@example.com`, `ines@example.com`, `jose@example.com` (all with password `user123`).

---

## Deploying to Railway

1. Create a new Railway project and add a **MySQL** plugin.
2. In your web service settings, add this environment variable to override the Railway-generated database name:
   ```
   MYSQLDATABASE=beirao_fusion
   ```
3. Connect to your Railway MySQL instance (via the Railway CLI or a MySQL GUI like TablePlus) and run:
   ```
   source setup/railway-schema.sql
   ```
4. Deploy the web service — Railway will use the Dockerfile automatically.
5. After the first deploy, open a Railway shell in the web service and run:
   ```bash
   php setup/seed.php
   ```

---

## Project structure

```
api/          AJAX endpoints (likes, bookmarks, comments, follows)
components/   Reusable PHP partials included by page files
connections/  Database connection helper
css/          Global stylesheet
imgs/         Static images (recipe photos, profile pictures, logos)
js/           Client-side scripts
scripts/      Form-action handlers (login, register, update profile…)
setup/        seed.php (demo data) · railway-schema.sql (Railway import)
site/         Page entry points
```
