<?php

namespace AppBundle\Security\User;

use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->userRepository->findUser($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of UserInterface, but got "%s".', get_class($user)));
        }

        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', $this->userRepository->getClassName(), get_class($user)));
        }

        if (null === $reloadedUser = $this->userRepository->find($user->getId())) {
            throw new UsernameNotFoundException(sprintf('User with ID "%s" could not be reloaded.', $user->getId()));
        }

        return $reloadedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        $userClass = $this->userRepository->getClassName();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }
}
