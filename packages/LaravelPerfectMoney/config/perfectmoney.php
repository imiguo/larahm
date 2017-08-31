<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Your Account ID
    |--------------------------------------------------------------------------
    |
    | The Account ID to use for authentication
    |
    */
    'account_id' => env('PM_ACCOUNT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Your Account Passphrase
    |--------------------------------------------------------------------------
    |
    | The Account Passphrase (password) used for authentication
    |
    */
    'passphrase' => env('PM_PASSPHRASE', ''),

    /*
    |--------------------------------------------------------------------------
    | Your Marchant Account
    |--------------------------------------------------------------------------
    |
    | The merchant’s Perfect Money® account to which the payment is to be made.
    |
    | Example: "U123456"
    |
    */
    'marchant_id' => env('PM_MARCHANT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Marchant Name
    |--------------------------------------------------------------------------
    |
    | The name the merchant wishes to have displayed as the Payee on the
    | Perfect Money® payment form.
    |
    | Example: "My company, Inc"
    |
    */
    'marchant_name' => env('PM_MARCHANT_NAME', ''),

    /*
    |--------------------------------------------------------------------------
    | Payment Units
    |--------------------------------------------------------------------------
    |
    | Specifies the units in which payment will be made.
    | Currency must correspond to selected account type (at Marchant Account).
    |
    | Supported: "USD", "EUR", "OAU"
    |
    */
    'units' => env('PM_UNITS', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Your Account Alternate Passphrase
    |--------------------------------------------------------------------------
    |
    | The Account Alternate Passphrase entered in your Perfect Money account
    |
    */
    'alternate_passphrase' => env('PM_ALTERNATE_PASSPHRASE', ''),

    /*
    |--------------------------------------------------------------------------
    | Payment URL
    |--------------------------------------------------------------------------
    |
    | The URL to which a form is submitted or to which a hypertext link
    | is taken by the buyer’s browser upon successful Perfect Money® payment
    | to the merchant. This is the buyer’s normal return path into
    | the merchant’s shopping cart system. This URL can specify a secure
    | protocol such as https.
    |
    */
    'payment_url' => env('PM_PAYMENT_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Payment URL Method (Optional)
    |--------------------------------------------------------------------------
    |
    | This field controls how the value for the PAYMENT_URL field is used.
    |
    | Supported:  null, "POST", "GET", "LINK"
    |
    */
    'payment_url_method' => env('PM_PAYMENT_URL_METHOD', null),

    /*
    |--------------------------------------------------------------------------
    | NoPayment URL
    |--------------------------------------------------------------------------
    |
    | The URL to which a form is submitted or to which a hypertext link is taken
    | by the buyer’s browser upon an unsuccessful or cancelled Perfect Money®
    | payment to the merchant.
    |
    | Note: this URL can be the same as that provided for PAYMENT_URL,
    | since status is provided on the form in hidden text fields to distinguish
    | between the two payment outcomes.
    |
    */
    'nopayment_url' => env('PM_NOPAYMENT_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | NoPayment URL Method (Optional)
    |--------------------------------------------------------------------------
    |
    | This field controls how the value for the NOPAYMENT_URL field is used
    |
    | Supported:  null, "POST", "GET", "LINK"
    |
    */
    'nopayment_url_method' => env('PM_NOPAYMENT_URL_METHOD', null),

    /*
    |--------------------------------------------------------------------------
    | Marchant Status URL (Optional)
    |--------------------------------------------------------------------------
    |
    | Marchant Status URL where will be sent the payment details
    | If status url not needed then set value as null.
    |
    | Legal URL types are “mailto:”, “http://”, and “https://”.
    | Non-standard port numbers are not supported.
    |
    */
    'status_url' => env('PM_STATUS_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Suggested Payment Description (Optional)
    |--------------------------------------------------------------------------
    |
    | Data for pre-entered MEMO input field
    | If description not required, left it null.
    |
    */
    'payment_memo' => env('PM_PAYMENT_MEMO', null),

    /*
    |--------------------------------------------------------------------------
    | Network timeout
    |--------------------------------------------------------------------------
    |
    | The max time spend for network
    |
    */
    'timeout' => env('TIMEOUT', 10),
];
