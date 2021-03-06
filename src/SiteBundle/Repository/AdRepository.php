<?php

namespace SiteBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * AdRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdRepository extends EntityRepository
{

    public function findAll()
    {
        return $this->findBy(array(), array('title' => 'ASC'));
    }

    public function getAdByParam($param)
    {
        $qb = $this->createQueryBuilder('adsearch');

        $qb->select('ad')
            ->from('SiteBundle:Ad', 'ad')
            ->leftJoin('ad.category', 'cat')
            ->orderBy('ad.title', 'ASC');

        $i=1;
        foreach ($param as $key => $value) {
            if(!empty($value)) {
                if ($key == "ad.title") {
                    $qb
                        ->andWhere($key . ' LIKE ?' . $i)
                        ->setParameter($i, '%' . addcslashes($value, '%_') . '%');
                } else {
                    $qb
                        ->andWhere($key . ' = ?' . $i)
                        ->setParameter($i, $value);
                }

                $i++;
            }
        }
        //var_dump($qb->getQuery()); exit;
        return $qb->getQuery()->getResult();
    }

    public function getFav($user) {

        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT a FROM SiteBundle:Ad a JOIN a.favoris f WHERE f.id = :id_user')
            ->setParameter('id_user', $user->getId());

        return $query->getResult();
    }

}
