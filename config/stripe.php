<?php

return[

    /*
    |--------------------------------------------------------------------------
    | Stripe Publishable Key
    |--------------------------------------------------------------------------
    |
    | The Stripe publishable key is generally used by the client side application
    | meant solely to identify your account with Stripe, they aren't secret.
    | In other words, they can safely be published in places like your Stripe.js,
    | Javascript code, or in an Android or iPhone app.
    |
    */

    'publishable' => env('STRIPE_PUBLISHABLE', null),


    /*
    |--------------------------------------------------------------------------
    | Stripe Secret Key
    |--------------------------------------------------------------------------
    |
    | The Stripe Secret Key should be confidential and only stored on our
    | server. They can perform any API request to Stripe without restriction.
    |
    */

    'secret' => env('STRIPE_SECRET', null),
];
