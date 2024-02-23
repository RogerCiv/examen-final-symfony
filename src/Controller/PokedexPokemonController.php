<?php

namespace App\Controller;

use App\Entity\PokedexPokemon;
use App\Form\PokedexPokemonType;
use App\Repository\PokedexPokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pokedex/pokemon')]
class PokedexPokemonController extends AbstractController
{
    #[Route('/', name: 'app_pokedex_pokemon_index', methods: ['GET'])]
    public function index(PokedexPokemonRepository $pokedexPokemonRepository): Response
    {
        return $this->render('pokedex_pokemon/index.html.twig', [
            'pokedex_pokemons' => $pokedexPokemonRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pokedex_pokemon_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pokedexPokemon = new PokedexPokemon();
        $form = $this->createForm(PokedexPokemonType::class, $pokedexPokemon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pokedexPokemon);
            $entityManager->flush();

            return $this->redirectToRoute('app_pokedex_pokemon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pokedex_pokemon/new.html.twig', [
            'pokedex_pokemon' => $pokedexPokemon,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pokedex_pokemon_show', methods: ['GET'])]
    public function show(PokedexPokemon $pokedexPokemon): Response
    {
        return $this->render('pokedex_pokemon/show.html.twig', [
            'pokedex_pokemon' => $pokedexPokemon,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pokedex_pokemon_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PokedexPokemon $pokedexPokemon, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PokedexPokemonType::class, $pokedexPokemon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_pokedex_pokemon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pokedex_pokemon/edit.html.twig', [
            'pokedex_pokemon' => $pokedexPokemon,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pokedex_pokemon_delete', methods: ['POST'])]
    public function delete(Request $request, PokedexPokemon $pokedexPokemon, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pokedexPokemon->getId(), $request->request->get('_token'))) {
            $entityManager->remove($pokedexPokemon);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pokedex_pokemon_index', [], Response::HTTP_SEE_OTHER);
    }
}
