<?php

namespace Humcommerce\LegalPages\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Humcommerce\LegalPages\Helper\Data;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DefaultLegalData implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Humcommerce\LegalPages\Helper\Data
     */
    protected $_helperData;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $configWriter
     * @param Data $helperData
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter,
        Data $helperData
    ) {

        $this->moduleDataSetup = $moduleDataSetup;

        $this->configWriter = $configWriter;
        $this->_helperData = $helperData;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $default_name = $this->_helperData->getConfigValue('general/store_information/name');
        $default_phone = $this->_helperData->getConfigValue('general/store_information/phone');
        $default_email = $this->_helperData->getConfigValue('trans_email/ident_general/email');
        $default_country = $this->_helperData->getConfigValue('general/store_information/country_id');
        $default_region = $this->_helperData->getConfigValue('general/store_information/region_id');
        $default_postcode = $this->_helperData->getConfigValue('general/store_information/postcode');
        $default_city = $this->_helperData->getConfigValue('general/store_information/city');
        $default_streetLine1 = $this->_helperData->getConfigValue('general/store_information/street_line1');
        $default_streetLine2 = $this->_helperData->getConfigValue('general/store_information/street_line2');
        $default_baseUrl = $this->_helperData->getConfigValue('web/unsecure/base_url');

        $domain_name = $default_baseUrl;
        $business_name = $default_name;
        $phone = $default_phone;
        $email = $default_email;
        $street = $default_streetLine1 . ' ' . $default_streetLine2;
        $city_state = $default_city . ', Zip/Post Code - ' . $default_postcode;
        $country = $default_country;
        $address = $default_streetLine1 . ' ' . $default_streetLine2;

        $this->configWriter->save(
            'humc_lp/humc_lp_general/humc_lp_general_domain_name',
            $domain_name,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->configWriter->save(
            'humc_lp/humc_lp_general/humc_lp_general_business_name',
            $business_name,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->configWriter->save(
            'humc_lp/humc_lp_general/humc_lp_general_phone',
            $phone,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->configWriter->save(
            'humc_lp/humc_lp_general/humc_lp_general_street',
            $street,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->configWriter->save(
            'humc_lp/humc_lp_general/humc_lp_general_city_state_zip',
            $city_state,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->configWriter->save(
            'humc_lp/humc_lp_general/humc_lp_general_email',
            $email,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->configWriter->save(
            'humc_lp/humc_lp_general/humc_lp_general_address',
            $address,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->configWriter->save(
            'humc_lp/humc_lp_general/humc_lp_general_country',
            $country,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get dependencies for the data patch.
     */
    public static function getDependencies()
    {

        return [
        ];
    }

    /**
     * Revert operations from apply.
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get aliases
     */
    public function getAliases()
    {
        return [];
    }
}
