<?php

/**
 * This file is part of the Jumph package.
 *
 * (c) Peter Nijssen
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jumph\Bundle\ClientBundle\Controller;

use Jumph\Bundle\ClientBundle\ClientEvents;
use Jumph\Bundle\ClientBundle\Entity\Company;
use Jumph\Bundle\ClientBundle\Form\Type\CompanyType;
use Jumph\Bundle\ClientBundle\Form\Filter\CompanyFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CompanyController extends Controller
{

    /**
     * @Template("JumphClientBundle:Company:overview.html.twig")
     *
     * Company overview page
     *
     * @param Request $request A Request instance
     *
     * @return Response A Response instance
     */
    public function overviewAction(Request $request)
    {
        $filterForm = $this->createForm(new CompanyFilterType());
        $filterForm->handleRequest($request);

        $filter = $this->get('jumph_client.company_filter');

        return array(
            'filterForm' => $filterForm->createView(),
            'companies' => $filter->getPaginatedResults(
                $filterForm,
                $request->query->get('page', 1),
                15
            )
        );
    }

    /**
     * @Template("JumphClientBundle:Company:view.html.twig")
     * @ParamConverter("company", class="JumphClientBundle:Company", options={"id" = "companyId"})
     *
     * View company
     *
     * @param Company $company
     *
     * @return Response A Response instance
     */
    public function viewAction(Company $company)
    {
        return array(
            'company' => $company
        );
    }

    /**
     * @Template("JumphClientBundle:Company:form.html.twig")
     *
     * Add company
     *
     * @param Request $request A Request instance
     *
     * @return Response A Response instance
     */
    public function addAction(Request $request)
    {
        $company = new Company();
        $companyForm = $this->createForm(new CompanyType(), $company);

        if ($request->isMethod('POST')) {
            $companyForm->handleRequest($request);
            if ($companyForm->isValid()) {
                $companyManager = $this->get('jumph_client.company_manager');
                $companyManager->create($company);

                $dispatcher = $this->container->get('event_dispatcher');
                $dispatcher->dispatch(ClientEvents::CREATE_COMPANY, new Event());

                $alertMessage = $this->get('jumph_app.alert_message');
                $alertMessage->success('Company created!');

                return $this->redirect($this->generateUrl('jumph_client_company_overview'));
            }
        }

        return array(
            'companyForm' => $companyForm->createView()
        );
    }

    /**
     * @Template("JumphClientBundle:Company:form.html.twig")
     * @ParamConverter("company", class="JumphClientBundle:Company", options={"id" = "companyId"})
     *
     * Edit company
     *
     * @param Request $request A Request instance
     * @param Company $company
     *
     * @return Response A Response instance
     */
    public function editAction(Request $request, Company $company)
    {
        $companyForm = $this->createForm(new CompanyType(), $company);

        if ($request->isMethod('POST')) {
            $companyForm->handleRequest($request);
            if ($companyForm->isValid()) {
                $companyManager = $this->get('jumph_client.company_manager');
                $companyManager->update($company);

                $dispatcher = $this->container->get('event_dispatcher');
                $dispatcher->dispatch(ClientEvents::UPDATE_COMPANY, new Event());

                $alertMessage = $this->get('jumph_app.alert_message');
                $alertMessage->success('Company updated!');

                return $this->redirect($this->generateUrl('jumph_client_company_overview'));
            }
        }

        return array(
            'companyForm' => $companyForm->createView()
        );
    }

    /**
     * @ParamConverter("company", class="JumphClientBundle:Company", options={"id" = "companyId"})
     *
     * Delete company
     *
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse A Response instance
     */
    public function deleteAction(Company $company)
    {
        $companyManager = $this->get('jumph_client.company_manager');
        $companyManager->delete($company);

        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(ClientEvents::DELETE_COMPANY, new Event());

        $alertMessage = $this->get('jumph_app.alert_message');
        $alertMessage->success('Company deleted!');

        return $this->redirect($this->generateUrl('jumph_client_company_overview'));
    }
}
