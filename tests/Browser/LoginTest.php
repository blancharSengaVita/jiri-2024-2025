<?php

use Illuminate\Support\Facades\Lang;
use Laravel\Dusk\Browser;
use App\Models\User;


test('that shows front-end errors of login form', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->assertAttribute('@email', 'type', 'email')
            ->assertAttribute('@email', 'required', '')
            ->assertAttribute('@password', 'type', 'password')
            ->assertAttribute('@password', 'required', '');
    });
});

//désactiver le required des inputs pour ce test ou alors demander au prof
//test('that shows errors of login form', function () {
//    $this->browse(function (Browser $browser) {
//        $browser->visit('/login')
//            ->press('Sign in')
//            ->waitFor('@email-error')
//            ->assertPresent('@email-error')
//            ->waitFor('@password-error')
//            ->assertPresent('@password-error');
//    });
//});

test('that there is no required error when email and password are populate', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->type('email', 'bla@bla.com')
            ->type('password', 'password')
            ->press('Sign in')
            ->waitFor('@email-error')
            ->assertDontSeeIn('@email-error', __('validation.required', ['attribute' => 'email']))
            ->assertMissing('@password-error');
    });
});

test('that it has a french validation message', function(){
        $this->assertTrue(
        Lang::hasForLocale('validation.required', 'en'),
        'English validation message not found'
    );
});


//        public function it_has_an_english_validation_message()
//{
//    $this->assertTrue(
//        \Lang::hasForLocale('validation.is_tall', 'en'),
//        'English validation message not found'
//    );
//}

//test('that a user can login', function () {
//    $this->browse(function (Browser $browser) {
//        $browser->visit('/login')
//            ->type('email', 'test@example.com')
//            ->type('password', 'password')
//            ->press('Sign in')
//            ->assertPathIs('/dashboard');
//    });
//});






