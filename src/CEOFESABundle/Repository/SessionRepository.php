<?php

namespace CEOFESABundle\Repository;

use Doctrine\ORM\EntityRepository;

class SessionRepository extends EntityRepository
{
    public function getOFs($idModule,$idType,$idStructure)
    {
    	return $this
    	->createQueryBuilder('sess')
    	->where('sess.sesStructure = :idStructure')
        ->andWhere('sess.sesModule = :idModule')
        ->andWhere('sess.sesMtype = :idType')
        ->groupBy('sess.sesOf')
        ->setParameters(array('idStructure' => $idStructure, 'idModule' => $idModule, 'idType' => $idType))
        ;
    }

    public function getSessions($idModule,$idType,$idOf,$idStructure)
    {
    	return $this
    	->createQueryBuilder('sess')
    	->where('sess.sesStructure = :idStructure')
        ->andWhere('sess.sesModule = :idModule')
        ->andWhere('sess.sesMtype = :idType')
        ->andWhere('sess.sesOf = :idOf')
        ->setParameters(array('idStructure' => $idStructure, 'idModule' => $idModule, 'idType' => $idType, 'idOf' => $idOf))
        ;
    }
}