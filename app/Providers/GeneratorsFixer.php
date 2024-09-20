<?php
namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use Way\Generators\GeneratorsServiceProvider; // IMPORTANT !!


class GeneratorsFixer extends GeneratorsServiceProvider
{
	/**
	 * Register the config paths
	 */
	public function registerConfig()
	{
		$userConfigFile    = config_path().'/generators.config.php'; // IMPORTANT !!
		$packageConfigFile = base_path().'/vendor/xethron/laravel-4-generators/src/config/config.php'; // IMPORTANT !!
		$config            = $this->app['files']->getRequire($packageConfigFile);

		if (file_exists($userConfigFile)) {
			$userConfig = $this->app['files']->getRequire($userConfigFile);
			$config     = array_replace_recursive($config, $userConfig);
		}

		$this->app['config']->set('generators.config', $config);
	}
}

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}