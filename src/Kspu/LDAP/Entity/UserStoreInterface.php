<?php
namespace Kspu\LDAP\Entity;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserStoreInterface extends ObjectRepository {
    function persist(UserInterface $user);
    function createUser();
} 