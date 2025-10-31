# tourze/credit-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)
[![Build Status](https://github.com/tourze/php-monorepo/actions/workflows/test.yml/badge.svg)](https://github.com/tourze/php-monorepo/actions)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/credit-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/credit-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)
[![License](https://img.shields.io/packagist/l/tourze/credit-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/credit-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

这是一个积分管理模块。

## 目录

- [功能特性](#功能特性)
- [安装说明](#安装说明)
- [依赖要求](#依赖要求)
- [快速开始](#快速开始)
- [配置说明](#配置说明)
- [文档说明](#文档说明)
- [控制台命令](#控制台命令)
- [高级用法](#高级用法)
- [贡献指南](#贡献指南)
- [许可证](#许可证)
- [更新日志](#更新日志)

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

## 依赖要求

本包需要以下依赖：

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 2.14 或更高版本
- symfony/security-core 用于用户管理
- tourze/lock-service-bundle 用于分布式锁

可选依赖：

- symfony/messenger 用于异步操作
- doctrine/doctrine-migrations-bundle 用于数据库迁移

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

## 配置说明

### Bundle 注册

在 `config/bundles.php` 中添加 Bundle：

```php
return [
    // ... 其他 bundles
    CreditBundle\CreditBundle::class => ['all' => true],
];
```

### 数据库结构

为积分系统表创建并执行迁移：

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

Bundle 会创建以下数据表：
- `credit_account` - 用户/系统积分账户
- `credit_transaction` - 积分交易记录
- `credit_currency` - 可用积分币种
- `credit_limit` - 交易限制
- `credit_adjust_request` - 手动调整请求
- `credit_consume_log` - 消费跟踪日志

### 服务配置

Bundle 使用 Symfony 的自动装配，无需手动配置服务。

可选配置 `config/packages/credit.yaml`：

```yaml
# 目前不需要特定的配置参数
# 服务通过 Bundle 的 services.yaml 自动配置
```

## 文档说明

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
此 Bundle 依赖 Symfony 的自动装配和服务自动配置。
基本操作无需特定的配置参数。
服务通过 Bundle 内的 `services.yaml` 文件自动配置。

关于 API、高级特性和性能优化建议的更多详细信息将后续补充。

## 控制台命令

此 Bundle 提供了多个用于积分管理的控制台命令：

### credit:adjust
自动调整积分余额以匹配最近一天内更新的账户的交易记录。

```bash
php bin/console credit:adjust
```

### credit:batch-adjust
使用 Excel 文件批量调整积分。

```bash
php bin/console credit:batch-adjust /path/to/file.xls
```

Excel 文件格式要求：
- 表头必须包含："账户名"、"变更数值"、"变更备注"、"积分名"

### credit:calc:expire-transaction
计算并处理指定账户的过期积分。

```bash
php bin/console credit:calc:expire-transaction <accountId>
```

### credit:decrease
减少指定用户的积分。

```bash
php bin/console credit:decrease <currency> <userId> <amount>
```

参数说明：
- `currency`: 积分货币代码
- `userId`: 用户 ID
- `amount`: 减少的数量

### credit:increase
增加指定用户的积分。

```bash
php bin/console credit:increase <currency> <userId> <amount>
```

参数说明：
- `currency`: 积分货币代码
- `userId`: 用户 ID
- `amount`: 增加的数量

### credit:send-notice
发送欠费提醒（待办 - 功能尚未实现）。

```bash
php bin/console credit:send-notice
```

## 高级用法

### 异步操作

在高性能场景下，使用异步方法：

```php
// 异步增加
$transactionService->asyncIncrease(
    'event_id',
    $account,
    100.0,
    '异步奖励'
);

// 异步减少
$transactionService->asyncDecrease(
    'event_id',
    $account,
    50.0,
    '异步购买'
);
```

### 积分过期

为积分设置过期时间：

```php
$expireTime = new \DateTime('+30 days');
$transactionService->increase(
    'event_id',
    $account,
    100.0,
    '限时奖励',
    $expireTime
);
```

### 交易限制

配置交易限制：

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

### 手动调整

创建调整请求：

```php
use CreditBundle\Entity\AdjustRequest;
use CreditBundle\Enum\AdjustRequestType;

$adjustRequest = new AdjustRequest();
$adjustRequest->setAccount($account);
$adjustRequest->setType(AdjustRequestType::INCREASE);
$adjustRequest->setAmount(500.0);
$adjustRequest->setRemark('错误更正的手动调整');
$entityManager->persist($adjustRequest);
$entityManager->flush();
```

## 贡献指南

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 获取详细信息。

## 许可证

The MIT License (MIT). 请查看 [License File](LICENSE) 获取更多信息。

## 已知问题

### 测试环境依赖

目前，PHPUnit 测试需要复杂的 Symfony 服务配置，在独立运行时可能因为缺少服务依赖而失败。这个问题正在 [Issue #706](https://github.com/tourze/php-monorepo/issues/706) 中跟踪。

开发和测试说明：
- PHPStan 分析工作正常
- 单个服务和实体测试可以通过与完整应用程序集成来完成
- 简单实体和值对象的单元测试按预期工作

## 更新日志

- 基于模板初始化 README 结构。
- 根据初步代码审查，添加了初步的功能特性、快速入门和文档部分。
- 填充文档部分，包含服务、实体和配置详细信息。
- 添加 API 文档、高级特性和性能优化建议说明。
- 创建 `CONTRIBUTING.md` 占位文件。
- 更新构建状态徽章为 GitHub Actions 并修正 `CONTRIBUTING.md` 链接。
- 添加已知问题部分，记录测试环境挑战。
