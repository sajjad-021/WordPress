<?php

/**
 * Class to scrape amazon products
 * @author Muhammed Atef
 * @link http://www.deandev.com
 * @version 1.0
 */

/*
 * From Jan 2019 amazon has changed it's api usage policy. API feature is mapped with sale you made in last month.
 * https://docs.aws.amazon.com/es_es/AWSECommerceService/latest/DG/TroubleshootingApplications.html
 *
 */
class wp_automatic_amazon_api_less {
	/**
	 * cURL handle
	 */
	private $ch = "";
	
	/**
	 * Your Amazon Associate Tag
	 * Now required, effective from 25th Oct.
	 * 2011
	 *
	 * @access private
	 * @var string
	 */
	private $associate_tag = "YOUR AMAZON ASSOCIATE TAG";
	private $region = "";
	public $is_next_page_available = false;
	function __construct(&$ch, $region) {
		$this->ch = $ch;
		$this->region = $region;
	}
	
	/**
	 * Return details of a product searched by ASIN
	 *
	 * @param int $asin_code
	 *        	ASIN code of the product to search
	 * @return mixed simpleXML object
	 */
	public function getItemByAsin($asin_code) {
		
		//save timing limit
		sleep(rand(3,5));
		
		// trim asin
		$asin_code = trim ( $asin_code );
		
		// item URL
		$item_url = "https://www.amazon.{$this->region}/dp/$asin_code";
		
		echo '<br>Item link:' . $item_url;
		
		// load the item page
		$x = 'error';
		curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
		curl_setopt ( $this->ch, CURLOPT_URL, trim ( $item_url ) );
		curl_setopt ( $this->ch, CURLOPT_ENCODING, "" );
		$exec = curl_exec ( $this->ch );
		// $exec = file_get_contents('test.txt');
		$x = curl_error ( $this->ch );
		
		// validate returned content
		if (trim ( $exec ) == '' || trim ( $x ) != '') {
			throw new Exception ( 'No valid reply returned from Amazon with a possible cURL err ' . $x );
		}
		
		if(stristr($exec,'/captcha/')){
			echo '<br>Amazon asked for Capacha... will die now and try next run';
			exit;
			throw new Exception ( 'Amazon asked for Capacha ' . $x );
		}
		
		// validate returned result
		if (! stristr ( $exec, $asin_code )) {
			
			echo $exec;
			
			throw new Exception ( 'No valid reply returned from Amazon can not find the item asin' );
			
		}
		
		
		//fix for eur getiting defected for amazon.it
		$exec = str_replace('iso-8859-1' , 'utf-8' , $exec );
		
		
		
		// dom
		$doc = new DOMDocument ();
		@$doc->loadHTML ( $exec );
		
		$xpath = new DOMXpath ( $doc );
		
		// title
		$elements = $xpath->query ( '//*[@id="productTitle"]' );
 		 
		
		$item_title = '';
		if ( $elements->length  > 0) {
			$title_element = $elements->item(0);
			$item_title = trim ( $title_element->nodeValue );
		}
 		
		 
		
		$ret['link_title'] = $item_title;
		
		// the description productDescription
		$item_description = '';
	 	preg_match_all('{<div id="productDescription.*?<p>(.*?)</p>[^<]}s' , $exec , $description_matches);
	 	$description_matches = $description_matches[1];
	 	if(isset($description_matches[0])){
	 			
	 		$item_description = $description_matches[0];
	 		$item_description = str_replace('</p><p>','<br>',$item_description);
	 		$item_description = str_replace( array('<p>' , '</p>') ,'',$item_description);
	 	} 
	 	
	 	if(trim($item_description) == '' && stristr($exec,'id="aplus"')){
	 	  	
	 		$elements = $xpath->query ( '//*[@id="aplus"]' );
	 		
	 	 
	 		if ($elements->length > 0) {
	 			 $item_description = $doc->saveHTML($elements->item(0));
	 			 $item_description = preg_replace( array( '{<style.*?style>}s' , '{<a.*?/a>}s' , '{<script.*?/script>}s' ) ,'',$item_description);
	 			 $item_description = strip_tags($item_description,'<p><br><img><h3>');
	 		}
	 		
	 	}
	 	
	 	$ret['item_description'] = $item_description;
		
		// features
		$elements = $xpath->query ( '//*[@id="feature-bullets"]/ul/li/span[@class="a-list-item"]' );
		
		$item_features = array (); 
		if ($elements->length > 0) {
			foreach ( $elements as $element ) {
				$item_features [] = trim ( ($element->nodeValue) );
			}
			unset ( $item_features [0] );
		}

		$ret['item_features'] = $item_features;
 		
		// images large":"
		preg_match_all ( '{colorImages\': \{.*?large":".*?".*?script>}s', $exec, $imgs_matches );
		
		 
		$possible_img_part = '';
		
		if(isset($imgs_matches[0])){
			
			$possible_img_part = $imgs_matches[0];
			$possible_img_part = $possible_img_part[0];
			 
		}
		
		if(trim($possible_img_part) != ''){
			preg_match_all ( '{large":"(.*?)"}s', $possible_img_part, $imgs_matches );
		}else{
			preg_match_all ( '{large":"(.*?)"}s', $exec, $imgs_matches );
		}
		 
		$item_images = array_unique ( $imgs_matches [1] );
		
		
		 
		if(count($item_images) == 0){
			//no images maybe a book
			echo '<br>No images found using method #1 trying method #2';
			
			//ebooksImageBlockContainer imageGalleryData
			if( stristr( $exec, 'imageGalleryData') ){
				preg_match('{imageGalleryData(.*?)dimensions}s', $exec, $poassible_book_imgs);
			}elseif( stristr( $exec, 'ebooksImageBlockContainer') ){
				preg_match('{<div id="ebooksImageBlockContainer(.*?)div>}s', $exec, $poassible_book_imgs);
			}elseif(stristr($exec,'mainImageContainer')){
				preg_match('{<div id="mainImageContainer(.*?)div>}s', $exec, $poassible_book_imgs);
			}elseif(stristr($exec,'main-image-container')){
				preg_match('{<div id="main-image-container(.*?)div>}s', $exec, $poassible_book_imgs);
			}
			
			$poassible_book_imgs = $poassible_book_imgs[0];
			
			if(trim($poassible_book_imgs) != ''){
				preg_match_all( '{https://.*?\.jpg}s' , $poassible_book_imgs , $possible_book_img_srcs );
				$possible_book_img_srcs = $possible_book_img_srcs[0];
				
				
				if( count($possible_book_img_srcs) > 0){
					$final_img = end($possible_book_img_srcs) ;
					$final_img = preg_replace('{,.*?\.}','.',$final_img);
					$item_images = array( $final_img );
				}
				
			 
			}
 			
		}
		
		 
		
		$ret['item_images'] = $item_images;
		
		 
		// prices priceblock_ourprice
		if(stristr($exec,'id="priceblock_dealprice')){
			$elements = $xpath->query ( '//*[@id="priceblock_dealprice"]' );
		}elseif( stristr($exec,'id="priceblock_ourprice') ){
			$elements = $xpath->query ( '//*[@id="priceblock_ourprice"]' );
		}elseif( stristr($exec,'id="priceblock_saleprice') ){
			$elements = $xpath->query ( '//*[@id="priceblock_saleprice"]' );
		}elseif(stristr($exec,'id="price_inside_buybox')){
			$elements = $xpath->query ( '//*[@id="price_inside_buybox"]' );
		}
		
		
		
 		 	
		$item_price = '';
		if ($elements->length > 0) {
			$item_price = trim ( $elements->item(0)->nodeValue );
			 
			$item_price = preg_replace('{ -.*}' , '' , $item_price);
		}elseif(stristr($exec,'offer-price')){
			
			//<span class="a-size-medium a-color-price offer-price a-text-normal">$16.98</span>
			preg_match_all ( '{ offer-price .*?>(.*?)</span>}s', $exec, $possible_price_matches );
			$possible_price_matches = $possible_price_matches[1];
			
			if(isset($possible_price_matches[0]) && trim($possible_price_matches[0]) != '' ) $item_price = $possible_price_matches[0] ;
			 
		}
		
		$ret['item_price'] = $item_price;
		
		// pre-sale price
		$elements = $xpath->query ( "//*[contains(@class, 'priceBlockStrikePriceString')]" );
		 
		$item_pre_price = $item_price;
		if ($elements->length > 0) {
			$item_pre_price = trim ( $elements->item(0)->nodeValue );
		
			$item_pre_price = preg_replace('{ -.*}' , '' , $item_pre_price);
 	
		}
		
		$ret['item_pre_price'] = $item_pre_price;
		
		//item link
		$ret['item_link'] = 'https://amazon.' . $this->region . '/dp/' .$asin_code ;
		$ret['item_reviews'] = 'https://www.amazon.' . $this->region . '/reviews/iframe?akid=AKIAJDYHK6WW2AYDNYJA&alinkCode=xm2&asin='.$asin_code.'&atag=iatefpro&exp=2035-07-19T16%3A07%3A21Z&v=2&sig=ofoCKfF6T0LDaPzBPX%252BB2tnjuzE3gCl%252BstWxTFdnCJQ%253D';
		 
		
		return $ret;
		 		
	}
	
	/**
	 * Return details of a product searched by keyword
	 *
	 * @param string $keyword
	 *        	keyword to search
	 * @param string $product_type
	 *        	type of the product
	 * @return mixed simpleXML object
	 */
	public function getItemByKeyword($keyword, $ItemPage, $product_type, $additionalParam = array(), $min = '', $max = '') {
		
		// next page flag reset
		$this->is_next_page_available = false;
		
		// encoded keyword
		$keyword_encoded = urlencode ( trim ( $keyword ) );
		
		$search_url = "https://www.amazon.{$this->region}/s?k=$keyword_encoded&ref=nb_sb_noss";
		
		// https://www.amazon.co.uk/s?k=iphone&ref=nb_sb_noss
		if ($ItemPage != 1) {
			
			$search_url .= "&page=$ItemPage";
		}
		
		echo $search_url;
		
		// curl get
		$x = 'error';
		$url = $search_url;
		curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
		curl_setopt ( $this->ch, CURLOPT_URL, trim ( $url ) );
		curl_setopt ( $this->ch, CURLOPT_HTTPHEADER, 'accept-encoding: utf-8' );
		curl_setopt ( $this->ch, CURLOPT_ENCODING, "" );
		
		$exec = curl_exec ( $this->ch );
		$x = curl_error ( $this->ch );
		
	
		
		// validate returned content
		if (trim ( $exec ) == '' || trim ( $x ) != '') {
			throw new Exception ( 'No valid reply returned from Amazon with a possible cURL err ' . $x );
		}
		
		// validate products found
		if (! stristr ( $exec, 'data-asin' )) {
			// throw new Exception('No items found') ;
			echo '<br>No items found';
			
			echo $exec;
			
			return array ();
		}
		
		
		// extract products
		preg_match_all ( '{data-asin="(.*?)"}', $exec, $productMatchs );
		$asins = $productMatchs [1];
		
		// last page
		if (stristr ( $exec, 'proceedWarning' )) {
			echo '<br>Reached end page of items';
			return array ();
		}
		
		// next page flag
		$possible_next_page = $ItemPage + 1;
		if (stristr ( $exec, 'page=' . $possible_next_page . '&' ))
			$this->is_next_page_available = true;
		
		return ($asins);
	}
	
	/**
	 * Return list of ASINs of items by scraping the page
	 *
	 * @param string $pageUrl
	 * @param string $ch
	 *        	curl handler
	 */
	public function getASINs($moreUrl ) {
		
		//save timing limit
		sleep(rand(3,5));
		
		// curl get
		$x = 'error';
		$url = $moreUrl;
		curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
		curl_setopt ( $this->ch, CURLOPT_URL, trim ( $url ) );
		curl_setopt ( $this->ch, CURLOPT_ENCODING, "" );
		
		$exec = curl_exec ( $this->ch );
		$x = curl_error ( $this->ch );
		
	 
		// validate reply
		if (trim ( $exec ) == '') {
			throw new Exception ( 'Empty reply from Amazon with possible curl error ' . $x );
		}
		
		// Capacha check
		if(stristr($exec,'/captcha/')){
			echo '<br>Amazon asked for Capacha..  will die now and try next run.';
			exit;
		}
		
		// validate products found
		if (! stristr ( $exec, 'data-asin' )) {
			// throw new Exception('No items found') ;
			echo '<br>No items found';
			
			echo $exec;
			
			return array ();
		}
		
		//dom  data-index
		// dom
		$doc = new DOMDocument ();
		@$doc->loadHTML ( $exec );
		
		$xpath = new DOMXpath ( $doc );
		
		// title
		$elements = $xpath->query ( '//*[@data-index]' );
		
		$all_valid_items_html = '';
		
		foreach($elements as $single_asin_element){
			
			$item_html = $doc->saveHtml( $single_asin_element) ;
			
			if(! stristr($item_html , 'a-row a-spacing-micro' ) && stristr($item_html,'a-price-whole')){
				$all_valid_items_html.= $item_html;
			}
		 	 
		}
		
		 
		// extract products
		preg_match_all ( '{data-asin="(.*?)"}', $all_valid_items_html, $productMatchs );
		$asins = $productMatchs [1];
		 
		
		if (stristr ( $exec, 'proceedWarning' )) {
			echo '<br>Reached end page of items';
			return array ();
		}
		 
		return ($asins);
	}
}

?>