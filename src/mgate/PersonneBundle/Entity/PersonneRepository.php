<?php

namespace mgate\PersonneBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PersonneRepository extends EntityRepository
{
    public function getMembreOnly()
    {
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('n')->from('mgatePersonneBundle:Personne', 'n')
          ->where('n.membre IS NOT NULL')
          //->where( $qb->expr()->neq('n.membre', null ))
          ;
        return $query;
    }
    
    public function getEmployeOnly()
    {
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('n')->from('mgatePersonneBundle:Personne', 'n')
          ->where('n.employe IS NOT NULL');
        return $query;
    }
    
    public function getNotUser()
    {
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('n')->from('mgatePersonneBundle:Personne', 'n')
          ->where('n.user IS NULL');
        return $query;
    }
}
