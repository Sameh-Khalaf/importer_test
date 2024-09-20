<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Xethron\MigrationsGenerator\MigrateGenerateCommand as BaseCommand;

class MigrateGenerateCommand extends BaseCommand
{
    /**
     * Get a directory path through a command option, or from the configuration.
     *
     * @param $option
     * @param $configName
     * @return string
     */
    protected function getPathByOptionOrConfig($option, $configName)
    {
        if ($path = $this->option($option)) return $path;
        
        return config()->get("generators.config.{$configName}");
    }
}
