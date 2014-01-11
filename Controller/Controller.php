<?php

namespace Morki\BounceBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;

class Controller extends ContainerAware
{
    public function has($id)
    {
        return $this->container->has($id);
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function getParameter($id)
    {
        return $this->container->getParameter($id);
    }

    public function persist($entity, $flush = false)
    {
        $this->getEntityManager()->persist($entity);
        
        if (true === $flush)
            $this->getEntityManager()->flush($entity);
    }

    public function remove($entity)
    {
        $this->getEntityManager()->remove($entity);
    }

    public function flush($entity = null)
    {
        $this->getEntityManager()->flush($entity);
    }

    public function translate($message)
    {
        return $this->get('translator')->trans($message);
    }

    public function jsonResponse($data)
    {
        return new JsonResponse($data);
    }

    public function referer()
    {
        $referer = $this->getRequest()->headers->get('referer');
        return $this->redirectUrl($referer);
    }

    public function addFlash($type, $message)
    {
        $this->getSession()->getFlashBag()->add($type, $message);
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

    public function redirect($route, array $parameters = array())
    {
        return $this->redirectUrl($this->generateUrl($route, $parameters));
    }

    public function reload(array $additional = array())
    {
        $route = $this->getRequest()->get('_route');
        $parameters = $this->getRequest()->get('_route_params');
        $query = $this->getRequest()->query->all();

        return $this->redirect($route, array_merge($parameters, $query, $additional));
    }

    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->container->get('form.factory')->createBuilder('form', $data, $options);
    }

    public function handleForm($type, $data = null, array $options = array())
    {
        $form = $this->createForm($type, $data, $options);
        $form->handleRequest($this->getRequest());

        return $form;
    }

    public function submitForm($form)
    {
        if ($form instanceof FormBuilderInterface)
            $form = $form->getForm();

        $form->handleRequest($this->getRequest());

        return $form;
    }

    public function createForm($type, $data = null, array $options = array())
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    public function createQuery()
    {
        return $this->get('doctrine.orm.entity_manager')->createQuery();
    }

    public function createQueryBuilder()
    {
        return $this->get('doctrine.orm.entity_manager')->createQueryBuilder();
    }

    public function isXHR()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }

    public function getRequest()
    {
        return $this->container->get('request');
    }
    
    public function getSession()
    {
        return $this->container->get('session');
    }

    public function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    public function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    public function getRepository($entity)
    {
        return $this->container->get('doctrine.orm.entity_manager')->getRepository($entity);
    }

    public function getUser()
    {
        if (null === $token = $this->container->get('security.context')->getToken())
            return null;

        if (!is_object($user = $token->getUser()))
            return null;

        return $user;
    }

    public function isGranted($attribute, $object = null)
    {
        return $this->container->get('security.context')->isGranted($attribute, $object);
    }

    public function redirectUrl($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    public function forward($controller, array $path = array(), array $query = array())
    {
        $path['_controller'] = $controller;
        $subRequest = $this->container->get('request')->duplicate($query, null, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    public function renderView($view, array $parameters = array())
    {
        return $this->container->get('templating')->render($view, $parameters);
    }

    public function stream($view, array $parameters = array(), StreamedResponse $response = null)
    {
        $templating = $this->container->get('templating');

        $callback = function () use ($templating, $view, $parameters) {
            $templating->stream($view, $parameters);
        };

        if (null === $response) {
            return new StreamedResponse($callback);
        }

        $response->setCallback($callback);

        return $response;
    }
}