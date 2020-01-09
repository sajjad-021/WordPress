<?php

// modify camp on the go
$campaign->camp_type = 'Feeds';
$campaign->camp_sub_type = 'Multi';

$cg_ml_cache = $camp_general ['cg_ml_cache']; // Cache enabled or disabled
$cg_ml_cnt_method = $camp_general ['cg_ml_cnt_method']; // pagination "auto" = no pagination
$wp_automatic_cache = get_post_meta ( $campaign->camp_id, 'wp_automatic_cache', 1 ); // cached links feed URL
$wp_automatic_nextpage = get_post_meta ( $campaign->camp_id, 'wp_automatic_nextpage', 1 ); // cached links feed URL


if( version_compare(PHP_VERSION, '5.6.3') < 0 ){
	echo '<br><span style="color:red">You must upgrade your PHP version to use this module. Now you are using ' . PHP_VERSION . ' minimum is 5.6.3</span>' ;
}


// cache enabled and there are cached items
if ($cg_ml_cache == 'enabled' && trim ( $wp_automatic_cache ) != '') {
	echo '<br>Cache found, loading items from the cache....';
	
	// injecting
	$campaign->feeds = $wp_automatic_cache;
	
} else {
	
	// loading the source URL
	$cg_ml_source = $camp_general ['cg_ml_source'];
	
	// main domain
	$pars = parse_url ( $cg_ml_source );
	$host = $pars ['host'];
	$the_path = $pars ['path'];
	
	if(trim($the_path) != ''){
		$the_path = preg_replace( '{/[^/]*$}' , ''  ,  $the_path ) ;
	}
	
	$http_prefix = stristr ( $cg_ml_source, 'https' ) ? 'https:' : 'http:';
	$host = stristr ( $cg_ml_source, 'https' ) ? 'https://' . $host : 'http://' . $host;
	
	// if pagination, load the pagination URL instead
	if (trim ( $cg_ml_cnt_method ) != 'auto' && trim ( $wp_automatic_nextpage ) != '' && $cg_ml_cache == 'enabled') {
		echo '<br>Pagination URL found, using it';
		$cg_ml_source = $wp_automatic_nextpage;
		
		if (! stristr ( $cg_ml_source, 'http' )) {
			
			if (stristr ( $cg_ml_source, '//' )) {
				
				$cg_ml_source = $http_prefix . $cg_ml_source;
			} else {
				
				if (substr ( $cg_ml_source, 0, 1 ) != '/')
					$cg_ml_source = '/' . $cg_ml_source;
				
				$cg_ml_source = $host . $cg_ml_source;
			}
		}
	}
	
	$cg_ml_lnk_method = $camp_general ['cg_ml_lnk_method'];
	
	// validate link existence
	if (trim ( $cg_ml_source ) == '') {
		echo '<br>No source found, please add the source URL and try again';
		return false;
	}
	
	// curl get
	echo '<br>Loading:' . $cg_ml_source;
	
	
	
	
	// curl ini
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
	curl_setopt ( $ch, CURLOPT_REFERER, 'http://www.bing.com/' );
	curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8' );
	curl_setopt ( $ch, CURLOPT_MAXREDIRS, 5 ); // Good leeway for redirections.
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 ); // Many login forms redirect at least once.
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, "cookie.txt" );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
	//proxy check
	if( in_array('OPT_USE_PROXY', $camp_opt) ){
		
		echo '<br>Proxy check needed';
		
		$proxies = get_option ( 'wp_automatic_proxy' );
		if (stristr ( $proxies, ':' )) {
			echo '<br>Proxy Found lets try';
			// listing all proxies
			
			$proxyarr = explode ( "\n", $proxies );
			
			foreach ( $proxyarr as $proxy ) {
				if (trim ( $proxy ) != '') {
					
					$auth = '';
					if (substr_count ( $proxy, ':' ) == 3) {
						echo '<br>Private proxy found .. using authentication';
						$proxy_parts = explode ( ':', $proxy );
						
						$proxy = $proxy_parts [0] . ':' . $proxy_parts [1];
						$auth = $proxy_parts [2] . ':' . $proxy_parts [3];
						
						curl_setopt ( $ch, CURLOPT_PROXY, trim ( $proxy ) );
						curl_setopt ( $ch, CURLOPT_PROXYUSERPWD, trim($auth) );
					} else {
						curl_setopt ( $ch, CURLOPT_PROXY, trim ( $proxy ) );
					}
					
					echo "<br>Trying using proxy :$proxy";
					
					curl_setopt ( $ch, CURLOPT_HTTPPROXYTUNNEL, 1 );
					
					curl_setopt ( $ch, CURLOPT_URL, 'www.bing.com/search?count=50&intlF=1&mkt=En-us&first=0&q=test' );
					// curl_setopt($ch, CURLOPT_URL, 'http://whatismyipaddress.com/');
					$exec = curl_exec ( $ch );
					$x = curl_error ( $ch );
					
					if ( trim($x) != '' ) {
						echo '<br>Curl Proxy Error:' . curl_error ( $ch );
					} else {
						
						if (stristr ( $exec, 'It appears that you are using a Proxy' ) || stristr ( $exec, 'excessive amount of traffic' )) {
							echo '<br>Proxy working but captcha met let s skip it';
						} elseif (stristr ( $exec, 'microsoft.com' )) {
							
							// succsfull connection here
							//   echo curl_exec($ch);
							// reordering the proxy
							$proxies = str_replace ( ' ', '', $proxies );
							
							if(trim($auth) != '')
								$proxy = $proxy.':'.$auth;
								
								$proxies = str_replace($proxy, '', $proxies);
								
								$proxies = str_replace ( "\n\n", "\n", $proxies );
								$proxies = "$proxy\n$proxies";
								//   echo $proxies;
								update_option ( 'wp_automatic_proxy', $proxies );
								
								echo '<br>Connected successfully using this proxy ';
								
								break;
								
						 
						} else {
							
							echo '<br>Proxy Reply:'.$exec;
							
						}
					}
				}
			}
			
		 
		 
			
			// proxifing the connection
		}else{
			echo '..No proxies';
		}
	 
	}
	
	
	
	if(in_array('OPT_FEED_ENCODING', $camp_opt )){
	
		curl_setopt( $ch, CURLOPT_ENCODING , "");
		
	}
	
	
	
	//cookie
	$cg_sn_cookie=$camp_general['cg_ml_cookie'];
	
	if(trim($cg_sn_cookie) != ''){
		$headers[] = "Cookie: $cg_sn_cookie ";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	
	$x = 'error';
	curl_setopt ( $ch, CURLOPT_HTTPGET, 1 );
	curl_setopt ( $ch, CURLOPT_URL, trim ( $cg_ml_source ) );
	$exec = curl_exec ( $ch );
	$x = curl_error ( $ch );
	
	// validate response
	if (trim ( $exec ) == '') {
		echo '<br>Did not return valid content ' . $x;
		return false;
	}
	
	// loading the dom
	require_once 'inc/class.dom.php';
	$wpAutomaticDom = new wpAutomaticDom ( $exec );
	
	// debug
	$debug = false;
	
	if (isset ( $_GET ['debug'] ))
		$debug = true;
	
	$wpAutomaticDom->debug = $debug; // debug
	
	$found_links = array (); // ini
	$found_titles = array ();
	
	if ($cg_ml_lnk_method == 'visual') {
		
		$cg_ml_lnk_visual = $camp_general ['cg_ml_lnk_visual'];
		$path = isset ( $cg_ml_lnk_visual [0] ) ? $cg_ml_lnk_visual [0] : '';
		
		if (trim ( $path ) == '') {
			echo '<br>No path found, please set the extraction rule for the first item link';
			return false;
		}
		
		foreach ( $cg_ml_lnk_visual as $path ) {
			
			echo '<br>Getting links for XPath:' . $path;
			
			try {
				$found_matches = ($wpAutomaticDom->getSimilarLinks ( $path ));
				$found_links_new = $found_matches [0];
				$found_titles_new = $found_matches [1];
				
				if (count ( $found_links_new ) > 0) {
					$found_links = array_merge ( $found_links, $found_links_new );
					$found_titles = array_merge ( $found_titles, $found_titles_new );
				}
			} catch ( Exception $e ) {
				echo '<br>Error:' . $e->getMessage ();
			}
		}
	} elseif ($cg_ml_lnk_method == 'css') {
		
		$cg_ml_lnk_css_type = $camp_general ['cg_ml_lnk_css_type'];
		$cg_ml_lnk_css = $camp_general ['cg_ml_lnk_css'];
		$cg_ml_lnk_css_wrap = $camp_general ['cg_ml_lnk_css_wrap'];
		$cg_ml_lnk_css_size = $camp_general ['cg_ml_lnk_css_size'];
		$finalContent = '';
		
		$i = 0;
		foreach ( $cg_ml_lnk_css_type as $singleType ) {
			
			if ($cg_ml_lnk_css_wrap [$i] == 'inner') {
				$inner = true;
			} else {
				$inner = false;
			}
			
			echo '<br>Extracting content by ' . $cg_ml_lnk_css_type [$i] . ' : ' . $cg_ml_lnk_css [$i];
			
			if ($cg_ml_lnk_css_type [$i] == 'class') {
				$content = $wpAutomaticDom->getContentByClass ( $cg_ml_lnk_css [$i], $inner );
		  
			} elseif ($cg_ml_lnk_css_type [$i] == 'id') {
				$content = $wpAutomaticDom->getContentByID ( $cg_ml_lnk_css [$i], $inner );
			} elseif ($cg_ml_lnk_css_type [$i] == 'xpath') {
				$content = $wpAutomaticDom->getContentByXPath ( stripslashes ( $cg_ml_lnk_css [$i] ), $inner );
			}
			
			if (is_array ( $content )) {
				
				if ($cg_ml_lnk_css_size [$i] == 'single') {
					$content = $content [0];
				} else {
					$content = implode ( "\n", $content );
				}
				
				$finalContent .= $content . "\n";
			}
			
			echo '<-- ' . strlen ( $content ) . ' chars';
			
			$i ++;
		} // foreach rule
	} elseif ($cg_ml_lnk_method == 'regex') {
		
		$cg_ml_lnk_regex = $camp_general ['cg_ml_lnk_regex'];
		$finalContent = '';
		$i = 0;
		foreach ( $cg_ml_lnk_regex as $cg_ml_lnk_regex_s ) {
			
			echo '<br>Extracting content by REGEX : ' . htmlentities ( stripslashes ( $cg_ml_lnk_regex_s ) );
			
			$content = $wpAutomaticDom->getContentByRegex ( stripslashes ( $cg_ml_lnk_regex_s ) );
			
			$content = implode ( "\n" , $content );
			
			echo '<-- ' . strlen ( $content ) . ' chars';
			
			if (trim ( $content ) != '') {
				$finalContent .= $content . "\n";
			}
			
			$i ++;
		}
	}
	
	if ($cg_ml_lnk_method == 'css' || $cg_ml_lnk_method == 'regex') {
		
		// get links
		 preg_match_all ( '{href\s*?=\s*?["|\'](.*?)["|\'].*?>(.*?)</a>}s', $finalContent, $link_matches );

		 if(count( $link_matches [1]) == 0 ){
		 	preg_match_all ( '{(http[s]?://.*?)[\s|\'|"]}s', $finalContent, $link_matches );
		 	
		 	$found_links = $found_titles = $link_matches [1];
		 	
		 }else{
			  
			$found_links = $link_matches [1];
			$found_titles = $link_matches [2];
		
		 }
			
		foreach ( $found_titles as $key => $found_title ) {
			$found_titles [$key] = strip_tags ( $found_title );
		}
		
		// uniqueness considering titles
		$found_links_unique = array_unique ( $found_links );
		$found_titles_unique = array ();
		
		//print_r ( $found_links_unique );
		foreach ( $found_links_unique as $key_unique => $found_link_unique ) {
			$found_titles_unique [$key_unique] = '';
			foreach ( $found_links as $key => $found_link ) {
				if ($found_link_unique == $found_link) {
					
					if ($found_titles [$key] != '') {
						
						// a title found
						$found_titles_unique [$key_unique] = $found_titles [$key];
						break;
					}
				}
			}
		}
		
		$found_links = array_values ( $found_links_unique );
		$found_titles = array_values ( $found_titles_unique );
	}
	
	// building the feed
	if (isset ( $found_links ) && count ( $found_links ) > 0) {
		
		// Pagination URL
		
		$finalContent = '';
		
		if ($cg_ml_cnt_method == 'visual') {
			
			$cg_ml_cnt_visual = $camp_general ['cg_ml_cnt_visual'];
			$path = isset ( $cg_ml_cnt_visual [0] ) ? $cg_ml_cnt_visual [0] : '';
			
			if (trim ( $path ) == '') {
				echo '<br>No path found for pagination, please set the extraction rule for pagination if you want to make use of pagination.';
			}
			
			foreach ( $cg_ml_cnt_visual as $xpath ) {
				
				// good we have the a tag if it is not the last one, make it the last
				if (! preg_match ( '{/a$}', $xpath ) && (stristr ( $xpath, '/a/' ) || stristr ( $xpath, '/a[' ))) {
					
					if (! stristr ( $xpath, '/a[' )) {
						$xpathParts = explode ( '/a/', $xpath );
						$lastPartIndex = count ( $xpathParts ) - 1;
						unset ( $xpathParts [$lastPartIndex] );
						$xpath = implode ( '/a/', $xpathParts ) . '/a';
					} else {
						$xpath = preg_replace ( '!(a\[\d*?\]).*!', "$1", $xpath );
					}
				}
				
				echo '<br>Getting link for XPath:' . $path;
				
				try {
					
					$finalContent = $wpAutomaticDom->getContentByXPath ( $xpath, false );
					
					if (is_array ( $finalContent ))
						$finalContent = implode ( '', $finalContent );
					
					echo '<-- ' . strlen ( $finalContent ) . ' chars';
				} catch ( Exception $e ) {
					echo '<br>Error:' . $e->getMessage ();
				}
			}
		} elseif ($cg_ml_cnt_method == 'css') {
			
			$cg_ml_cnt_css_type = $camp_general ['cg_ml_cnt_css_type'];
			$cg_ml_cnt_css = $camp_general ['cg_ml_cnt_css'];
			$cg_ml_cnt_css_wrap = $camp_general ['cg_ml_cnt_css_wrap'];
			$cg_ml_cnt_css_size = $camp_general ['cg_ml_cnt_css_size'];
			$finalContent = '';
			
			$i = 0;
			foreach ( $cg_ml_cnt_css_type as $singleType ) {
				
				if ($cg_ml_cnt_css_wrap [$i] == 'inner') {
					$inner = true;
				} else {
					$inner = false;
				}
				
				echo '<br>Extracting content by ' . $cg_ml_cnt_css_type [$i] . ' : ' . $cg_ml_cnt_css [$i];
				
				if ($cg_ml_cnt_css_type [$i] == 'class') {
					$content = $wpAutomaticDom->getContentByClass ( $cg_ml_cnt_css [$i], $inner );
				 
				} elseif ($cg_ml_cnt_css_type [$i] == 'id') {
					$content = $wpAutomaticDom->getContentByID ( $cg_ml_cnt_css [$i], $inner );
				} elseif ($cg_ml_cnt_css_type [$i] == 'xpath') {
					$content = $wpAutomaticDom->getContentByXPath ( stripslashes ( $cg_ml_cnt_css [$i] ), $inner );
				}
				
				if (is_array ( $content )) {
					
					if ($cg_ml_cnt_css_size [$i] == 'single') {
						$content = $content [0];
					} else {
						$content = implode ( "\n", $content );
					}
					
					$finalContent .= $content . "\n";
				}
				
				echo '<-- ' . strlen ( $content ) . ' chars';
				
				$i ++;
			} // foreach rule
		} elseif ($cg_ml_cnt_method == 'regex') {
			
			$cg_ml_cnt_regex = $camp_general ['cg_ml_cnt_regex'];
			$finalContent = '';
			$i = 0;
			foreach ( $cg_ml_cnt_regex as $cg_ml_cnt_regex_s ) {
				
				echo '<br>Extracting content by REGEX : ' . htmlentities ( stripslashes ( $cg_ml_cnt_regex_s ) );
				
				$content = $wpAutomaticDom->getContentByRegex ( stripslashes ( $cg_ml_cnt_regex_s ) );
				
				$content = implode ( $content );
				
				echo '<-- ' . strlen ( $content ) . ' chars';
				
				if (trim ( $content ) != '') {
					$finalContent .= $content . "\n";
				}
				
				$i ++;
			}
		}
		
		// get pagination link
		
		if (trim ( $finalContent ) != '') {
			
			  
			
			preg_match_all ( '{href\s*?=\s*?["|\'](.*?)["|\'].*?>(.*?)</a>}s', $finalContent, $link_matches );
			
			$found_links_pagination = $link_matches [1];
			
			if (isset ( $found_links_pagination [0] ) && trim ( $found_links_pagination [0] ) != '') {
				
				$found_links_pagination [0] = str_replace ( '&amp;', '&', $found_links_pagination [0] );
				
				echo '<br>Pagination next page URL:' . $found_links_pagination [0];
				update_post_meta ( $campaign->camp_id, 'wp_automatic_nextpage', $found_links_pagination [0] );
			
			}elseif( preg_match('{^http}' , trim($finalContent)  ) ){ 
				
				echo '<br>Pagination next page URL:' . trim($finalContent);
				update_post_meta ( $campaign->camp_id, 'wp_automatic_nextpage', trim($finalContent) );
			
			} else {
				echo '<br>Can not find next page URL, resetting page index';
				delete_post_meta ( $campaign->camp_id, 'wp_automatic_nextpage' );
			}
		} else {
			echo '<br>No pagination info';
			delete_post_meta ( $campaign->camp_id, 'wp_automatic_nextpage' );
		}
		
		// good we have links, let us build a feed
		echo '<br>Approved found links: ' . count ( $found_links );
		
		// building feed content
		$i = 0;
		$rss = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
  <title>W3Schools Home Page</title>
  <link>https://www.w3schools.com</link>
  <description>Free web building tutorials</description>';
		
		foreach ( $found_links as $found_link ) {
			
			$found_title = htmlspecialchars ( $found_titles [$i] );
			
			if (! stristr ( $found_link, 'http' )) {
				
				if (stristr ( $found_link, '//' )) {
					$found_link = $http_prefix . $found_link;
				} else {
					
					
					if( preg_match ( '{^/}' ,  $found_link ) ){ 
						$found_link = $host  . $found_link;
					}else{
						
						if(trim ($the_path) != ''){
							$found_link = $host . $the_path .  '/' . $found_link;
						}else{
							$found_link = $host . '/' . $found_link;
						}
						
						
					}
					
					
					 
				}
			}
			
			$skipped_link = htmlspecialchars ( $found_link );
			
			if( trim($found_title) == '' )  $found_title = $skipped_link;
			
			$rss .= "
		<item>
		    <title>$found_title</title>
		    <link>$skipped_link</link>
		    <description>$found_title</description>
	    </item>";
			
			if ($debug)
				echo '<br>link#' . $i . ': ' . $found_link;
			
			$i ++;
		}
		
		$rss .= '
</channel>
</rss>';
		
		$upload_dir = wp_upload_dir ();
		$fname = md5 ( $cg_ml_source ) . '_' . $campaign->camp_id . '.xml';
		$filePath = $upload_dir ['basedir'] . '/' . $fname;
		$fileUrl = $upload_dir ['baseurl'] . '/' . $fname;
		file_put_contents ( $filePath, $rss );
		
		// Save cache URL
		update_post_meta ( $campaign->camp_id, 'wp_automatic_cache', $fileUrl );
		
		echo '<br>Generated feed URL:' . $fileUrl;
		
		// injecting
		$campaign->feeds = $fileUrl;
	} else {
		// No links found pagination and cache handling goes here. lets destroy them.
		echo '<br>No links were found';
		
		delete_post_meta ( $campaign->camp_id, 'wp_automatic_nextpage' );
		delete_post_meta ( $campaign->camp_id, 'wp_automatic_cache' );
	}
}

// feeds class
require_once 'core.feeds.php';

 