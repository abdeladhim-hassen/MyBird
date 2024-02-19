<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Form\AnswerType;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping\Id;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    /**
     * @Route("/", name="question")
     */
    public function index(QuestionRepository $questionRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $Questions = $paginator->paginate(
            $questionRepository->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            3 /*limit per page*/
        );
        return $this->render('question/index.html.twig', [
            'Questions' => $Questions,
        ]);
    }

    /**
     * @Route("/question/new", name="question_new")
     */
    public function create(Request $request){
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $Question = new Question();
        $form = $this->createForm(QuestionType::class, $Question);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $Question->setCreatedAt(new \DateTime());
            $Question->setAuthor($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Question);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your Question was Posted'
            );
            return $this->redirectToRoute('question');

        }
        return $this->render('question/new.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/question/{id}", name="question_show", requirement)
     */
    public  function  Show(Request $request, QuestionRepository $questionRepository)
    {
        $QuestionId = $request->attributes->get('id');
        $Question = $questionRepository->find($QuestionId);
        $Answer = new Answer();
        $AnswerForm = $this->createForm(AnswerType::class, $Answer);
        $AnswerForm->handleRequest($request);
        $this->addAnswer($AnswerForm, $Answer, $Question);
        return $this->render('question/Show.html.twig',[
            'Question'=>$Question,
            'AnswerForm' => $AnswerForm->createView()
        ]);

    }

    /**
     * @Route("/question/{id}/edit", name="question_edit")
     */
        public function edit(Question $question, Request $request)
    {
        if ($this->getUser() !== $question->getAuthor()) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your Question was edited'
            );
            return $this->redirectToRoute('question_show', ['id' => $question->getId()]);
        }

        return $this->render('/question/edit.html.twig', [
            'Question' => $question,
            'editForm' => $form->createView()
        ]);
    }

    private function addAnswer($AnswerForm, $Answer, $Question)
    {
        if($AnswerForm->isSubmitted() && $AnswerForm->isValid()){
            $Answer->setCreatedAt(new \DateTimeImmutable());
            $Answer->setQuestion($Question);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Answer);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your Answer was added'
            );
            return $this->redirectToRoute('question_show', ['id' => $Question->getId()]);
        }
    }


}
