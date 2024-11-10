<?php
namespace scrapper\collectlinks;

require __DIR__ ."/../../../vendor/autoload.php";

use DiDom\Document;
use Exception;
use GuzzleHttp\Client;
use SimpleXMLElement;
use DOMDocument;

class SitemapScrapper{

    public $xml_array;
    public $links_array = [];
    public $catalog_links_array = [];
    public $succes_catalog_links_array = [];
    public $products_links_array = [];
    public $sitemap_url = 'https://www.komod43.ru/sitemap.xml';
    public $client;
    public $xml;
    public $document;
    public $filename = 'output.txt';
    public $html;
    public $check_pages_array = [];
    public $check_page;
    public $products_links_file = 'products_file.txt';
    public $products = [];
    public $product_count_limit = 100;

    public function __construct(){
        $this->create_client_connect();
    }

    public function create_client_connect(){
        $this->client = new Client([
            'verify' => false,
            'timeout' => 20,
        ]);
    }

    public function get_request(){
        $response = $this->client->request('GET', $this->sitemap_url);
        $this->xml = $response->getBody()->getContents();
    }

    public function get_all_links(){
        $this->document = new Document($this->xml);
        $this->xml_array = $this->document->find('url loc');
    }

    public function get_links(){
        foreach ($this->xml_array as $link){
            $domElement = $link->getNode();
            array_push($this->links_array, $domElement->nodeValue);
        }
    }

    public function get_catalog_links(){
        $pattern = $pattern = '/^https:\/\/www\.komod43\.ru\/catalog\/[a-zA-Z0-9-]+\.html$/';        ;
        foreach($this->links_array as $link){
            if(preg_match($pattern, $link)){
                array_push($this->catalog_links_array, $link);
            }
        }
    }

    public function check_links(){
        foreach($this->catalog_links_array as $link){
            sleep(rand(8, 16));
            try{
                $response = $this->client->request('GET', $link);
                sleep(rand(1, max: 3));
                array_push($this->succes_catalog_links_array, $link);
            }
            catch(Exception $e){
                echo $link."\n";
                echo $e;
            }
        }
        print_r($this->succes_catalog_links_array);
        $string = implode(PHP_EOL, $this->succes_catalog_links_array);
        file_put_contents($this->filename, $string);
    }

    public function get_pages(){
        echo "start\n";
        $this->check_page = new Document();
        $this->check_pages_array = file($this->filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach($this->check_pages_array as $link){
            sleep(rand(8, 16));
            $hrefs = [];
            try{
                $response = $this->client->request('GET', $link);
                $this->html = $response->getBody()->getContents();
                $this->document = new Document($this->html);
                $div_blocks = $this->document->find('div.pagination-container');
                if (!empty($div_blocks)) {
                    foreach ($div_blocks as $div_block) {
                        $pages = $div_block->find('a');
                        $pages = array_unique($pages);
                        foreach ($pages as $page) {
                            $page = $page->getAttribute('href');
                            $href = "https://www.komod43.ru$page";
                            array_push($this->check_pages_array, $href);
                        }
                    print_r($this->check_pages_array);
                    }
                }
                sleep(rand(1, max: 3));
            }
            catch(Exception $e){
                echo $link."\n";
                echo $e;
            }
            $this->check_pages_array = array_unique($this->check_pages_array);
            $string = implode(PHP_EOL, $this->check_pages_array);
            file_put_contents($this->filename, $string);
        }
    }

    public function get_products(){
        echo "start\n";
        $this->check_page = new Document();
        $this->check_pages_array = file($this->filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach($this->check_pages_array as $link){
            sleep(rand(8, 16));
            $hrefs = [];
            try{
                $response = $this->client->request('GET', $link);
                $this->html = $response->getBody()->getContents();
                $this->document = new Document($this->html);
                $div_blocks = $this->document->find('div.col-xs-12.text-center');
                if (!empty($div_blocks)) {
                    foreach ($div_blocks as $div_block) {
                        $products = $div_block->find('a');
                        $products = array_unique($products);
                        foreach ($products as $product) {
                            $product = $product->getAttribute('href');
                            $href = "https://www.komod43.ru$product";
                            array_push($this->products_links_array, $href);
                            
                        }
                    print_r($this->products_links_array);
                    }
                    
                }
                sleep(rand(1, max: 3));
            }

            catch(Exception $e){
                echo $link."\n";
                echo $e;
            }
            $this->products_links_array = array_unique($this->products_links_array);
            $string = implode(PHP_EOL, $this->products_links_array);
            file_put_contents($this->products_links_file, $string);
        }
    }

    public function parse_product_carts(){
        echo "start parse\n";
        $this->products_links_array = file($this->products_links_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $count = 0;
        foreach($this->products_links_array as $link){
            $variable = [];
            $variation = [];
            $image_array = [];
            $product_data = [];
            $sizes_and_colors_array = [];
            $prices_array = [];
            sleep(rand(8, 10));
            try{
                $response = $this->client->request('GET', $link);
                $this->html = $response->getBody()->getContents();
                $this->document = new Document($this->html);
                $name = $this->document->find('h1.name');
                $name = $name[0]->getNode()->nodeValue;
                $description = $this->document->find('p.text');
                $description = $description[0]->getNode()->nodeValue;
                if ($description == ''){
                    $description = 'None';
                }
                $img_container = $this->document->find('a.horizontal-thumb');
                $imgs = $img_container[0]->find('img');
                foreach($imgs as $img){
                    $img = $img->getAttribute('src');
                    array_push($image_array, $img);
                }
                $sizes_and_colors = $this->document->find('div.col-xs-5.visible-lg.visible-md');
                foreach($sizes_and_colors as $size_and_color){
                    array_push($sizes_and_colors_array, $size_and_color->getNode()->textContent);
                }
                $prices = $this->document->find('div[data-price]');
                foreach($prices as $price){
                    array_push($prices_array, $price->getNode()->textContent);
                }
                array_push($variation, $name, $sizes_and_colors_array, $prices_array);
                array_push($variable, $name, $description, $image_array);
                array_push($product_data, $variable, $variation);
                array_push($this->products, $product_data);
                $count += 1;
                if ($count >= $this->product_count_limit){
                    break;
                }
            }

            catch(Exception $e){
                echo $link."\n";
                echo $e;
            }
        }

        $xml_products = new SimpleXMLElement('<products/>');
        $xml_variable = $xml_products->addChild("variable");
        $xml_variation = $xml_products->addChild("variation");
        $xml_products->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml_products->addAttribute('xsi:noNamespaceSchemaLocation', 'products.xsd');
        $name_count = 1;
        foreach ($this->products as $product) {
            $Node_variable = $xml_variable->addChild("product_variable_$name_count");
            $Node_variable->addChild('name', $product[0][0]);
            $Node_variable->addChild('description', $product[0][1]);
            for($i=0; $i < count($product[0][2]); $i++){
                $Node_variable->addChild("image_$i", $product[0][2][$i]);
            }
            
            $Node_variation = $xml_variation->addChild("product_variation_$name_count");
            $Node_variation->addChild('name', $product[1][0]);
            for($i=0; $i < count($product[1][1]); $i++){
                $Node_variation->addChild("size_and_color_$i", $product[1][1][$i]);
            }
            for($i=0; $i < count($product[1][2]); $i++){
                $Node_variation->addChild("price_$i", $product[1][2][$i]);
            }
            $name_count += 1;
        }
        $xmlFile = 'products.xml';
        $xmlString = $xml_products->asXML();
        $dom = new DOMDocument();
        $dom->loadXML($xmlString);
        $dom->formatOutput = true;
        $dom->save($xmlFile);
        echo "XML файл успешно создан: $xmlFile";

    }
}

