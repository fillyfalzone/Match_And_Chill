<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
* @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function participate($userId, $eventId)
{
    // Crée une instance de QueryBuilder pour construire la requête DQL
    $qb = $this->createQueryBuilder('u');

    // Sélectionne l'entité User, optimisant la requête en chargeant uniquement les données nécessaires
    $qb->select('u')
    ->join('u.paticipateEvents', 'p') // Correction ici: utilise 'paticipateEvents' au lieu de 'participations'
    ->join('p.usersParticipate', 'e') // Cette ligne semble incorrecte basée sur votre modèle. Vous devriez avoir besoin d'une approche différente pour accéder à l'événement.
    ->where('u.id = :userId') // Condition pour filtrer sur l'ID de l'utilisateur
    ->andWhere('e.id = :eventId') // Condition supplémentaire pour filtrer sur l'ID de l'événement
    ->setParameters([
        'userId' => $userId, // Définit le paramètre userId pour la requête
        'eventId' => $eventId, // Définit le paramètre eventId pour la requête
    ]);

    // Exécute la requête pour obtenir le résultat et le retourne
    return $qb->getQuery()->getOneOrNullResult(); // Manque un point-virgule ici dans votre code original
}

  
//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
