<?php
namespace Kspu\LDAP\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\UnitOfWork;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Kspu\LDAP\Entity\UserStoreInterface;

abstract class BaseUserRepository extends EntityRepository implements UserStoreInterface, UserProviderInterface {
    /**
     * @inheritdoc
     */
    public final function persist(UserInterface $user) {
        if(!$this->supportsClass(get_class($user))) return;

        $dbUser = $this->findOneBy(array('username' => $user->getUsername()));

        if($dbUser === null) {
            $this->_em->persist($user);
        } else {
            $this->_em->merge($user);
        }

        $this->_em->flush();
    }

    /**
     * @inheritdoc
     */
    public final function loadUserByUsername($username) {
        $user = $this->findOneBy(array('username' => $username));
        if($user === null) {
            $ex = new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUsername($username);

            throw $ex;
        }

        return $user;
    }
    /**
     * @inheritdoc
     */
    public final function refreshUser(UserInterface $user) {
        if(!$this->supportsClass(get_class($user)))
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));

        $state = $this->_em->getUnitOfWork()->getEntityState($user);

        if($state !== UnitOfWork::STATE_MANAGED) {
            $user = $this->_em->merge($user);
            $this->_em->refresh($user);
            return $user;
            //return $this->loadUserByUsername($user->getUsername());
        } else
            return $user;
    }
}