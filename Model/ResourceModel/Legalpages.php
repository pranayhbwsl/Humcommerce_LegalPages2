<?php

namespace Humcommerce\LegalPages\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Legalpages extends AbstractDb
{
    /**
     * Construct method for legalpages resource model.
     */
    protected function _construct()
    {

        $this->_init('humcommerce_legalpages', 'legalpage_entry_id');
    }
}
