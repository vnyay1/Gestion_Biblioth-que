<?php
// src/Controller/LivreController.php
namespace App\Controller;

use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/livre")
 */
class LivreController extends AbstractController
{
    /**
     * @Route("/", name="livre_index")
     */
    public function index(LivreRepository $repo)
    {
        return $this->render('livre/index.html.twig', [
            'livres' => $repo->findAll()
        ]);
    }

    /**
     * @Route("/new", name="livre_new")
     */
    public function new(Request $request, EntityManagerInterface $em)
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($livre);
            $em->flush();

            return $this->redirectToRoute('livre_index');
        }

        return $this->render('livre/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="livre_show")
     */
    public function show(Livre $livre)
    {
        return $this->render('livre/show.html.twig', [
            'livre' => $livre
        ]);
    }

    /**
     * @Route("/{id}/edit", name="livre_edit")
     */
    public function edit(Request $request, Livre $livre, EntityManagerInterface $em)
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('livre_index');
        }

        return $this->render('livre/edit.html.twig', [
            'form' => $form->createView(),
            'livre' => $livre
        ]);
    }

    /**
     * @Route("/{id}/delete", name="livre_delete", methods={"POST"})
     */
    public function delete(Request $request, Livre $livre, EntityManagerInterface $em)
    {
        if ($this->isCsrfTokenValid('delete'.$livre->getId(), $request->request->get('_token'))) {
            $em->remove($livre);
            $em->flush();
        }

        return $this->redirectToRoute('livre_index');
    }
}
