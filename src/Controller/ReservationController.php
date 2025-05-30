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
use Symfony\Bundle\SecurityBundle\Security;

// #[Route('/admin')]
final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
            'statuts' => ReservationStatut::cases()
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

        if ($this->getUser()) {
            $this->addFlash('success', 'Réservation enregistrée');
            return $this->redirectToRoute('app_client_show', ['id' => $this->getUser()->getId()]);
        }
        return $this->redirectToRoute('app_client_show');
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

        return $this->render('reservation/resume.html.twig', [
            'prestation' => $prestation,
            'date' => new \DateTime($date),
            'heure' => $heure,
            'user' => $user,
        ]);
    }

    #[Route('/reservation/{id}/statut', name: 'app_reservation_update_statut', methods: ['POST'])]
    public function updateStatut(
        Reservation $reservation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_COIFFEUSE');

        $newStatut = $request->request->get('statut');

        // Vérification que le statut est valide
        if (!in_array($newStatut, ReservationStatut::values())) {
            throw new \InvalidArgumentException("Statut invalide");
        }

        // Récupération de l'utilisateur lié à la réservation
        $client = $reservation->getUtilisateur();

        // Incrément du compteur de visites si statut "confirmée"
        if ($newStatut === ReservationStatut::PASSEE->value && $client !== null) {
            $client->setVisite($client->getVisite() + 1);
            $em->persist($client);
        }

        // Mise à jour du statut
        $reservation->setStatut(ReservationStatut::from($newStatut));
        $em->flush();

        return $this->redirectToRoute('app_reservation');
    }

}
