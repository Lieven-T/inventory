<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    public function csv()
    {
        
    }
    
    public function register()
    {
        
    }
    
    public function delete()
    {
        
    }
    
    public function index()
    {
        return $this->render('main/index.html.twig');
    }
}
