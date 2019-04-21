<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends AbstractType
{

    private function getConfig(string $label, string $placeholder, $required = true)
    {
        return [
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ],
            'required' => $required
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class,
                $this->getConfig('Email', 'Your email'))
            ->add('username', TextType::class,
                $this->getConfig('Username', 'Your username'))
            ->add('password', PasswordType::class, 
                $this->getConfig('Password', 'Your password'))
            ->add('confirmPassword', PasswordType::class, 
                $this->getConfig('Confirm password', 'Confirm your password'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
