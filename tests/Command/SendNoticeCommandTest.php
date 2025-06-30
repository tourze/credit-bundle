<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\SendNoticeCommand;
use PHPUnit\Framework\TestCase;

class SendNoticeCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new SendNoticeCommand();
        
        $this->assertInstanceOf(SendNoticeCommand::class, $command);
        $this->assertEquals('credit:send-notice', SendNoticeCommand::NAME);
    }
} 