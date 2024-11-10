<?php
namespace scrapper;
require __DIR__."/vendor/autoload.php";

use scrapper\collectlinks\SitemapScrapper;

$scrapper = new SitemapScrapper();
$scrapper->get_request();
$scrapper->get_links();
$scrapper->get_catalog_links();
$scrapper->check_links();
$scrapper->get_pages();
$scrapper->get_products();
$scrapper->parse_product_carts();

