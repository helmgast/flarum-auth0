<?php


use Helmgast\Auth\Auth0\Auth0AuthController;
use Helmgast\Auth\Auth0\AddGroupListener;
use Flarum\Extend;
use Illuminate\Contracts\Events\Dispatcher;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\Routes('forum'))
        ->get('/auth/auth0', 'auth.auth0', Auth0AuthController::class),

    // (new Extend\Compat(function (Dispatcher $events) {
    //     $events->subscribe(AddGroupListener::class);
    // })),
];
