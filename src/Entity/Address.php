<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Street address is required.')]
    #[Assert\Length(max: 255, maxMessage: 'Street address cannot be longer than {{ limit }} characters.')]
    private ?string $street = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'City is required.')]
    #[Assert\Length(max: 100, maxMessage: 'City name cannot be longer than {{ limit }} characters.')]
    private ?string $city = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'State/Province is required.')]
    #[Assert\Length(max: 100, maxMessage: 'State/Province cannot be longer than {{ limit }} characters.')]
    private ?string $state = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Postal code is required.')]
    #[Assert\Length(max: 20, maxMessage: 'Postal code cannot be longer than {{ limit }} characters.')]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Country is required.')]
    #[Assert\Length(max: 100, maxMessage: 'Country name cannot be longer than {{ limit }} characters.')]
    private ?string $country = null;

    #[ORM\OneToOne(inversedBy: 'address', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get full formatted address as a single string
     */
    public function getFullAddress(): string
    {
        return sprintf(
            '%s, %s, %s %s, %s',
            $this->street,
            $this->city,
            $this->state,
            $this->postalCode,
            $this->country
        );
    }

    public function __toString(): string
    {
        return $this->getFullAddress();
    }
}
