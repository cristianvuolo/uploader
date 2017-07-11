<?php

namespace CristianVuolo\Uploader\Providers;

use Illuminate\Support\ServiceProvider;

class UploaderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . '/../configs/' => base_path('config')]);
        require_once __DIR__ . "/../Helpers/UploaderHelpers.php";
    }

    public function register()
    {
        
    }
}