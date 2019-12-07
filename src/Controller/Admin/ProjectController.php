<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Form\DeleteType;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectController extends AbstractController
{
    /**
     * @param Request $request
     * @param ProjectRepository $projectRepository
     * @return Response
     */
    public function index(Request $request, ProjectRepository $projectRepository): Response
    {
        $query = $projectRepository->createQueryBuilder('p');

        $searchQ = trim($request->query->get('q'));
        if (strlen($searchQ) > 0) {
            $query
                ->where(
                    $query->expr()->like('p.name', $query->expr()->literal('%' . $searchQ . '%'))
                );
        }

        return $this->render('admin/project/index.html.twig', [
            'projects' => $query->getQuery()->getResult(),
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
        $project = new Project();
        $project->setIsActive(true);
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project
                ->setCreatedBy($this->getUser())
                ->setCreatedAt(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($project);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('backend.project.messages.new_successful'));

            return $this->redirectToRoute('project_index');
        }

        return $this->render('admin/project/new.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Project $project
     * @return Response
     */
    public function show(Project $project): Response
    {
        return $this->render('admin/project/show.html.twig', [
            'project' => $project,
        ]);
    }

    /**
     * @param Request $request
     * @param Project $project
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, Project $project, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project
                ->setUpdatedBy($this->getUser())
                ->setUpdatedAt(new \DateTime());

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('backend.project.messages.edit_successful'));

            return $this->redirectToRoute('project_index');
        }

        return $this->render('admin/project/edit.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Project $project
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete(Request $request, Project $project, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(DeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($project);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('backend.project.messages.deleted_successful'));

            return $this->redirectToRoute('project_index');
        }

        return $this->render('admin/project/delete.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }
}
