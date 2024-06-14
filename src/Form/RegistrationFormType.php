<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('username', TextType::class, [
                'label' => false,
                'row_attr' => ['class' => 'form-group'],
                'attr' => ['placeholder' => 'Логин', 'class' => 'form-control']
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Принять пользовательское соглашение',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
                'label_attr' => ['class' => 'checkbox-inline'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                    'row_attr' => ['class' => 'form-group'],

                ],
                'mapped' => false,
                'first_options' => [
                    'label' => false,
                    'attr' => ['placeholder' => 'Пароль'],
                    'help' => 'Пароль должен содержать не менее 8 символов',
                    'help_attr' => ['class' => 'text-muted'],
                ],
                'second_options' => ['label' => false, 'attr' => ['placeholder' => 'Повторите пароль']],

                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
