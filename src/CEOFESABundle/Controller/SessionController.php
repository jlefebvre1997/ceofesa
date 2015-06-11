<?php

namespace CEOFESABundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CEOFESABundle\Repository\StructureRepository;


/**
 * Session controller.
 *
 * @Route("/session")
 */
class SessionController extends Controller
{
	/**
    * @Route(
    * 	path="/",
    * 	name="session_index"
    * )
    * @Method({"GET"})
    * @Template("::Session\index.html.twig")
    */
    public function indexAction(Request $request)
    {
       	$form = $this->createChooseForm();

        return array(
            'choose_form' => $form->createView(),
        );
    }

   /**
    * @Route(
    * 	path="/list",
    * 	name="session_list"
    * )
    * @Method({"POST"})
    * @Template("::Session\index.html.twig")
    */
    public function listAction(Request $request)
    {
    	$form = $this->createChooseForm();

    	$form->handleRequest($request);
        // data is an array
        $data = $form->getData();
        $module = $data['module'];
        $modType = $data['type'];
        $of = $data['of'];
        $id = $this->get('session')->get('structure');

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('CEOFESABundle:Session')->getSessions($module->getModId(),$modType->getMtyId(),$of->getStrId(),$id)->getQuery()->getResult();

		return array(
		    'choose_form' => $form->createView(),
		    'entities' => $entities,
		    'module' => $module,
		    'type' => $modType,
		    'of' => $of,
		);
	}

    /**
     * Création d'un formulaire pour choisir les "paramètres" des sessions à afficher
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createChooseForm()
    {
        $id = $this->get('session')->get('structure');
        $em = $this->getDoctrine()->getManager();

        $data = array();
        $formBuilder = $this->createFormBuilder($data)
            ->add('module','entity',array(
                'class' => 'CEOFESABundle\Entity\Module',
                'property' => 'modCode',
                'multiple' => false,
            ))
            ->add('type','entity',array(
                'class' => 'CEOFESABundle\Entity\ModuleT',
                'property' => 'mtyType',
                'multiple' => false,
            ))
            ->add('of','choice',array(
                'required'  => true,
                'multiple'  => false,
                'empty_value' => '',
            ))
            ->add('voir','submit', array(
                'attr' => array('class' => 'btn-primary')
            ))
        ;
        $formBuilder
        	->setAction($this->generateUrl('session_list'))
			->setMethod('POST')
		;

        $formBuilder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
    		$formulaire = $event->getForm();
    		$data = $event->getData();

    		$ofId = $data['of'];
        	if($ofId != null){ 
            $formulaire->remove('of');
            $formulaire->add('of','entity',array(
	                'class' => 'CEOFESABundle\Entity\Structure',
	                'property' => 'strNom',
	                'label' => 'OF',
	                'multiple' => false,
	                'query_builder' => function(StructureRepository $repo) use ($ofId) {
	                    return $repo->getStructure($ofId);
	                }
	            ));
	        }

        });

        $form = $formBuilder->getForm();

        return $form;
    }

    /**
     * Traitement backoffice de l'AJAX.
     * 
     * @Route("/ajax", name="session_ajax")
     *
     */
    public function sessionAjaxAction(Request $request){

        $moduleId = $request->request->get('module_id');
        $typeId = $request->request->get('type_id');
        $id = $this->get('session')->get('structure');
        $em = $this->getDoctrine()->getManager();

        $rep = $em->getRepository('CEOFESABundle:Session')->getOFs($moduleId, $typeId, $id);

        $structures = $rep->getQuery()->getResult();

        $OFList = array();

        foreach ($structures as $structure) {
            $p = array();
            $p['id'] = $structure->getSesOf()->getStrId();
            $p['nom'] = $structure->getSesOf()->getStrNom();
            $OFList[] = $p;
        }

        return new JsonResponse($OFList);
    }
}