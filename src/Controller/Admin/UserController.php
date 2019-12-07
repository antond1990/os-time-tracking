<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 * @package App\Controller\Admin
 */
class UserController extends AbstractController
{

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, UserRepository $userRepository)
    {
        $query = $userRepository->createQueryBuilder('p');

        $searchQ = trim($request->query->get('q'));
        if (strlen($searchQ) > 0) {
            $query
                ->where(
                    $query->expr()->like('p.name', $query->expr()->literal('%' . $searchQ . '%'))
                );
        }

        $users = $query->getQuery()->getResult();

        $params = [
            'users' => $users,
            'search_query' => $searchQ,
        ];
        return $this->render('admin/user/index.html.twig', $params);
    }

}
