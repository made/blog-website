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

namespace App\Newsletter\FormType;

use App\Newsletter\Model\NewsletterEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NewsletterForm
 * @package App\Newsletter\Form
 */
class NewsletterEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control',  'placeholder' => 'E-Mail', 'aria-label' => 'E-Mail'],
            ])
            ->add('register', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NewsletterEmail::class,
        ]);
    }
}
