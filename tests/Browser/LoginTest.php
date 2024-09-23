<?php

use Laravel\Dusk\Browser;
use App\Models\User;

test('example', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Laravel');
    });
});


test('shows errors of login form', function () {

    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->type('email', 'bla@bla.com')
            ->type('password', 'password')
            ->press('Sign in')
            ->waitFor('@email-error')
            ->assertPresent('@email-error')
            ->waitFor('@password-error')
            ->assertPresent('@password-error');
    });
});



