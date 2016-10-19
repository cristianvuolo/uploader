<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 19/10/2016
 * Time: PM 07:12
 */

namespace CristianVuolo\Uploader;

use Illuminate\Support\ServiceProvider;

class UploaderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publish([__DIR__ . '/../configs/' => base_path('config')]);
    }
}