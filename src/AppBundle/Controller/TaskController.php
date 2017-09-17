<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Form\Type\TaskType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class TaskController extends Controller
{
    /**
     * @Route("/tasks", name="task_list")
     * @Method({"GET"})
     */
    public function listAction()
    {
        $response = $this->render('task/list.html.twig',
            ['tasks' => $this->getDoctrine()->getRepository('AppBundle:Task')
                ->findAllForUser(
                    $this->get('security.token_storage')->getToken()->getUser()
                )
            ]
        );
        $response->setSharedMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    /**
     * @Route("/tasks/create", name="task_create")
     * @Method({"GET", "POST"})
     */
    public function createAction(Request $request, Response $response = NULL)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $user = $this->getDoctrine()->getRepository('TodoSecurityBundle:User')->findOneBy(array('username' => $this->get('security.token_storage')->getToken()->getUser()->getUsername()));
        $task->setUser($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');
            if($response){
                $response->expire();
            }
            return $this->redirectToRoute('task_list');
        }

        $response = $this->render('task/create.html.twig', ['form' => $form->createView()]);
        $response->setSharedMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;

    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Task $task, Request $request, Response $response = NULL)
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');
            if($response){
                $response->expire();
            }
            return $this->redirectToRoute('task_list');
        }


        $response = $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
        $response->setSharedMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @Method({"POST"})
     */
    public function toggleTaskAction(Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @Method({"POST"})
     */
    public function deleteTaskAction(Task $task)
    {
        if($task->getUser() === NULL){
            if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
                $this->addFlash('error', 'Vous ne pouvez pas supprimer cette tâche. Seul un administrateur du site le peut.');
                return $this->redirectToRoute('task_list');
            }
        } elseif ($task->getUser()->getUsername() !== $this->get('security.token_storage')->getToken()->getUser()->getUsername()){
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette tâche. Seul son propriétaire le peut.');
            return $this->redirectToRoute('task_list');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
