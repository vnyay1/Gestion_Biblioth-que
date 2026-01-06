<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/livre')]
class LivreController extends AbstractController
{
    #[Route('/', name: 'livre_index', methods: ['GET'])]
    public function index(LivreRepository $repo): Response
    {
        $livres = $repo->findAll();
        
        return $this->render('livre/index.html.twig', [
            'livres' => $livres,
            'total' => count($livres)
        ]);
    }

    #[Route('/new', name: 'livre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($livre);
                $em->flush();

                $this->addFlash('success', 'Le livre "' . $livre->getTitre() . '" a été ajouté avec succès !');
                return $this->redirectToRoute('livre_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'ajout du livre : ' . $e->getMessage());
            }
        }

        return $this->render('livre/new.html.twig', [
            'form' => $form,
            'livre' => $livre
        ]);
    }

    #[Route('/{id}', name: 'livre_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Livre $livre): Response
    {
        return $this->render('livre/show.html.twig', [
            'livre' => $livre
        ]);
    }

    #[Route('/{id}/edit', name: 'livre_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Livre $livre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                
                $this->addFlash('success', 'Le livre "' . $livre->getTitre() . '" a été modifié avec succès !');
                return $this->redirectToRoute('livre_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('livre/edit.html.twig', [
            'form' => $form,
            'livre' => $livre
        ]);
    }

    #[Route('/{id}/delete', name: 'livre_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Livre $livre, EntityManagerInterface $em): Response
    {
        $titreLivre = $livre->getTitre();
        
        if ($this->isCsrfTokenValid('delete' . $livre->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($livre);
                $em->flush();
                
                $this->addFlash('success', 'Le livre "' . $titreLivre . '" a été supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Suppression annulée.');
        }

        return $this->redirectToRoute('livre_index');
    }

    /**
     * Recherche de livres par titre ou auteur
     */
    #[Route('/search', name: 'livre_search', methods: ['GET'])]
    public function search(Request $request, LivreRepository $repo): Response
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->redirectToRoute('livre_index');
        }

        $livres = $repo->searchByTitreOrAuteur($query);
        
        return $this->render('livre/index.html.twig', [
            'livres' => $livres,
            'total' => count($livres),
            'search_query' => $query
        ]);
    }

    /**
     * Marquer un livre comme emprunté
     */
    #[Route('/{id}/emprunter', name: 'livre_emprunter', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function emprunter(Request $request, Livre $livre, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('emprunter' . $livre->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
        }

        if (!$livre->isDisponible()) {
            $this->addFlash('warning', 'Ce livre n\'est pas disponible.');
            return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
        }

        $livre->emprunter();
        $em->flush();

        $this->addFlash('success', 'Le livre a été marqué comme emprunté.');
        return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
    }

    /**
     * Marquer un livre comme retourné
     */
    #[Route('/{id}/retourner', name: 'livre_retourner', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function retourner(Request $request, Livre $livre, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('retourner' . $livre->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
        }

        if ($livre->isDisponible()) {
            $this->addFlash('warning', 'Ce livre est déjà disponible.');
            return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
        }

        $livre->retourner();
        $em->flush();

        $this->addFlash('success', 'Le livre a été marqué comme retourné.');
        return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
    }
}