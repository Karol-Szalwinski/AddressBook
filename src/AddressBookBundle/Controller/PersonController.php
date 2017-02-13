<?php

namespace AddressBookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

use AddressBookBundle\Entity\Address;
use AddressBookBundle\Entity\Person;
use AddressBookBundle\Entity\Email;
use AddressBookBundle\Entity\Phone;

class PersonController extends Controller {

    /**
     * @Route("/")
     */
    public function showIndexAction() {
        $repo = $this->getDoctrine()->getRepository("AddressBookBundle:Person");
        $persons = $repo->findOrderedByName();

        return $this->render('AddressBookBundle:Person:show_index.html.twig', ["persons" => $persons]);
    }

    /**
     * @Route("/{id}", requirements={"id" : "\d+"})
     */
    public function showPersonAction($id) {
        $repo = $this->getDoctrine()->getRepository("AddressBookBundle:Person");
        $person = $repo->find($id);
        if ($person == null) {
            throw $this->createNotFoundException();
        }
        return $this->render('AddressBookBundle:Person:show_person.html.twig', ["person" => $person]);
    }

    /**
     * @Route("/new")
     * @Method("GET")
     */
    public function newPersonAction() {
        $person = new Person();
        $action = $this->generateUrl('addressbook_person_newperson');
        $formPerson = $this->generatePersonForm($person, $action);

        return $this->render('AddressBookBundle:Person:new.html.twig', [
                    "formPerson" => $formPerson->createView(),
        ]);
    }

    /**
     * @Route("/new")
     * @Method("POST")
     */
    public function createPersonAction(Request $request) {
        $person = new Person();
        $form = $this->generatePersonForm($person, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $person = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();
            return $this->redirectToRoute('addressbook_person_showperson', [
                        "id" => $person->getId(),
            ]);
        }


        return $this->redirectToRoute('addressbook_person_newperson');
    }

    private function generatePersonForm($person, $action) {
        $form = $this->createFormBuilder($person)
                ->setAction($action)
                ->add('name', 'text')
                ->add('surname', 'text')
                ->add('description', 'text')
                ->add('save', 'submit', array('label' => 'Dodaj osobę'))
                ->getForm();
        return $form;
    }

    /**
     * @Route("/{id}/modify" , requirements={"id" : "\d+"})
     */
    public function modifyAction(Request $request, $id) {
        $personRepo = $this->getDoctrine()->getRepository("AddressBookBundle:Person");
        $person = $personRepo->find($id);
        $form = $this->generatePersonForm($person, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $request->getMethod() == 'POST' && $form->isValid()) {
            $person = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();
            return $this->redirectToRoute('addressbook_person_showperson', [
                        "id" => $id,
            ]);
        }

        $address = new Address();
        $action = $this->generateUrl('addressbook_person_createaddress', ["id" => $id]);
        $formAddress = $this->generateAddressForm($address, $action);

        $email = new Email();
        $action = $this->generateUrl('addressbook_person_createemail', ["id" => $id]);
        $formEmail = $this->generateEmailForm($email, $action);

        $phone = new Phone();
        $action = $this->generateUrl('addressbook_person_createphone', ["id" => $id]);
        $formPhone = $this->generatePhoneForm($phone, $action);

        return $this->render('AddressBookBundle:Person:new.html.twig', [
                    "formPerson" => $form->createView(),
                    "formAddress" => $formAddress->createView(),
                    "formEmail" => $formEmail->createView(),
                    "formPhone" => $formPhone->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete" , requirements={"id" : "\d+"})
     */
    public function deleteAction($id) {
        $personRepo = $this->getDoctrine()->getRepository("AddressBookBundle:Person");
        $em = $this->getDoctrine()->getManager();
        $personToDelete = $personRepo->find($id);
        if ($personToDelete != null) {
            $em->remove($personToDelete);
            $em->flush();
        }
        return $this->redirectToRoute("addressbook_person_showindex");
    }

    /**
     * @Route("{id}/addAddress", requirements={"id" : "\d+"})
     * @Method("POST")
     */
    public function createAddressAction(Request $request, $id) {

        $address = new Address();
        $form = $this->generateAddressForm($address, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $repo = $this->getDoctrine()->getRepository("AddressBookBundle:Person");
            $person = $repo->find($id);

            $address = $form->getData();
            $address->setPerson($person);

            $person->addAddress($address);

            $em = $this->getDoctrine()->getManager();
            $em->persist($address);
            $em->flush();
        }
        return $this->redirectToRoute('addressbook_person_modify', ["id" => $id]);
    }

    /**
     * @Route("{id}/addEmail", requirements={"id" : "\d+"})
     * @Method("POST")
     */
    public function createEmailAction(Request $request, $id) {

        $email = new Email();
        $form = $this->generateEmailForm($email, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $repo = $this->getDoctrine()->getRepository("AddressBookBundle:Person");
            $person = $repo->find($id);

            $email = $form->getData();
            $email->setPerson($person);

            $person->addEmail($email);

            $em = $this->getDoctrine()->getManager();
            $em->persist($email);
            $em->flush();
        }
        return $this->redirectToRoute('addressbook_person_modify', ["id" => $id]);
    }

    /**
     * @Route("{id}/addPhone", requirements={"id" : "\d+"})
     * @Method("POST")
     */
    public function createPhoneAction(Request $request, $id) {

        $phone = new Phone();
        $form = $this->generatePhoneForm($phone, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $repo = $this->getDoctrine()->getRepository("AddressBookBundle:Person");
            $person = $repo->find($id);

            $phone = $form->getData();
            $phone->setPerson($person);

            $person->addPhone($phone);

            $em = $this->getDoctrine()->getManager();
            $em->persist($phone);
            $em->flush();
        }
        return $this->redirectToRoute('addressbook_person_modify', ["id" => $id]);
    }

    private function generateAddressForm($address, $action) {
        $form = $this->createFormBuilder($address)
                ->setAction($action)
                ->add('city', 'text')
                ->add('street', 'text')
                ->add('homeNumber', 'text')
                ->add('localNumber', 'text')
                ->add('save', 'submit', array('label' => 'Dodaj adres'))
                ->getForm();
        return $form;
    }

    private function generateEmailForm($email, $action) {
        $form = $this->createFormBuilder($email)
                ->setAction($action)
                ->add('address', 'text')
                ->add('type', 'text')
                ->add('save', 'submit', array('label' => 'Dodaj email'))
                ->getForm();
        return $form;
    }

    private function generatePhoneForm($phone, $action) {
        $form = $this->createFormBuilder($phone)
                ->setAction($action)
                ->add('number', 'text')
                ->add('type', 'text')
                ->add('save', 'submit', array('label' => 'Dodaj telefon'))
                ->getForm();
        return $form;
    }

}
