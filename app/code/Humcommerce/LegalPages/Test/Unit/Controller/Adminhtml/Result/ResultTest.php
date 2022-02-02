<?php
 
namespace Humcommerce\LegalPages\Test\Unit\Controller\Adminhtml\Result;

use Humcommerce\LegalPages\Test\Unit\Controller\Adminhtml\Result\Helper;
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

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Humcommerce\LegalPages\Controller\Adminhtml\Result\Result;
use Magento\Framework\Serialize\SerializerInterface;

class ResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Humcommerce\LegalPages\Controller\Adminhtml\Result\Result
     */
    protected $resultClass;
 
    /**
     * @var string
     */
    protected $expectedMessage;

    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var GuzzleHttp\ClientFactory
     */
    protected $clientFactory;

    /**
     * @var GuzzleHttp\Psr7\ResponseFactory
     */
    protected $responseFactory;

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
     * @var Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var Humcommerce\LegalPages\Test\Unit\Controller\Adminhtml\Result\Helper
     */
    protected $helperClass;

    /**
     * @var Magento\Store\Model\StoreRepository
     */
    protected $store_repository;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

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
     * Set up for unit test cases.
     *
     * @return Void
     */
    public function setUp(): void
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resultClass = $objectManager->get(Result::class);

        $this->helperClass = $objectManager->create(Helper::class);

        $this->resultPageFactory = $this->getMockBuilder(PageFactory::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->clientFactory = $this->getMockBuilder(ClientFactory::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->clientFactory->method('create')->willReturn($this->helperClass);

        $this->responseFactory = $this->getMockBuilder(ResponseFactory::class)
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();
        $this->legalpages = $this->getMockBuilder(\Humcommerce\LegalPages\Model\LegalpagesFactory::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->legalpages->method('create')->willReturn($this->helperClass);

        $this->collectionFactory = $this->getMockBuilder(
            \Humcommerce\LegalPages\Model\ResourceModel\Legalpages\CollectionFactory::class
        )
        ->disableOriginalConstructor()
        ->getMock();
        $this->collectionFactory->method('create')->willReturn($this->helperClass);

        $this->pageFactory = $this->getMockBuilder(\Magento\Cms\Model\PageFactory::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->pageFactory->method('create')->willReturn($this->helperClass);
        $this->store_repository = $this->getMockBuilder(\Magento\Store\Model\StoreRepository::class)
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();
        $this->_helperData = $this->getMockBuilder(\Humcommerce\LegalPages\Helper\Data::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->_helperData->method('getConfigValue')->willReturn($this->helperClass);

        $this->backendUrl = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();
        $this->configWriter = $this->getMockBuilder(\Magento\Framework\App\Config\Storage\WriterInterface::class)
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();
        $this->cacheTypeList = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeListInterface::class)
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->serializer->method('unserialize')->willReturn($this->helperClass->unserialize());

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->resultClass = $this->objectManagerHelper->getObject(
            Result::class,
            [
            'resultPageFactory' => $this->resultPageFactory,
            'resultJsonFactory' => $this->resultJsonFactory,
            'clientFactory' => $this->clientFactory,
            'messageManager'  => $this->messageManager,
            'legalpages'  => $this->legalpages,
            'collectionFactory'  => $this->collectionFactory,
            'pageFactory'  => $this->pageFactory,
            'store_repository' => $this->store_repository,
            'storeManager' => $this->storeManager,
            'helperData' => $this->_helperData,
            'backendUrl' => $this->backendUrl,
            'configWriter' => $this->configWriter,
            'cacheTypeList' => $this->cacheTypeList,
            'serializer' => $this->serializer
            ]
        );
    }
 
    public function testDoRequest()
    {
        $this->assertEquals(
            $this->helperClass->request('', 'get_clauses', ''),
            $this->resultClass->doRequest('get_clauses')
        );
    }

    public function testPageSection()
    {

        $this->assertIsArray($this->resultClass->legalpagesGetPageSections('privacy_policy'));
    }

    public function testTranslationSubfields()
    {

        $this->assertIsArray($this->resultClass->getTranslationsForSubfields($this->helperClass->getSubfields()));
    }

    public function testSectionSettingSave()
    {
        $fields = '{
            "id": "personal_info",
            "title": "What kind of personal information is collected?",
            "description": "",
            "type": "section",
            "position": 1,
            "parent": "general_information",
            "collapsible": false,
            "sub_fields": {
              "personal_info_name": {
                "id": "personal_info_name",
                "title": "Name",
                "description": "",
                "type": "checkbox",
                "position": 1,
                "parent": "personal_info",
                "name": "personal_info_name",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_email": {
                "id": "personal_info_email",
                "title": "Email",
                "description": "",
                "type": "checkbox",
                "position": 2,
                "parent": "personal_info",
                "name": "personal_info_email",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_phone": {
                "id": "personal_info_phone",
                "title": "Phone",
                "description": "",
                "type": "checkbox",
                "position": 3,
                "parent": "personal_info",
                "name": "personal_info_phone",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_address": {
                "id": "personal_info_address",
                "title": "Address",
                "description": "",
                "type": "checkbox",
                "position": 4,
                "parent": "personal_info",
                "name": "personal_info_address",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_social_media": {
                "id": "personal_info_social_media",
                "title": "Social Media Profile information  (Sign in with  Facebook, Twitter etc)",
                "description": "",
                "type": "checkbox",
                "position": 5,
                "parent": "personal_info",
                "name": "personal_info_social_media",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_business": {
                "id": "personal_info_business",
                "title": "Nature & Size of the Business",
                "description": "",
                "type": "checkbox",
                "position": 6,
                "parent": "personal_info",
                "name": "personal_info_business",
                "value": "1",
                "checked": false,
                "sub_fields": [
                  
                ]
              },
              "personal_info_advertising": {
                "id": "personal_info_advertising",
                "title": "Nature & Size of the Advertising Inventory",
                "description": "",
                "type": "checkbox",
                "position": 7,
                "parent": "personal_info",
                "name": "personal_info_advertising",
                "value": "1",
                "checked": false,
                "sub_fields": [
                  
                ]
              }
            }
          }';

          $formData = [
            "personal_info_name" => 1,
            "personal_info_advertising" => 1
          ];

          $returnedObj = $this->resultClass->legalpagesPageSectionsSettingsSave(json_decode($fields), $formData);

          $this->assertTrue($returnedObj->sub_fields->personal_info_name->checked);
          $this->assertTrue($returnedObj->sub_fields->personal_info_advertising->checked);
    }

    public function testSectionsClauseSave()
    {
        $fields = '{
            "id": "personal_info",
            "title": "What kind of personal information is collected?",
            "description": "",
            "type": "section",
            "position": 1,
            "parent": "general_information",
            "collapsible": false,
            "sub_fields": {
              "personal_info_name": {
                "id": "personal_info_name",
                "title": "Name",
                "description": "",
                "type": "checkbox",
                "position": 1,
                "parent": "personal_info",
                "name": "personal_info_name",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_email": {
                "id": "personal_info_email",
                "title": "Email",
                "description": "",
                "type": "checkbox",
                "position": 2,
                "parent": "personal_info",
                "name": "personal_info_email",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_phone": {
                "id": "personal_info_phone",
                "title": "Phone",
                "description": "",
                "type": "checkbox",
                "position": 3,
                "parent": "personal_info",
                "name": "personal_info_phone",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_address": {
                "id": "personal_info_address",
                "title": "Address",
                "description": "",
                "type": "checkbox",
                "position": 4,
                "parent": "personal_info",
                "name": "personal_info_address",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_social_media": {
                "id": "personal_info_social_media",
                "title": "Social Media Profile information  (Sign in with  Facebook, Twitter etc)",
                "description": "",
                "type": "checkbox",
                "position": 5,
                "parent": "personal_info",
                "name": "personal_info_social_media",
                "value": "1",
                "checked": true,
                "sub_fields": [
                  
                ]
              },
              "personal_info_business": {
                "id": "personal_info_business",
                "title": "Nature & Size of the Business",
                "description": "",
                "type": "checkbox",
                "position": 6,
                "parent": "personal_info",
                "name": "personal_info_business",
                "value": "1",
                "checked": false,
                "sub_fields": [
                  
                ]
              },
              "personal_info_advertising": {
                "id": "personal_info_advertising",
                "title": "Nature & Size of the Advertising Inventory",
                "description": "",
                "type": "checkbox",
                "position": 7,
                "parent": "personal_info",
                "name": "personal_info_advertising",
                "value": "1",
                "checked": false,
                "sub_fields": [
                  
                ]
              }
            }
          }';
          $this->assertIsArray($this->resultClass->legalpagesPageSectionsClausesSave(json_decode($fields)));
    }

    public function testGetPagePreview()
    {
        $output = $this->resultClass->getPagePreview('privacy_policy');

        $this->assertTrue(is_string($output) && ( strip_tags($output) !== $output ));
        $output = $this->resultClass->getPagePreview('terms_of_use');
        $this->assertTrue(is_string($output) && ( strip_tags($output) !== $output ));
    }

    public function testGetPagePreviewText()
    {
        $previewText = $this->resultClass->getPagePreviewText('privacy_policy');
        $this->assertTrue(is_string($previewText) && ( strip_tags($previewText) !== $previewText ));
    }

    public function testLegalpagesGetTemplate()
    {
        $templates = $this->resultClass->legalpagesGetTemplate();
        $this->assertTrue(is_array($templates));
    }

    public function testLegalpagesGetSettings()
    {
        $settings = $this->resultClass->legalpagesGetSettings('privacy_policy');
        $this->assertTrue(is_array($settings));
    }

    public function testGetAvailableLanguages()
    {
        $languages = $this->resultClass->getAvailableLanguages();

        $expectedLanguages = [
        'de_DE',
        'es_ES'
        ];
        $this->assertEquals($languages, $expectedLanguages);
    }

    public function testGetSettingFieldsByPage()
    {
        $settingsFields = $this->resultClass->getSettingFieldsByPage('privacy_policy');

        $this->assertTrue(is_array($settingsFields));
    }
}
