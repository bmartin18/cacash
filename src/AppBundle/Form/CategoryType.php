<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CategoryType
 */
class CategoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];

        $builder
            ->add('parent', EntityType::class, [
                'label' => 'Catégorie parent',
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => '-- Choisir une catégorie --',
                'required' => false,
                'query_builder' => function (ServiceEntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->where('c.user = :user')
                            ->setParameter('user', $user)
                            ->andWhere('c.parent IS NULL')
                        ->orderBy('c.name', 'ASC')
                        ;
                },
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom de la catégorie',
                'required' => true,
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
            'user' =>  null,
            'data_class' => Category::class,
            'translation_domain' => false,
        ]);
    }
}
