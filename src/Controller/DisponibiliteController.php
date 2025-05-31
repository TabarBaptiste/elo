<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Form\DisponibiliteForm;
use App\Repository\DisponibiliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/coiffeuse/disponibilite')]
final class DisponibiliteController extends AbstractController
{
    #[Route(name: 'app_disponibilite_index', methods: ['GET'])]
    public function index(DisponibiliteRepository $disponibiliteRepository): Response
    {
        return $this->render('disponibilite/index.html.twig', [
            'disponibilites' => $disponibiliteRepository->findAll(),
        ]);
    }


    #[Route('/new', name: 'app_disponibilite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $disponibilite = new Disponibilite();
        $form = $this->createForm(DisponibiliteForm::class, $disponibilite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable('today');
            $debut = $disponibilite->getDebut();
            $fin = $disponibilite->getFin();

            if ($debut < $now || $fin < $now) {
                $this->addFlash('error', 'Les dates doivent être postérieures ou égales à aujourd\'hui.');
                return $this->redirectToRoute('app_disponibilite_new');
            }

            if ($debut >= $fin) {
                $this->addFlash('error', 'La date de début doit être avant la date de fin.');
                return $this->redirectToRoute('app_disponibilite_new');
            }

            // Vérifie qu’il n’y a pas de chevauchement
            $overlap = $em->getRepository(Disponibilite::class)->createQueryBuilder('d')
                ->where('(:debut < d.fin) AND (:fin > d.debut)')
                ->setParameter('debut', $debut)
                ->setParameter('fin', $fin)
                ->getQuery()
                ->getResult();

            if ($overlap) {
                $this->addFlash('error', 'Une disponibilité existe déjà sur cette période.');
                return $this->redirectToRoute('app_disponibilite_new');
            }

            $em->persist($disponibilite);
            $em->flush();

            return $this->redirectToRoute('app_disponibilite_index');
        }

        return $this->render('disponibilite/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_disponibilite_show', methods: ['GET'])]
    public function show(Disponibilite $disponibilite): Response
    {
        return $this->render('disponibilite/show.html.twig', [
            'disponibilite' => $disponibilite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_disponibilite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Disponibilite $disponibilite, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DisponibiliteForm::class, $disponibilite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable('today');
            $debut = $disponibilite->getDebut();
            $fin = $disponibilite->getFin();

            if ($debut < $now || $fin < $now) {
                $this->addFlash('error', 'Les dates doivent être postérieures ou égales à aujourd\'hui.');
                return $this->redirectToRoute('app_disponibilite_edit', ['id' => $disponibilite->getId()]);
            }

            if ($debut >= $fin) {
                $this->addFlash('error', 'La date de début doit être avant la date de fin.');
                return $this->redirectToRoute('app_disponibilite_edit', ['id' => $disponibilite->getId()]);
            }

            // Vérifie qu’il n’y a pas de chevauchement avec d’autres (sauf lui-même)
            $overlap = $em->getRepository(Disponibilite::class)->createQueryBuilder('d')
                ->where('(:debut < d.fin) AND (:fin > d.debut) AND d.id != :id')
                ->setParameter('debut', $debut)
                ->setParameter('fin', $fin)
                ->setParameter('id', $disponibilite->getId())
                ->getQuery()
                ->getResult();

            if ($overlap) {
                $this->addFlash('error', 'Une autre disponibilité existe déjà sur cette période.');
                return $this->redirectToRoute('app_disponibilite_edit', ['id' => $disponibilite->getId()]);
            }

            $em->flush();

            return $this->redirectToRoute('app_disponibilite_index');
        }

        return $this->render('disponibilite/edit.html.twig', [
            'form' => $form,
            'disponibilite' => $disponibilite,
        ]);
    }

    #[Route('/{id}', name: 'app_disponibilite_delete', methods: ['POST'])]
    public function delete(Request $request, Disponibilite $disponibilite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $disponibilite->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($disponibilite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_disponibilite_index', [], Response::HTTP_SEE_OTHER);
    }
}
