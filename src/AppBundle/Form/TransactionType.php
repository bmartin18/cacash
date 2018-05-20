<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use AppBundle\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => '-- Choisir une catégorie --',
                'required' => false,
                'query_builder' => function (ServiceEntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin('c.parent', 'p')
                        ->orderBy('p.name, c.name', 'ASC')
                    ;
                },
                'group_by' => function ($category) {
                    if (is_null($category->getParent())) {
                        return "Catégories principales";
                    }

                    return $category->getParent()->getName();
                },
            ])
            ->add('checked', CheckboxType::class, [
                'label' => 'Pointage',
                'required' => false,
            ])
            ->add('amountDisplayable', IntegerType::class, [
                'label' => 'Montant',
                'required' => true,
            ])
            ->add('debit', CheckboxType::class, [
                'label' => 'Débit',
                'required' => false,
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
