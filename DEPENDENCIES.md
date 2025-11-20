# Dependencies and setup

This project is a plain PHP app intended to run under XAMPP (Apache + PHP + MySQL). There are no third-party Composer libraries required by the app code, but the following PHP extensions must be enabled for the app to function correctly:

- PHP >= 7.4
- ext-pdo
- ext-pdo_mysql
- ext-json

Setup steps (Windows / XAMPP):

1. Ensure XAMPP is installed and Apache + MySQL services are running.
2. Open your `php.ini` (e.g. `C:\\xampp\\php\\php.ini`) and make sure the following extensions are enabled (remove `;` if present):

```
extension=pdo_mysql
```

3. Restart Apache from the XAMPP control panel.

Optional (Composer):

- A `composer.json` file is included to declare the required PHP version and extensions. If you use Composer to manage dependencies, run:

```
cd C:\\xampp\\htdocs\\Don
composer install
```

This will not install additional packages (none are required), but will validate the environment and create a `vendor/` directory if you later add Composer packages.

If you need mail sending or templating functionality later, consider adding packages such as `phpmailer/phpmailer` or `twig/twig` via Composer.

M-Pesa (Daraja) integration
- We added `guzzlehttp/guzzle` to `composer.json` to allow calling the Safaricom Daraja API.
- After updating `composer.json`, run:

```
cd C:\\xampp\\htdocs\\Don
composer install
```

- Edit `mpesa.php` and set your Daraja credentials: `MPESA_CONSUMER_KEY`, `MPESA_CONSUMER_SECRET`, `MPESA_PASSKEY`, and `MPESA_CALLBACK_URL`.
- The helper provides `mpesa_stk_push($phone, $amount, $accountRef, $description)` which `generate_bill.php` now calls automatically when the `payment_method` is `MPesa`.

Notes:
- The current `mpesa.php` uses Guzzle. If Composer isn't available, I can update it to use PHP streams instead.
- You'll need to set up a reachable `MPESA_CALLBACK_URL` to receive confirmation notifications from Safaricom.
