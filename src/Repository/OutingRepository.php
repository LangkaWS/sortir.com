<?php

namespace App\Repository;

use App\Entity\Outing;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Outing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Outing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Outing[]    findAll()
 * @method Outing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OutingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Outing::class);
    }

    // /**
    //  * @return Outing[] Returns an array of Outing objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Outing
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findWithFilter($campus, $nameContains, $minDate, $maxDate, $isOrganizer, $isParticipant, $isNotParticipant, $isPassed, $user)
    {
        $qb = $this->createQueryBuilder('o');
  
        $qb->andWhere('o.startDate >= :val')
            ->setParameter('val', (new DateTime())->sub(new DateInterval("P".$value."M"))->format("Y-m-d H:i:s"))
            ->orderBy('o.startDate', 'ASC')

        if($campus) {
            $qb->andWhere('o.campus = :campus')
            ->setParameter('campus', $campus);
        }

        if($nameContains) {
            $qb->andWhere('o.outingName LIKE :name')
            ->setParameter('name', '%'.$nameContains.'%');
        }

        if($minDate && $maxDate) {
            $qb->andWhere('o.startDate BETWEEN :minDate AND :maxDate')
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate);
        } else if($minDate) {
            $qb->andWhere('o.startDate >= :minDate')
            ->setParameter('minDate', ($minDate.' 00:00:00'));
        } else if($maxDate) {
            $qb->andWhere('o.startDate <= :maxDate')
            ->setParameter('maxDate', ($maxDate.' 00:00:00'));
        }

        if($isOrganizer) {
            $qb->andWhere('o.organizer = :organizer')
            ->setParameter('organizer', $user);
        }

        if($isParticipant) {
            $qb->andWhere(':user MEMBER OF o.participants')
            ->setParameter('user', $user);
        }

        if($isNotParticipant) {
            $qb->andWhere(':user NOT MEMBER OF o.participants')
            ->setParameter('user', $user);
        }

        if($isPassed) {
            $qb->andWhere('o.state = :val')
            ->setParameter('val', 5);
        }


        return $qb->getQuery()->getResult()

    public function findByNotArchived($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.startDate >= :val')
            ->setParameter('val', (new DateTime())->sub(new DateInterval("P".$value."M"))->format("Y-m-d H:i:s"))
            ->orderBy('o.startDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
