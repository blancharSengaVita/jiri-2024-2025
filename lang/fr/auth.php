<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lignes de langue d'authentification
    |--------------------------------------------------------------------------
    |
    | Les lignes de langue suivantes sont utilisées lors de l'authentification
    | pour divers messages que nous devons afficher à l'utilisateur. Vous êtes
    | libre de modifier ces lignes en fonction des exigences de votre application.
    |
    */

    'failed' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
    'password' => 'Le mot de passe fourni est incorrect.',
    'throttle' => 'Trop de tentatives de connexion. Veuillez réessayer dans :seconds secondes.',

    'login' => [
        'title' => 'Connectez-vous à votre compte',
        'remember_me' => 'Se souvenir de moi',
        'forgot_password' => 'Mot de passe oublié ?',
        'sign_in' => 'Connexion',
    ],
    'forgot_password' => [
        'title' => 'Mot de passe oublié ?',
        'description' => 'Entrez votre email et nous vous enverrons des instructions pour réinitialiser votre mot de passe.',
        'back_to_login' => 'Retour à la page de connexion',
        'send_reset_link' => 'Envoyer le lien de réinitialisation',
    ],
    'reset_password' => [
        'title' => 'Nouveau mot de passe',
        'description' => 'Votre nouveau mot de passe doit être différent des mots de passe précédemment utilisés.',
        'back_to_login' => 'Retour à la page de connexion',
        'set_new_password' => 'Enregistrer le nouveau mot de passe',
        'password' => 'Nouveau mot de passe',
        'password_repeat' => 'Confirmer le nouveau mot de passe',
    ],
    'create_password' => [
        'title' => 'Créer votre mot de passe',
        'description' => 'Renseigner votre adresse e-mail et votre mot de passe ci-dessous.',
        'back_to_login' => 'Retour à la page de connexion',
        'set_new_password' => 'Enregistrer votre mot de passe',
        'password' => 'Mot de passe',
        'password_repeat' => 'Confirmer le mot de passe',
    ],
    'verify_email' => [
        'title' => 'Vérifiez votre adresse e-mail',
        'description' => 'Avant de commencer, nous devons vérifier votre adresse e-mail.',
        'resend_verification_link' => 'Envoyer le lien de vérification',
        'logout' => 'Déconnexion',
    ],
    'confirm_password' => [
        'title' => 'Ceci est un espace sécurisé',
        'description' => 'Veuillez renseigner votre mot de passe avant de pouvoir poursuivre.',
        'confirm' => 'Confirmer',
    ],
    'fields' => [
        'email' => [
            'label' => 'E-mail',
            'placeholder' => 'john@example.com',
        ],
        'password' => [
            'label' => 'Mot de passe',
            'placeholder' => '············',
            'forgot' => 'Mot de passe oublié ?',
        ],
        'password_new' => [
            'label' => 'Nouveau mot de passe',
            'placeholder' => '············',
        ],
        'password_confirm' => [
            'label' => 'Confirmer le nouveau mot de passe',
            'placeholder' => '············',
        ],
    ],
];
