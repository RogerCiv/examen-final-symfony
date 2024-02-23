<?php

namespace App\Entity;

use App\Repository\PokedexRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokedexRepository::class)]
class Pokedex
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?User $entrenador = null;

    #[ORM\OneToMany(targetEntity: PokedexPokemon::class, mappedBy: 'pokedex',cascade: ['persist'])]
    private Collection $pokedexPokemon;

    public function __construct()
    {
        $this->pokedexPokemon = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntrenador(): ?User
    {
        return $this->entrenador;
    }

    public function setEntrenador(?User $entrenador): static
    {
        $this->entrenador = $entrenador;

        return $this;
    }

    /**
     * @return Collection<int, PokedexPokemon>
     */
    public function getPokedexPokemon(): Collection
    {
        return $this->pokedexPokemon;
    }

    public function addPokedexPokemon(PokedexPokemon $pokedexPokemon): static
    {
        if (!$this->pokedexPokemon->contains($pokedexPokemon)) {
            $this->pokedexPokemon->add($pokedexPokemon);
            $pokedexPokemon->setPokedex($this);
        }

        return $this;
    }

    public function removePokedexPokemon(PokedexPokemon $pokedexPokemon): static
    {
        if ($this->pokedexPokemon->removeElement($pokedexPokemon)) {
            // set the owning side to null (unless already changed)
            if ($pokedexPokemon->getPokedex() === $this) {
                $pokedexPokemon->setPokedex(null);
            }
        }

        return $this;
    }
}
