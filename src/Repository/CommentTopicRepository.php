<?php

namespace App\Repository;

use App\Entity\CommentTopic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommentTopic>
 *
 * @method CommentTopic|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentTopic|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentTopic[]    findAll()
 * @method CommentTopic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentTopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentTopic::class);
    }

//    /**
//     * @return CommentTopic[] Returns an array of CommentTopic objects
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

//    public function findOneBySomeField($value): ?CommentTopic
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
