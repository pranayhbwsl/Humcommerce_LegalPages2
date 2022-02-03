<?php

namespace Humcommerce\LegalPages\Model;

use Humcommerce\LegalPages\Model\ResourceModel\Legalpages as LegalPagesResource;

class Legalpages extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Construct method for legalpages model.
     */
    protected function _construct()
    {
        $this->_init(LegalPagesResource::Class);
    }
}
