<?php

namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Table(
 *     name="users",
 *     indexes={
 *          @Index(name="users_email_idx", columns={"email"}),
 *          @Index(name="users_firstname_idx", columns={"firstname"}),
 *          @Index(name="users_lastname_idx", columns={"lastname"}),
 *          @Index(name="users_roles_idx", columns={"roles"}),
 *          @Index(name="users_created_at_idx", columns={"created_at"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity(fields={"email"})
 */
class User implements AdvancedUserInterface
{
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var string
     *
     * @ORM\Column(type="guid", length=32)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank(message="user.email.not_blank")
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="user.firstname.not_blank")
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="user.lastname.not_blank")
     */
    private $lastname;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $enabled = true;

    /**
     * @var string
     *
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=127)
     */
    private $password;

    /**
     * @var string[]
     * @Assert\Type(type="array")
     * @ORM\Column(type="jsonb", nullable=true)
     */
    private $roles = [];

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $locked = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->password = sha1(uniqid(mt_rand(), true));
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return array_unique(array_merge([self::ROLE_USER], $this->roles));
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role)
    {
        return in_array($role, $this->roles, true);
    }

    /**
     * @param string $role
     */
    public function addRole(string $role)
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return $this->locked;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getSalt()
    {
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->lastname.' '.$this->firstname;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return $this
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     *
     * @return $this
     */
    public function setLocked(bool $locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
