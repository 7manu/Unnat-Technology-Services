# Unnat Technology Services

Public marketing website plus a full admin control centre: every word, link and image on
the site is editable from the browser, alongside SEO configuration, page creation, a blog
and a backlink/keyword tracker.

The stack is unchanged — semantic HTML, one CSS design system, progressive JavaScript and
PHP/MySQL. There is no build step and no third-party framework.

## Local preview

```sh
php -S 127.0.0.1:8080
```

Then open `http://127.0.0.1:8080/`. The PHP built-in server does not read `.htaccess`, so
during local preview use `index.php` and `contact.php` directly.

## Database configuration

Set `UTS_DB_HOST`, `UTS_DB_USER`, `UTS_DB_PASSWORD` and `UTS_DB_NAME` in the server
environment. On hosts where environment variables are unavailable, copy
`backend/_conn.example.php` to `backend/_conn.local.php` and enter the credentials there.
The local config file is ignored by Git.

Any database password previously committed to the repository should be rotated.

### Tables

Nothing needs to be imported by hand. The first time you open `admin.php` after signing
in, `cms_install()` creates every missing table and seeds it:

| Table | Holds |
| --- | --- |
| `cms_content` | Every editable string on the public site, keyed by its place |
| `cms_settings` | Global SEO, analytics, schema, sitemap and blog settings |
| `cms_nav` | Header and footer menu links |
| `cms_pages` | Pages created from the admin panel |
| `cms_posts` | Blog articles |
| `cms_seo` | Per-route meta, Open Graph, Twitter, schema and sitemap settings |
| `cms_keywords` | Keyword planner and rank tracker |
| `cms_backlinks` | Backlink register |
| `cms_redirects` | 301/302/410 rules applied before a page renders |
| `cms_media` | Uploaded images |
| `cms_admins` | Administrator accounts (bcrypt hashes) |
| `cms_audit` | Change log shown in Activity log |

The existing `products` and `query` tables are untouched. Use **Dashboard → Re-check
database & content keys** after deploying an update that adds new content keys.

## Administrator sign-in

Open `/login.php`.

| Field | Value |
| --- | --- |
| Mobile number (ID) | `9818059661` |
| Password | `UTS@Admin#2026` |

Change it immediately from **Admin accounts → Change your own password**. The password is
stored only as a bcrypt hash, so it can be replaced but never read back.

To override the built-in fallback on the server instead, set `UTS_ADMIN_MOBILE` and
`UTS_ADMIN_PASSWORD_HASH`; generate the hash with PHP's `password_hash()`.

## Admin panel

`admin.php` is a single entry point; each section loads a module from `backend/admin/`.
All mutations post to `backend/admin_action.php`, which verifies the CSRF token, validates
input and writes through prepared statements.

**Overview** — dashboard with counts and an SEO readiness checklist, client inquiries,
activity log.

**Content** — *Website content* lists every visible string on the site, grouped by page and
then by section, with a search box. Keys name the place they belong to, for example
`home.hero.headline_prefix` or `footer.bottom.copyright`. You can also add your own custom
fields and output them in a template with `cms_text('your.key')`. Also here: *Pages*
(create pages from a template with title, cover image, description and body), *Blog*,
*Products* and the *Media library*.

Every page you create chooses its own placement with three independent tick boxes —
**header menu (desktop)**, **mobile menu bar** and **footer** (with a column picker). Tick
header only and the link is hidden on phones; tick mobile only and it is hidden on
desktop; tick both to show it everywhere. The menu links are created, renamed and removed
for you, and any link can be fine-tuned afterwards in *Links & URLs*, which has the same
"Shown on" control for every navigation entry.

The blog is linked from the header menu and the footer out of the box. Its landing page
lists every published article as a card with a cover image (falling back to a configurable
placeholder), category, date, reading time, summary and a **Read full article** button;
the three newest articles also appear on the homepage.

**SEO** — *Page SEO* per route (meta title, description, keywords, canonical, index/follow,
extra robots directives, Open Graph, Twitter cards, JSON-LD, hreflang, sitemap priority and
change frequency, custom head tags). *Keywords* for planning and rank tracking.
*Backlinks* for the inbound-link register. *Links & URLs* to add, edit, reorder or delete
any menu link. *Redirects* for 301s after a URL changes.

**Configuration** — site identity, default SEO, search-console/analytics verification
codes (Google, Bing, Yandex, Pinterest, Meta), GA4, GTM, Meta Pixel, Clarity, Hotjar,
custom head/body snippets, organisation schema, sitemap and `robots.txt` content, blog and
page behaviour. Plus administrator accounts.

## Public URLs

| URL | Rendered by |
| --- | --- |
| `/` | `index.php` (via `DirectoryIndex`) |
| `/contact.html` | `contact.php` (via rewrite) |
| `/products.php` | `products.php` |
| `/blog.php`, `/blog.php?post=slug` | `blog.php` |
| `/page.php?slug=…` | `page.php` |
| `/sitemap.xml` | `sitemap.php` (via rewrite) |
| `/robots.txt` | `robots.php` (via rewrite) |

`index.html` and `contact.html` remain as redirect stubs so the site still works on a
server without `mod_rewrite`; the static `sitemap.xml` and `robots.txt` are fallbacks for
the same reason.

## How the content system works

`backend/cms_defaults.php` is the master registry: one entry per visible string, with its
key, page, section, label, field type and current wording. It is used to seed the database
**and** as the fallback when MySQL is unreachable — so the public site keeps rendering its
copy even if the database is down.

Templates read content through `cms_text('key')` (escaped), `cms_raw()` (unescaped),
`cms_link()` (validated URL) and `cms_html()` (sanitised rich text). Admin-supplied HTML is
run through `cms_sanitize_html()`, which strips scripts, iframes, inline event handlers and
`javascript:` URLs.

## Security notes

- Admin session cookie is `HttpOnly`, `SameSite=Strict` and `Secure` over HTTPS.
- Every admin form carries a CSRF token that is checked server-side.
- All database writes use prepared statements.
- `backend/.htaccess` blocks direct requests to the includes; `assets/uploads/.htaccess`
  disables script execution for uploaded files.
- `admin.php` and `login.php` send `noindex, nofollow, noarchive` and are disallowed in
  `robots.txt`.
