<?php

namespace Kspu\LDAP\Entity;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class UserListener implements EventSubscriberInterface
{
    /**
     * @var UserStoreInterface
     */
    private $repo;

    /**
     * @param UserStoreInterface $repo
     */
    public function __construct(UserStoreInterface $repo) {
        $this->repo = $repo;
    }

    public function authSuccess(AuthenticationEvent $event) {
        $user = $event->getAuthenticationToken()->getUser();
        if(!is_object($user)) return;
        if(get_class($user) !== $this->repo->getClassName()) return;
        $this->repo->persist($user);
    }

    public static function getSubscribedEvents() {
        return array(
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'authSuccess',
        );
    }
}
