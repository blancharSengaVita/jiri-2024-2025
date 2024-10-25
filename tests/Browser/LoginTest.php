<?php

use Illuminate\Support\Facades\Lang;
use Laravel\Dusk\Browser;
use App\Models\User;


test('that shows front-end errors of login form', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->assertAttribute('@email', 'type', 'email')
            ->assertAttribute('@email', 'required', 'true')
            ->assertAttribute('@password', 'type', 'password')
            ->assertAttribute('@password', 'required', 'true');
    });
});

test('that there is no required error when email and password are populate', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->type('email', 'bla@bla.com')
            ->type('password', 'password')
            ->press(__('auth.login.sign_in'))
            ->waitFor('@email-error')
            ->assertDontSeeIn('@email-error', __('validation.required', ['attribute' => 'email']))
            ->assertMissing('@password-error');
    });
});

test('that my page is in french', function(){
        $this->assertTrue(
        Lang::hasForLocale('auth.login.title', 'fr'),
    );
});

test('that a user can login', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->type('email', 'anchar2107@gmail.com')
            ->type('password', 'password')
            ->press(__('auth.login.sign_in'))
            ->waitForRoute('pages.dashboard')
            ->assertPathIs('/dashboard');
        //assert de voir un titre ici c'est mieux
    });
});



