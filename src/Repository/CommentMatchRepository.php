<?php

namespace App\Repository;

use App\Entity\CommentMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommentMatch>
 *
 * @method CommentMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentMatch[]    findAll()
 * @method CommentMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentMatch::class);
    }
    

//    /**
//     * @return CommentMatch[] Returns an array of CommentMatch objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CommentMatch
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
