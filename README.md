# Laravel Application Installer

A personal modified version of the [norman-huth/laravel-installer](https://github.com/Muetze42/laravel-installer) with
the following changes:

* Remove [Starter Kits](https://laravel.com/docs/starter-kits) option
* Add [Tailwind CSS](https://tailwindcss.com) option
* Tailwind CSS will be installed with
  plugins [@tailwindcss/forms](https://www.npmjs.com/package/@tailwindcss/forms)
  and [tailwind-scrollbar](https://www.npmjs.com/package/tailwind-scrollbar)
* Add [FontAwesome (Vue.js)](https://fontawesome.com) option
* Add [ESLint](https://eslint.org) option
* Add [IDE Helper Generator for Laravel](https://github.com/barryvdh/laravel-ide-helper) option
* Add „[Sass](https://sass-lang.com) instead of CSS“ option
* Add [norman-huth/php-library](https://github.com/Muetze42/php-library) option
* Add a complete working setup if install [Inertia.js](https://inertiajs.com/)
* response as JSON on API path [app.php](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/app.php#L20)
* Set `redirectGuestsTo` `/` in [app.php](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/app.php#L16)
* Add optional [pnpm](https://pnpm.io/) / [npm](https://www.npmjs.com/) dependencies installation and assets compiling
* Automatic add [phpcs.xml](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/phpcs.xml) file
* Change [.editorconfig](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/.editorconfig) file
* Add [Model](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/stubs/model.stub)
  and [Migration \(Create\)](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/stubs/migration.create.stub) stubs
* Add [other stubs](https://github.com/Muetze42/advanced-laravel-installer/tree/main/storage/stubs): [psr-12 „4.2 Using traits“](https://www.php-fig.org/psr/psr-12/#42-using-traits) formatted
* Transform target directory to lower cases
* Copy [lang](https://github.com/laravel/framework/tree/10.x/src/Illuminate/Translation/lang/en) directory into the project after composer install
* Add [norman-huth/nova-assets-versioning](https://github.com/Muetze42/nova-assets-versioning) if install [Laravel Nova](https://nova.laravel.com/)
* Add [Laravel-activitylog](https://spatie.be/docs/laravel-activitylog) option
* Add [Laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary) option
* Publish and customize diverse configs and migration
* Install [Laravel Dusk](https://laravel.com/docs/dusk) for development
* Add [Laravel Pint](https://laravel.com/docs/pint) file with mixed Laravel & [PSR-12](https://www.php-fig.org/psr/psr-12/) rules 
  configuration ([Rules](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/pint.json))
* Extend [TestCase](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/test-case/93519c98470fc8240aed892b40c5a9fc.stub)
* Add [Laravel Pint](https://laravel.com/docs/pint) and [PHPMD](https://phpmd.org/) as Composer script
* Add custom error pages option
* Add Fonts ([Inter](https://github.com/rsms/inter) & [Fira Code](https://github.com/tonsky/FiraCode))
* Add helper files option
* Uncomment database setting in the `phpunit.xml` file
* Run [Laravel Pint](https://laravel.com/docs/pint) after installation
* Add `deploy.sh`, `.php-cs-fixer.cache`, `/deploy/*.sh` to `.gitignore`
* Add [Sentry](https://sentry.io/) option
* Change rename base Controller to AbstractController

This installer only has the Vue.js as option, because I created this only according to my **personal** needs.  
I create each of my Laravel applications with this installer to save time on an initial setup.

## Install

```shell
composer global require norman-huth/advanced-laravel-installer:"@dev"
```

### Don't forget to register the installer after install

```shell
lura register norman-huth/advanced-laravel-installer
```

## Run

```shell
lura
```

---

[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://vshymanskyy.github.io/StandWithUkraine/)
