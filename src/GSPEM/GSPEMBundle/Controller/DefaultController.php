<?php

namespace GSPEM\GSPEMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GSPEM\GSPEMBundle\Entity\Material;
use GSPEM\GSPEMBundle\Entity\StockMaestro;

use GSPEM\GSPEMBundle\Entity\MaterialType;
use GSPEM\GSPEMBundle\Entity\Sitio;
use GSPEM\GSPEMBundle\Entity\SitioType;
use GSPEM\GSPEMBundle\Entity\User;
use GSPEM\GSPEMBundle\Entity\Tarea;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Session;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $user=$this->get('security.token_storage')->getToken()->getUser();
        //var_dump($user->getRoles()[0]);
        if($user->getRoles()[0]!='ROLE_ADMIN'){
            if ($user->getDisabled()){
                $encoders = array(new XmlEncoder(), new JsonEncoder());
                $normalizers = array(new ObjectNormalizer());

                $serializer = new Serializer($normalizers, $encoders);

                return new Response($serializer->serialize(array("access denied"),"json"),200,array('Content-Type'=>'application/json'));
            }
        }
        if ($user->getRoles()[0]!='ROLE_ADMIN'){
            return $this->render('GSPEMGSPEMBundle:Default:index.html.twig',array("user"=>"user"));
        }else {
            return $this->render('GSPEMGSPEMBundle:Default:index.html.twig',array("user"=>"admin"));
        }
    }

    public function loadTwigAction($template)
    {
        return $this->render('GSPEMGSPEMBundle:Default:'.$template.'.html.twig');
    }

    public function getMaterialesAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $stmt = $em->getConnection()->createQueryBuilder()
            ->select("m.id as id ,mt.id as type_id, m.id_custom as idCustom , m.descript as descript ,mt.name  as type , m.name as name")
            ->from("materiales", "m")
            ->leftJoin("m", "materiales_type", "mt", "m.type = mt.id")
            ->orderBy('m.name', 'ASC')
            ->execute();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize($stmt->fetchAll(),"json"),200,array('Content-Type'=>'application/json'));
    }



    
    
    public function getTareasAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $stmt = $em->getConnection()->createQueryBuilder()
            ->select("t.id as id ,t.name as name,t.descript as descript ,t.inicio as inicio ,t.fin as fin , 
            t.nota as nota , t.estado as estado,u.first_name as tecnico ,u.id as tecnico_id , us.first_name as creador,s.name as sitio,s.id as sitio_id")
            ->from("tareas", "t")
            ->leftJoin("t", "sitios", "s", "t.sitio = s.id")
            ->leftJoin("t", "users", "u", "t.tecnico = u.id")
            ->leftJoin("t", "users", "us", "t.creador = us.id")
            ->orderBy('t.inicio', 'ASC')
            ->execute();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return new Response($serializer->serialize($stmt->fetchAll(),"json"),200,array('Content-Type'=>'application/json'));
    }


    public function getTareasByTecAction(){
        $user=$this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();

        $stmt = $em->getConnection()->createQueryBuilder()
            ->select("t.id as id ,t.name as name,t.descript as descript ,t.inicio as inicio ,t.fin as fin , 
            t.nota as nota , t.estado as estado,u.first_name as tecnico ,u.id as tecnico_id , us.first_name as creador,s.name as sitio,s.id as sitio_id")
            ->from("tareas", "t")
            ->leftJoin("t", "sitios", "s", "t.sitio = s.id")
            ->leftJoin("t", "users", "u", "t.tecnico = u.id")
            ->leftJoin("t", "users", "us", "t.creador = us.id")
            ->where("u.id=".$user->getId())
            ->orderBy('t.inicio', 'ASC')
            ->execute();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return new Response($serializer->serialize($stmt->fetchAll(),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function deleteTareaAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\Tarea');
        $repoData = $repo->findOneBy(array("id"=>$request->get("id")));
        $em->remove($repoData);
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function setTareaStateAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\Tarea');
        $repoData = $repo->findOneBy(array("id"=>$request->get("id")));
        $repoData->setEstado($request->get("estado"));

        if($request->get("estado")==2){
            // fin tarea
            $repoData->setFin(new \DateTime());
        }

        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function saveTareaAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $user=$this->get('security.token_storage')->getToken()->getUser();

        if($request->get("id")>0){
            $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\Tarea');
            $tarea = $repo->findOneBy(array("id"=>$request->get("id")));
        }else{
            $tarea = new Tarea();
            $tarea->setInicio(new \DateTime());
        }
        $tarea->setName($request->get("name"));
        $tarea->setCreador($user->getId());
        $tarea->setTecnico($request->get("tecnico"));

        $tarea->setNota($request->get("nota"));
        $tarea->setSitio($request->get("sitio"));
        $tarea->setEstado(0);
        $tarea->setDescript($request->get("descript"));


        if(!$request->get("id")){
            $em->persist($tarea);
        }
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }



    public function getSitiosAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $stmt = $em->getConnection()->createQueryBuilder()
            ->select("s.id as id ,s.name as name , s.direccion,s.latitud as lat , s.longitud as longitud ,st.id as type_id,  s.descript as descript ,st.name  as type ")
            ->from("sitios", "s")
            ->leftJoin("s", "sitios_type", "st", "s.type = st.id")
            ->execute();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return new Response($serializer->serialize($stmt->fetchAll(),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function saveSitiosAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        if($request->get("id")>0){
            $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\Sitio');
            $sitio = $repo->findOneBy(array("id"=>$request->get("id")));
        }else{
            $sitio = new Sitio();
        }

        $sitio->setLat($request->get("lat"));
        $sitio->setName($request->get("name"));

        $sitio->setLong($request->get("long"));
        $sitio->setDescript($request->get("descript"));
        $sitio->setDireccion($request->get("direccion"));

        if(!$request->get("id")){
            $em->persist($sitio);
        }
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }



    public function saveMaterialesAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        if($request->get("id")>0){
            $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\Material');
            $material = $repo->findOneBy(array("id"=>$request->get("id")));
        }else{

            $material = new Material();
            $stock = new StockMaestro();
        }

        $material->setName($request->get("name"));
        $material->setDescript($request->get("descript"));
        $material->setDate(new \DateTime());
        $material->setType($request->get("type"));
        $material->setIdCustom($request->get("id_custom"));

        if(!$request->get("id")){
            $em->persist($material);
        }


        $em->flush();

        // NEW ENTRADA DE STOCK EN 0
        if(!$request->get("id")){
            $stock->setCant(0);// arranca en 0;
            $stock->setMaterial($material->getId());
            $em->persist($stock);
        }

        $em->flush();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function deleteMaterialAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\Material');
        $repoData = $repo->findOneBy(array("id"=>$request->get("id")));
        $em->remove($repoData);
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function deleteSitiosAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\Sitio');
        $repoData = $repo->findOneBy(array("id"=>$request->get("id")));
        $em->remove($repoData);
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function deleteMaterialTypeAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\MaterialType');
        $repoData = $repo->findOneBy(array("id"=>$request->get("id")));
        $em->remove($repoData);
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function deleteSitioTypeAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\SitioType');
        $repoData = $repo->findOneBy(array("id"=>$request->get("id")));
        $em->remove($repoData);
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function deleteUsuarioAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\User');
        $repoData = $repo->findOneBy(array("id"=>$request->get("id")));
        $em->remove($repoData);
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function saveMaterialesTypeAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        if($request->get("id")>0){
            $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\MaterialType');
            $material_type = $repo->findOneBy(array("id"=>$request->get("id")));

        }else{
            $material_type = new MaterialType();
        }
        $material_type->setName($request->get("name"));
        
        $material_type->setDescript($request->get("descript"));

        if(!$request->get("id")){
            $em->persist($material_type);
        }
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function saveSitioTypeAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        if($request->get("id")>0){
            $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\SitioType');
            $sitio_type = $repo->findOneBy(array("id"=>$request->get("id")));
        }else{
            $sitio_type = new SitioType();
        }
        $sitio_type->setName($request->get("name"));
        $sitio_type->setDescript($request->get("descript"));
        if(!$request->get("id")){
            $em->persist($sitio_type);
        }
        $em->flush();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }


    public function setUserAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $factory = $this->get('security.encoder_factory');



        if($request->get("id")>0){
            $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\User');
            $user = $repo->findOneBy(array("id"=>$request->get("id")));
        }else{
            $user = new User();
            $user->setCreatedAt( new \DateTime());
        }


        $user->setModifiedAt(new \DateTime());
        $user->setName($request->get("nombre"));
        $user->setLastName($request->get("apellido"));
        $user->setUsername ($request->get("username"));

        if($request->get('password')!=""){
            $encoder = $factory->getEncoder($user);
            $encodedPasswordToken = $encoder->encodePassword($request->get('password'), $user->getSalt());
            $user->setPassword ($encodedPasswordToken);
        }

        $user->setDisabled(0);
        $user->setPhone($request->get("phone"));
        $user->setMail($request->get("mail"));
        $user->setView($request->get("view"));

        if(!$request->get("id")){
            $em->persist($user);
        }
        $em->flush();

        //falta ver lo de las mesas

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function setUserStateAction(\Symfony\Component\HttpFoundation\Request $request){
        $em = $this->getDoctrine()->getEntityManager();
        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\User');
        $user = $repo->findOneBy(array("id"=>$request->get("id")));


        $user->setDisabled($request->get("state"));

        if(!$request->get("id")){
            $em->persist($user);
        }
        $em->flush();

        //falta ver lo de las mesas

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize(array("process"=>true),"json"),200,array('Content-Type'=>'application/json'));
    }



    public function getMaterialesTypeAction(){
        $em = $this->getDoctrine()->getEntityManager();
        
        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\MaterialType');

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return new Response($serializer->serialize($repo->findAll(),"json"),200,array('Content-Type'=>'application/json'));

    }

    public function  getUsersAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\User');
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize($repo->findAll(),"json"),200,array('Content-Type'=>'application/json'));
    }

    public function  getUserProfileAction(){

        $user=$this->get('security.token_storage')->getToken()->getUser();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        return new Response($serializer->serialize($user,"json"),200,array('Content-Type'=>'application/json'));
    }

    public function getSitiosTypeAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $repo =$em->getRepository('GSPEM\GSPEMBundle\Entity\SitioType');

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return new Response($serializer->serialize($repo->findAll(),"json"),200,array('Content-Type'=>'application/json'));
    }

}
