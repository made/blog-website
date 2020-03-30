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

namespace App\Newsletter\Service;

use App\Entity\Newsletter;
use App\Newsletter\Model\NewsletterEmail;
use App\Newsletter\Model\NewsletterToken;
use App\Repository\NewsletterRepository;
use App\Util\RandomTokenGenerator;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

/**
 * Class NewsletterService
 * @package App\Newsletter\Service
 */
class NewsletterService
{
    /**
     * @var NewsletterRepository
     */
    private $repository;

    /**
     * NewsletterService constructor.
     * @param NewsletterRepository $repository
     */
    public function __construct(NewsletterRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param NewsletterEmail $newsletterEmail
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function registerEmail(NewsletterEmail $newsletterEmail)
    {
        $result = $this->repository->findByEmail($newsletterEmail->getEmail());

        if ($result) {
            // ToDo: Custom Exception -> goto Token input
            throw new Exception('email already exists bruh');
        }

        $newsletter = new Newsletter();
        $newsletter
            ->setEmail($newsletterEmail->getEmail())
            ->setList(Newsletter::NEWSLETTER_LIST_NAME)
            ->setToken(RandomTokenGenerator::generate())
            ->setCreationDate(new DateTime());

        return $this->repository->persist($newsletter);
    }

    /**
     * @param string $email
     * @param NewsletterToken $token
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function confirmToken(string $email, NewsletterToken $token)
    {
        /** @var Newsletter $result */
        $result = $this->repository->findByEmail($email);

        if (!$result || $result->getToken() !== $token->getToken()) {
            throw new Exception('no entry found in db or token invalid - confirm via e-mail');
        }

        $result
            ->setStatus(true)
            ->setConfirmationDate(new DateTime());

        return $this->repository->persist($result);
    }
}