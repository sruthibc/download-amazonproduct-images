<?php

/**
 * Image Download from Amazon with AWS product advertising API
 *
*/

class BL_ImageCover {
    
    var $arrAmazonURL;
    
    function __construct() {
        $this->setDBClass();
        // an array of Amazon url , here Im using a couple of album urls
        $this->$this->arrAmazonAlbumURLs = array("https://www.amazon.com/Not-Even-Happiness-JULIE-BYRNE/dp/B01MG9MGWX","https://www.amazon.com/Life-Without-Sound-Cloud-Nothings/dp/B01M6W3LQG"); 
        require('/awspaapi5.php'); 
    }
        
    function downloadCoverImages(){                                
        
        if(!empty($this->$this->arrAmazonAlbumURLs)){
             $i = 0;
            foreach($this->$this->arrAmazonAlbumURLs as $album){            
                
                $part = explode('/dp/', $album['amazon']);
                $strASIN = $part[1];

                $saveimagepath = $strASIN.'_cover.jpg';
                            
                
                $serviceName="ProductAdvertisingAPI";
                $region="us-east-1";
                $accessKey="****YOUR ACCESS KEY****";
                $secretKey="*****YOUR SECRET KEY*******";            
                $host="webservices.amazon.com";
                $uriPath="/paapi5/getitems";

                $awsv4 = new AwsV4 ($accessKey, $secretKey);
                $awsv4->setRegionName($region);
                $awsv4->setServiceName($serviceName);
                $awsv4->setPath ($uriPath); 
                $awsv4->setRequestMethod ("POST");
                $awsv4->addHeader ('content-encoding', 'amz-1.0');
                $awsv4->addHeader ('content-type', 'application/json; charset=utf-8');
                $awsv4->addHeader ('host', $host);
                $awsv4->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems');                   
                                                                
                    $payload="{"
                            ." \"ItemIds\": ["
                            ."  \"".trim($strASIN)."\""
                            ." ],"
                            ." \"Resources\": ["
                            ."  \"Images.Primary.Large\""
                            ." ],"
                            ." \"PartnerTag\": \"****YOUT PARTNER TAG****\","
                            ." \"PartnerType\": \"Associates\","
                            ." \"Marketplace\": \"www.amazon.com\""
                            ."}";


                    $awsv4->setPayload ($payload);

                    $headers = $awsv4->getHeaders ();
                    $headerString = "";
                    foreach ( $headers as $key => $value ) {
                        $headerString .= $key . ': ' . $value . "\r\n";
                    }
                    $params = array (
                            'http' => array (
                                'header' => $headerString,
                                'method' => 'POST',
                                'content' => $payload
                            )
                        );
                    $stream = stream_context_create ( $params );

                    $fp = @fopen ( 'https://'.$host.$uriPath, 'rb', false, $stream );

                    if (! $fp) {
                        echo ' - Cant Open AWS'.'<br>';
                        //throw new Exception ( "Exception Occured" );
                    }else{

                        $response = @stream_get_contents ( $fp );

                        if ($response !== false) {

                            $arrResponse = json_decode($response, true);
                                                       

                            if(!empty($arrResponse['ItemsResult']['Items'][0]['Images']['Primary']['Large']['URL'])){
                                    $options  = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36'));
                                    $context  = stream_context_create($options);
                                    
                                    $Imgresponse = file_get_contents($arrResponse['ItemsResult']['Items'][0]['Images']['Primary']['Large']['URL'], false, $context); 
                                     
                                    if(file_exists($saveimagepath)){
                                        unlink($saveimagepath);
                                    }
                                    if(file_put_contents($saveimagepath, $Imgresponse)){
                                        $download = 1;                                         
                                        echo " - Image Download Success".'<br>';
                                    }                                                    
                            } else{
                                echo " - URL is empty".'<br>';
                            }
                        } else{
                            echo ' - Somthing Wrong'.'<br>';
                        }
                    }                    
                    $i++;                    
            }                                           
                                     
        }
                
    }
    
    
}

