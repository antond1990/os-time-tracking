<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\DeleteType;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskController extends AbstractController
{
    /**
     * @param Request $request
     * @param TaskRepository $taskRepository
     * @return Response
     */
    public function index(Request $request, TaskRepository $taskRepository): Response
    {
        $query = $taskRepository->createQueryBuilder('t');

        $searchQ = trim($request->query->get('q'));
        if (strlen($searchQ) > 0) {
            $query
                ->where(
                    $query->expr()->like('t.name', $query->expr()->literal('%' . $searchQ . '%'))
                );
        }

        return $this->render('task/index.html.twig', [
            'tasks' => $query->getQuery()->getResult(),
            'search_query' => $searchQ,
        ]);
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function new(Request $request, TranslatorInterface $translator): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task
                ->setCreatedBy($this->getUser())
                ->setCreatedAt(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('task.messages.new_successful'));

            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Task $task
     * @return Response
     */
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    /**
     * @param Request $request
     * @param Task $task
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, Task $task, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task
                ->setUpdatedBy($this->getUser())
                ->setUpdatedAt(new \DateTime());

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('task.messages.edit_successful'));

            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Task $task
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete(Request $request, Task $task, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(DeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($task);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('task.messages.deleted_successful'));

            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/delete.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }
}
