<?php
namespace Humcommerce\LegalPages\Test\Unit\Controller\Adminhtml\Result;

class Helper
{
    /**
     * @var String called api endpoint
     */
    protected $currentApiEndpoint;

    /**
     * @var String Field added for filter
     */
    protected $fieldToFilter;

    public function __construct($currentApiEndpoint = '', $fieldToFilter = '')
    {
        $this->currentApiEndpoint = $currentApiEndpoint;
        $this->fieldToFilter = $fieldToFilter;
    }

    public function request($requestMethod, $uriEndpoint, $params)
    {
        $this->currentApiEndpoint = $uriEndpoint;

        return new self($this->currentApiEndpoint);
    }

    public function getBody()
    {
        return new self($this->currentApiEndpoint);
    }

    public function getContents()
    {
        $jsonDataSettings = '{
                    "general_information": {
                        "id": "general_information",
                        "title": "",
                        "description": "",
                        "enabled": true,
                        "checked": true,
                        "fields": {
                          "personal_info": {
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
                          },
                          "allow_third_party": {
                            "id": "allow_third_party",
                            "title": "Do you allow third-party services to collect information?",
                            "description": "",
                            "type": "section",
                            "position": 2,
                            "parent": "general_information",
                            "collapsible": false,
                            "sub_fields": {
                              "allow_third_party_yes": {
                                "id": "allow_third_party_yes",
                                "title": "Yes",
                                "description": "",
                                "type": "radio",
                                "position": 1,
                                "parent": "allow_third_party",
                                "name": "allow_third_party_radio",
                                "value": "yes",
                                "checked": true,
                                "sub_fields": {
                                  "third_party_services": {
                                    "id": "third_party_services",
                                    "title": "Choose from the following third-party services:",
                                    "description": "",
                                    "type": "section",
                                    "position": 1,
                                    "parent": "allow_third_party_yes",
                                    "collapsible": false,
                                    "sub_fields": {
                                      "third_party_services_advertising": {
                                        "id": "third_party_services_advertising",
                                        "title": "Advertising",
                                        "description": "This type of service allows User Data to be utilized for
                                         advertising 
                                        communication purposes. These communications are displayed 
                                        in the form of banners and
                                         other advertisements on this Website, possibly based
                                          on User interests.",
                                        "parent": "third_party_services",
                                        "type": "checkbox",
                                        "position": 1,
                                        "name": "third_party_services_advertising",
                                        "value": "1",
                                        "checked": false,
                                        "sub_fields": {
                                          "tps_advertising": {
                                            "id": "tps_advertising",
                                            "title": "",
                                            "description": "",
                                            "parent": "third_party_services_advertising",
                                            "type": "select2",
                                            "position": 1,
                                            "name": "tps_advertising",
                                            "value": "",
                                            "checked": false,
                                            "sub_fields": {
                                              "yahoo_app_publishing_advertising": {
                                                "id": "yahoo_app_publishing_advertising",
                                                "title": "Yahoo App Publishing Advertising",
                                                "description": "",
                                                "parent": "tps_advertising",
                                                "type": "select2option",
                                                "position": 1,
                                                "name": "",
                                                "value": "yahoo_app_publishing_advertising",
                                                "checked": false,
                                                "sub_fields": [
                                                  
                                                ]
                                              },
                                              "yellowhammer": {
                                                "id": "yellowhammer",
                                                "title": "YellowHammer",
                                                "description": "",
                                                "parent": "tps_advertising",
                                                "type": "select2option",
                                                "position": 2,
                                                "name": "",
                                                "value": "yellowhammer",
                                                "checked": false,
                                                "sub_fields": [
                                                  
                                                ]
                                              },
                                              "yieldlab": {
                                                "id": "yieldlab",
                                                "title": "Yieldlab",
                                                "description": "",
                                                "parent": "tps_advertising",
                                                "type": "select2option",
                                                "position": 3,
                                                "name": "",
                                                "value": "yieldlab",
                                                "checked": false,
                                                "sub_fields": [
                                                  
                                                ]
                                                            }
                                                        }
                                                    }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }';

            $jsonDataPreview = json_encode('<div><p>Sample Preview</p></div>');

            $jsonDataLanguages = json_encode([
              'de_DE',
              'es_ES'
            ]);
            $returnValue = '';

        switch ($this->currentApiEndpoint) {
            case 'get_privacy_settings':
                $returnValue = $jsonDataSettings;
                break;
            case 'get_clauses':
                $returnValue = $jsonDataSettings;
                break;
            case 'get_content':
                $returnValue = $jsonDataPreview;
                break;
            case 'get-languages':
                $returnValue = $jsonDataLanguages;
                break;
        }

        return $returnValue;
    }

    public function getSubfields()
    {
        $subFields = '
                {
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
                ';
        return json_decode($subFields);
    }

    public function addFieldToFilter($value, $filter)
    {

        return new self('', $filter);
    }
    public function getData()
    {

        $configData = [
        '0' => [
          'legalpage_entry_value' => [
            'lp-lang' => 'en_US',
            'lp-store' => '1',
          ]
          ]
          ];
        $previewHtml = [
          '0' => [
            'legalpage_entry_value' => '<div><p>Sample preview</p></div>'
          ]
            ];

            $returnValue = '';
        if (is_array($this->fieldToFilter)) {

            foreach ($this->fieldToFilter as $key => $value) {
                if (false !== strpos($value, 'preview')) {
                    $returnValue = $previewHtml;

                }
            }
        } elseif (false !== strpos($this->fieldToFilter, 'config')
         || false !== strpos($this->fieldToFilter, 'option')) {
            $returnValue = $configData;
        }
          return $returnValue;
    }

    public function checkIdentifier($pageIdentifier, $selected_store)
    {
        return true;
    }

    public function setTitle($title)
    {
        return new self();
    }

    public function setIdentifier($pageIdentifier)
    {
        return new self();
    }

    public function setIsActive($active)
    {
        return new self();
    }

    public function setPageLayout($layout)
    {
        return new self();
    }

    public function setStores($stores)
    {
        return new self();
    }
    
    public function setContent($content)
    {
        return new self();
    }

    public function save()
    {
        return new self();
    }

    public function unserialize()
    {
        return [
        'lp-lang' => 'en_US',
        'lp-store' => '1',
        ];
    }

    public function scopeConfig()
    {
        return new self();
    }
    public function getValue()
    {
        return 'sample config value';
    }
}
