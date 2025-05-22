<?php

namespace App\Form;

use App\Entity\Chat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Required;

class ChatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userIds', CollectionType::class, [
                'entry_type' => IntegerType::class,
                'mapped' => false,
                'required' => true,
                'compound' => true,
                'allow_add' => true,
                'constraints' => [
                    new Count(null, 1, null, null, null, null, null, null, ['groups' => ['input']]),
                    new NotBlank(['groups' => ['input']]),
                    new NotNull(['groups' => ['input']]),
                    new Required(['groups' => ['input']]),
                ],
            ])
            ->add('name', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chat::class,
            'csrf_protection' => false,
            'validation_groups' => ['input']
        ]);
    }
}
