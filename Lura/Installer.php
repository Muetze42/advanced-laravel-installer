<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use NormanHuth\Helpers\Str;

define('LARAVEL_INSTALLER_DIR', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'laravel-installer');

require LARAVEL_INSTALLER_DIR . DIRECTORY_SEPARATOR . 'Lura' . DIRECTORY_SEPARATOR . 'LaravelInstaller.php';

class Installer extends LaravelInstaller
{
    protected bool $installInertia = true;
    protected string $installFontAwesome = 'no';
    protected bool $installIdeHelper = true;
    protected bool $installHeadlessUi = true;
    protected bool $installTailwindCss = true;
    protected bool $installEslint = true;
    protected bool $installHelpersCollection = true;
    protected bool $useScss = true;
    protected bool $installActivitylog = false;
    protected bool $installMedialibrary = false;
    protected bool $installErrorPages = true;

    protected bool $addProjectHelperFiles = false;

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

        $this->command->filesystem->copyDirectory(
            $this->command->cwdDisk->path(
                $this->appFolder . '/vendor/laravel/framework/src/Illuminate/Translation/lang'
            ),
            $this->command->cwdDisk->path($this->appFolder . '/lang')
        );

        $this->runCommand('php artisan session:table');
        $this->runCommand('php artisan queue:table');

        if ($this->installActivitylog) {
            $command = [
                'php artisan vendor:publish',
                '--provider="Spatie\Activitylog\ActivitylogServiceProvider"',
                '--tag="activitylog-config"',
            ];
            $this->runCommand(implode(' ', $command));

            $contents = file_get_contents(dirname(__DIR__) . '/storage/activity-log/migration.stub');
            $this->command->cwdDisk->put(
                $this->appFolder . '/database/migrations/' . $this->getMigrationFileName('CreateActivityLogTable'),
                $contents
            );

            $contents = file_get_contents(dirname(__DIR__) . '/storage/activity-log/model.stub');
            $this->command->cwdDisk->put(
                $this->appFolder . '/app/Models/Activity.php',
                $contents
            );

            $file = $this->appFolder . '/config/activitylog.php';
            $contents = file_get_contents($file);
            $contents = str_replace(
                'Spatie\\Activitylog\\Models\\Activity::class',
                'App\\Models\\Activity::class',
                $contents
            );
            $this->command->cwdDisk->put(
                $file,
                $contents
            );
        }

        if ($this->installMedialibrary) {
            $command = [
                'php artisan vendor:publish',
                '--provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"',
                '--tag="migrations"',
            ];
            $this->runCommand(implode(' ', $command));
            $command = [
                'php artisan vendor:publish',
                '--provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"',
                '--tag="config"',
            ];
            $this->runCommand(implode(' ', $command));
            $file = $this->appFolder . '/config/media-library.php';

            $contents = file_get_contents(dirname(__DIR__) . '/storage/Media.php');
            $this->command->cwdDisk->put(
                $this->appFolder . '/app/Models/Media.php',
                $contents
            );

            $contents = file_get_contents($file);
            if ($this->installHelpersCollection) {
                $contents = str_replace(
                    'Spatie\\MediaLibrary\\Support\\PathGenerator\\DefaultPathGenerator::class',
                    'NormanHuth\\HelpersLaravel\\Spatie\\MediaLibrary\\CustomPathGenerator::class',
                    $contents
                );
            }
            $contents = str_replace(
                'Spatie\\MediaLibrary\\MediaCollections\\Models\\Media::class',
                'App\\Models\\Media::class',
                $contents
            );
            $this->command->cwdDisk->put(
                $file,
                $contents
            );
        }

        if ($this->installErrorPages) {
            $contents = file_get_contents(dirname(__DIR__) . '/storage/error-pages/error-pages.css');
            $this->command->cwdDisk->put(
                $this->appFolder . '/public/css/error-pages.css',
                $contents
            );
            $contents = file_get_contents(dirname(__DIR__) . '/storage/error-pages/minimal.blade.php');
            $this->command->cwdDisk->put(
                $this->appFolder . '/resources/views/errors/minimal.blade.php',
                $contents
            );
            $files = glob(dirname(__DIR__) . '/storage/error-pages/*.svg');
            foreach ($files as $file) {
                $contents = file_get_contents($file);
                $this->command->cwdDisk->put(
                    $this->appFolder . '/public/assets/' . basename($file),
                    $contents
                );
            }
        }

        // Publish new Sanctum config
        $this->command->cwdDisk->delete($this->appFolder . '/config/sanctum.php');
        $this->runCommand('php artisan vendor:publish --tag=sanctum-config');

        $this->renameMigrations();
        $this->npmDependencies();
    }

    protected function renameMigrations(): void
    {
        $migrations = $this->command->cwdDisk->allFiles($this->appFolder . '/database/migrations');

        $rename = [
            'create_sessions_table.php',
            'create_jobs_table.php',
            'create_activity_log_table.php',
            'create_media_table.php',
        ];
        foreach ($migrations as $migration) {
            $migration = basename($migration);
            $name = substr($migration, 18);
            if (!in_array($name, $rename)) {
                continue;
            }
            $this->command->cwdDisk->move(
                $this->appFolder . '/database/migrations/' . $migration,
                $this->appFolder . '/database/migrations/' . substr($migration, 0, 11) . '000000_' . $name,
            );
        }
    }

    /**
     * @return void
     */
    protected function npmDependencies(): void
    {
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
            static::addDependency($devRequirements, 'barryvdh/laravel-ide-helper', '2.13');
        }
        if ($this->installHelpersCollection) {
            static::addDependency(
                $devRequirements,
                'norman-huth/helpers-collection-laravel',
                'v1.1.7'
            );
        }
        if ($this->installNova) {
            static::addDependency($requirements, 'norman-huth/nova-assets-versioning', '1.0');
        }
        if ($this->installActivitylog) {
            static::addDependency($requirements, 'spatie/laravel-activitylog', '4.7');
        }
        if ($this->installMedialibrary) {
            static::addDependency($requirements, 'spatie/laravel-medialibrary', '10.13');
        }
        static::addDependency($devRequirements, 'laravel/dusk', '7.11');

        data_set($composerJson, 'require-dev', $devRequirements);
        data_set($composerJson, 'require', $requirements);

        if ($this->addProjectHelperFiles) {
            data_set($composerJson, 'autoload.files', ['functions/helpers.php']);
            data_set($composerJson, 'autoload-dev.files', ['functions/helpers-dev.php']);

            $this->command->cwdDisk->put(
                $this->appFolder . '/functions/helpers.php',
                "<?php\n"
            );
            $this->command->cwdDisk->put(
                $this->appFolder . '/functions/helpers-dev.php',
                "<?php\n"
            );
        }

        $postUpdateCmdScripts = data_get($composerJson, 'scripts.post-update-cmd', []);
        $postUpdateCmdScripts[] = './vendor/bin/pint';
        data_set($composerJson, 'scripts.post-update-cmd', $postUpdateCmdScripts);
        $phpmdDirs = 'app,database,config,routes';
        if ($this->addProjectHelperFiles) {
            $phpmdDirs .= ',functions';
        }
        data_set($composerJson, 'scripts.code-quality', [
            './vendor/bin/pint',
            'phpmd ' . $phpmdDirs . ' text phpmd.xml'
        ]);

        $description = data_get($composerJson, 'description', '');
        if ($description && !str_ends_with($description, '.')) {
            $description .= '.';
        }
        $description .= ' Created with norman-huth/advanced-laravel-installer.';
        data_set($composerJson, 'description', trim($description));

        $this->command->cwdDisk->put(
            $this->appFolder . '/composer.json',
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $packageJson = json_decode($this->command->cwdDisk->get($this->appFolder . '/package.json'), true);
        $devDependencies = data_get($packageJson, 'devDependencies', []);
        $dependencies = data_get($packageJson, 'dependencies', []);

        // Update Axios
        static::addDependency($devRequirements, 'axios', '1.5.1');

        // bootstrap.js prettier
        $contents = file_get_contents(dirname(__DIR__) . '/storage/bootstrap.js');
        $this->command->cwdDisk->put($this->appFolder . '/resources/js/bootstrap.js', $contents);
        // PHPCS Controller
//        $contents = file_get_contents(dirname(__DIR__) . '/storage/Controller.php');
//        $this->command->cwdDisk->put($this->appFolder . '/app/Http/Controllers/Controller.php', $contents);
        // JSON Response for errors on API path and prevent route login not found
        $contents = file_get_contents(dirname(__DIR__) . '/storage/Handler.php');
        $this->command->cwdDisk->put($this->appFolder . '/app/Exceptions/Handler.php', $contents);
        // prevent route login not found
        $contents = file_get_contents(dirname(__DIR__) . '/storage/Authenticate.php');
        $this->command->cwdDisk->put($this->appFolder . '/app/Http/Middleware/Authenticate.php', $contents);
        // add debug log channel
        $contents = $this->command->cwdDisk->get($this->appFolder . '/config/logging.php');
        $replace = file_get_contents(dirname(__DIR__) . '/storage/logging.stub');
        $contents = str_replace("'slack' => [", $replace, $contents);
        $this->command->cwdDisk->put($this->appFolder . '/config/logging.php', $contents);

        // Files
        //$files = ['/.editorconfig', '/phpcs.xml', '/pint.json', '/deploy.sh', '/phpmd.xml'];
        $files = ['/.editorconfig', '/pint.json', '/deploy.sh', '/phpmd.xml'];
        foreach ($files as $file) {
            $contents = file_get_contents(dirname(__DIR__) . '/storage' . $file);
            $this->command->cwdDisk->put($this->appFolder . $file, $contents);
        }

        $gitIgnore = trim($this->command->cwdDisk->get($this->appFolder . '/.gitignore'));
        $entries = ['deploy.sh', '.php-cs-fixer.cache', '/deploy/*.sh'];
        foreach ($entries as $entry) {
            if (!str_contains($gitIgnore, $entry)) {
                $gitIgnore .= "\n" . $entry;
            }
        }
        $this->command->cwdDisk->put($this->appFolder . '/.gitignore', $gitIgnore . "\n");

        if ($this->installFontAwesome != 'no') {
            static::addDependency($dependencies, '@fortawesome/vue-fontawesome', '3.0.3');
            static::addDependency($dependencies, '@fortawesome/fontawesome-svg-core', '6.4.2');
            static::addDependency($dependencies, '@fortawesome/free-brands-svg-icons', '6.4.2');
        }

        if ($this->installFontAwesome == 'Pro') {
            $items = [
                'pro-duotone-svg-icons',
                'pro-light-svg-icons',
                'pro-regular-svg-icons',
                'pro-solid-svg-icons',
            ];
            foreach ($items as $item) {
                static::addDependency($dependencies, '@fortawesome/' . $item, '6.4.2');
            }
        }
        if ($this->installFontAwesome == 'Free') {
            $items = ['free-regular-svg-icons', 'free-solid-svg-icons'];
            foreach ($items as $item) {
                static::addDependency($dependencies, '@fortawesome/' . $item, '6.4.2');
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

            static::addDependency($dependencies, 'sass', '1.69.3');
            static::addDependency($dependencies, 'sass-loader', '13.3.2');
        }

        if ($this->installTailwindCss) {
            static::addDependency($devDependencies, 'tailwindcss', '3.3.3');
            static::addDependency($devDependencies, 'postcss', '8.4.3');
            static::addDependency($devDependencies, 'autoprefixer', '10.4.1');
            static::addDependency($devDependencies, '@tailwindcss/forms', '0.5.6');
            static::addDependency($devDependencies, 'tailwind-scrollbar', '3.0.5');

            $files[] = '/postcss.config.js';
            $files[] = $this->useScss ? '/tailwind.config.scss.js' : '/tailwind.config.js';
            foreach ($files as $file) {
                $contents = file_get_contents(dirname(__DIR__) . '/storage' . $file);
                $this->command->cwdDisk->put(
                    $this->appFolder . str_replace('tailwind.config.scss.js', 'tailwind.config.js', $file),
                    $contents
                )
                ;
            }

            $stylesheet = "@tailwind base;\n@tailwind components;\n@tailwind utilities;\n";
            if ($this->useScss) {
                $stylesheet .= "\n//@import \"fonts/inter-var\";\n//@import \"fonts/fira-code\";\n";
            }
            $target = $this->useScss ? '/resources/scss/app.scss' : 'resources/css/app.css';
            $this->command->cwdDisk->put($this->appFolder . $target, $stylesheet);

            $fonts = glob(dirname(__DIR__) . '/storage/fonts/*', GLOB_ONLYDIR);
            foreach ($fonts as $font) {
                $files = glob($font . '/*');
                foreach ($files as $file) {
                    $filename = basename($file);
                    $target = str_ends_with($filename, '.scss') ? '/resources/scss/fonts/' :
                        '/resources/fonts/' . basename($font) . '/';

                    $contents = file_get_contents($file);
                    $this->command->cwdDisk->put(
                        $this->appFolder . $target . $filename,
                        $contents
                    );
                }
            }
        }

        if ($this->installHeadlessUi) {
            static::addDependency($devDependencies, '@headlessui/vue', '1.7.16');
        }

        if ($this->installEslint) {
            static::addDependency(
                $devDependencies,
                '@babel/plugin-syntax-dynamic-import',
                '7.8.3'
            );
            static::addDependency($devDependencies, '@vue/eslint-config-prettier', '8.0.0');
            static::addDependency($devDependencies, 'eslint', '8.51.0');
            static::addDependency($devDependencies, 'eslint-plugin-vue', '9.17.0');
            static::addDependency($devDependencies, '@rushstack/eslint-patch', '1.5.1');

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
            $this->installFontAwesome
        );

        $this->installIdeHelper = $this->command->confirm(
            'Install IDE Helper Generator for Laravel?',
            $this->installIdeHelper
        );
        $this->installHelpersCollection = $this->command->confirm(
            'Install IDE norman-huth/helpers-collection-laravel?',
            $this->installHelpersCollection
        );
        $this->installTailwindCss = $this->command->confirm('Install Tailwind CSS?', $this->installTailwindCss);
        $this->useScss = $this->command->confirm('Use SCSS instead of CSS?', $this->useScss);
        if ($this->installInertia) {
            $this->installHeadlessUi = $this->command->confirm('Install HeadlessUI VUE?', $this->installHeadlessUi);
            $this->installEslint = $this->command->confirm('Install ESLint?', $this->installEslint);
        }

        $this->installActivitylog = $this->command->confirm(
            'Install spatie/laravel-activitylog?',
            $this->installActivitylog
        );

        $this->installMedialibrary = $this->command->confirm(
            'Install spatie/laravel-medialibrary?',
            $this->installMedialibrary
        );

        $this->installErrorPages = $this->command->confirm(
            'Install custom error pages?',
            $this->installErrorPages
        );

        $this->addProjectHelperFiles = $this->command->confirm(
            'Add custom helper files for the project?',
            $this->addProjectHelperFiles
        );
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getMigrationFileName(string $name): string
    {
        return date('Y_m_d_') . '000000_' . Str::snake(trim($name, '_')) . '.php';
    }
}
