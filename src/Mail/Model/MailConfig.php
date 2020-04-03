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

namespace App\Mail\Model;

/**
 * Class MailConfig
 * @package App\Mail\Model
 */
class MailConfig
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var array
     */
    private $to;

    /**
     * @var array|null
     */
    private $cc;

    /**
     * @var array|null
     */
    private $bcc;

    /**
     * @var string|null
     */
    private $subject;

    /**
     * @var string|null
     */
    private $body;

    /**
     * @return bool
     */
    public function hasCc(): bool
    {
        return !empty($this->cc);
    }

    /**
     * @return bool
     */
    public function hasBcc(): bool
    {
        return !empty($this->bcc);
    }

    /**
     * @return bool
     */
    public function hasSubject(): bool
    {
        return !empty($this->subject);
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return MailConfig
     */
    public function setFrom(string $from): MailConfig
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param array $to
     * @return MailConfig
     */
    public function setTo(array $to): MailConfig
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getCc(): ?array
    {
        return $this->cc;
    }

    /**
     * @param array|null $cc
     * @return MailConfig
     */
    public function setCc(?array $cc): MailConfig
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getBcc(): ?array
    {
        return $this->bcc;
    }

    /**
     * @param array|null $bcc
     * @return MailConfig
     */
    public function setBcc(?array $bcc): MailConfig
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     * @return MailConfig
     */
    public function setSubject(?string $subject): MailConfig
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     * @return MailConfig
     */
    public function setBody(?string $body): MailConfig
    {
        $this->body = $body;
        return $this;
    }
}
