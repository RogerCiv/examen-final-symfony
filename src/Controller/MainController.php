<?php

namespace App\Controller;

use App\Entity\Pokedex;
use App\Entity\PokedexPokemon;
use App\Entity\Pokemon;
use App\Repository\PokedexPokemonRepository;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{

    #[Route('/', name: 'app_main')]
    public function index(EntityManagerInterface $entityManager, Request $request,PokedexPokemonRepository $pokedexPokemonRepository, PokemonRepository $pokemonRepository): Response
    {
        $user= $this->getUser();
        if($user == null){
            return $this->redirectToRoute('app_login');
        }
        // dd($pokemons);
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['entrenador' => $user]);


        // Obtener todos los Pokémon del usuario
    $pokemonUsuario = $pokedexPokemonRepository->findBy(['pokedex' => $pokedex]);

    // Crear el formulario de batalla
    $battleForm = $this->createFormBuilder()
        ->add('pokemonUsuarioId', EntityType::class, [
            'class' => PokedexPokemon::class,
            'choices' => $pokemonUsuario,
            'choice_label' => 'pokemon.nombre', // Asumiendo que hay una relación 'pokemon' en PokedexPokemon
            'label' => 'Selecciona tu Pokémon para la batalla',
        ])
        ->getForm();

    $battleForm->handleRequest($request);

    // Procesar el formulario si ha sido enviado
    if ($battleForm->isSubmitted() && $battleForm->isValid()) {
        // Obtener el Pokémon seleccionado por el usuario desde el formulario
        $pokemonUsuarioId = $battleForm->get('pokemonUsuarioId')->getData()->getId();

      
    $pokemonesOponente = $pokemonRepository->findAll();

    // Seleccionar uno aleatorio
    $indiceAleatorio = array_rand($pokemonesOponente);
    $pokemonOponente = $pokemonesOponente[$indiceAleatorio];

        // Obtener el Pokémon del usuario desde su id
        $pokemonUsuario = $pokedexPokemonRepository->find($pokemonUsuarioId);

        // Verificar que el Pokémon del usuario pertenece a su Pokédex
        if ($pokemonUsuario === null || $pokemonUsuario->getPokedex()->getEntrenador() !== $user) {
            // Manejar el caso en el que el Pokémon no pertenece al usuario
            $this->addFlash('error_message', 'No se encontró el Pokémon seleccionado en tu Pokédex.');
        } else {
            // Asignar niveles y fuerzas aleatorios al Pokémon oponente
            $nivelOponente = mt_rand(1, 20);
            $fuerzaOponente = mt_rand(5, 100);

         // Calcular el valor de la batalla para ambos Pokémon
$valorBatallaUsuario = $pokemonUsuario->getNivel() * $pokemonUsuario->getFuerza();
$valorBatallaOponente = $nivelOponente * $fuerzaOponente;

// Determinar al ganador de la batalla
$ganador = null;
if ($valorBatallaUsuario > $valorBatallaOponente) {
    $ganador = $pokemonUsuario;
} elseif ($valorBatallaUsuario < $valorBatallaOponente) {
    $ganador = $pokemonOponente;
}

// Incrementar el nivel del Pokémon ganador solo si es el Pokémon del usuario
if ($ganador !== null && $ganador === $pokemonUsuario) {
    $nivelGanador = $ganador->getNivel();
    $ganador->setNivel($nivelGanador + 1);
}

            // Guardar los cambios en la base de datos
            $entityManager->flush();

            // Preparar datos para pasar a la plantilla
            $resultado = [
                'ganador' => $ganador,
                'pokemonOponente' => $pokemonOponente,
                'nivelOponente' => $nivelOponente,
                'fuerzaOponente' => $fuerzaOponente,
            ];
            // dd($resultado);

//             $ganadorNombre = ($ganador instanceof PokedexPokemon) ? $ganador->getPokemon()->getNombre() : $ganador->getNombre();
// $this->addFlash('battle_message', '¡La batalla ha terminado! El ganador es: ' . $ganadorNombre);
$ganadorNombre = ($ganador instanceof PokedexPokemon) ? $ganador->getPokemon()->getNombre() : $ganador->getNombre();

if ($ganador === $pokemonUsuario) {
    $mensaje = '¡Felicidades! Has ganado la batalla contra ' . $ganadorNombre;
} elseif ($ganador !== null) {
    $mensaje = '¡La batalla ha terminado! El ganador es: ' . $ganadorNombre . '. Pero esta vez no fuiste tú.';
} else {
    $mensaje = 'La batalla terminó en empate. ¡Inténtalo de nuevo!';
}

$this->addFlash('battle_message', $mensaje);

            return $this->redirectToRoute('app_main', [
                'pokemonUsuarioId' => $pokemonUsuarioId,
                'resultado' => $resultado, // Ajusta esto según sea necesario
            ]);
        }
    }
        return $this->render('main/index.html.twig', [
            'user' => $user,
            'pokedex' => $pokedex,
            'battleForm' => $battleForm->createView(),
        ]);
    }

    #[Route('/mostrar-pokemon', name: 'mostrar_pokemon')]
    public function mostrarPokemon(PokemonRepository $pokemonRepository): Response
    {
        $pokemons= $pokemonRepository->findAll();

        //Coger pokemon aleatorio

        $pokemon= $pokemons[array_rand($pokemons)];

        return $this->render('main/index.html.twig', [
            'pokemon' => $pokemon,
        ]);


    }

    #[Route('/cazar-pokemon/{id}', name: 'cazar_pokemon')]
    public function cazarPokemon($id, PokemonRepository $pokemonRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

    // Verificar si el usuario está autenticado
    if ($user === null) {
        return $this->redirectToRoute('app_login');
    }
    $pokemon = $pokemonRepository->find($id);
    // dd($pokemon);
    $probabilidadCaptura = mt_rand(0, 100);

    $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['entrenador' => $user]);

    // Definir la probabilidad de captura deseada (60%)
    $probabilidadDeseada = 60;

    // Verificar si la captura es exitosa
     // Verificar si la captura es exitosa
     if ($probabilidadCaptura <= $probabilidadDeseada) {
        // Crear una nueva instancia de Pokedex si no existe
        if (!$pokedex) {
            $pokedex = new Pokedex();
            $pokedex->setEntrenador($user);

            $entityManager->persist($pokedex);
        }

        // Crear una nueva instancia de PokedexPokemon y configurarla con valores iniciales
        $pokedexPokemon = new PokedexPokemon();
        $pokedexPokemon
            ->setPokedex($pokedex)
            ->setPokemon($pokemon)
            ->setNivel(1) // Establecer el nivel inicial en 1
            ->setFuerza(10); // Establecer la fuerza inicial en 10

        // Agregar el Pokemon capturado al Pokedex del usuario
        $pokedex->addPokedexPokemon($pokedexPokemon);

        // Guardar cambios en la base de datos
        $entityManager->flush();

        $mensaje = '¡Has capturado a ' . $pokemon->getNombre() . ' con éxito!';
    } else {
        $mensaje = 'La captura de ' . $pokemon->getNombre() . ' ha fallado. ¡Inténtalo de nuevo!';
    }
    // Almacenar el mensaje en la sesión
$this->addFlash('captura_message', $mensaje);


    return $this->redirectToRoute('app_main');
    }

    #[Route('/cancelar-caza', name: 'cancelar_caza')]
    public function cancelarCaza(): Response
    {
        return $this->redirectToRoute('app_main');
    }

    #[Route('/entrenar-pokemon', name: 'entrenar_pokemon', methods: ['POST'])]
public function entrenarPokemon(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    if ($user === null) {
        return $this->redirectToRoute('app_login');
    }

    $pokedexPokemonId = $request->request->get('pokemonId');
    $pokedexPokemon = $entityManager->getRepository(PokedexPokemon::class)->find($pokedexPokemonId);

    if ($pokedexPokemon && $pokedexPokemon->getPokedex()->getEntrenador() === $user) {
        // Entrenar al Pokémon aumentando su fuerza en 10
        $pokedexPokemon->setFuerza($pokedexPokemon->getFuerza() + 10);

        // Guardar cambios en la base de datos
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_main');
}

// #[Route('/enfrentar-pokemon', name: 'enfrentar_pokemon', methods: ['POST'])]
//     public function enfrentarPokemon(Request $request, EntityManagerInterface $entityManager, PokemonRepository $pokemonRepository): Response
//     {
//         $user = $this->getUser();

//         // Verificar si el usuario está autenticado
//         if ($user === null) {
//             return $this->redirectToRoute('app_login');
//         }

//         // Obtener el Pokémon aleatorio
//         $pokemonOponente = $pokemonRepository->getRandomPokemon();

//         // Obtener el Pokémon seleccionado por el usuario desde el formulario
//         $pokemonUsuarioId = $request->request->get('pokemonUsuarioId');
//         $pokemonUsuario = $entityManager->getRepository(PokedexPokemon::class)->find($pokemonUsuarioId);

//         // Verificar que el Pokémon del usuario pertenece a su Pokédex
//         if ($pokemonUsuario === null || $pokemonUsuario->getPokedex()->getEntrenador() !== $user) {
//             // Manejar el caso en el que el Pokémon no pertenece al usuario
//             $this->addFlash('error_message', 'No se encontró el Pokémon seleccionado en tu Pokédex.');
//             return $this->redirectToRoute('app_main');
//         }

//         // Asignar niveles y fuerzas aleatorios al Pokémon oponente
//         $nivelOponente = mt_rand(1, 10);
//         $fuerzaOponente = mt_rand(5, 20);

//         // Calcular el valor de la batalla para ambos Pokémon
//         $valorBatallaUsuario = $pokemonUsuario->getNivel() * $pokemonUsuario->getFuerza();
//         $valorBatallaOponente = $nivelOponente * $fuerzaOponente;

//         // Determinar al ganador de la batalla
//         $ganador = ($valorBatallaUsuario > $valorBatallaOponente) ? $pokemonUsuario : $pokemonOponente;

//         // Incrementar el nivel del Pokémon ganador
//         $nivelGanador = $ganador->getNivel();
//         $ganador->setNivel($nivelGanador + 1);

//         // Guardar los cambios en la base de datos
//         $entityManager->flush();

//         // Redirigir a la página principal con un mensaje de resultado
//         $this->addFlash('battle_message', '¡La batalla ha terminado! El ganador es: ' . $ganador->getNombre());

//         return $this->redirectToRoute('app_main');
//     }

#[Route('/enfrentar-pokemon', name: 'enfrentar_pokemon', methods: ['POST'])]
public function enfrentarPokemon(Request $request, EntityManagerInterface $entityManager, PokemonRepository $pokemonRepository): Response
{
    $user = $this->getUser();

    // Verificar si el usuario está autenticado
    if ($user === null) {
        return $this->redirectToRoute('app_login');
    }

    // Obtener el Pokémon aleatorio
    $pokemonOponente = $pokemonRepository->getRandomPokemon();

    // Obtener el Pokémon seleccionado por el usuario desde el formulario
    $pokemonUsuarioId = $request->request->get('pokemonUsuarioId');
    $pokemonUsuario = $entityManager->getRepository(PokedexPokemon::class)->find($pokemonUsuarioId);

    // Verificar que el Pokémon del usuario pertenece a su Pokédex
    if ($pokemonUsuario === null || $pokemonUsuario->getPokedex()->getEntrenador() !== $user) {
        // Manejar el caso en el que el Pokémon no pertenece al usuario
        $this->addFlash('error_message', 'No se encontró el Pokémon seleccionado en tu Pokédex.');
        return $this->redirectToRoute('app_main');
    }

    // Asignar niveles y fuerzas aleatorios al Pokémon oponente
    $nivelOponente = mt_rand(1, 10);
    $fuerzaOponente = mt_rand(5, 20);

    // Calcular el valor de la batalla para ambos Pokémon
    $valorBatallaUsuario = $pokemonUsuario->getNivel() * $pokemonUsuario->getFuerza();
    $valorBatallaOponente = $nivelOponente * $fuerzaOponente;

    // Determinar al ganador de la batalla
    $ganador = ($valorBatallaUsuario > $valorBatallaOponente) ? $pokemonUsuario : $pokemonOponente;

    // Incrementar el nivel del Pokémon ganador
    $nivelGanador = $ganador->getNivel();
    $ganador->setNivel($nivelGanador + 1);

    // Guardar los cambios en la base de datos
    $entityManager->flush();

    // Preparar datos para pasar a la plantilla
    $resultado = [
        'ganador' => $ganador,
        'pokemonOponente' => $pokemonOponente,
        'nivelOponente' => $nivelOponente,
        'fuerzaOponente' => $fuerzaOponente,
    ];


    // Redirigir a la página principal con el resultado de la batalla
    return $this->redirectToRoute('app_main', ['resultado' => $resultado]);
}


}
