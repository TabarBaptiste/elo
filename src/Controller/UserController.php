<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Repository\ReservationPrestationRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class UserController extends AbstractController
{
    #[Route('coiffeuse/user', name: 'app_client')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $userRepository->findAll(),
        ]);
    }

    #[Route('/user/{id}', name: 'app_client_show')]
    public function client(
        User $user,
        ReservationPrestationRepository $repo
    ): Response {
        $currentUser = $this->getUser();

        // Redirection si non connecté
        if (!$currentUser) {
            return $this->redirectToRoute('app_prestation_index');
        }

        // Si ce n’est pas le bon utilisateur ET qu’il n’a pas le rôle coiffeuse
        $isSameUser = $currentUser->getId() === $user->getId();
        $isCoiffeuse = $this->isGranted('ROLE_COIFFEUSE');

        if (!$isSameUser && !$isCoiffeuse) {
            return $this->redirectToRoute('app_prestation_index');
        }

        // OK : accès autorisé
        $prestationsReservees = $repo->findByUser($user);

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'role' => $user->getRoles()[0] ?? null,
            'prestationsReservees' => $prestationsReservees,
            'NombreprestationsReservees' => count($prestationsReservees),
        ]);
    }

    #[Route('/user/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function deleteUser(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->get('_token'))) {

            // Supprimer les réservations associées à l'utilisateur
            $reservations = $user->getReservations();
            foreach ($reservations as $reservation) {
                $entityManager->remove($reservation);
            }

            // Vérifie si l'utilisateur à supprimer est celui connecté
            $connectedUser = $tokenStorage->getToken()?->getUser();
            $isSelfDeletion = $connectedUser instanceof User && $connectedUser->getId() === $user->getId();

            // Supprimer l'utilisateur
            $entityManager->remove($user);
            $entityManager->flush();

            if ($isSelfDeletion) {
                // Déconnexion propre : invalide session et vide le token
                $session->invalidate();
                $tokenStorage->setToken(null);

                // Redirection vers accueil
                return $this->redirectToRoute('app_prestation_index');
            }
        }

        return $this->redirectToRoute('app_client', [], Response::HTTP_SEE_OTHER);
    }
}
