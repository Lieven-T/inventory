<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Computer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use \DateTime;
use Psr\Log\LoggerInterface;

class MainController extends AbstractController
{
    public function csv()
    {
        $filename = 'inventory.csv';
        $computers = $this->getDoctrine()->getRepository(Computer::class)->findAll();
        
        $fileContent = '"Hostname";"Model";"MAC Adres";"Wifi MAC Adres";"Serienummer";"Processor";"RAM";"SSD/HDD";"Opslagruimte";"OS Versie";"Installatiedatum";"Registratiedatum"';
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
            $fileContent .= $computer->getOsVersion() . '";"';
            $fileContent .= $computer->getInstallDate() ? $computer->getInstallDate()->format('d/m/Y H:i:s') . '";"' : '";"';
            $fileContent .= $computer->getQueryDate()->format('d/m/Y H:i:s') . '"';
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
        if ($macAddress && strlen($macAddress.length)) $queryBuilder->andWhere('c.macAddress like :macAddress')->setParameter('macAddress', $macAddress);
        if ($serialNumber && strlen($macAddress.length)) $queryBuilder->andWhere('c.serialNumber like :serialNumber')->setParameter('serialNumber', $serialNumber);

        $computers = $queryBuilder->getQuery()->getArrayResult();
        return new JsonResponse(array('computers' => $computers));
    }
    
    public function register(LoggerInterface $logger)
    {
        $request = Request::createFromGlobals();
        $hostname = $request->request->get('hostName');
        $macAddress = $request->request->get('macAddress');
        $wifiMacAddress = $request->request->get('wifiMacAddress');
        $model = $request->request->get('model');
        $serialNumber = $request->request->get('serialNumber');
        $mediaType = $request->request->get('mediaType');
        $diskSize = $request->request->get('diskSize');
        $ramSize = $request->request->get('ramSize');
        $processor = $request->request->get('processor');
        $queryDate = $request->request->get('queryDate');
        $installDate = $request->request->get('installDate');
        $osVersion = $request->request->get('osVersion');
        
        $repository = $this->getDoctrine()->getRepository(Computer::class);
        $entityManager = $this->getDoctrine()->getManager();

        $computerByMacAddress = $repository->findOneBy(['macAddress' => $macAddress]);
        $computerByHostName = $repository->findOneBy(['hostname' => $hostname]);
        $currentComputer = null;    

        if(!($computerByMacAddress || $computerByHostName))
        {
            // None exist, create new computer
            $currentComputer = new Computer();
            $entityManager->persist($currentComputer);
        } else {
            // Both exist
            if ($computerByMacAddress && $computerByHostName && $computerByMacAddress->getMacAddress()) {
                // If a computer with the known MAC-address appears with a different hostname, assume it's removed
                if ($computerByMacAddress->getId() != $computerByHostName->getId()) $entityManager->remove($computerByHostName);
                $currentComputer = $computerByMacAddress;
            } else {
               // If only one of two is found, use that one
               $currentComputer = $computerByHostName ?: $computerByMacAddress;
            }
        }
        
        $logger->error($hostname . " has date " . $installDate);

        $currentComputer->setHostName($hostname);
        $currentComputer->setMacAddress($macAddress);
        $currentComputer->setWifiMacAddress($wifiMacAddress);
        $currentComputer->setSerialNumber($serialNumber);
        $currentComputer->setRamSize((int)$ramSize);
        $currentComputer->setMediaType($mediaType);
        $currentComputer->setDiskSize((int)$diskSize);
        $currentComputer->setModel($model);
        $currentComputer->setQueryDate(new DateTime());
        $currentComputer->setInstallDate(DateTime::createFromFormat('d/m/Y H:i:s', $installDate));
        $currentComputer->setProcessor($processor);
        $currentComputer->setOsVersion($osVersion);
        
        $entityManager->flush();        

        $response = new Response();
        $response->setContent('<html><body><h1>OK!</h1></body></html>');
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        return $response->send();
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
