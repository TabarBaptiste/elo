<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Form\PrestationForm;
use App\Repository\PrestationRepository;
use App\Repository\DisponibiliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrestationController extends AbstractController
{
    #[Route('/', name: 'app_prestation_index', methods: ['GET'])]
    public function index(PrestationRepository $prestationRepository): Response
    {
        return $this->render('prestation/index.html.twig', [
            'prestations' => $prestationRepository->findAll(),
        ]);
    }

    #[Route('/prestation/new', name: 'app_prestation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $prestation = new Prestation();
        $form = $this->createForm(PrestationForm::class, $prestation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($prestation);
            $entityManager->flush();

            return $this->redirectToRoute('app_prestation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prestation/new.html.twig', [
            'prestation' => $prestation,
            'form' => $form,
        ]);
    }

    #[Route('/prestation/{id}', name: 'app_prestation_show', methods: ['GET'])]
    public function show(Prestation $prestation, DisponibiliteRepository $dispoRepo): Response
    {
        $disponibilites = $dispoRepo->findAll();
        $grouped = [];

        foreach ($disponibilites as $dispo) {
            $dateKey = $dispo->getDebut()->format('Y-m-d');

            // Durée de la prestation (en minutes)
            $duree = $prestation->getDuree();

            $current = clone $dispo->getDebut(); // début de la plage
            $end = $dispo->getFin();             // fin de la plage

            // Boucle sur les créneaux disponibles
            while ($current < $end) {
                $finCreneau = (clone $current)->add(new \DateInterval('PT' . $duree . 'M'));

                // Si le créneau dépasse la fin de la plage, on arrête
                if ($finCreneau > $end) {
                    break;
                }

                $grouped[$dateKey][] = [
                    'heureDebut' => $current->format('H:i'),
                    'heureFin' => $finCreneau->format('H:i'),
                ];

                // Avancer au prochain créneau (sans pause)
                $current = $finCreneau;
            }
        }

        ksort($grouped); // trier les dates

        return $this->render('prestation/show.html.twig', [
            'prestation' => $prestation,
            'disponibilites' => $grouped,
        ]);
    }

    #[Route('/prestation/{id}/edit', name: 'app_prestation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Prestation $prestation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PrestationForm::class, $prestation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_prestation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prestation/edit.html.twig', [
            'prestation' => $prestation,
            'form' => $form,
        ]);
    }

    #[Route('/prestation/{id}/delete', name: 'app_prestation_delete', methods: ['POST'])]
    public function delete(Request $request, Prestation $prestation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $prestation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($prestation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_prestation_index', [], Response::HTTP_SEE_OTHER);
    }
}

//* Pause de 5min entre prestation
// public function show(Prestation $prestation, DisponibiliteRepository $dispoRepo): Response
// {
//     $disponibilites = $dispoRepo->findAll();

//     $grouped = [];

//     foreach ($disponibilites as $dispo) {
//         $dateKey = $dispo->getDebut()->format('Y-m-d');

//         // Durée de la prestation
//         $duree = $prestation->getDuree(); // en minutes
//         $pause = 5; // minutes

//         $current = clone $dispo->getDebut(); // début de la plage
//         $end = $dispo->getFin(); // fin de la plage

//         // Boucle sur les créneaux disponibles
//         while ($current < $end) {
//             $finCreneau = (clone $current)->add(new \DateInterval('PT' . $duree . 'M'));

//             // Si le créneau déborde, on sort
//             if ($finCreneau > $end) {
//                 break;
//             }

//             $grouped[$dateKey][] = [
//                 'heureDebut' => $current->format('H:i'),
//                 'heureFin' => $finCreneau->format('H:i'),
//             ];

//             // Ajouter pause et avancer
//             $current = $finCreneau->add(new \DateInterval('PT' . $pause . 'M'));
//             // dd($current);
//         }
//     }

//     ksort($grouped); // trier les dates

//     return $this->render('prestation/show.html.twig', [
//         'prestation' => $prestation,
//         'disponibilites' => $grouped,
//     ]);
// }
