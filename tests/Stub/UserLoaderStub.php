<?php

namespace CreditBundle\Tests\Stub;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserLoaderStub implements UserLoaderInterface
{
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return null;
    }
}
