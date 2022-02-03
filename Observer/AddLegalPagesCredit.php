<?php
namespace Humcommerce\LegalPages\Observer;

use Humcommerce\LegalPages\Helper\Data;

class AddLegalPagesCredit implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Humcommerce\LegalPages\Helper\Data
     */
    protected $_helperData;

    /**
     * Construct method for observer.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->_helperData = $helperData;
    }

    /**
     * Execute observer.
     *
     * @param Object $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $action = $observer->getData('full_action_name');

        $giveCredit = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_give_credit');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $page = $objectManager->get(\Magento\Cms\Model\Page::Class);

        $layout = $observer->getData('layout');

        $pageIdentifier = $page->getIdentifier();

        $giveCredit = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_give_credit');

        if (! $giveCredit || $action !== 'cms_page_view' || false === strpos($pageIdentifier, 'legal-')) {
            $layout->unsetElement('legalpages_give_credit');
        }
    }
}
