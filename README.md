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
* Add [norman-huth/helpers-collection-laravel](https://github.com/Muetze42/helpers-collection-laravel) option
* Add a complete working setup if install [Inertia.js](https://inertiajs.com/)
* [Exception handler](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/Handler.php#L46) response
  as JSON on API path
* Set application's "home" route to `/`
  in [RouteServiceProvider](https://github.com/laravel/laravel/blob/10.x/app/Providers/RouteServiceProvider.php#L20)
* Add `ForceJsonResponse` middleware for API routes if `norman-huth/helpers-collection-laravel` selected to install
* Add optional [pnpm](https://pnpm.io/) / [npm](https://www.npmjs.com/) dependencies installation and assets compiling
* Automatic add [phpcs.xml](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/phpcs.xml) file
* Change [.editorconfig](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/.editorconfig) file
* Add [Model](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/stubs/model.stub)
  and [Migration \(Create\)](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/stubs/migration.create.stub)
  stubs
* Add [other stubs](https://github.com/Muetze42/advanced-laravel-installer/tree/main/storage/stubs): [psr-12 „4.2 Using traits“](https://www.php-fig.org/psr/psr-12/#42-using-traits) formatted
* Transform target directory to lower cases
* Throw an 404 `HttpException` if route `login` not exist instead an `RouteNotFoundException`<br>
  \([Exception Handler](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/Handler.php#L56) & [Authenticate Middleware](https://github.com/Muetze42/advanced-laravel-installer/blob/main/storage/Authenticate.php#L16)\)
* Add Logging Channel `debug`

This installer only has the Vue.js as option, because I created this only according to my **personal** needs.  
I create each of my Laravel applications with this installer to save time on initial setup.

## Install

```shell
composer global require norman-huth/advanced-laravel-installer:"@dev"
```

### Don't forget to register the installer after install

```shell
lura register norman-huth/laravel-installer
```

## Run

```shell
lura
```

---

[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://vshymanskyy.github.io/StandWithUkraine/)

[![Woman. Life. Freedom.](https://raw.githubusercontent.com/Muetze42/Muetze42/2033b219c6cce0cb656c34da5246434c27919bcd/files/iran-banner-big.svg)](https://linktr.ee/CurrentPetitionsFreeIran)
