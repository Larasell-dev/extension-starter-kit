# Larasell Extension Template

<!-- template:start -->
A starting point for [Larasell](https://larasell.dev) extensions, with the tooling and conventions
of Larasell's own packages. Create a repository from this template, then rename it to your
extension:

```bash
php configure.php --vendor=acme --name=wishlists
composer install
npm install
composer lint   # renaming can change the order of use statements
composer test
```

`configure.php` renames the package, namespace, service provider and config, removes this section
and deletes itself.

<!-- template:end -->
## Installation

```bash
composer require larasell-dev/extension
php artisan migrate
```

## Development

```bash
composer test       # Pest
composer phpstan    # Larastan, level 8
composer lint       # Pint
npm run commitlint  # Commitlint on the last commit
composer serve      # The playground, see below
```

`composer serve` runs a playground: a Larasell admin panel with this extension installed, at
http://localhost:8000/admin. Open `/admin/__dev/login` to sign in as an admin. `composer
playground` prepares it without serving, e.g. after adding a migration or sample data.
