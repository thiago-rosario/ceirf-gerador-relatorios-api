<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

test('removed starter kit routes return 404 JSON even when HTML is requested', function (string $method, string $uri) {
    $response = $this->call($method, $uri, server: ['HTTP_ACCEPT' => 'text/html']);

    $response->assertNotFound()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure(['message']);
})->with([
    'home' => ['GET', '/'],
    'dashboard' => ['GET', '/dashboard'],
    'profile settings' => ['GET', '/settings/profile'],
    'login page' => ['GET', '/login'],
    'login submission' => ['POST', '/login'],
    'registration' => ['POST', '/register'],
    'password reset' => ['POST', '/forgot-password'],
]);

test('an unknown API endpoint returns 404 JSON even when HTML is requested', function () {
    $response = $this->get('/api/missing', ['Accept' => 'text/html']);

    $response->assertNotFound()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure(['message']);
});

test('validation errors return 422 JSON even when HTML is requested', function () {
    Route::post('/api/_test/validation', function (Request $request): void {
        $request->validate(['name' => ['required']]);
    })->middleware('api');

    $response = $this->post('/api/_test/validation', [], ['Accept' => 'text/html']);

    $response->assertUnprocessable()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonValidationErrors(['name' => 'The name field is required.']);
});
