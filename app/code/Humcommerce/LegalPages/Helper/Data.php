<?php

namespace Humcommerce\LegalPages\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    /**
     * Return config value
     *
     * @param string $field
     * @param string $storeId
     *
     * @return String config value
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
