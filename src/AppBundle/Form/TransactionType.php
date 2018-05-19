<?php

namespace AppBundle\Form;

use AppBundle\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TransactionType
 */
class TransactionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hash', TextType::class, [
                'label' => 'ID',
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'label' => 'Label',
                'required' => true,
            ])
            ->add('checked', CheckboxType::class, [
                'label' => 'Pointage',
                'required' => false,
            ])
            ->add('pool', CheckboxType::class, [
                'label' => 'Cagnotte',
                'required' => false,
            ])
            ->add('amountDisplayable', IntegerType::class, [
                'label' => 'Montant',
                'required' => true,
            ])
            ->add('debit', CheckboxType::class, [
                'label' => 'Débit',
                'required' => false,
                'data' => true,
            ])
            ->add('transactionAtDisplayable', TextType::class, [
                'label' => 'Date de la transaction',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'translation_domain' => false,
        ]);
    }
}
