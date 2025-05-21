<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\ReservationRepository;
use App\Repository\PrestationRepository;
use App\Entity\ReservationPrestation;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\ReservationStatut;

// #[Route('/admin')]
final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
            'statuts' => ReservationStatut::cases(), // ← ici
        ]);
    }

    #[Route('/reservation/confirm', name: 'app_reservation_confirm', methods: ['POST'])]
    public function confirm(
        Request $request,
        EntityManagerInterface $em,
        PrestationRepository $prestationRepo,
    ): Response {
        $prestationId = $request->request->get('prestation_id');
        $date = $request->request->get('date');
        $heure = $request->request->get('heure');

        $prestation = $prestationRepo->find($prestationId);
        if (!$prestation) {
            throw $this->createNotFoundException('Prestation non trouvée.');
        }

        $reservation = new Reservation();
        $reservation->setDateReservation(new \DateTime($date));
        $reservation->setHeureReservation(\DateTime::createFromFormat('H:i', $heure));
        $reservation->setStatut(ReservationStatut::EN_ATTENTE);

        // Si utilisateur connecté
        if ($this->getUser()) {
            $reservation->setUtilisateur($this->getUser());
        } else {
            // Création d'un utilisateur temporaire ou simple stockage des données
            $user = new User();
            $user->setPrenom($request->request->get('prenom'));
            $user->setNom($request->request->get('nom'));
            $user->setEmail($request->request->get('email'));
            dd($user->getVisite());
            $em->persist($user);
            $reservation->setUtilisateur($user);
        }

        // Lier la prestation via l'entité d'association (si tu en as une)
        $reservationPrestation = new ReservationPrestation();
        $reservationPrestation->setPrestation($prestation);
        $reservationPrestation->setReservation($reservation);

        $em->persist($reservation);
        $em->persist($reservationPrestation);
        $em->flush();

        $this->addFlash('success', 'Réservation enregistrée avec succès !');

        return $this->redirectToRoute('app_prestation_index');
    }

    #[Route('/reservation/{id}/statut', name: 'app_reservation_update_statut', methods: ['POST'])]
    public function updateStatut(
        Reservation $reservation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_COIFFEUSE');

        $newStatut = $request->request->get('statut');

        if (!in_array($newStatut, array_map(fn($case) => $case->value, ReservationStatut::cases()))) {
            throw new \InvalidArgumentException("Statut invalide");
        }

        dd($this->getUser());
        // Création d'un utilisateur temporaire ou simple stockage des données
        $user = new User();
        dd($user->getVisite());
        $em->persist($user);

        $reservation->setStatut(ReservationStatut::from($newStatut));
        $em->flush();

        return $this->redirectToRoute('app_reservation');
    }

}
