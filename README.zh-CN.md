# tourze/credit-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)
[![Build Status](https://github.com/tourze/php-monorepo/actions/workflows/packages%2Fcredit-bundle%2F.github%2Fworkflows%2Fphpunit.yml/badge.svg)](https://github.com/tourze/php-monorepo/actions/workflows/packages/credit-bundle/.github/workflows/phpunit.yml)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/credit-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/credit-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)

这是一个积分管理模块。

## 功能特性

- 管理用户和系统的多币种积分账户。
- 支持积分增加、减少和转移操作。
- 提供账户和交易管理服务。
- 支持积分操作的异步处理。
- 支持积分过期和交易备注。
- 允许将交易与其他业务实体关联。
- 管理交易限制和调整请求。
- 提供用于积分管理任务的命令行工具。

## 安装说明

```bash
composer require tourze/credit-bundle
```

## 快速开始

```php
<?php

use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use CreditBundle\Entity\Currency; // 您需要获取/创建一个 Currency 实例
use App\Entity\User; // 您需要获取/创建一个 User 实例 (实现 Symfony\Component\Security\Core\User\UserInterface)

// 先决条件:
// 1. 确保 AccountService 和 TransactionService 可用 (例如，通过 Symfony DI 注入).
// 2. 获取或创建一个 `Currency` 实体实例.
// 3. 获取或创建 `User` 实体实例.

/**
 * @var AccountService $accountService
 * @var TransactionService $transactionService
 * @var Currency $currencyInstance // 例如，从数据库获取或创建
 * @var User $userOne // 您的用户实体实例
 * @var User $userTwo // 您的用户实体实例
 */

// 获取/创建用户积分账户
$accountUserOne = $accountService->getAccountByUser($userOne, $currencyInstance);
$accountUserTwo = $accountService->getAccountByUser($userTwo, $currencyInstance);

// 为用户一增加积分
$eventNoIncrease = 'YOUR_UNIQUE_EVENT_ID_1'; // 确保此ID对于每个操作都是唯一的
$transactionService->increase(
    $eventNoIncrease,
    $accountUserOne,
    100.0, // 金额
    '欢迎奖励' // 备注
);

// 为用户一减少积分
$eventNoDecrease = 'YOUR_UNIQUE_EVENT_ID_2'; // 确保此ID唯一
$transactionService->decrease(
    $eventNoDecrease,
    $accountUserOne,
    25.0, // 金额
    '购买商品 #123' // 备注
);

// 从用户一转移积分到用户二
$eventNoTransfer = $transactionService->transfer(
    $accountUserOne,
    $accountUserTwo,
    50.0, // 金额
    '给用户二的礼物'
);

// 异步操作示例:
// $transactionService->asyncIncrease(...);
// $transactionService->asyncDecrease(...);

// 注意: 查看 Account 实体 (例如 getEndingBalance()) 或相关服务以获取余额检索方法。

```

## 文档

此 Bundle 提供了几个用于管理积分的服务:

-   `CreditBundle\Service\AccountService`: 用于管理积分账户 (检索、为用户或系统创建账户)。
-   `CreditBundle\Service\TransactionService`: 用于执行积分交易，如增加、减少和转移。
-   `CreditBundle\Service\CreditIncreaseService`: 处理增加积分的逻辑，包括异步操作。
-   `CreditBundle\Service\CreditDecreaseService`: 处理减少积分的逻辑，包括异步操作和消耗跟踪。
-   `CreditBundle\Service\CurrencyService`: 用于管理积分系统中使用的货币。
-   `CreditBundle\Service\TransactionLimitService`: 用于管理交易限制。

关键实体:
-   `CreditBundle\Entity\Account`: 代表用户或系统的积分账户，与特定货币关联。
-   `CreditBundle\Entity\Transaction`: 代表单次积分交易 (增加或减少)。
-   `CreditBundle\Entity\Currency`: 代表货币类型 (例如，积分、虚拟货币)。
-   `CreditBundle\Entity\Limit`: 定义账户的交易限制。
-   `CreditBundle\Entity\AdjustRequest`: 用于管理手动积分调整。
-   `CreditBundle\Entity\ConsumeLog`: 记录收入交易中的积分如何被支出交易消耗。

配置说明:
此 Bundle 依赖 Symfony 的自动装配 (autowiring) 和服务自动配置 (service autoconfiguration)。在您的 `config/packages/credit.yaml` 或类似文件中，基本操作无需特定的 Bundle 级别配置参数。服务通过 Bundle 内的 `services.yaml` 文件自动配置。

关于 API、高级特性和性能优化建议的更多详细信息将后续补充。

## 如何贡献

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 获取详细信息。

## 授权许可

The MIT License (MIT). 请查看 [License File](LICENSE) 获取更多信息。

## 更新日志

- 基于模板初始化 README 结构。
- 根据初步代码审查，添加了初步的功能特性、快速入门和文档部分。
