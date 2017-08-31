# Laravel Perfect Money

## Install

Via Composer

``` bash
$ composer require entimm/laravel-perfectmoney
```

Add Provider

``` php
entimm\LaravelPerfectMoney\PerfectMoneyServiceProvider::class,
```

Add Aliases

``` php
'PerfectMoney' => entimm\LaravelPerfectMoney\PerfectMoney::class,
```

##Configuration

Publish Configuration file
```
php artisan vendor:publish --provider="entimm\LaravelPerfectMoney\PerfectMoneyServiceProvider" --tag="config"
```

Edit .env

Add these lines at .env file, follow config/perfectmoney.php for configuration descriptions.
``` php
PM_ACCOUNTID=100000
PM_PASSPHRASE=your_pm_password
PM_MARCHANTID=U123456
PM_MARCHANT_NAME="My Company"
PM_UNITS=USD
PM_ALT_PASSPHRASE=your_alt_passphrase
PM_PAYMENT_URL=http://example.com/success
PM_PAYMENT_URL_METHOD=null
PM_NOPAYMENT_URL=http://example.com/fail
PM_NOPAYMENT_URL_METHOD=null
PM_STATUS_URL=null
PM_SUGGESTED_MEMO=null
```

##Customizing views (Optional)

If you want to customize form, follow these steps.

### 1.Publish view
```
php artisan vendor:publish --provider="entimm\LaravelPerfectMoney\PerfectMoneyServiceProvider" --tag="views"
```
### 2.Edit your view at /resources/views/vendor/perfectmoney/perfectmoney.php

## Usage

###Render Shopping Cart Form

``` php
PerfectMoney::render();
```

Sometimes you will need to customize the payment form. Just pass the parameters to render method .

``` php
PerfectMoney::render(['PAYMENT_UNITS' => 'EUR'], 'custom_view');
```

## API MODULES
### Get Balance
``` php
$balances = PerfectMoney::getBalance();
```

### Send Money
``` php
// Required Fields
$amount = 10.00;
$sendTo = 'U1234567';

// Optional Fields
$description = 'Optional Description for send money';
$payment_id = 'Optional_payment_id';

// Send Funds with all fields
$sendMoney = PerfectMoney::sendMoney($amount, $sendTo, $description, $payment_id);

// Send Funds with required fields
$sendMoney = PerfectMoney::sendMoney($amount, $sendTo);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-packagist]: https://packagist.org/packages/entimm/laravel-perfectmoney