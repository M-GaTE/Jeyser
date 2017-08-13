<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\TresoBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * NoteDeFraisRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FactureRepository extends EntityRepository
{
    /**
     * Renvoie les facture d'achat ou de vente sur un mois selon la date d'emmision pour les factures d'achat et d'encaissement pour les factures de vente
     * YEAR MONTH DAY sont défini dans DashBoardBundle/DQL (qui doit devenir FrontEndBundle).
     *
     * @return array
     */
    public function findAllTVAByMonth($type, $month, $year, $trimestriel = false)
    {
        $date = ($type == 1 ? 'dateEmission' : 'dateVersement');
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('f')
                     ->from('MgateTresoBundle:Facture', 'f')
                     ->where('f.type ' . ($type == Facture::TYPE_ACHAT ? '=' : '>') . ' ' . Facture::TYPE_ACHAT);
        if ($trimestriel) {
            $query->andWhere("MONTH(f.$date) >= $month")
                  ->andWhere("MONTH(f.$date) < ($month + 2)");
        } else {
            $query->where("MONTH(f.$date) = $month");
        }

        $query->andWhere("YEAR(f.$date) = $year")->orderBy("f.$date");

        return $query->getQuery()->getResult();
    }
}
