<?php
// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Prestation;
use App\Entity\Disponibilite;
// use App\Entity\Reservation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        //
        // 1) Création des utilisateurs
        //
        $usersData = [
            [
                'email'    => 'client@example.com',
                'prenom'   => 'Alice',
                'nom'      => 'Client',
                'roles'    => ['ROLE_USER'],
                'password' => 'Motdepasse'
            ],
            [
                'email'    => 'coiffeuse@example.com',
                'prenom'   => 'Chloe',
                'nom'      => 'Coiffeuse',
                'roles'    => ['ROLE_COIFFEUSE'],
                'password' => 'Motdepasse'
            ],
            [
                'email'    => 'admin@example.com',
                'prenom'   => 'Anthoine',
                'nom'      => 'Admin',
                'roles'    => ['ROLE_ADMIN'],
                'password' => 'Motdepasse'
            ],
        ];

        $userReferences = [];
        foreach ($usersData as $uData) {
            $user = new User();
            $user->setEmail($uData['email']);
            $user->setPrenom($uData['prenom']);
            $user->setNom($uData['nom']);
            $user->setRoles($uData['roles']);
            // Hasher le mot de passe
            $hashed = $this->passwordHasher->hashPassword($user, $uData['password']);
            $user->setPassword($hashed);

            // initialise visite et createdAt par défaut (constructeur gère createdAt en datetimeImmutable et visite à 0)
            $manager->persist($user);
            $userReferences[$uData['email']] = $user;
        }

        //
        // 2) Création de quelques prestations
        //
        $prestationsData = [
            [
                'nom'         => 'Coupe enfant',
                'prix'        => 10.0,
                'duree'       => 30,
                'description' => 'Coupe pour enfants jusqu’à 12 ans.',
                'formule'     => false
            ],
            [
                'nom'         => 'Coupe adulte',
                'prix'        => 20.0,
                'duree'       => 45,
                'description' => 'Coupe classique pour adulte, tonte incluse.',
                'formule'     => false
            ],
            [
                'nom'         => 'Shampooing + Coupe',
                'prix'        => 25.0,
                'duree'       => 60,
                'description' => 'Shampooing suivi d’une coupe standard.',
                'formule'     => true
            ],
            [
                'nom'         => 'Coloration',
                'prix'        => 40.0,
                'duree'       => 90,
                'description' => 'Coloration complète des cheveux.',
                'formule'     => false
            ],
            [
                'nom'         => 'Tresses simples',
                'prix'        => 30.0,
                'duree'       => 60,
                'description' => 'Tresses simples.',
                'formule'     => false
            ],
            [
                'nom'         => 'Tresses motifs',
                'prix'        => 40.0,
                'duree'       => 120,
                'description' => 'Tresses avec motifs.',
                'formule'     => false
            ],
            [
                'nom'         => 'Nattes',
                'prix'        => 30.0,
                'duree'       => 90,
                'description' => '',
                'formule'     => false
            ],
        ];

        $prestationReferences = [];
        foreach ($prestationsData as $pData) {
            $presta = new Prestation();
            $presta->setNom($pData['nom']);
            $presta->setPrix($pData['prix']);
            $presta->setDuree($pData['duree']);
            $presta->setDescription($pData['description']);
            $presta->setFormule($pData['formule']);

            $manager->persist($presta);
            $prestationReferences[$pData['nom']] = $presta;
        }

        //
        // 3) Création de disponibilités
        //
        // Pour la coiffeuse : on va créer 5 jours consécutifs à partir de demain,
        // chaque jour avec une plage 09h00-17h00 et 14h00-18h00, par exemple.
        //

        $today = new \DateTimeImmutable('today');
        for ($i = 1; $i <= 5; $i++) {
            // Créer une date pour demain + i-1
            $jour = $today->modify("+$i day");

            // Plage 1 : 09:00 → 12:00
            $debut1 = (clone $jour)->setTime(9, 0, 0);
            $fin1   = (clone $jour)->setTime(12, 0, 0);
            $dispo1 = new Disponibilite();
            $dispo1->setDebut(\DateTime::createFromFormat('Y-m-d H:i:s', $debut1->format('Y-m-d H:i:s')));
            $dispo1->setFin(\DateTime::createFromFormat('Y-m-d H:i:s', $fin1->format('Y-m-d H:i:s')));
            $manager->persist($dispo1);

            // Plage 2 : 14:00 → 18:00
            $debut2 = (clone $jour)->setTime(14, 0, 0);
            $fin2   = (clone $jour)->setTime(18, 0, 0);
            $dispo2 = new Disponibilite();
            $dispo2->setDebut(\DateTime::createFromFormat('Y-m-d H:i:s', $debut2->format('Y-m-d H:i:s')));
            $dispo2->setFin(\DateTime::createFromFormat('Y-m-d H:i:s', $fin2->format('Y-m-d H:i:s')));
            $manager->persist($dispo2);
        }

        // Enfin, on « flush » tout
        $manager->flush();
    }
}
