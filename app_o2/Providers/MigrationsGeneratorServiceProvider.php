<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Xethron\MigrationsGenerator\MigrationsGeneratorServiceProvider as BaseProvider;

class MigrationsGeneratorServiceProvider extends BaseProvider
{
    public function register()
	{
		$this->app->singleton('migration.generate',
            function($app) {
                return new MigrateGenerateCommand(
                    $app->make('Way\Generators\Generator'),
                    $app->make('Way\Generators\Filesystem\Filesystem'),
                    $app->make('Way\Generators\Compilers\TemplateCompiler'),
                    $app->make('migration.repository'),
                    $app->make('config')
                );
            });

		$this->commands('migration.generate');

		// Bind the Repository Interface to $app['migrations.repository']
		$this->app->bind('Illuminate\Database\Migrations\MigrationRepositoryInterface', function($app) {
			return $app['migration.repository'];
		});
	}
}
