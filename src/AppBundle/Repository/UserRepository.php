<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository
{
    /**
     * Finds a user by email.
     *
     * @param string $email
     *
     * @return object|UserInterface
     */
    public function findUser(string $email)
    {
        return $this->findOneBy(['email' => $email]);
    }
}
