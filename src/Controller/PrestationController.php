<?php

namespace App\Controller;

use App\Entity\Prestation;
    use App\Form\PrestationForm;
    use App\Repository\PrestationRepository;
use App\Repository\DisponibiliteRepository;
use Symfony\Bundle\SecurityBundle\Security;
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

        // Regrouper les disponibilités par date (formatée en Y-m-d)
        $grouped = [];
        foreach ($disponibilites as $dispo) {
            $dateKey = $dispo->getDate()->format('Y-m-d');
            $grouped[$dateKey][] = [
                'heureDebut' => $dispo->getHeureDebut()->format('H:i'),
                'heureFin' => $dispo->getHeureFin()->format('H:i'),
            ];
        }

        return $this->render('prestation/show.html.twig', [
            'prestation' => $prestation,
            'disponibilites' => $grouped,
        ]);
    }

    #[Route('/reservation/resume/', name: 'app_reservation_resume', methods: ['GET'])]
    public function resume(Request $request, PrestationRepository $prestationRepo, Security $security): Response
    {
        $prestation = $prestationRepo->find($request->query->get('prestation_id'));
        if (!$prestation) {
            throw $this->createNotFoundException('Prestation introuvable.');
        }

        $date = $request->query->get('date');
        $heure = $request->query->get('heure');
        $user = $security->getUser();

        return $this->render('prestation/resume.html.twig', [
            'prestation' => $prestation,
            'date' => new \DateTime($date),
            'heure' => $heure,
            'user' => $user,
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
