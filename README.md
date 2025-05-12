# tourze/credit-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)
[![Build Status](https://github.com/tourze/php-monorepo/actions/workflows/packages%2Fcredit-bundle%2F.github%2Fworkflows%2Fphpunit.yml/badge.svg)](https://github.com/tourze/php-monorepo/actions/workflows/packages/credit-bundle/.github/workflows/phpunit.yml)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/credit-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/credit-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)

This is a credit management module.

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

## Quick Start

```php
<?php

use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use CreditBundle\Entity\Currency; // You'll need to obtain/create a Currency instance
use App\Entity\User; // You'll need to obtain/create a User instance (implementing Symfony\Component\Security\Core\User\UserInterface)

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

## Documentation

This bundle provides several services to manage credits:

-   `CreditBundle\Service\AccountService`: For managing credit accounts (retrieving, creating for users or system).
-   `CreditBundle\Service\TransactionService`: For performing credit transactions like increase, decrease, and transfer.
-   `CreditBundle\Service\CreditIncreaseService`: Handles the logic for increasing credits, including asynchronous operations.
-   `CreditBundle\Service\CreditDecreaseService`: Handles the logic for decreasing credits, including asynchronous operations and consumption tracking.
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
This bundle relies on Symfony's autowiring and service autoconfiguration. There are no specific bundle-level configuration parameters required in your `config/packages/credit.yaml` or similar files for basic operation. Services are automatically configured via `services.yaml` within the bundle.

API Documentation:
- Detailed API documentation is planned and will be linked here. For now, please refer to the service and entity class docblocks for method signatures and descriptions.

Advanced Features:
- **Asynchronous Operations:** Certain credit operations (e.g., `asyncIncrease`, `asyncDecrease` in `TransactionService`) can be handled asynchronously for improved performance and responsiveness.
- **Credit Expiration:** The `Transaction` entity includes an `expireTime` field, allowing for time-limited credits. Logic for handling expiration (e.g., `CalcExpireTransactionCommand`) is present.
- **Transaction Limits:** The `Limit` entity and `TransactionLimitService` can be used to define and enforce limits on credit transactions.
- **Manual Adjustments:** The `AdjustRequest` entity and related services/commands (e.g., `AdjustCreditCommand`) support manual credit adjustments with an approval workflow (implied by `AdjustRequestStatus`).
- **Consumption Tracking:** `ConsumeLog` entity tracks how income transactions are utilized by outgoing transactions, providing detailed audit trails.

Performance Optimization Suggestions:
- Utilize asynchronous operations for non-critical credit updates.
- Implement appropriate database indexing, especially for `Transaction` and `Account` tables on frequently queried columns.
- Consider caching strategies for frequently accessed, rarely changing data like currency information or system account details.

Further details will be added as the bundle evolves.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Changelog

- Initial README structure based on template.
- Added preliminary Features, Quick Start, and Documentation sections based on initial code review.
- Populated Documentation section with details on services, entities, and configuration.
- Added notes on API documentation, advanced features, and performance optimization suggestions.
- Created a placeholder `CONTRIBUTING.md` file.
- Updated build status badge to GitHub Actions and corrected `CONTRIBUTING.md` link.
