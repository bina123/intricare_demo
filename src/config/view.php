<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most of your templates are stored within the resources/views directory.
    | You may also specify additional paths for the framework to look for
    | your views in when rendering them.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored. Typically, this is within the storage/framework/views directory.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

    /*
    |--------------------------------------------------------------------------
    | Pagination View
    |--------------------------------------------------------------------------
    |
    | Here you may specify which pagination view you wish to use by default
    | throughout your application. Supported options: "bootstrap-5", "tailwind"
    |
    */

    'pagination' => 'bootstrap-5',
];
