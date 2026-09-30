# CodeIgniter 4 Activity — Balaba & Mabuti

**IT PROF EL 4 — Semester Project**
Pair: **Balaba & Mabuti** · Repository: `spc-itprofel4-2026/balaba-mabuti`

CodeIgniter 4 application starter set up and running as the CodeIgniter activity output for the course.

## Requirements

- PHP 8.2 or higher (installed: PHP 8.3.33) with `intl`, `mbstring`, `json`
- Composer 2
- `mysqlnd` only if using MySQL

## How to Run

```cmd
cd "path\to\MABUTI-BALABA"
composer install
copy env .env
php spark serve
```

Open http://localhost:8080 — the CodeIgniter welcome page should appear.

Edit `app/Views/welcome_message.php` to change the page content and `app/Config/Routes.php` for routes.

## Output

The generated output (welcome page and run evidence) is in:

- [docs/BALABA-MABUTI OUTPUT.pdf](docs/BALABA-MABUTI%20OUTPUT.pdf)
- [docs/MABUTI-BALABA-IT-PROOF-EL4.docx.pdf](docs/MABUTI-BALABA-IT-PROOF-EL4.docx.pdf)
- [docs/Week6-Submission-Parts1-3.md](docs/Week6-Submission-Parts1-3.md)

## Project Structure

```
MABUTI-BALABA/
├── app/
│   ├── Config/          # Configuration (routes, database, app settings)
│   ├── Controllers/     # Request handlers (Home.php)
│   ├── Models/          # Database models
│   └── Views/           # Page templates (welcome_message.php)
├── public/              # Web root (index.php lives here — point server to this)
├── writable/            # Logs, cache, sessions (must be writable)
├── tests/               # PHPUnit tests
├── docs/                # Submission documents and outputs
├── env                  # Environment template (copy to .env)
└── spark                # Command-line tool
```

## Notes

- `index.php` is inside `public/` — the web server must point to `public/`, not the project root.
- `.env` and `vendor/` are not committed (see `.gitignore`); run `composer install` after cloning.

## References

- [CodeIgniter 4 User Guide](https://codeigniter.com/user_guide/)
- [CodeIgniter Official Site](https://codeigniter.com)
