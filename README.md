# tourze/credit-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)
[![Build Status](https://github.com/tourze/php-monorepo/actions/workflows/test.yml/badge.svg)](https://github.com/tourze/php-monorepo/actions)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/credit-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/credit-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)
[![License](https://img.shields.io/packagist/l/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

This is a credit management module.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Dependencies](#dependencies)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Documentation](#documentation)
- [Console Commands](#console-commands)
- [Advanced Usage](#advanced-usage)
- [Contributing](#contributing)
- [License](#license)
- [Changelog](#changelog)

## Features

- Manages user and system credit accounts for multiple currencies.
- Supports credit increase, decrease, and transfer operations.
- Provides services for account and transaction management.
- Includes asynchronous processing for credit operations.
- Supports credit expiration and transaction remarks.
- Allows associating transactions with other business entities.
- Manages transaction limits and adjustment requests.
- Provides command-line tools for credit management tasks.

## Installation

```bash
composer require tourze/credit-bundle
```

## Dependencies

This bundle requires:

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 2.14 or higher
- symfony/security-core for user management
- tourze/lock-service-bundle for distributed locking

Optional dependencies:

- symfony/messenger for async operations
- doctrine/doctrine-migrations-bundle for database migrations

## Quick Start

```php
<?php

use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use CreditBundle\Entity\Currency; // You'll need to obtain/create a Currency instance
use App\Entity\User; // User instance implementing UserInterface

// Prerequisites:
// 1. Ensure AccountService and TransactionService are available (e.g., via Symfony DI).
// 2. Obtain or create a `Currency` entity instance.
// 3. Obtain or create `User` entity instances.

/**
 * @var AccountService $accountService
 * @var TransactionService $transactionService
 * @var Currency $currencyInstance // e.g., fetched from DB or created
 * @var User $userOne // An instance of your User entity
 * @var User $userTwo // An instance of your User entity
 */

// Get/create credit accounts for users
$accountUserOne = $accountService->getAccountByUser($userOne, $currencyInstance);
$accountUserTwo = $accountService->getAccountByUser($userTwo, $currencyInstance);

// Increase credit for User One
$eventNoIncrease = 'YOUR_UNIQUE_EVENT_ID_1'; // Ensure this is unique per operation
$transactionService->increase(
    $eventNoIncrease,
    $accountUserOne,
    100.0, // Amount
    'Welcome bonus' // Remark
);

// Decrease credit for User One
$eventNoDecrease = 'YOUR_UNIQUE_EVENT_ID_2'; // Ensure this is unique
$transactionService->decrease(
    $eventNoDecrease,
    $accountUserOne,
    25.0, // Amount
    'Purchase item #123' // Remark
);

// Transfer credit from User One to User Two
$eventNoTransfer = $transactionService->transfer(
    $accountUserOne,
    $accountUserTwo,
    50.0, // Amount
    'Gift for User Two'
);

// For asynchronous operations:
// $transactionService->asyncIncrease(...);
// $transactionService->asyncDecrease(...);

// Note: Check the Account entity (e.g., getEndingBalance()) or related services for balance retrieval methods.

```

## Configuration

### Bundle Registration

Add the bundle to your `config/bundles.php`:

```php
return [
    // ... other bundles
    CreditBundle\CreditBundle::class => ['all' => true],
];
```

### Database Schema

Create and run migrations for the credit system tables:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

The bundle creates the following tables:
- `credit_account` - Credit accounts for users/system
- `credit_transaction` - Credit transaction records
- `credit_currency` - Available credit currencies
- `credit_limit` - Transaction limits
- `credit_adjust_request` - Manual adjustment requests
- `credit_consume_log` - Consumption tracking logs

### Service Configuration

The bundle uses Symfony's autowiring. No manual service configuration is required.

Optional configuration in `config/packages/credit.yaml`:

```yaml
# Currently no specific configuration parameters are required
# Services are automatically configured via the bundle's services.yaml
```

## Documentation

This bundle provides several services to manage credits:

-   `CreditBundle\Service\AccountService`: For managing credit accounts (retrieving, creating for users or system).
-   `CreditBundle\Service\TransactionService`: For performing credit transactions like increase, decrease, and transfer.
-   `CreditBundle\Service\CreditIncreaseService`: Handles credit increase logic and async operations.
-   `CreditBundle\Service\CreditDecreaseService`: Handles credit decrease logic and consumption tracking.
-   `CreditBundle\Service\CurrencyService`: For managing currencies used in the credit system.
-   `CreditBundle\Service\TransactionLimitService`: For managing transaction limits.

Key Entities:
-   `CreditBundle\Entity\Account`: Represents a credit account for a user or system, tied to a currency.
-   `CreditBundle\Entity\Transaction`: Represents a single credit transaction (increase or decrease).
-   `CreditBundle\Entity\Currency`: Represents a currency type (e.g., points, virtual cash).
-   `CreditBundle\Entity\Limit`: Defines transaction limits for accounts.
-   `CreditBundle\Entity\AdjustRequest`: For managing manual credit adjustments.
-   `CreditBundle\Entity\ConsumeLog`: Logs how credit from income transactions is consumed by outgoing transactions.

Configuration:
This bundle relies on Symfony's autowiring and service autoconfiguration. 
No specific configuration parameters are required for basic operation. 
Services are automatically configured via the bundle's `services.yaml`.

API Documentation:
- Detailed API documentation is planned and will be linked here. For now, please refer to the service and entity class docblocks for method signatures and descriptions.

### Advanced Features

- **Asynchronous Operations:** Certain credit operations can be handled asynchronously
- **Credit Expiration:** Support for time-limited credits with automatic expiration
- **Transaction Limits:** Define and enforce limits on credit transactions
- **Manual Adjustments:** Support manual credit adjustments with approval workflow
- **Consumption Tracking:** Detailed audit trails for transaction consumption

### Performance Optimization

- Utilize asynchronous operations for non-critical credit updates
- Implement appropriate database indexing on frequently queried columns
- Consider caching strategies for currency and system account data

## Console Commands

The bundle provides several console commands for credit management:

### credit:adjust
Automatically adjust credit balances to match transaction records for accounts updated in the last day.

```bash
php bin/console credit:adjust
```

### credit:batch-adjust
Batch adjust credits using an Excel file.

```bash
php bin/console credit:batch-adjust /path/to/file.xls
```

Excel file format requirements:
- Headers must include: "账户名" (Account Name), "变更数值" (Change Value), "变更备注" (Change Remark), "积分名" (Credit Name)

### credit:calc:expire-transaction
Calculate and process expired credits for a specific account.

```bash
php bin/console credit:calc:expire-transaction <accountId>
```

### credit:decrease
Decrease credits for a specific user.

```bash
php bin/console credit:decrease <currency> <userId> <amount>
```

Parameters:
- `currency`: Credit currency code
- `userId`: User ID
- `amount`: Amount to decrease

### credit:increase
Increase credits for a specific user.

```bash
php bin/console credit:increase <currency> <userId> <amount>
```

Parameters:
- `currency`: Credit currency code
- `userId`: User ID
- `amount`: Amount to increase

### credit:send-notice
Send overdraft notifications (TODO - functionality not yet implemented).

```bash
php bin/console credit:send-notice
```

## Advanced Usage

### Asynchronous Operations

For high-performance scenarios, use async methods:

```php
// Async increase
$transactionService->asyncIncrease(
    'event_id',
    $account,
    100.0,
    'Async bonus'
);

// Async decrease
$transactionService->asyncDecrease(
    'event_id',
    $account,
    50.0,
    'Async purchase'
);
```

### Credit Expiration

Set expiration dates for credits:

```php
$expireTime = new \DateTime('+30 days');
$transactionService->increase(
    'event_id',
    $account,
    100.0,
    'Limited time bonus',
    $expireTime
);
```

### Transaction Limits

Configure transaction limits:

```php
use CreditBundle\Entity\Limit;
use CreditBundle\Enum\LimitType;

$limit = new Limit();
$limit->setAccount($account);
$limit->setType(LimitType::DAILY);
$limit->setAmount(1000.0);
$entityManager->persist($limit);
$entityManager->flush();
```

### Manual Adjustments

Create adjustment requests:

```php
use CreditBundle\Entity\AdjustRequest;
use CreditBundle\Enum\AdjustRequestType;

$adjustRequest = new AdjustRequest();
$adjustRequest->setAccount($account);
$adjustRequest->setType(AdjustRequestType::INCREASE);
$adjustRequest->setAmount(500.0);
$adjustRequest->setRemark('Manual adjustment for error correction');
$entityManager->persist($adjustRequest);
$entityManager->flush();
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Known Issues

### Test Environment Dependencies

Currently, PHPUnit tests require complex Symfony service configuration and may fail in isolation due to missing service dependencies. This is being tracked in [Issue #706](https://github.com/tourze/php-monorepo/issues/706).

For development and testing:
- PHPStan analysis works correctly
- Individual service and entity testing can be done through integration with the full application
- Unit tests for simple entities and value objects work as expected

## Changelog

- Initial README structure based on template.
- Added preliminary Features, Quick Start, and Documentation sections based on initial code review.
- Populated Documentation section with details on services, entities, and configuration.
- Added notes on API documentation, advanced features, and performance optimization suggestions.
- Created a placeholder `CONTRIBUTING.md` file.
- Updated build status badge to GitHub Actions and corrected `CONTRIBUTING.md` link.
- Added Known Issues section documenting test environment challenges.
