<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Computer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use \DateTime;
use \DateTimeZone;
use Psr\Log\LoggerInterface;

class MainController extends AbstractController
{
    public function csv()
    {
        $filename = 'inventory.csv';
        $computers = $this->getDoctrine()->getRepository(Computer::class)->findAll();
        
        $fileContent = '"Hostname";"Model";"MAC Adres";"Wifi MAC Adres";"Serienummer";"Processor";"RAM";"SSD/HDD";"Opslagruimte";"Vrije ruimte";"OS Versie";"Installatiedatum";"Registratiedatum";"AutoPilot Hash"';
        $fileContent .= "\n";
        foreach ($computers as $computer)
        {
            $fileContent .= '"' . $computer->getHostName() . '";"';
            $fileContent .= $computer->getModel() . '";"';
            $fileContent .= $computer->getMacAddress() . '";"';
            $fileContent .= $computer->getWifiMacAddress() . '";"';
            $fileContent .= $computer->getSerialNumber() . '";"';
            $fileContent .= $computer->getProcessor() . '";"';
            $fileContent .= $computer->getRamSize() . '";"';
            $fileContent .= $computer->getMediaType() . '";"';
            $fileContent .= $computer->getDiskSize() . '";"';
            $fileContent .= $computer->getFreeSpace() . '";"';
            $fileContent .= $computer->getOsVersion() . '";"';
            $fileContent .= $computer->getInstallDate() ? $computer->getInstallDate()->format('d/m/Y H:i:s') . '";"' : '";"';
            $fileContent .= $computer->getQueryDate()->format('d/m/Y H:i:s') . '";"';
            $fileContent .= $computer->getAutoPilotHash() . '"';
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
    
   public function query(Request $request)
    {
        $hostname = $request->query->get('hostName');
        $macAddress = $request->query->get('macAddress');
        $serialNumber = $request->query->get('serialNumber');

        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('c')->from(Computer::class, 'c');
        if ($hostname && strlen($hostname)) $queryBuilder->andWhere('c.hostname like :hostname')->setParameter('hostname', $hostname);
        if ($macAddress && strlen($macAddress)) $queryBuilder->andWhere('c.macAddress like :macAddress')->setParameter('macAddress', $macAddress);
        if ($serialNumber && strlen($serialNumber)) $queryBuilder->andWhere('c.serialNumber like :serialNumber')->setParameter('serialNumber', $serialNumber);

        $computers = $queryBuilder->getQuery()->getArrayResult();
        return new JsonResponse(array('computers' => $computers));
    }
        
    public function delete()
    {
        $request = Request::createFromGlobals();
        $id = $request->request->get('id');

        $entityManager = $this->getDoctrine()->getManager();
        $computer = $entityManager->getRepository(Computer::class)->find($id);
        $entityManager->remove($computer);
        $entityManager->flush();
        
        return $this->redirectToRoute('index');
    }
    
    public function index(LoggerInterface $logger)
    {
        $computers = $this->getDoctrine()->getRepository(Computer::class)->findAll();
        return $this->render('main/index.html.twig', ['computers' => $computers]);
    }
}
