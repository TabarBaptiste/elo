<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Repository\ReservationPrestationRepository;

final class UserController extends AbstractController
{
    #[Route('coiffeuse/user', name: 'app_client')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $userRepository->findAll(),
        ]);
    }

    #[Route('user/{id}', name: 'app_client_show')]
public function client(User $user, ReservationPrestationRepository $repo): Response
{
    $prestationsReservees = $repo->findByUser($user);

    return $this->render('user/show.html.twig', [
        'user' => $user,
        'role' => $user->getRoles()[0],
        'prestationsReservees' => $prestationsReservees,
        'NombreprestationsReservees' => count($prestationsReservees),
    ]);
}

}
