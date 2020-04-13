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

namespace App\Mailer\Model;

use App\Mailer\Service\MailService;

/**
 * Class MailConfig
 * @package App\Mailer\Model
 */
class MailConfiguration
{
    /**
     * @var string
     */
    private $from = 'noreply@made.dev';

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
    private $subject = 'no subject specified.';

    /**
     * @var string|null
     */
    private $body = 'no body specified.';

    /**
     * @var string|null
     */
    private $attachmentPath;

    /**
     * Name of a twig html template
     *
     * @var string|null
     */
    private $htmlTemplate;

    /**
     * @var string|null
     */
    private $textTemplate;

    /**
     * Values that should be passed to the template
     * @link https://symfony.com/doc/current/mailer.html#html-content
     * @var array|null
     */
    private $templateContext;

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
     * @return bool
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachmentPath);
    }

    /**
     * @return bool
     */
    public function hasHtmlTemplate(): bool
    {
        return !empty($this->htmlTemplate);
    }

    /**
     * @return bool
     */
    public function hasTextTemplate(): bool
    {
        return !empty($this->textTemplate);
    }

    /**
     * @return bool
     */
    public function hasTemplateContext(): bool
    {
        return !empty($this->templateContext);
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
     * @return MailConfiguration
     */
    public function setFrom(string $from): MailConfiguration
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
     * @return MailConfiguration
     */
    public function setTo(array $to): MailConfiguration
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
     * @return MailConfiguration
     */
    public function setCc(?array $cc): MailConfiguration
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
     * @return MailConfiguration
     */
    public function setBcc(?array $bcc): MailConfiguration
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
     * @return MailConfiguration
     */
    public function setSubject(?string $subject): MailConfiguration
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
     * @return MailConfiguration
     */
    public function setBody(?string $body): MailConfiguration
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttachmentPath(): ?string
    {
        return $this->attachmentPath;
    }

    /**
     * @param string|null $attachmentPath
     * @return MailConfiguration
     */
    public function setAttachmentPath(?string $attachmentPath): MailConfiguration
    {
        $this->attachmentPath = $attachmentPath;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHtmlTemplate(): ?string
    {
        return $this->htmlTemplate;
    }

    /**
     * @param string|null $htmlTemplate
     * @return MailConfiguration
     */
    public function setHtmlTemplate(?string $htmlTemplate): MailConfiguration
    {
        $this->htmlTemplate = $htmlTemplate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTextTemplate(): ?string
    {
        return $this->textTemplate;
    }

    /**
     * @param string|null $textTemplate
     * @return MailConfiguration
     */
    public function setTextTemplate(?string $textTemplate): MailConfiguration
    {
        $this->textTemplate = $textTemplate;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getTemplateContext(): ?array
    {
        return $this->templateContext;
    }

    /**
     * @param array|null $templateContext
     * @return MailConfiguration
     */
    public function setTemplateContext(?array $templateContext): MailConfiguration
    {
        $this->templateContext = $templateContext;
        return $this;
    }
}
