<?php

namespace App\Form;

use App\Entity\Prestation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrestationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $commonAttr = [
            'class' => 'form-control',
            'style' => 'width: 100%; padding: 0.5rem; border-radius: 5px; border: 1px solid #ccc;'
        ];

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => $commonAttr,
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'attr' => $commonAttr,
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => $commonAttr,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => $commonAttr + ['rows' => 4],
            ])
            ->add('formule', CheckboxType::class, [
                'label' => 'Formule',
                'required' => false,
                'attr' => [
                    'style' => 'margin-top: 8px; margin-bottom: 8px;',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prestation::class,
        ]);
    }
}
