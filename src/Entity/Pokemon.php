<?php

namespace App\Entity;

use App\Repository\PokemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokemonRepository::class)]
class Pokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo = null;

    #[ORM\Column(length: 255)]
    private ?string $imagen = null;

    #[ORM\OneToMany(targetEntity: PokedexPokemon::class, mappedBy: 'pokemon')]
    private Collection $pokedexPokemon;

    public function __construct()
    {
        $this->pokedexPokemon = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(string $imagen): static
    {
        $this->imagen = $imagen;

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
            $pokedexPokemon->setPokemon($this);
        }

        return $this;
    }

    public function removePokedexPokemon(PokedexPokemon $pokedexPokemon): static
    {
        if ($this->pokedexPokemon->removeElement($pokedexPokemon)) {
            // set the owning side to null (unless already changed)
            if ($pokedexPokemon->getPokemon() === $this) {
                $pokedexPokemon->setPokemon(null);
            }
        }

        return $this;
    }
    
}
