# Laravel Moneybird API Client

A PHP wrapper for the Moneybird API that provides an easy-to-use interface for interacting with your Moneybird administration.

## Installation

1. Install the package via composer:

```bash
composer require kobalt/laravel-moneybird
```

2. Publish the config file:

```bash
php artisan vendor:publish --provider="Kobalt\LaravelMoneybird\MoneybirdServiceProvider"
```

3. Add these variables to your .env file:

```env
MONEYBIRD_CLIENT_ID=your-client-id
MONEYBIRD_CLIENT_SECRET=your-client-secret
MONEYBIRD_REDIRECT_URI=https://your-app.com/moneybird/callback
```

4. Run the migrations:

```bash
php artisan migrate
```

5. Add the HasMoneybirdConnection trait to your User model:

```php
use Kobalt\LaravelMoneybird\Traits\HasMoneybirdConnection;

class User extends Authenticatable
{
    use HasMoneybirdConnection;
}
```

## Usage

### Connect User to Moneybird

Add a connect button to your view:

```php
@if(!auth()->user()->moneybird_access_token)
    <a href="{{ route('moneybird.connect') }}" class="btn btn-primary">
        Connect to Moneybird
    </a>
@else
    <div class="alert alert-success">
        Connected to Moneybird
    </div>
@endif
```

### Working with Resources

Once a user is connected, you can use the API like this:

```php
use Kobalt\LaravelMoneybird\Resources\Contact;
use Kobalt\LaravelMoneybird\Resources\SalesInvoice;
use Kobalt\LaravelMoneybird\Resources\Estimate;
use Kobalt\LaravelMoneybird\Resources\Product;

class InvoiceController extends Controller
{
    public function index()
    {
        // Initialize resources (will automatically use the authenticated user's token)
        $contacts = new Contact('your-administration-id');
        $invoices = new SalesInvoice('your-administration-id');

        // Get all contacts
        $allContacts = $contacts->all();

        // Create a new invoice
        $newInvoice = $invoices->create([
            'contact_id' => '123456789',
            'details_attributes' => [
                [
                    'description' => 'Development services',
                    'price' => 100.00,
                    'amount' => 4,
                    'tax_rate_id' => 'tax-rate-id'
                ]
            ]
        ]);

        // Send the invoice
        $invoices->send($newInvoice['id'], [
            'sending_method' => 'email',
            'email_address' => 'client@example.com'
        ]);
    }
}
```

### Error Handling

The package throws specific exceptions that you can catch:

```php
use Kobalt\LaravelMoneybird\Exceptions\ValidationException;
use Kobalt\LaravelMoneybird\Exceptions\NotFoundException;
use Kobalt\LaravelMoneybird\Exceptions\AuthenticationException;
use Kobalt\LaravelMoneybird\Exceptions\MoneybirdException;

try {
    $invoice = $invoices->find('non-existent-id');
} catch (ValidationException $e) {
    // Handle validation errors
    $errors = $e->getErrors();
    Log::error('Validation failed:', $errors);
} catch (AuthenticationException $e) {
    // Handle authentication errors
    // You might want to disconnect the user
    auth()->user()->disconnectMoneybird();
} catch (NotFoundException $e) {
    // Handle not found errors
} catch (MoneybirdException $e) {
    // Handle other Moneybird-specific errors
}
```

### Available Resources

- Contacts
- Sales Invoices
- Estimates (Quotes)
- Products

Each resource provides these common methods:
- `all()` - Get all records
- `find(string $id)` - Get a specific record
- `create(array $data)` - Create a new record
- `update(string $id, array $data)` - Update a record
- `delete(string $id)` - Delete a record

Some resources have additional methods:

#### SalesInvoice
- `send(string $id, array $options)` - Send invoice by email
- `registerPayment(string $id, array $paymentData)` - Mark invoice as paid

#### Estimate
- `send(string $id, array $options)` - Send estimate by email
- `convertToInvoice(string $id)` - Convert estimate to invoice

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the MIT license.