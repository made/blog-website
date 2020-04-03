<?php

/**
 * Made Blog
 * Copyright (c) 2019-2020 Made
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Repository;

use App\Entity\Newsletter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class NewsletterRepository
 * @package App\Repository
 */
class NewsletterRepository extends ServiceEntityRepository
{
    /**
     * NewsletterRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Newsletter::class);
    }

    /**
     * @param string $email
     * @return Newsletter|null
     * @throws NonUniqueResultException
     */
    public function findByEmail(string $email)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('n')
            ->from(Newsletter::class, 'n')
            ->where('n.email = :email')
            ->andWhere('n.list = :list');

        $qb->setParameters([
            'email' => $email,
            'list' => Newsletter::NEWSLETTER_LIST_NAME
        ]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Newsletter $newsletter
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist(Newsletter $newsletter)
    {
        $this->_em->persist($newsletter);
        $this->_em->flush();
    }
}