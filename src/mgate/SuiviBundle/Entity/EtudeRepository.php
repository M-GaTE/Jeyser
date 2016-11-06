<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\SuiviBundle\Entity;

use Doctrine\ORM\EntityRepository;
use n7consulting\RhBundle\Entity\Competence;

/**
 * EtudeRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EtudeRepository extends EntityRepository
{
    public function findByNumero($numero)
    {
        $mandat = (int) ($numero / 100);
        $num = $numero % 100;

        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('e')
            ->from('mgateSuiviBundle:Etude', 'e')
            ->where("e.mandat = $mandat")
            ->andWhere("e.num = $num");

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $nom
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * Création d'une méthode précise au lieu d'utiliser findOneByNom pour permettre l'ajout ultérieur de jointure
     */
    public function getByNom($nom)
    {
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('e')
            ->from('mgateSuiviBundle:Etude', 'e')
            ->where('e.nom = :nom')
            ->setParameter('nom', $nom);

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getEtudesCa()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('e')
            ->from('mgateSuiviBundle:Cc', 'cc')
            ->leftJoin('cc.etude', 'e');
        //->addSelect('e')
        //->where('e.cc IS NOT NULL')
        //->addOrderBy('cc.dateSignature');

        return $query->getQuery()->getResult();
    }

    public function findByCompetence(Competence $competence)
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('e')
            ->from('mgateSuiviBundle:Etude', 'e')
            ->leftJoin('e.competences', 'c')
            ->addSelect('c')
            ->leftJoin('e.phases', 'p')->addSelect('p')//cette requete n'est utilisée que sur la page RH du bundle N7Consulting. Comme elle affiche le nombre de JEH, ajout d'une jointure sur les phases pour éviter de faire une requete sur les phases a chaque étude.
            ->leftJoin('e.cc', 'cc')->addSelect('cc')
            ->leftJoin('e.ap', 'ap')->addSelect('ap')
            ->where(':competence MEMBER OF e.competences')
            ->setParameter('competence', $competence)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $etat array variable pour récuperer les études selon leurs etats d'avancement
     * @param $order array tableau des champs sur lesquels ordonnés les études.
     * Requete spéciale pour afficher le pipeline des études.
     * A permis de réduire le nombre de requetes de 109 à 34. Il est possible de réduire encore plus le nombre de requetes, mais la page se met alors à diverger en temps, car les reuqtes sont de plus en plus longues
     */
    public function getPipeline(array $etat, array $orders = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')
            ->from('mgateSuiviBundle:Etude', 'e')
            ->where('e.stateID = :stateId')
            ->setParameter('stateId', $etat[key($etat)]);

        if ($orders !== null) {
            foreach ($orders as $column => $value) {
                $qb->orderBy('e.'.$column, $value);
            }
        }
        //les jointures
        $qb
            ->leftJoin('e.avs', 'avs')
            ->addSelect('avs')
            ->leftJoin('e.ap', 'ap')
            ->addSelect('ap')
            ->leftJoin('e.cc', 'cc')
            ->addSelect('cc')
            ->leftJoin('e.clientContacts', 'clientContacts')
            ->addSelect('clientContacts')//on laisse le champ faitPar d'un contact client en asynchrone, car ça ne devrait pas trop diverger, et que ça rajoute beaucoup d'informations dans la requete pour pas grand chose en termes de fonctionnalités.
            ->leftJoin('e.prospect', 'prospect')
            ->addSelect('prospect')
            ->leftJoin('e.phases', 'phases')
            ->addSelect('phases')
//            ->leftJoin('e.procesVerbaux', 'procesVerbaux')
//            ->addSelect('procesVerbaux')
//            ->leftJoin('e.factures', 'factures')
//            ->addSelect('factures')
            ->leftJoin('e.suiveur', 'suiveur')
            ->addSelect('suiveur')
            ->leftJoin('e.missions', 'missions')
            ->addSelect('missions')
            ->leftJoin('missions.repartitionsJEH', 'repartitionsJEH')
            ->addSelect('repartitionsJEH');

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * Get all project according to their state. Mainly used in VuCA to display negociate and current project.
     *
     * @param array      $states 2 states whom you want all projects in that states
     * @param array|null $orders how should project be ordered
     *
     * @return array of projects
     */
    public function getTwoStates(array $states = [1, 2], array $orders = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')
            ->from('mgateSuiviBundle:Etude', 'e')
            ->where('e.stateID = :stateNegociate or e.stateID= :stateCurrent')
            ->setParameter('stateNegociate', $states[0])
            ->setParameter('stateCurrent', $states[1]);
        if ($orders !== null) {
            foreach ($orders as $column => $value) {
                $qb->orderBy('e.'.$column, $value);
            }
        }
        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * @param $search string a pattern we'd like to search in etudes' name
     * @param int $limit the number of etudes that research should return
     * @return array
     */
    public function searchByNom($search,$limit = 10){
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')
            ->from('mgateSuiviBundle:Etude', 'e')
            ->where('e.nom LIKE :nom')
            ->setParameter('nom', '%'.$search.'%')
            ->setMaxResults($limit);
        $query = $qb->getQuery();
        return $query->getResult();
    }


}
