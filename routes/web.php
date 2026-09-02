<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('api.docs');
});

Route::view('/docs', 'api-docs')->name('api.docs');

Route::get('/docs/openapi.json', function () {
    return response()->json([
        'openapi' => '3.0.3',
        'info' => ['title' => 'E-Commerce REST API', 'version' => '1.0.0'],
        'servers' => [['url' => url('/api/v1')]],
        'paths' => collect(Route::getRoutes())->filter(fn ($route) => str_starts_with($route->uri(), 'api/v1/'))->mapWithKeys(function ($route) {
            $path = '/'.str_replace('api/v1/', '', $route->uri());
            $methods = collect($route->methods())->reject(fn ($method) => $method === 'HEAD');
            return [$path => $methods->mapWithKeys(fn ($method) => [strtolower($method) => ['responses' => ['200' => ['description' => 'JSON response']]]])->all()];
        })->all(),
    ]);
})->name('api.docs.openapi');
