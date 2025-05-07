# CreditBundle 数据流转图

## 实体关系图

```mermaid
classDiagram
    class Currency {
        +String currency
        +String name
        +Boolean main
        +Boolean valid
        +String remark
    }

    class Account {
        +String name
        +Currency currency
        +BizUser user
        +String endingBalance
        +String increasedAmount
        +String decreasedAmount
        +String expiredAmount
    }

    class Transaction {
        +String eventNo
        +Account account
        +String amount
        +String balance
        +String remark
        +Currency currency
        +DateTime expireTime
        +String relationId
        +String relationModel
        +Array context
    }

    class ConsumeLog {
        +Transaction costTransaction
        +Transaction consumeTransaction
        +String amount
    }

    class Limit {
        +Account account
        +String type
        +String value
        +DateTime startTime
        +DateTime endTime
    }

    class TransferLog {
        +String eventNo
        +Account fromAccount
        +Account toAccount
        +String amount
        +String remark
    }

    class AdjustRequest {
        +Account account
        +String amount
        +String oldAmount
        +String newAmount
        +String reason
        +String status
    }

    Currency "1" -- "多" Account : 一种币种可有多个账户
    Account "1" -- "多" Transaction : 一个账户有多笔交易
    Account "1" -- "多" Limit : 一个账户有多个限制
    Transaction "1" -- "多" ConsumeLog : 一笔收入可被多笔支出消费
    Account "1" -- "多" AdjustRequest : 一个账户可有多个调整请求
    Currency "1" -- "多" Transaction : 交易属于某种币种
    BizUser "1" -- "多" Account : 一个用户可有多个账户
```

## 积分流转流程图

```mermaid
flowchart TD
    A[开始] --> B{操作类型}

    B -->|增加积分| C[increase方法]
    B -->|减少积分| D[decrease方法]
    B -->|转账| E[transfer方法]

    C --> C1[检查每日/每月限额]
    C1 --> C2[检查账户自定义限额]
    C2 --> C3[锁定账户]
    C3 --> C4[创建Transaction记录]
    C4 --> C5[更新Account余额]
    C5 --> C6[发布IncreasedEvent事件]
    C6 --> Z[结束]

    D --> D1[发布DecreasedEvent事件]
    D1 --> D2{需本地执行?}
    D2 -->|是| D3[检查可用余额]
    D2 -->|否| Z
    D3 --> D4[检查账户限额]
    D4 --> D5[检查是否需要合并小额积分]
    D5 -->|是| D6[执行积分合并]
    D5 -->|否| D7[按FIFO原则消费积分]
    D6 --> D7
    D7 --> D8[创建消费Transaction记录]
    D8 --> D9[创建ConsumeLog记录]
    D9 --> D10[更新Account余额]
    D10 --> Z

    E --> E1[开启事务]
    E1 --> E2[调用decrease方法]
    E2 --> E3[调用increase方法]
    E3 --> E4[提交事务]
    E4 --> Z
```

## 积分过期处理流程

```mermaid
flowchart TD
    A[开始] --> B[获取即将过期积分信息]
    B --> C[计算过期积分总额]
    C --> D[锁定账户]
    D --> E[调用decrease方法]
    E --> F[标记isExpired=true]
    F --> G[更新Account过期积分统计]
    G --> Z[结束]
```

## 事件系统数据流

```mermaid
sequenceDiagram
    participant Client
    participant TransactionService
    participant EventDispatcher
    participant EventSubscriber
    participant ExternalSystem

    Client->>TransactionService: 调用increase()
    TransactionService->>TransactionService: 处理积分增加
    TransactionService->>EventDispatcher: 发布IncreasedEvent
    EventDispatcher->>EventSubscriber: 通知订阅者
    EventSubscriber->>ExternalSystem: 可选:同步到外部系统

    Client->>TransactionService: 调用decrease()
    TransactionService->>EventDispatcher: 发布DecreasedEvent
    EventDispatcher->>EventSubscriber: 通知订阅者
    EventSubscriber->>ExternalSystem: 可选:查询外部系统
    EventSubscriber->>TransactionService: 设置是否本地执行
    TransactionService->>TransactionService: 如需本地执行则处理
```

## 账户余额计算逻辑

```mermaid
flowchart LR
    A[获取账户余额] --> B{有外部事件订阅器?}
    B -->|是| C[调用外部系统查询]
    B -->|否| D[返回Account.endingBalance]
    C --> E{外部返回结果?}
    E -->|是| F[返回外部系统余额]
    E -->|否| D
```

## 数据一致性保障流程

```mermaid
flowchart TD
    A[开始] --> B[使用事务操作]
    B --> C[使用锁服务锁定账户]
    C --> D[操作前刷新实体状态]
    D --> E[先发事件后执行本地逻辑]
    E --> F[使用snowflake生成唯一事件号]
    F --> G[使用ConsumeLog跟踪消费记录]
    G --> Z[结束]
```

## 小额积分合并策略

```mermaid
flowchart TD
    A[开始] --> B{是否开启自动合并?}
    B -->|否| Z[结束]
    B -->|是| C{消费金额是否足够大?}
    C -->|否| Z
    C -->|是| D[检查消费记录数量]
    D --> E{是否超过阈值?}
    E -->|否| Z
    E -->|是| F[根据时间窗口策略分组]
    F --> F1[处理无过期时间积分组]
    F1 --> F2[处理有过期时间积分组]
    F2 --> G[创建新的合并积分记录]
    G --> H[更新原记录余额为0]
    H --> I[创建消费日志关联记录]
    I --> Z
```

## 时间窗口合并策略

```mermaid
flowchart LR
    A[开始] --> B{选择时间窗口策略}
    B -->|exact| C1[按精确时间点分组]
    B -->|daily| C2[按天分组]
    B -->|weekly| C3[按周分组]
    B -->|monthly| C4[按月分组]

    C1 --> D[合并每组积分]
    C2 --> D
    C3 --> D
    C4 --> D
    D --> E[保留最早的过期时间]
    E --> F[创建新积分记录]
    F --> Z[结束]
```

## 积分合并后的消费流程

```mermaid
flowchart TD
    A[开始] --> B[按FIFO原则查询积分记录]
    B --> C{是否有合并积分记录?}
    C -->|是| D[优先消费合并记录]
    C -->|否| E[消费普通积分记录]
    D --> F[创建消费日志]
    E --> F
    F --> G[更新Account余额]
    G --> Z[结束]
```

## 积分优化配置项

| 配置名称 | 说明 | 默认值 |
|---------|------|--------|
| CREDIT_AUTO_MERGE_ENABLED | 是否启用自动合并小额积分 | true |
| CREDIT_AUTO_MERGE_THRESHOLD | 触发合并的记录数阈值 | 100 |
| CREDIT_AUTO_MERGE_MIN_AMOUNT | 触发合并的最小消费金额 | 100.0 |
| CREDIT_MIN_AMOUNT_TO_MERGE | 被合并的最大积分额度 | 5.0 |
| CREDIT_TIME_WINDOW_STRATEGY | 时间窗口策略(exact/daily/weekly/monthly) | monthly |

## 时间窗口策略比较

| 策略名称 | 窗口粒度 | 合并效率 | 过期时间精度损失 | 适用场景 |
|---------|---------|---------|--------------|---------|
| exact | 精确到秒 | 低 | 无 | 过期时间精度要求高 |
| daily | 按天合并 | 中 | 最多1天 | 日常消费 |
| weekly | 按周合并 | 高 | 最多1周 | 大额频繁消费 |
| monthly | 按月合并 | 最高 | 最多1个月 | 大额不频繁消费 |
