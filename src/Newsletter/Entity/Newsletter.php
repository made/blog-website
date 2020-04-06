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

namespace App\Newsletter\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Newsletter
 *
 * This class along with its repository should later be put in the /Newsletter directory for easy future extraction.
 * Unfortunately it is not currently possible, because multiple sources are not supported.
 * It works when defining two mappings, but then the doctrine migrations ignore the additional configuration.
 *
 * @link https://github.com/doctrine/DoctrineBundle/issues/209
 *
 * @package App\Newsletter\Entity
 * @ORM\Entity(repositoryClass="App\Newsletter\Repository\NewsletterRepository")
 */
class Newsletter
{
    public const NEWSLETTER_LIST_NAME = 'made_blog_newsletter';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $list;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $code;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", options={"default" : "CURRENT_TIMESTAMP"})
     */
    private $creationDate;

    /**
     * @var bool
     *
     * @ORM\Column(type="smallint", length=1, options={"default" : 0})
     */
    private $confirmed = 0;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $confirmationDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Newsletter
     */
    public function setId(int $id): Newsletter
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return Newsletter
     */
    public function setLocale(string $locale): Newsletter
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string
     */
    public function getList(): string
    {
        return $this->list;
    }

    /**
     * @param string $list
     * @return Newsletter
     */
    public function setList(string $list): Newsletter
    {
        $this->list = $list;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Newsletter
     */
    public function setEmail(string $email): Newsletter
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Newsletter
     */
    public function setCode(string $code): Newsletter
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * @param bool $confirmed
     * @return Newsletter
     */
    public function setConfirmed(bool $confirmed): Newsletter
    {
        $this->confirmed = $confirmed;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    /**
     * @param DateTime $creationDate
     * @return Newsletter
     */
    public function setCreationDate(DateTime $creationDate): Newsletter
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getConfirmationDate(): DateTime
    {
        return $this->confirmationDate;
    }

    /**
     * @param DateTime $confirmationDate
     * @return Newsletter
     */
    public function setConfirmationDate(DateTime $confirmationDate): Newsletter
    {
        $this->confirmationDate = $confirmationDate;
        return $this;
    }
}
