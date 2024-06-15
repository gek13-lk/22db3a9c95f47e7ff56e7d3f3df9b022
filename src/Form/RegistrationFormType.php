<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
                'attr' => ['placeholder' => 'Логин', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Логин не может быть пустым',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'row_attr' => ['class' => 'form-group'],
                'attr' => ['placeholder' => 'example@yandex.ru', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'e-mail не может быть пустым',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Принять пользовательское соглашение',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Для работы в нашей системе необходимо принять пользовательское соглашение',
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
                ],
                'second_options' => ['label' => false, 'attr' => ['placeholder' => 'Повторите пароль']],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Пароль должен содержать минимум {{ limit }} символов',
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
