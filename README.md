# Laravel Application Installer

A personal modified version of the [norman-huth/laravel-installer](https://github.com/Muetze42/laravel-installer) with
the following changes:

* Remove [Starter Kits](https://laravel.com/docs/starter-kits) option
* Add [Tailwind CSS](https://tailwindcss.com) option
* Tailwind CSS will be installed with
  plugins [@tailwindcss/forms](https://www.npmjs.com/package/@tailwindcss/forms) and [tailwind-scrollbar](https://www.npmjs.com/package/tailwind-scrollbar)
* Add [FontAwesome (Vue.js)](https://fontawesome.com) option
* Add [ESLint](https://eslint.org) option
* Add [IDE Helper Generator for Laravel](https://github.com/barryvdh/laravel-ide-helper) option
* Add [Sass](https://sass-lang.com) as default stylesheet by default option
* Automatic add [phpcs.xml](storage/phpcs.xml) file
* Change [.editorconfig](storage/.editorconfig) file

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
