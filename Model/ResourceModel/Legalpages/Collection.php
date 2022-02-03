<?php

namespace Humcommerce\LegalPages\Model\ResourceModel\Legalpages;

use Humcommerce\LegalPages\Model\Legalpages;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Humcommerce\LegalPages\Model\ResourceModel\Legalpages as LegalPagesResource;

class Collection extends AbstractCollection
{
    /**
     * Costruct for collection.
     */
    protected function _construct()
    {

        $this->_init(
            Legalpages::Class,
            LegalPagesResource::Class
        );
    }
}
