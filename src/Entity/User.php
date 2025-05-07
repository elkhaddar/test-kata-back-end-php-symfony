<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Email cannot be blank")
     * @Assert\Email(message="Please provide a valid email address")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Username cannot be blank")
     * @Assert\Length(min=3, minMessage="Username must be at least 3 characters long")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Password cannot be blank")
     * @Assert\Length(min=5, minMessage="Password must be at least 6 characters long")
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=5)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Wishlist::class, mappedBy="user")
     */
    private $wishlists;

    public function __construct()
    {
        $this->wishlists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array {
        // Vérifie si l'email est celui de l'admin et assigne le rôle correspondant
        return $this->email === "admin@admin.com" ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }

    public function getWishlists(): Collection
    {
        return $this->wishlists;
    }
    
    // La méthode `getSalt()` est nécessaire si vous utilisez des encodeurs de mot de passe comme bcrypt ou argon2
    public function getSalt(): ?string
    {
        return null; // bcrypt/argon2 n'a pas besoin de salt explicite
    }

    // Cette méthode efface les informations sensibles (mot de passe)
    public function eraseCredentials()
    {
        // Aucun besoin d'effacer ici, mais vous pouvez ajouter des actions si vous stockez des données temporaires
    }
}

