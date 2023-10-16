<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Support\Str;

define('LARAVEL_INSTALLER_DIR', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'laravel-installer');

require LARAVEL_INSTALLER_DIR . DIRECTORY_SEPARATOR . 'Lura' . DIRECTORY_SEPARATOR . 'LaravelInstaller.php';

class Installer extends LaravelInstaller
{
    protected bool $installInertia = true;
    protected string $installFontAwesome;
    protected bool $installIdeHelper = true;
    protected bool $installHeadlessUi = false;
    protected bool $installTailwindCss;
    protected bool $installEslint = false;
    protected bool $installHelpersCollection = true;
    protected bool $useScss = true;

    protected function setStorageDisk(): void
    {
        $dir = LARAVEL_INSTALLER_DIR . DIRECTORY_SEPARATOR . 'storage';
        $this->storage = $this->command->createFilesystem($dir);
    }

    /**
     * @return void
     */
    protected function afterComposerInstall(): void
    {
        parent::afterComposerInstall();
        if ($this->installInertia) {
            $installNovaNpmDependencies = $this->command->choice(
                'Would You like install NPM dependencies and compile the assets?',
                [
                    'no',
                    'Yes with NPM',
                    'Yes with PNPM',
                ],
                0
            );

            if ($installNovaNpmDependencies == 'Yes with NPM') {
                $this->runCommand('npm i && npm run build');
            }

            if ($installNovaNpmDependencies == 'Yes with PNPM') {
                $this->runCommand('pnpm i && pnpm run build');
            }
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getRepoSlug(string $name): string
    {
        return Str::lower(parent::getRepoSlug($name));
    }

    /**
     * @return void
     */
    protected function customChanges(): void
    {
        $composerJson = json_decode($this->command->cwdDisk->get($this->appFolder . '/composer.json'), true);
        $requirements = data_get($composerJson, 'require', []);
        $devRequirements = data_get($composerJson, 'require-dev', []);
        if ($this->installIdeHelper) {
            $devRequirements = static::addPackage($devRequirements, 'barryvdh/laravel-ide-helper', '*');
        }
        if ($this->installHelpersCollection) {
            $requirements = static::addPackage(
                $requirements,
                'norman-huth/helpers-collection-laravel',
                '^v1.1.7'
            );
        }

        data_set($composerJson, 'require-dev', $devRequirements);
        data_set($composerJson, 'require', $requirements);
        $this->command->cwdDisk->put(
            $this->appFolder . '/composer.json',
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $packageJson = json_decode($this->command->cwdDisk->get($this->appFolder . '/package.json'), true);
        $devDependencies = data_get($packageJson, 'devDependencies', []);
        $dependencies = data_get($packageJson, 'dependencies', []);

        // Update Axios
        $devDependencies = static::addPackage(
            $devDependencies,
            'axios',
            $this->formatVersion('axios', '^1.5.1')
        );

        // bootstrap.js prettier
        $contents = file_get_contents(dirname(__DIR__) . '/storage/bootstrap.js');
        $this->command->cwdDisk->put($this->appFolder . '/resources/js/bootstrap.js', $contents);
        // PHPCS Controller
        $contents = file_get_contents(dirname(__DIR__) . '/storage/Controller.php');
        $this->command->cwdDisk->put($this->appFolder . '/app/Http/Controllers/Controller.php', $contents);
        // JSON Response for errors on API path
        $contents = file_get_contents(dirname(__DIR__) . '/storage/Handler.php');
        $this->command->cwdDisk->put($this->appFolder . '/app/Exceptions/Handler.php', $contents);

        // QS files
        $files = ['/.editorconfig', '/phpcs.xml'];
        foreach ($files as $file) {
            $contents = file_get_contents(dirname(__DIR__) . '/storage' . $file);
            $this->command->cwdDisk->put($this->appFolder . $file, $contents);
        }

        if ($this->installFontAwesome != 'no') {
            $dependencies = static::addPackage(
                $dependencies,
                '@fortawesome/vue-fontawesome',
                $this->formatVersion('@fortawesome/vue-fontawesome', '3.0.3')
            );
            $dependencies = static::addPackage(
                $dependencies,
                '@fortawesome/fontawesome-svg-core',
                $this->formatVersion('@fortawesome/fontawesome-svg-core', '6.4.2')
            );
            $dependencies = static::addPackage(
                $dependencies,
                '@fortawesome/free-brands-svg-icons',
                $this->formatVersion('@fortawesome/free-brands-svg-icons', '6.4.2')
            );
        }

        if ($this->installFontAwesome == 'Pro') {
            $items = [
                'pro-duotone-svg-icons',
                'pro-light-svg-icons',
                'pro-regular-svg-icons',
                'pro-solid-svg-icons',
            ];
            foreach ($items as $item) {
                $dependencies = static::addPackage(
                    $dependencies,
                    '@fortawesome/' . $item,
                    $this->formatVersion('@fortawesome/' . $item, '6.4.2')
                );
            }
        }
        if ($this->installFontAwesome == 'Free') {
            $items = ['free-regular-svg-icons', 'free-solid-svg-icons'];
            foreach ($items as $item) {
                $dependencies = static::addPackage(
                    $dependencies,
                    '@fortawesome/' . $item,
                    $this->formatVersion('@fortawesome/' . $item, '6.4.2')
                );
            }
        }

        $viteConfig = $this->appFolder . '/vite.config.js';
        if ($this->installInertia) {
            $contents = file_get_contents(dirname(__DIR__) . '/storage/vite.config.js');
            $this->command->cwdDisk->put($viteConfig, $contents);

            $contents = file_get_contents(dirname(__DIR__) . '/storage/Home.vue');
            $this->command->cwdDisk->put($this->appFolder . '/resources/js/Pages/Home/Index.vue', $contents);
            $contents = file_get_contents(dirname(__DIR__) . '/storage/HomeController.php');
            $this->command->cwdDisk->put($this->appFolder . '/app/Http/Controllers/HomeController.php', $contents);
            $contents = file_get_contents(dirname(__DIR__) . '/storage/web.php');
            $this->command->cwdDisk->put($this->appFolder . '/routes/web.php', $contents);
        }

        if ($this->useScss) {
            $this->command->cwdDisk->deleteDirectory($this->appFolder . '/resources/css');
            $this->command->cwdDisk->put($this->appFolder . '/resources/scss/app.scss', "\n");

            $contents = $this->command->cwdDisk->get($viteConfig);
            $contents = str_replace('resources/css/app.css', 'resources/scss/app.scss', $contents);
            $this->command->cwdDisk->put($viteConfig, $contents);

            $devDependencies = static::addPackage(
                $devDependencies,
                'sass',
                $this->formatVersion('sass', '1.69.3')
            );
            $devDependencies = static::addPackage(
                $devDependencies,
                'sass-loader',
                $this->formatVersion('sass-loader"', '13.3.2')
            );
        }

        if ($this->installTailwindCss) {
            $devDependencies = static::addPackage(
                $devDependencies,
                'tailwindcss',
                $this->formatVersion('tailwindcss', '3.3.3')
            );
            $devDependencies = static::addPackage(
                $devDependencies,
                'postcss',
                $this->formatVersion('postcss', '8.4.3')
            );
            $devDependencies = static::addPackage(
                $devDependencies,
                'autoprefixer',
                $this->formatVersion('autoprefixer', '10.4.1')
            );
            $devDependencies = static::addPackage(
                $devDependencies,
                '@tailwindcss/forms',
                $this->formatVersion('@tailwindcss/forms', '0.5.6')
            );
            $devDependencies = static::addPackage(
                $devDependencies,
                'tailwind-scrollbar',
                $this->formatVersion('tailwind-scrollbar', '3.0.5')
            );

            $files = ['/postcss.config.js', '/tailwind.config.js'];
            foreach ($files as $file) {
                $contents = file_get_contents(dirname(__DIR__) . '/storage' . $file);
                $this->command->cwdDisk->put($this->appFolder . $file, $contents);
            }

            $stylesheet = "@tailwind base;\n@tailwind components;\n@tailwind utilities;\n";
            $target = $this->useScss ? '/resources/scss/app.scss' : 'resources/css/app.css';
            $this->command->cwdDisk->put($this->appFolder . $target, $stylesheet);
        }

        if ($this->installHeadlessUi) {
            $devDependencies = static::addPackage(
                $devDependencies,
                '@headlessui/vue',
                $this->formatVersion('@headlessui/vue', '1.7.16')
            );
        }

        if ($this->installEslint) {
            $devDependencies = static::addPackage(
                $devDependencies,
                '@babel/plugin-syntax-dynamic-import',
                $this->formatVersion('@babel/plugin-syntax-dynamic-import', '7.8.3')
            );
            $devDependencies = static::addPackage(
                $devDependencies,
                '@vue/eslint-config-prettier',
                $this->formatVersion('@vue/eslint-config-prettier', '8.0.0')
            );
            $devDependencies = static::addPackage(
                $devDependencies,
                'eslint',
                $this->formatVersion('eslint', '8.51.0')
            );
            $devDependencies = static::addPackage(
                $devDependencies,
                'eslint-plugin-vue',
                $this->formatVersion('eslint-plugin-vue', '9.17.0')
            );
            $devDependencies = static::addPackage(
                $devDependencies,
                '@rushstack/eslint-patch',
                $this->formatVersion('@rushstack/eslint-patch', '1.5.1')
            );

            $files = ['/.eslintignore', '/.eslintrc.cjs', '/.prettierignore', '/.prettierrc.yaml'];
            foreach ($files as $file) {
                $contents = file_get_contents(dirname(__DIR__) . '/storage' . $file);
                $this->command->cwdDisk->put($this->appFolder . $file, $contents);
            }
        }

        data_set($packageJson, 'devDependencies', $devDependencies);
        data_set($packageJson, 'dependencies', $dependencies);
        $this->command->cwdDisk->put(
            $this->appFolder . '/package.json',
            json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        if ($this->installInertia) {
            $contents = file_get_contents(dirname(__DIR__) . '/storage/app.js');
            $this->command->cwdDisk->put($this->appFolder . '/resources/js/app.js', $contents);
        }

        $contents = file_get_contents(dirname(__DIR__) . '/storage/app.blade.php');
        if ($this->useScss) {
            $contents = str_replace('resources/css/app.css', 'resources/scss/app.scss', $contents);
        }
        if ($this->installTailwindCss) {
            $contents = str_replace('<body>', '<body class="antialiased">', $contents);
        }
        $name = $this->installInertia ? 'app' : 'layout';
        if (!$this->installInertia) {
            $contents = str_replace(
                ['@inertiaHead', '@inertia'],
                ['<!-- meta description etc. -->', '@yield(\'content\')'],
                $contents
            );
        }
        $this->command->cwdDisk->put($this->appFolder . '/resources/views/' . $name . '.blade.php', $contents);

        $stubs = glob(dirname(__DIR__) . '/storage/stubs/*.stub');
        foreach ($stubs as $stub) {
            $this->command->cwdDisk->put(
                $this->appFolder . '/stubs/' . basename($stub),
                file_get_contents($stub)
            );
        }

        $contents = file_get_contents($this->appFolder . '/app/Providers/RouteServiceProvider.php');
        $this->command->cwdDisk->put(
            $this->appFolder . '/app/Providers/RouteServiceProvider.php',
            str_replace('/home', '/', $contents)
        );

        $contents = file_get_contents($this->appFolder . '/app/Http/Kernel.php');
        $contents = str_replace(
            '\Illuminate\Routing\Middleware\ThrottleRequests::class.',
            '\Illuminate\Routing\Middleware\ThrottleRequests::class . ',
            $contents
        );
        if ($this->installHelpersCollection) {
            $contents = str_replace(
                '\Illuminate\Routing\Middleware\ThrottleRequests::class',
                '\NormanHuth\HelpersLaravel\App\Http\Middleware\ForceJsonResponse::class,' .
                "\n            \Illuminate\Routing\Middleware\ThrottleRequests::class",
                $contents
            );
        }
        $this->command->cwdDisk->put($this->appFolder . '/app/Http/Kernel.php', $contents);
    }

    /**
     * @return void
     */
    protected function questions(): void
    {
        $this->questionDev();
        $this->questionInertia();
        $this->questionNova();
        $this->questionDocker();
        $this->installFontAwesome = $this->command->choice(
            'Install Font Awesome Vue?',
            [
                'no',
                'Pro',
                'Free',
            ],
            'no'
        );

        $this->installIdeHelper = $this->command->confirm(
            'Install IDE Helper Generator for Laravel?',
            $this->installIdeHelper
        );
        $this->installHelpersCollection = $this->command->confirm(
            'Install IDE norman-huth/helpers-collection-laravel?',
            $this->installHelpersCollection
        );
        $this->installTailwindCss = $this->command->confirm('Install Tailwind CSS?', true);
        $this->useScss = $this->command->confirm('Use SCSS instead of CSS?', $this->useScss);
        if ($this->installInertia) {
            $this->installHeadlessUi = $this->command->confirm('Install HeadlessUI VUE?', true);
            $this->installEslint = $this->command->confirm('Install ESLint?', true);
        }
    }
}
