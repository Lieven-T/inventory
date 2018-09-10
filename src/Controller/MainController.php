<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Computer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MainController extends AbstractController
{
    public function csv()
    {
        $filename = 'inventory.csv';
        $computers = $this->getDoctrine()->getRepository(Computer::class)->findAll();
        
        $fileContent = '"Hostname";"Model";"MAC Adres";"Serienummer";"Processor";"RAM";"SSD/HDD";"Opslagruimte"';
        $fileContent .= "\n";
        foreach ($computers as $computer)
        {
            $fileContent .= '"' . $computer->getHostName() . '";"';
            $fileContent .= $computer->getModel() . '";"';
            $fileContent .= $computer->getMacAddress() . '";"';
            $fileContent .= $computer->getSerialNumber() . '";"';
            $fileContent .= $computer->getProcessor() . '";"';
            $fileContent .= $computer->getRamSize() . '";"';
            $fileContent .= $computer->getMediaType() . '";"';
            $fileContent .= $computer->getDiskSize() . '"';
            $fileContent .= "\n";
        }

        // Return a response with a specific content
        $response = new Response($fileContent);

        // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        // Set the content disposition
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/csv');

        // Dispatch request
        return $response;
    }
    
    public function register()
    {
        $request = Request::createFromGlobals();
        $hostname = $request->request->get('hostName');
        $macAddress = $request->request->get('macAddress');
        $model = $request->request->get('model');
        $serialNumber = $request->request->get('serialNumber');
        $mediaType = $request->request->get('mediaType');
        $diskSize = $request->request->get('diskSize');
        $ramSize = $request->request->get('ramSize');
        $processor = $request->request->get('processor');
        
        $repository = $this->getDoctrine()->getRepository(Computer::class);
        $entityManager = $this->getDoctrine()->getManager();

        $computerByMacAddress = $repository->findOneBy(['macAddress' => $macAddress]);
        $computerByHostName = $repository->findOneBy(['hostname' => $hostname]);
        $currentComputer = null;    

        if(!($computerByMacAddress || $computerByHostName))
        {
            $currentComputer = new Computer();
            $entityManager->persist($currentComputer);
        } else {
            if ($computerByMacAddress && $computerByHostName) {
                if ($computerByMacAddress->getId() != $computerByHostName->getId())
                    $entityManager->remove($computerByHostName);
                $currentComputer = $computerByMacAddress;
            } else {
               $currentComputer = $computerByHostName ?: $computerByMacAddress;
            }
        }

        $currentComputer->setHostName($hostname);
        $currentComputer->setMacAddress($macAddress);
        $currentComputer->setSerialNumber($serialNumber);
        $currentComputer->setRamSize((int)$ramSize);
        $currentComputer->setMediaType($mediaType);
        $currentComputer->setDiskSize((int)$diskSize);
        $currentComputer->setModel($model);
        // $currentComputer.setQueryDate($queryDate);
        $currentComputer->setProcessor($processor);

        $entityManager->flush();        

        $response = new Response();
        $response->setContent('<html><body><h1>OK!</h1></body></html>');
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->send();
    }
    
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $computer = $entityManager->getRepository(Computer::class)->find($id);
        $entityManager->remove($computer);
        $entityManager->flush();
    }
    
    public function index()
    {
        $computers = $this->getDoctrine()->getRepository(Computer::class)->findAll();
        return $this->render('main/index.html.twig', ['computers' => $computers]);
    }
}
