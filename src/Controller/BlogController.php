<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;



class BlogController extends AbstractController
{
    private $repoArticle;
    private $repoCategory;

    public function __construct(ArticleRepository $repoArticle, CategoryRepository $repoCategory)
    {
        $this->repoArticle = $repoArticle;
        $this->repoCategory = $repoCategory;
    }
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $categories = $this->repoCategory->findAll();
        // $repo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $this->repoArticle->findAll();

        $articles = $paginator->paginate(
            $articles, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            12 // Nombre de résultats par page
        );



        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'categories' => $categories
        ]);
    }

    public function add(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());

            if ($article->getPicture() !== null) {
                $file = $form->get('picture')->getData();
                $fileName =  uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'), // Le dossier dans lequel le fichier va être charger
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($fileName);
            }

            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            $em = $this->getDoctrine()->getManager(); // On récupère l'entity manager
            $em->persist($article); // On gère notre entité à l'entity manager (on persist l'entité)
            $em->flush(); // On execute la requete

            return new Response('L\'article a bien été enregistrer.');
        }

        return $this->render('blog/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //paramConverter

    public function show(Article $articles)
    {

        return $this->render('blog/show.html.twig', [
            'article' => $articles
        ]);
    }
    // render() un tableau qui contient les variables que nous envoyons à notre page et que nous pourrons afficher 

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Article $article, Request $request)
    {
        $oldPicture = $article->getPicture();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());

            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            if ($article->getPicture() !== null && $article->getPicture() !== $oldPicture) {
                $file = $form->get('picture')->getData();
                $fileName = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($fileName);
            } else {
                $article->setPicture($oldPicture);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return new Response('L\'article a bien été modifié.');
        }

        return $this->render('blog/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }

    public function remove($id): Response
    {
        return new Response('<h1> Supprimer l\'article' . $id . ' </h1>');
    }

    public function admin()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            [],
            ['lastUpdateDate' => 'DESC']
        );

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'articles' => $articles,
            'users' => $users
        ]);
    }
}
