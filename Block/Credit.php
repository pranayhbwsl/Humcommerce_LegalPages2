<?php
namespace Humcommerce\LegalPages\Block;

use Magento\Framework\View\Page\Title;

class Credit extends \Magento\Framework\View\Element\Template
{
    /**
     * Returns current page title.
     *
     * @return String
     */
    public function getPageTitle()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $title = $objectManager->get(Title::Class);
        return $title->getShort();
    }
}
