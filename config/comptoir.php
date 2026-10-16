<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Facturation Comptoir (POS) feature flag
    |--------------------------------------------------------------------------
    |
    | When false, nav links and routes behind EnsureComptoirEnabled are disabled.
    | Default off so incomplete installs do not expose the module.
    |
    */
    // filter_var handles "true"/"false"/"1"/"0" correctly; (bool)"false" is true in PHP.
    // Default ON so the side menu and routes are available after deploy.
    'enabled' => filter_var(env('COMPTOIR_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Money tolerance (FCFA-style rounding)
    |--------------------------------------------------------------------------
    */
    'money_tolerance' => 0.009,

    /*
    |--------------------------------------------------------------------------
    | Product search default limit
    |--------------------------------------------------------------------------
    */
    'product_search_limit' => 50,

];
