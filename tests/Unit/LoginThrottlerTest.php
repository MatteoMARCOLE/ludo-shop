<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\User;
use App\Service\LoginThrottler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class LoginThrottlerTest extends TestCase
{
    private LoginThrottler $loginThrottler;

    protected function setUp(): void
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $this->loginThrottler = new LoginThrottler($entityManager);
    }

    public function testRecordFailureIncrementsAttempts(): void
    {
        $user = new User();

        $this->loginThrottler->recordFailure($user);

        $this->assertSame(1, $user->getFailedLoginAttempts());
    }

    public function testFifthFailureLocksUser(): void
    {
        $user = new User();
        $user->setFailedLoginAttempts(4);

        $this->loginThrottler->recordFailure($user);

        $this->assertSame(5, $user->getFailedLoginAttempts());
        $this->assertNotNull($user->getLockedUntil());
        $this->assertTrue($this->loginThrottler->isLocked($user));
    }

    public function testResetClearsAttemptsAndLock(): void
    {
        $user = new User();
        $user->setFailedLoginAttempts(5);
        $user->setLockedUntil(
            new \DateTimeImmutable('+15 minutes')
        );

        $this->loginThrottler->reset($user);

        $this->assertSame(0, $user->getFailedLoginAttempts());
        $this->assertNull($user->getLockedUntil());
        $this->assertFalse($this->loginThrottler->isLocked($user));
    }

    public function testExpiredLockIsNotLocked(): void
    {
        $user = new User();
        $user->setLockedUntil(
            new \DateTimeImmutable('-1 minute')
        );

        $this->assertFalse($this->loginThrottler->isLocked($user));
    }
}