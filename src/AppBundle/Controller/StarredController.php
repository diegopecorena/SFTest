<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class StarredController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return new JsonResponse([
                'title' => 'List of repositories',
                'repositories' => $this->getRepositories($this->getUsersFromUrl($request))
            ], 200);
    }


    /* get all the users from the URL */
    protected function getUsersFromUrl(Request $request)
    {
        $users = $request->query->get('users');
        return explode(',', $users);
    } 


    /* get all the repositories */
    protected function getRepositories(Array $users)
    {
        $client = $this->get('guzzle.client');

        $results = Array();

        foreach ($users as $user) {
            $repositories = $client->get('https://api.github.com/users/' . $user . '/starred');              

            foreach ($repositories->send()->json() as $repository) {
                $items['name'] = $repository['name'];
                $items['owner'] = $repository['owner']['login'];
                $items['html_url'] = $repository['html_url'];
                $items['stargazers_count'] = $repository['stargazers_count'];
                $items['user'] = $user;                
                $items['full_name'] = $repository['full_name'];
                $results[] = $items;
            }

        }

        return $this->orderArray($results, 'name');
    }       


    /* function that sort the array */ 
    protected function orderArray($toOrderArray, $field) 
    {
        $position = array();
        $newRow = array();

        foreach ($toOrderArray as $key => $row) {
            $position[$key] = strtolower($row[$field]);
            $newRow[$key] = $row;
        }

        asort($position);

        $returnArray = array();
        
        foreach ($position as $key => $pos) {     
            $returnArray[] = $newRow[$key];
        }
        
        return $returnArray;
    }
}
