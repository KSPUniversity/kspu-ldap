<?php
namespace Kspu\LDAP\Entity;

use FR3D\LdapBundle\Model\LdapUserInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseUser implements UserInterface, LdapUserInterface, \Serializable, EquatableInterface {
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=80, unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=160, unique=true)
     */
    protected $dn;

    /**
     * @var string
     * @ORM\Column(type="string", length=200)
     */
    protected $fio;

    public function serialize() {
        return serialize(array(
            $this->username,
            $this->fio,
            $this->dn,
        ));
    }

    public function unserialize($serialized) {
        list(
            $this->username,
            $this->fio,
            $this->dn,
            ) = unserialize($serialized);
    }

    public final function isEqualTo(UserInterface $user) {
        return $this->username === $user->getUsername();
    }

    /**
     * @param string $dn Distinguished Name
     */
    public final function setDn($dn) {
        $this->dn = $dn;
    }

    /**
     * @return string Distinguished Name
     */
    public final function getDn() {
        return $this->dn;
    }

    public final function setPassword($password) {}
    public final function getPassword() {return '';}
    public final function getSalt() {return null;}

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getFio() {
        return $this->fio;
    }

    public function setFio($fio) {
        $this->fio = $fio;
    }

    public function eraseCredentials() {}

    abstract public function addRole(RoleInterface $r);
}