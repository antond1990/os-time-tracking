<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Form\CustomerType;
use App\Form\DeleteType;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerController extends AbstractController
{
    /**
     * @param CustomerRepository $customerRepository
     * @return Response
     */
    public function index(Request $request, CustomerRepository $customerRepository): Response
    {
        $query = $customerRepository->createQueryBuilder('c')
            ->where('c.id > 0');

        $searchQ = $request->query->get('q');
        if (strlen($searchQ) > 0) {
            $query = $query
                ->andWhere($query->expr()->like('c.name', $query->expr()->literal('%' . $searchQ . '%')));
        }

        $customers = $query->getQuery()->getResult();

        $params = [
            'customers' => $customers,
            'search_query' => $searchQ,
        ];
        return $this->render('admin/customer/index.html.twig', $params);
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function new(Request $request, TranslatorInterface $translator): Response
    {
        $customer = new Customer();
        $customer->setIsActive(true);
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setCreatedAt(new \DateTime());
            $customer->setCreatedBy($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($customer);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('backend.customer.messages.new_successfull'));

            return $this->redirectToRoute('customer_index');
        }

        return $this->render('admin/customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Customer $customer
     * @return Response
     */
    public function show(Customer $customer): Response
    {
        return $this->render('admin/customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    /**
     * @param Request $request
     * @param Customer $customer
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, Customer $customer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('backend.customer.messages.edit_successfull'));

            return $this->redirectToRoute('customer_index');
        }

        return $this->render('admin/customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Customer $customer
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function delete(Request $request, Customer $customer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(DeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($customer);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('backend.customer.messages.deleted_successfull'));

            return $this->redirectToRoute('customer_index');
        }

        $params = [
            'form' => $form->createView(),
            'object' => $customer,
        ];
        return $this->render('admin/delete.html.twig', $params);
    }
}
