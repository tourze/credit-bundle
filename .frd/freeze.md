# `credit-bundle` 资金冻结/解冻功能需求 (FRD)

## 1. 背景与目标

### 1.1 背景
平台多个业务模块（如 `order-commission-bundle` 的佣金提现、未来可能的预售/保证金业务）需要在用户资金上执行一个“预扣款”或“冻结”操作。这是一个多步骤、可能异步的流程。

当前的 `TransactionService` 只提供了 `increase` 和 `decrease` 这样的不可逆操作。在提现等场景中，如果先 `decrease`，后续流程（如人工审核、三方打款）失败后，需要再 `increase` 来“回滚”。这种模式存在资金安全风险，若“回滚”失败，将导致用户资金永久损失。

为了提供一个安全、标准的资金预处理流程，`credit-bundle` 作为平台的核心钱包服务，必须升级其能力，提供原子化的资金冻结与解冻接口。

### 1.2 目标
- 为 `TransactionService` 增加资金的冻结、解冻、确认扣款（提交）三种原子化操作。
- 确保用户资金在任何中间状态（冻结中）都是安全的，且最终流向（解冻回可用或确认扣除）是明确的。
- 账户模型需升级，能同时记录“可用余额”和“冻结余额”。

## 2. 核心能力 (API 设计)

`TransactionService` 需要新增以下接口：

```php
/**
 * 冻结用户一部分可用余额。
 *
 * @param int    $userId 用户ID
 * @param int    $amount 冻结金额（正整数）
 * @param string $reason 业务原因
 * @param string $referenceType 关联业务类型 (e.g., "commission_withdraw")
 * @param string $referenceId   关联业务单号 (e.g., WithdrawRequest ID)
 *
 * @return Transaction 成功则返回代表此冻结操作的交易对象
 * @throws InsufficientBalanceException 如果可用余额不足
 */
public function freeze(int $userId, int $amount, string $reason, string $referenceType, string $referenceId): Transaction;

/**
 * 解冻一个之前被冻结的交易。
 *
 * @param int    $originalFreezeTxId 原始冻结交易的ID
 * @param string $reason             解冻原因 (e.g., "提现申请被驳回")
 *
 * @return Transaction 成功则返回代表此解冻操作的新交易对象
 * @throws TransactionNotFoundException 如果找不到原始交易
 * @throws InvalidTransactionStatusException 如果原始交易并非处于可解冻状态
 */
public function unfreeze(int $originalFreezeTxId, string $reason): Transaction;

/**
 * 确认扣款一个之前被冻结的交易。
 * 这会将冻结金额从冻结余额中永久性地扣除。
 *
 * @param int    $originalFreezeTxId 原始冻结交易的ID
 * @param string $reason             确认原因 (e.g., "提现已打款成功")
 *
 * @return Transaction 成功则返回代表此确认操作的新交易对象
 * @throws TransactionNotFoundException 如果找不到原始交易
 * @throws InvalidTransactionStatusException 如果原始交易并非处于可确认状态
 */
public function commit(int $originalFreezeTxId, string $reason): Transaction;
```

数据模型上，`credit_account` 表需要从单一的 `balance` 字段，升级为 `available_balance` 和 `frozen_balance` 两个字段。

## 3. 使用场景：分销佣金提现

1.  **发起提现**：用户在分销后台为金额 `X` 发起提现申请。
2.  **冻结资金**：`order-commission-bundle` 调用 `TransactionService::freeze(userId, X, ...)`。
3.  **余额变更**：`credit-bundle` 内部执行：
    - 检查 `available_balance >= X`。
    - `available_balance -= X`
    - `frozen_balance += X`
    - 创建一条状态为 `FROZEN` 的交易记录。
4.  **审核与打款**：
    - **场景A：提现被驳回**
        - `order-commission-bundle` 调用 `TransactionService::unfreeze(originalTxId, ...)`。
        - `credit-bundle` 内部执行：
            - `frozen_balance -= X`
            - `available_balance += X`
            - 更新原交易状态为 `UNFROZEN` 或创建一条新的 `UNFREEZE` 交易。
    - **场景B：提现成功**
        - `order-commission-bundle` 在确认三方打款成功后，调用 `TransactionService::commit(originalTxId, ...)`。
        - `credit-bundle` 内部执行：
            - `frozen_balance -= X`
            - 更新原交易状态为 `COMMITTED` 或创建一条新的 `COMMIT` 交易。

## 4. 验收条件

### 4.1 功能性验收
- [ ] 调用 `freeze` 时，若 `available_balance` 不足，必须抛出 `InsufficientBalanceException` 异常，且余额无任何变化。
- [ ] `freeze` 成功后，`available_balance` 和 `frozen_balance` 的变更必须正确。
- [ ] 对一个已冻结的交易，调用 `unfreeze` 后，`available_balance` 和 `frozen_balance` 必须恢复到 `freeze` 操作之前的状态。
- [ ] 对一个已冻结的交易，调用 `commit` 后，`frozen_balance` 必须被正确扣除，`available_balance` 保持不变。
- [ ] 所有资金操作（freeze, unfreeze, commit）必须在数据库事务中执行，保证原子性。
- [ ] 针对同一笔 `originalFreezeTxId` 的 `unfreeze` 或 `commit` 操作必须是幂等的。
- [ ] 必须有清晰的交易流水，记录 `FROZEN`, `UNFROZEN`, `COMMITTED` 等状态。

### 4.2 非功能性验收
- [ ] 接口响应时间应在 100ms 以内。
- [ ] 必须有完整的审计日志，记录每一次资金操作的请求方、时间、金额、用户等信息。
