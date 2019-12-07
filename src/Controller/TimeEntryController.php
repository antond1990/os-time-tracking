<?php

namespace App\Controller;

use App\Entity\TimeEntry;
use App\Form\DeleteType;
use App\Form\TimeEntryType;
use App\Repository\TaskRepository;
use App\Repository\TimeEntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class TimeEntryController extends AbstractController
{
    /**
     * @param Request $request
     * @param TimeEntryRepository $timeEntryRepository
     * @return Response
     */
    public function index(Request $request, TimeEntryRepository $timeEntryRepository): Response
    {
        $query = $timeEntryRepository
            ->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
        ;

        $searchQ = trim($request->query->get('q'));
        if (strlen($searchQ) > 0) {
            $query
                ->where(
                    $query->expr()->like('p.name', $query->expr()->literal('%' . $searchQ . '%'))
                );
        }

        return $this->render('time_entry/index.html.twig', [
            'timeEntries' => $query->getQuery()->getResult(),
            'search_query' => $searchQ,
        ]);
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function new(Request $request, TranslatorInterface $translator, TaskRepository $taskRepository): Response
    {
        if (sizeof($taskRepository->findTasksForUser($this->getUser())) == 0) {
            $this->addFlash('warning', $translator->trans('time_entry.messages.no_tasks_message'));
            return $this->redirectToRoute('task_new');
        }

        $timeEntry = new TimeEntry();
        $timeEntry->setDay(new \DateTime());

        $form = $this->createForm(TimeEntryType::class, $timeEntry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $timeEntry
                ->setUser($this->getUser())
                ->setCreatedBy($this->getUser())
                ->setCreatedAt(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($timeEntry);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('time_entry.messages.new_successful'));

            return $this->redirectToRoute('time_entry_index');
        }

        return $this->render('time_entry/new.html.twig', [
            'timeEntry' => $timeEntry,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param TimeEntry $timeEntry
     * @return Response
     */
    public function show(TimeEntry $timeEntry): Response
    {
        return $this->render('time_entry/show.html.twig', [
            'timeEntry' => $timeEntry,
        ]);
    }

    /**
     * @param Request $request
     * @param TimeEntry $timeEntry
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, TimeEntry $timeEntry, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(TimeEntryType::class, $timeEntry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $timeEntry
                ->setUpdatedBy($this->getUser())
                ->setUpdatedAt(new \DateTime());

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('time_entry.messages.edit_successful'));

            return $this->redirectToRoute('time_entry_index');
        }

        return $this->render('time_entry/edit.html.twig', [
            'timeEntry' => $timeEntry,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param TimeEntry $timeEntry
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete(Request $request, TimeEntry $timeEntry, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(DeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($timeEntry);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('time_entry.messages.deleted_successful'));

            return $this->redirectToRoute('time_entry_index');
        }

        return $this->render('time_entry/delete.html.twig', [
            'form' => $form->createView(),
            'timeEntry' => $timeEntry,
        ]);
    }
}
