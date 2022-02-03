<?php

namespace Humcommerce\LegalPages\Controller\Adminhtml\Result;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;

use Magento\Framework\Message\ManagerInterface;

use Magento\Store\Model\StoreRepository;

use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;

use Magento\Framework\Serialize\SerializerInterface;

use Humcommerce\LegalPages\Helper\Data;

class Result extends \Magento\Framework\App\Action\Action
{

     /**
      * @var Magento\Framework\View\Result\PageFactory
      */
    protected $resultPageFactory;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;
    
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Humcommerce\LegalPages\Model\LegalpagesFactory
     */
    protected $legalpages;

    /**
     * @var Humcommerce\LegalPages\Model\ResourceModel\Legalpages\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var Magento\Store\Model\StoreRepository
     */
    protected $store_repository;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $store_manager;

    /**
     * @var Humcommerce\LegalPages\Helper\Data
     */
    protected $_helperData;

    /**
     * @var Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Construct method for result controller.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param ManagerInterface $messageManager
     * @param \Hummingbird\Legalpages\Model\LegalpagesFactory $legalpages
     * @param \Hummingbird\Legalpages\Model\ResourceModel\Legalpages\CollectionFactory $collectionFactory
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param StoreRepository $store_repository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Data $helperData
     * @param UrlInterface $backendUrl
     * @param WriterInterface $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        ManagerInterface $messageManager,
        \Humcommerce\LegalPages\Model\LegalpagesFactory $legalpages,
        \Humcommerce\LegalPages\Model\ResourceModel\Legalpages\CollectionFactory $collectionFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        StoreRepository $store_repository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $helperData,
        UrlInterface $backendUrl,
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        SerializerInterface $serializer
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->messageManager = $messageManager;

        $this->collectionFactory = $collectionFactory;

        $this->legalpages = $legalpages;

        $this->pageFactory = $pageFactory;

        $this->store_repository = $store_repository;
        $this->store_manager = $storeManager;

        $this->_helperData = $helperData;

        $this->backendUrl = $backendUrl;
        $this->configWriter = $configWriter;
        
        $this->cacheTypeList = $cacheTypeList;
        $this->serializer = $serializer;

        return parent::__construct($context);
    }

    /**
     * Exwcute ajax requests.
     *
     * @return Object $result
     */
    public function execute()
    {
        $step = $this->getRequest()->getParam('step');
        $page = $this->getRequest()->getParam('page');
        $action = $this->getRequest()->getParam('action');

        $result = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        
        switch ($action) {

            case 'getting_started':
                $templateData = $this->legalpagesGetTemplate();
                $result->setData(['data' => $templateData]);
                break;
                
            case 'get_policy_settings':
                $collection = $this->collectionFactory->create();
                $policy_config_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    $page . '_config'
                )->getData();
                $config = $this->serializer->unserialize($policy_config_entry['0']['legalpage_entry_value']);
        
                $selected_store = $config['lp-store'];

                $legal_pages_page_sections = '';

                $model = $this->legalpages->create();
                $collection = $this->collectionFactory->create();
                $selected_store = $config['lp-store'];
                $policy_settings_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    ['eq' => $page. '_settings_' . $selected_store]
                )->getData();
                if (! $policy_settings_entry) {

                    $legal_pages_page_sections = $this->legalpagesGetPageSections($page);
                    $data = [
                        'legalpage_entry_type'=> $page . '_settings_' . $selected_store,
                        'legalpage_entry_value'=> $this->serializer->serialize($legal_pages_page_sections)
                    ];
                    $model->setData($data);
                    $model->save();
                } else {
                    $legal_pages_page_sections = $this->serializer->unserialize(
                        $policy_settings_entry['0']['legalpage_entry_value']
                    );
                    $legal_pages_page_sections = $this->convertDataToAPiFormat($legal_pages_page_sections);
                }
        
                $result->setData(['data' => $legal_pages_page_sections]);
                break;
            case 'page_sections_save':
                $data = $this->getRequest()->getParam('data');

                $this->legalpagesPageSectionsSave($page, $data);
                $result->setData(['success' => true]);

                break;

            case 'page_preview':
                $page_preview = $this->getPagePreview($page);
                $collection = $this->collectionFactory->create();
                $policy_config_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    $page . '_config'
                )->getData();
                $config = $this->serializer->unserialize($policy_config_entry['0']['legalpage_entry_value']);
        
                $selected_store = $config['lp-store'];

                $model = $this->legalpages->create();
                $collection = $this->collectionFactory->create();
                $policy_preview_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    ['eq' => $page . '_preview_' . $selected_store]
                )->getData();
                if (! $policy_preview_entry) {
                    $data = [
                        'legalpage_entry_type'=>$page . '_preview_' . $selected_store,
                        'legalpage_entry_value'=> $page_preview
                    ];
                    $model->setData($data);
                    $model->save();
                } else {
                    $legalpage_entry_id = $policy_preview_entry['0']['legalpage_entry_id'];

                    $model->load($legalpage_entry_id);
                    $data = [
                        'legalpage_entry_type'=>$page . '_preview_' . $selected_store,
                        'legalpage_entry_value'=> $page_preview
                    ];
                    $model->addData($data);
                    $model->save();
                }
                $result->setData(['data' => $page_preview]);
                break;
            
            case 'page_preview_save':
                $pageName = str_replace('_', '-', $page);

                $this->pagePreviewSave($page);
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::Class);
                $collection = $this->collectionFactory->create();
                $policy_config_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    $page . '_config'
                )->getData();
                $config = $this->serializer->unserialize($policy_config_entry['0']['legalpage_entry_value']);
        
                $selected_store = $config['lp-store'];
                $pageIdentifier = str_replace('_', '-', $page);
                $legalpageUrl = $this->backendUrl->getUrl('cms/page/index');
                $pageIdentifier = 'legal-' . $pageIdentifier . '-page-' . $selected_store;
                $RedirectionUrl = $storeManager->getStore()->getBaseUrl() . $pageIdentifier;

                $result->setData([
                    'url' => $RedirectionUrl,
                    'legalpageUrl' => $legalpageUrl
                ]);
                break;
            case 'page_settings':
                $response = $this->legalpagesGetSettings($page);
                $result->setData(['data' => $response]);

                break;
            case 'page_settings_save':
                $data = $this->getRequest()->getParam('data');

                $this->legalpagesSettingsSave($page, $data);
                $result->setData(['success' => true]);

                break;
        }
        return $result;
    }

    /**
     * Do API request with provided params
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return Response
     */
    public function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ) {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => 'https://api.wpeka.com/wp-json/wplegal/v2/'
        ]]);

        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }

    /**
     * Returns page sections.
     *
     * @param string $page
     *
     * @return Array $lp_section_fields
     */
    public function legalpagesGetPageSections($page)
    {
        $lp_sections = [];

        switch ($page) {
            case 'privacy_policy':
                    $response = $this->doRequest('get_privacy_settings');
                    
                    $responseBody = $response->getBody();
                    $responseContent = $responseBody->getContents();
                    $lp_sections = (array) json_decode($responseContent);
                break;
            case 'terms_of_use':
                $response = $this->doRequest('get_clauses');
                    
                $responseBody = $response->getBody();
                $responseContent = $responseBody->getContents();
                $lp_sections = (array) json_decode($responseContent);
                break;
        }

        foreach ($lp_sections as $key => $lp_section) {
            if ('terms_of_use' === $page) {
                if (empty($lp_section->fields)) {
                    $lp_section->fields = json_decode(
                        $this->doRequest('get_clause_settings?clause=' . $key)->getBody()->getContents()
                    );
                }
            }
            if ('privacy_policy' === $page) {

                //Remove cookie audit code from api retrieved data.
                $fields_sequence = [
                    'fields',
                    'enable_ccpa_disclosure',
                    'sub_fields',
                    'enable_ccpa_disclosure_yes',
                    'sub_fields',
                    'cookie_audit_table'
                ];
                $lp_section_modify =  &$lp_section;

                foreach ($fields_sequence as $current_field) {
                    if (isset($lp_section_modify->$current_field)) {
                        if ('cookie_audit_table' === $current_field) {
                            unset($lp_section_modify->$current_field);
                        } else {
                            $lp_section_modify = &$lp_section_modify->$current_field;
                        }
                    }
                }

            }

            $lp_section->type    = 'heading';
            $lp_sections[ $key ] = $lp_section;
        }
        $lp_section_fields = [];
        foreach ($lp_sections as $key => $section) {
            if (! empty($section->title)) {
                $section->title = $section->title;
            }
            if (! empty($section->description)) {
                $section->description = $section->description;
            }
            if (! empty($section->fields)) {
                $fields         = (array)$section->fields;
                $section_fields = [];
                foreach ($fields as $section_key => $field) {
                    if (! empty($field->title)) {
                        $field->title = $field->title;
                    }
                    if (! empty($field->description)) {
                        $field->description = $field->description;
                    }
                    if (! empty($field->sub_fields)) {
                        $field->sub_fields = $this->getTranslationsForSubfields((array)$field->sub_fields);
                    }
                    $section_fields[ $section_key ] = $field;
                }
                $section->fields = $fields;
            }
            $lp_section_fields[ $key ] = $section;
        }
        return $lp_section_fields;
    }

    /**
     * Processes subfields.
     *
     * @param array $fields
     *
     * @return Array $section_fields
     */
    public function getTranslationsForSubfields($fields)
    {
        $section_fields = [];
        foreach ($fields as $key => $field) {
            if (! empty($field->title)) {
                $field->title = $field->title;
            }
            if (! empty($field->description)) {
                $field->description = $field->description;
            }
            if (! empty($field->sub_fields)) {
                $field->sub_fields = $this->getTranslationsForSubfields($field->sub_fields);
            }
            $section_fields[ $key ] = $field;
        }
        return $section_fields;
    }

    /**
     * Save page sections preferences.
     *
     * @param string $page
     * @param array $formData
     */
    public function legalpagesPageSectionsSave($page, $formData)
    {
        $collection = $this->collectionFactory->create();
        $policy_config_entry = $collection->addFieldToFilter('legalpage_entry_type', $page . '_config')->getData();
        $config = $this->serializer->unserialize($policy_config_entry['0']['legalpage_entry_value']);

        $selected_store = $config['lp-store'];

        switch ($page) {
            case 'terms_of_use':
                $model = $this->legalpages->create();

                $collection = $this->collectionFactory->create();
                $policy_settings_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    ['eq' => $page . '_settings_' . $selected_store]
                )->getData();
                $policy_options = $this->serializer->unserialize($policy_settings_entry['0']['legalpage_entry_value']);
                $policy_options = $this->convertDataToAPiFormat($policy_options);
                $data = [];
                foreach ($policy_options as $key => $option) {
                    if (isset($formData[ $key ])) {
                        $option->checked = true;
                        $fields          = $option->fields;
                        $settings_data   = [];

                        foreach ($fields as $field_key => $field) {
                            $field_data                  = $this->legalpagesPageSectionsSettingsSave($field, $formData);

                            $settings_data[ $field_key ] = $field_data;
                        }
                        $option->fields = $settings_data;
                    } else {
                        $option->checked = false;
                    }
                    $data[ $key ] = $option;
                }
                $legal_pages_page_sections = '';

                $model = $this->legalpages->create();
                $collection = $this->collectionFactory->create();
                $policy_settings_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    ['eq' => $page. '_settings_' . $selected_store]
                )->getData();
                if (! $policy_settings_entry) {

                    $SettingsData = [
                        'legalpage_entry_type'=> $page . '_settings_' . $selected_store,
                        'legalpage_entry_value'=> $this->serializer->serialize($data)
                    ];
                    $model->setData($SettingsData);
                    $model->save();
                } else {
                    $legalpage_entry_id = $policy_settings_entry['0']['legalpage_entry_id'];

                    $model->load($legalpage_entry_id);
                    $SettingsData = [
                        'legalpage_entry_type'=> $page . '_settings_' . $selected_store,
                        'legalpage_entry_value'=> $this->serializer->serialize($data)
                    ];
                    $model->addData($SettingsData);
                    $model->save();
                }
            
                $options = $this->legalpagesClausesSaveHelper($data);

                $data = [];
                foreach ($options as $option) {
                    $data = $this->performArrayMerge($data, $option);
                }
                $collection = $this->collectionFactory->create();

                $policy_options_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    $page . '_options_' . $selected_store
                )->getData();

                if (! $policy_options_entry) {

                    $data = [
                        'legalpage_entry_type'=>$page . '_options_' . $selected_store,
                        'legalpage_entry_value'=> $this->serializer->serialize($data)
                    ];
                    $model->setData($data);
                    $model->save();
                } else {
                    $legalpage_entry_id = $policy_options_entry['0']['legalpage_entry_id'];

                    $model->load($legalpage_entry_id);
                    $data = [
                        'legalpage_entry_type'=> $page . '_options_' . $selected_store,
                        'legalpage_entry_value'=> $this->serializer->serialize($data)
                    ];
                    $model->addData($data);
                    $model->save();
                }
                break;
            case 'privacy_policy':
                $model = $this->legalpages->create();

                $collection = $this->collectionFactory->create();
                $privacy_policy_settings_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    ['eq' => $page . '_settings_' . $selected_store]
                )->getData();
                $privacy_options = $this->serializer->unserialize(
                    $privacy_policy_settings_entry['0']['legalpage_entry_value']
                );
                $privacy_options = $this->convertDataToAPiFormat($privacy_options);
                $data = [];
                foreach ($privacy_options as $key => $option) {
                    if (isset($formData[ $key ])) {
                        $option->checked = true;
                        $fields          = $option->fields;
                        $settings_data   = [];

                        foreach ($fields as $field_key => $field) {
                            $field_data                  = $this->legalpagesPageSectionsSettingsSave($field, $formData);

                            $settings_data[ $field_key ] = $field_data;
                        }
                        $option->fields = $settings_data;
                    } else {
                        $option->checked = false;
                    }
                    $data[ $key ] = $option;
                }
            
                $legal_pages_page_sections = '';

                $model = $this->legalpages->create();
                $collection = $this->collectionFactory->create();
                $policy_settings_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    ['eq' => $page. '_settings_' . $selected_store]
                )->getData();
                if (! $policy_settings_entry) {

                    $SettingsData = [
                        'legalpage_entry_type'=> $page . '_settings_' . $selected_store,
                        'legalpage_entry_value'=> $this->serializer->serialize($data)
                    ];
                    $model->setData($SettingsData);
                    $model->save();
                } else {
                    $legalpage_entry_id = $policy_settings_entry['0']['legalpage_entry_id'];

                    $model->load($legalpage_entry_id);
                    $SettingsData = [
                        'legalpage_entry_type'=> $page . '_settings_' . $selected_store,
                        'legalpage_entry_value'=> $this->serializer->serialize($data)
                    ];
                    $model->addData($SettingsData);
                    $model->save();
                }
                $options = $this->legalpagesClausesSaveHelper($data);
                $data = [];
                foreach ($options as $option) {
                    $data = $this->performArrayMerge($data, $option);
                }
                $collection = $this->collectionFactory->create();

                $privacy_policy_options_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    $page . '_options_' . $selected_store
                )->getData();

                if (! $privacy_policy_options_entry) {

                    $data = [
                        'legalpage_entry_type'=>$page . '_options_' . $selected_store,
                        'legalpage_entry_value'=> $this->serializer->serialize($data)
                    ];
                    $model->setData($data);
                    $model->save();
                } else {
                    $legalpage_entry_id = $privacy_policy_options_entry['0']['legalpage_entry_id'];

                    $model->load($legalpage_entry_id);
                    $data = [
                        'legalpage_entry_type'=>$page . '_options_' . $selected_store,
                        'legalpage_entry_value'=> $this->serializer->serialize($data)
                    ];
                    $model->addData($data);
                    $model->save();
                }
                break;
        }
    }

    /**
     * Performs array merge operation.
     *
     * If array_merge used inside the foreach loop directly it throws phpcs error
     *
     * @param array $data
     * @param array $options
     *
     * @return Array
     */
    public function performArrayMerge($data, $options)
    {
        return array_merge($data, $options);
    }

    /**
     * Clauses save helper function.
     *
     * @param array $data
     *
     * @return Array $options
     */
    public function legalpagesClausesSaveHelper($data)
    {
        $options = [];
        foreach ($data as $key => $value) {
            if ($value->checked) {
                if (isset($value->fields) && ! empty($value->fields)) {
                    $subfields = $value->fields;
                    foreach ($subfields as $sub_key => $sub_fields) {
                        $options[ $sub_key ]         = $this->legalpagesPageSectionsClausesSave($sub_fields);
                        $options[ $sub_key ][ $key ] = true;
                    }
                }
            }
        }
        return $options;
    }

    /**
     * Save settings for the page.
     *
     * @param array $field
     * @param array $formData
     *
     * @return Array $field
     */
    public function legalpagesPageSectionsSettingsSave($field, $formData)
    {

        switch ($field->type) {
            case 'section':
                if (isset($field->sub_fields) && ! empty($field->sub_fields)) {
                    foreach ($field->sub_fields as $sub_field) {

                        $this->legalpagesPageSectionsSettingsSave($sub_field, $formData);

                    }
                }
                return $field;
            case 'select2':
                if (isset($formData[ $field->name ])) {
                    $field->value = $formData[ $field->name ];
                    $sub_fields   = $field->sub_fields;
                    foreach ($formData[ $field->name ] as $value) {
                        $sub_fields[$value]->checked = true;
                    }
                    $field->sub_fields = $sub_fields;
                }

                break;
            case 'input':
                if (isset($formData[ $field->name ])) {
                    $field->value = $formData[ $field->name ];
                }

                return $field;
            case 'textarea':
                if (isset($formData[ $field->name ])) {
                    $field->value = $formData[ $field->name ];
                }

                return $field;
            case 'checkbox':
                if (isset($formData[ $field->name ])) {
                    $field->checked = true;
                    if (isset($field->sub_fields) && ! empty($field->sub_fields)) {
                        foreach ($field->sub_fields as $sub_field) {
                            $this->legalpagesPageSectionsSettingsSave($sub_field, $formData);
                        }
                    }
                } else {
                    $field->checked = false;
                }

                return $field;
            case 'radio':
                if (isset($formData[ $field->name ]) && $formData[ $field->name ] === $field->value) {
                    $field->checked = true;
                    if (isset($field->sub_fields) && ! empty($field->sub_fields)) {
                        foreach ($field->sub_fields as $sub_field) {
                            $this->legalpagesPageSectionsSettingsSave($sub_field, $formData);
                        }
                    }
                } else {
                    $field->checked = false;
                }

                return $field;
            case 'wpeditor':
                if (isset($formData[ $field->name ])) {
                    $field->value = $formData[ $field->name ];
                }

                return $field;
        }
    }

    /**
     * Save clauses of the page settings.
     *
     * @param array $field
     * @param array $data
     *
     * @return Array $field
     */
    public function legalpagesPageSectionsClausesSave($field, $data = [])
    {
        switch ($field->type) {
            case 'section':
                if (isset($field->sub_fields) && ! empty($field->sub_fields)) {
                    foreach ($field->sub_fields as $sub_key => $sub_field) {
                        $data = $this->legalpagesPageSectionsClausesSave($sub_field, $data);
                    }
                }
                break;
            case 'select2':
                if (isset($field->value) && ! empty($field->value)) {
                    $data[ $field->id ] = $field->value;
                }
                break;
            case 'input':
                if (isset($field->value) && ! empty($field->value)) {
                    $data[ $field->id ] = $field->value;
                }
                break;
            case 'textarea':
                if (isset($field->value) && ! empty($field->value)) {
                    $data[ $field->id ] = $field->value;
                }
                break;
            case 'checkbox':
                if ($field->checked) {
                    $data[ $field->id ] = true;
                    if (isset($field->sub_fields) && ! empty($field->sub_fields)) {
                        foreach ($field->sub_fields as $sub_key => $sub_field) {
                            $data = $this->legalpagesPageSectionsClausesSave($sub_field, $data);
                        }
                    }
                }
                break;
            case 'radio':
                if ($field->checked) {
                    $data[ $field->id ] = true;
                    if (isset($field->sub_fields) && ! empty($field->sub_fields)) {
                        foreach ($field->sub_fields as $sub_key => $sub_field) {
                            $data = $this->legalpagesPageSectionsClausesSave($sub_field, $data);
                        }
                    }
                }
                break;
            case 'wpeditor':
                if (isset($field->value) && ! empty($field->value)) {
                    $data[ $field->id ] = $field->value;
                }
                break;
        }
        return $data;
    }

    /**
     * Generate page preview.
     *
     * @param string $page
     *
     * @return string $page_preview
     */
    public function getPagePreview($page)
    {

        $preview_text = $this->getPagePreviewText($page);

        $page_preview = '<div class="page_preview">';

        switch ($page) {
            case 'privacy_policy':
                if (! empty($preview_text)) {
                    $page_preview .= '<h1>';
                    $page_preview .= 'Privacy Policy';
                    $page_preview .= '</h1>';
                }
                break;
            case 'terms_of_use':
                if (! empty($preview_text)) {
                    $page_preview .= '<h1>';
                    $page_preview .= __('Terms and Conditions', 'wplegalpages');
                    $page_preview .= '</h1>';
                }
                break;
        }

        $page_preview .= $preview_text;
        $page_preview .= '</div>';
        return $page_preview;
    }

    /**
     * Generate page preview text.
     *
     * @param string $page
     *
     * @return string $preview_text
     */
    public function getPagePreviewText($page)
    {

        $collection = $this->collectionFactory->create();
        $policy_config_entry = $collection->addFieldToFilter('legalpage_entry_type', $page . '_config')->getData();
        $config = $this->serializer->unserialize($policy_config_entry['0']['legalpage_entry_value']);

        $selected_language = $config['lp-lang'];
        $selected_store = $config['lp-store'];

        $domain_name = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_domain_name');
        $business_name = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_business_name');
        $phone = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_phone');
        $email = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_email');
        $street = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_street');
        $city_state = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_city_state_zip');
        $country = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_country');
        $address = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_address');

        switch ($page) {
            case 'privacy_policy':
                $collection = $this->collectionFactory->create();

                $general              = [];
                    $general['domain']    = $domain_name;
                    $general['business']  = $business_name;
                    $general['phone']     = $phone;
                    $general['email']     = $email;
                    $general['street']    = $street;
                    $general['cityState'] = $city_state;
                    $general['country']   = $country;
                    $general['language']  = $selected_language;
                    $general['last_updated'] = gmdate('F j, Y');

                    $privacy_policy_options_entry = $collection->addFieldToFilter(
                        'legalpage_entry_type',
                        $page . '_options_' . $selected_store
                    )->getData();
                    $options = $privacy_policy_options_entry['0']['legalpage_entry_value'];
                    $options = $this->serializer->unserialize($options);
                $preview_text = $this->getPreviewFromRemote($page, $options, $general, $selected_language);

                break;
            
            case 'terms_of_use':
                $general              = [];
                $general['domain']    = $domain_name;
                $general['business']  = $business_name;
                $general['phone']     = $phone;
                $general['email']     = $email;
                $general['street']    = $street;
                $general['cityState'] = $city_state;
                $general['country']   = $country;
                $general['language']  = $selected_language;
                $general['last_updated'] = gmdate('F j, Y');

                $collection = $this->collectionFactory->create();

                $policy_options_entry = $collection->addFieldToFilter(
                    'legalpage_entry_type',
                    $page . '_options_' . $selected_store
                )->getData();
                $options = $policy_options_entry['0']['legalpage_entry_value'];
                $options = $this->serializer->unserialize($options);
                $preview_text = $this->getPreviewFromRemote($page, $options, $general, $selected_language);
                break;
        }

        return $preview_text;
    }

    /**
     * Generate page preview text by calling api.
     *
     * @param string $page
     * @param array $options
     * @param array $lp_general
     * @param string $lang
     *
     * @return string $text
     */
    private function getPreviewFromRemote($page, $options, $lp_general, $lang = 'en_US')
    {
        $text = '';
        $response = $this->doRequest(
            'get_content',
            [
            'form_params' => [
                'page'       => $page,
                'options'    => $options,
                'lp_general' => $lp_general,
                'lang'       => $lang,
                ]
             ],
            Request::HTTP_METHOD_POST
        );
                    
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();
        $text = json_decode($responseContent);

        return $text;
    }

    /**
     * Save Page preview.
     *
     * @param string $page
     *
     * @return string $text
     */
    public function pagePreviewSave($page)
    {

        $collection = $this->collectionFactory->create();
        $policy_config_entry = $collection->addFieldToFilter('legalpage_entry_type', $page . '_config')->getData();
        $config = $this->serializer->unserialize($policy_config_entry['0']['legalpage_entry_value']);
        $selected_store = $config['lp-store'];

        $model = $this->legalpages->create();
        $collection = $this->collectionFactory->create();
        $policy_preview_entry = $collection->addFieldToFilter(
            'legalpage_entry_type',
            ['eq' => $page . '_preview' . '_' . $selected_store]
        )->getData();
        $policy_preview = $policy_preview_entry['0']['legalpage_entry_value'];
        $pageIdentifier = str_replace('_', '-', $page);
        $pageIdentifier = 'legal-' . $pageIdentifier . '-page-' . $selected_store;
        //check if page already exists
        $pageFactory = $this->pageFactory->create();
        $page_id = $pageFactory->checkIdentifier($pageIdentifier, $selected_store);

        if ($page_id) {

            $newPageContent = $policy_preview;
            $newPage = $this->pageFactory->create()->load(
                $pageIdentifier,
                'identifier'
            );
            if ($newPage->getId()) {
                $newPage->setContent($newPageContent)->setStores([$selected_store]);
                $newPage->save();
            }
        } else {

            $title = '';
            switch ($page) {
                case 'privacy_policy':
                    $title = 'Privacy Policy Page';
                    break;
                case 'terms_of_use':
                    $title = 'Terms of Use Page';
            }
                    $new_page = $this->pageFactory->create();
                    $new_page->setTitle($title)
                        ->setIdentifier($pageIdentifier)
                        ->setIsActive(true)
                        ->setPageLayout('1column')
                        ->setStores([$selected_store])
                        ->setContent($policy_preview)
                        ->save();
            
        }
    }

    /**
     * Generate legal pages list templates.
     *
     * @return array $data
     */
    public function legalpagesGetTemplate()
    {
        $lp_pages = [
            'privacy_policy'            => [
                'title'   => 'Privacy Policy',
                'desc'    => 'Privacy policies are legally required almost for all businesses.
                 Getting started is easy, simply click below to begin.
                  Compatible with GDPR and LGPD',
                'btn_txt' => 'Create',
                'enabled' => true,
            ],
            'terms_of_use'              => [
                'title'   => 'Terms and Conditions',
                'desc'    => 'Use Terms and Conditions to govern the relationship with your  users,
                 it also covers "limitations of liability". This is mandatory for e-commerce.
                  Simply click below to begin.',
                'btn_txt' => 'Create',
                'lang'    => [ 'English', 'Spanish' ],
                'enabled' => true,
            ]
        ];
        foreach ($lp_pages as $key => $lpage) {

            $field  = [
                'name'        => 'policy_template',
                'label'       => $lpage['title'],
                'value'       => $key,
                'type'        => 'radio',
                'description' => $lpage['desc'],
            ];
            $data[] = $field;
        }
        return $data;
    }

    /**
     * Generate legal page settings.
     *
     * @param string $page
     * @return array $data
     */
    public function legalpagesGetSettings($page)
    {
        $data = [];
        $model = $this->legalpages->create();
        $collection = $this->collectionFactory->create();
        $pageConfig = $collection->addFieldToFilter('legalpage_entry_type', ['eq' => $page . '_config'])->getData();

        $selected_lang = 'en_US';
        $selected_storeId = 1;
        if ($pageConfig) {
            $pageConfig = $this->serializer->unserialize($pageConfig[0]['legalpage_entry_value']);
            $selected_lang = isset($pageConfig['lp-lang']) ? $pageConfig['lp-lang'] : 'en_US';
            $selected_storeId = isset($pageConfig['lp-store']) ? $pageConfig['lp-store'] : 1;
        }

        $languages    = $this->getAvailableLanguages();
        $translations = [
            'de_DE' => [
                'language' => 'de_DE',
                'native_name' => 'Deutsch',
    
            ],
            'es_ES' => [
                'language' => 'es_ES',
                'native_name' => 'Español',
            ],
            'fr_FR' => [
                'language' => 'fr_FR',
                'native_name' => 'Français',
            ],
            'it_IT' => [
                'language' => 'it_IT',
                'native_name' => 'Italiano',
            ],
            'pt_BR' => [
                'language' => 'pt_BR',
                'native_name' => 'Português do Brasil',
            ],
        ];

        $options   = [];
        $options[] = [
            'value'    => 'en_US',
            'label'    => 'English (United States)',
            'selected' => 'en_US' === $selected_lang ? true : false,
        ];
        foreach ($languages as $locale) {
            if (isset($translations[ $locale ])) {
                $translation = $translations[ $locale ];
                $options[]   = [
                    'value'    => $translation['language'],
                    'label'    => $translation['native_name'],
                    'selected' => $translation['language'] === $selected_lang ? true : false,
                ];
 
            }
        }
        $data[] = [
            'name'    => 'lp-lang',
            'label'   => 'Select Language',
            'type'    => 'select',
            'options' => $options,

        ];

        $stores = $this->store_repository->getList();
        $store_options = [];
        foreach ($stores as $store) {
            $storeId = $store['store_id'];
            $store_options[] = [
                'value' => $storeId,
                'label' => $store['name'],
                'selected' => $storeId ===  $selected_storeId ? true : false,
            ];
        }

        $data[] = [
            'name'    => 'lp-store',
            'label'   => 'Select Store',
            'type'    => 'select',
            'options' => $store_options,

        ];
        $lp_settings = $this->getSettingFieldsByPage($page);
        foreach ($lp_settings as $key => $setting) {
            $field  = [
                'name'     => $key,
                'label'    => $setting['title'],
                'value'    => $setting['value'],
                'type'     => 'text',
                'required' => $setting['required'],
            ];
            $data[] = $field;
        }
        return $data;
    }

    /**
     * Get available languages for a legalpage.
     *
     * @return array $languages
     */
    public function getAvailableLanguages()
    {

        $response = $this->doRequest(
            'get-languages'
        );
                    
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();

        $languages = json_decode($responseContent);

        return $languages;
    }

    /**
     * Get settings fields as per legalpage.
     *
     * @param string $page
     * @return array $fields
     */
    public function getSettingFieldsByPage($page)
    {
        $fields        = [];

        $domain_name = '';
        $business_name = '';
        $phone = '';
        $email = '';
        $street        = '';
        $city_state    = '';
        $country       = '';
        $address       = '';

        $domain_name = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_domain_name');
        $business_name = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_business_name');
        $phone = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_phone');
        $email = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_email');
        $street = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_street');
        $city_state = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_city_state_zip');
        $country = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_country');
        $address = $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_address');

        switch ($page) {
            case 'terms_of_use':
                $fields = [
                    'lp-domain-name'   => [
                        'title'    => 'Domain Name',
                        'value'    => $domain_name,
                        'required' => true,
                    ],
                    'lp-business-name' => [
                        'title'    => 'Business Name',
                        'value'    => $business_name,
                        'required' => true,
                    ],
                    'lp-street'        => [
                        'title'    => 'Street',
                        'value'    => $street,
                        'required' => false,
                    ],
                    'lp-city-state'    => [
                        'title'    => 'City, State, Zip code',
                        'value'    => $city_state,
                        'required' => false,
                    ],
                    'lp-country'       => [
                        'title'    => 'Country',
                        'value'    => $country,
                        'required' => false,
                    ],
                    'lp-email'         => [
                        'title'    => 'Email',
                        'value'    => $email,
                        'required' => true,
                    ],
                ];
                break;
            case 'privacy_policy':
                $fields = [
                    'lp-domain-name'   => [
                        'title'    => 'Domain Name',
                        'value'    => $domain_name,
                        'required' => true,
                    ],
                    'lp-business-name' => [
                        'title'    => 'Business Name',
                        'value'    => $business_name,
                        'required' => true,
                    ],
                    'lp-phone'     => [
                        'title'    => 'Phone',
                        'value'    => $phone,
                        'required' => true,
                    ],
                    'lp-email'         => [
                        'title'    => 'Email',
                        'value'    => $email,
                        'required' => true,
                    ],
                ];
                break;
 
        }

        return $fields;
    }

    /**
     * Save legalpages settings.
     *
     * @param string $page
     * @param array $data
     * @return array $data
     */
    public function legalpagesSettingsSave($page, $data)
    {

        $model = $this->legalpages->create();
        $collection = $this->collectionFactory->create();

        $pageConfig = $collection->addFieldToFilter('legalpage_entry_type', ['eq' => $page . '_config'])->getData();

        if (!$pageConfig) {
            $rowData = [
                'legalpage_entry_type'=>$page . '_config',
                'legalpage_entry_value'=> $this->serializer->serialize($data)
            ];
            $model->setData($rowData);
            $model->save();

        } else {
            $rowData = [
                'legalpage_entry_type'=>$page . '_config',
                'legalpage_entry_value'=> $this->serializer->serialize($data)
            ];

            $legalpage_entry_id = $pageConfig['0']['legalpage_entry_id'];

            $model->load($legalpage_entry_id);
            $model->addData($rowData);
            $model->save();
        }

        $domain_name =  isset($data['lp-domain-name'])
        ? $data['lp-domain-name']
        : $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_domain_name');

        $business_name = isset($data['lp-business-name'])
        ? $data['lp-business-name']
        : $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_business_name');

        $phone = isset($data['lp-phone'])
        ? $data['lp-phone']
        : $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_phone');

        $email = isset($data['lp-email'])
        ? $data['lp-email']
        : $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_email');

        $street = isset($data['lp-street'])
        ? $data['lp-street']
        : $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_street');

        $city_state = isset($data['lp-city-state'])
        ? $data['lp-city-state']
        : $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_city_state_zip');

        $country = isset($data['lp-country'])
        ? $data['lp-country']
        : $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_country');

        $address = isset($data['lp-address'])
        ? $data['lp-address']
        : $this->_helperData->getConfigValue('humc_lp/humc_lp_general/humc_lp_general_address');

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

        $this->cacheTypeList->cleanType('config');
    }

    /**
     * Convert serialized data to api format.
     *
     * @param array $data
     * @return array
     */
    public function convertDataToAPiFormat($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && $key !== 'fields' && $key !== 'sub_fields') {
                $value = $this->convertDataToAPiFormat($value);
                $data[$key] = (object)$value;
            } elseif (is_array($value) && ($key === 'fields' || $key === 'sub_fields')) {
                $value = $this->convertDataToAPiFormat($value);
                $data[$key] = $value;

            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
