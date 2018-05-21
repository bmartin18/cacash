<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use AppBundle\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CategoryController
 */
class CategoryController extends Controller
{
    /** @var CategoryRepository */
    private $categoryRepository;

    /**
     * CategoryController constructor.
     *
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/categories.html", name="categories")
     *
     * @return Response
     */
    public function indexCategoriesAction()
    {
        return $this->render('category/index.html.twig');
    }

    /**
     * @Route("/categories/list", name="list_categories")
     *
     * @return Response
     */
    public function listCategoriesAction()
    {
        $categories = $this
            ->categoryRepository
            ->getParentList($this->getUser())
        ;

        return $this->render('category/list.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/category/create", name="create_category")
     *
     * @Route("/category/edit/{slug}-{categoryId}.html", name="edit_category",
     *      requirements={
     *          "slug": "[a-z0-9\-]+",
     *          "categoryId": "\d+"
     *      }
     * )
     *
     * @param Request $request
     * @param int     $categoryId
     *
     * @return RedirectResponse|Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createCategoryAction(Request $request, $categoryId = null)
    {
        $category = new Category();

        $action = $this->generateUrl('create_category');
        $template = "form";

        if ($categoryId) {
            $category = $this
                ->categoryRepository
                ->find($categoryId)
            ;

            $action = $this->generateUrl('edit_category', ['slug' => $category->getSlug(), 'categoryId' => $category->getId()]);
            $template = "edit";

            if ($this->getUser() !== $category->getUser()) {
                throw $this->createAccessDeniedException('You cannot access to this category.');
            }
        }

        $form = $this->createForm(CategoryType::class, $category, [
            'user' => $this->getUser(),
            'action' => $action,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $this->categoryRepository->save($category);

            $this->addFlash(
                'notice',
                'Catégorie '.$category->getName().' enregistrée avec succès.'
            );

            return $this->redirectToRoute('categories');
        }

        return $this->render(sprintf('category/%s.html.twig', $template), array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/category/delete/{slug}-{id}", name="delete_Category",
     *      requirements={
     *          "id": "\d+",
     *          "slug": "[a-z0-9\-]+"
     *      }
     * )
     *
     * @ParamConverter("id", class="AppBundle:Category")
     *
     * @param Category $category
     *
     * @return RedirectResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteCategoryAction(Category $category)
    {
        if ($this->getUser() !== $category->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this category.');
        }

        $this->categoryRepository->delete($category);

        $this->addFlash(
            'notice',
            'Catégorie '.$category->getName().' supprimée avec succès.'
        );

        return $this->redirectToRoute('categories');
    }
}
