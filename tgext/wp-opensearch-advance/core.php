<?php

class Wpos_Code
{
    private $option;
    private static $instance;

    public static function instance()
    {
        if (self::$instance) {
            return self::$instance;
        } else {
            self::$instance = new self();
            return self::$instance;
        }
    }

    public function init()
    {
        add_action('wp_head', array($this, 'wpos_metatag') );
//Add ajax to logined user, just for test
add_action('wp_ajax_opensearch_description', array($this, 'getOpensearchDescription'));
add_action('wp_ajax_opensearch_suggest', array($this, 'getOpensearchSuggest'));
        //Add ajax for non-logined user, browser
add_action('wp_ajax_nopriv_opensearch_description', array($this, 'getOpensearchDescription'));
add_action('wp_ajax_nopriv_opensearch_suggest', array($this, 'getOpensearchSuggest'));
    }

public function wpos_metatag()
{
    $options = new scbOptions('wpos_options', __FILE__);
    echo '<link rel="search" href="' . get_bloginfo ( 'url' ) . '/opensearch.xml" type="application/opensearchdescription+xml" title="' . $options->get("short_name") . '"/>';
}

public function getOpensearchDescription()
{
    require_once dirname( __FILE__ )."/inc/OpenSearch.php";
    //header("Content-type: application/opensearchdescription+xml");
    header("Content-type: application/xml");
    $options = new scbOptions('wpos_options', __FILE__);
    $OpenSearch = new OpenSearch();

    $OpenSearch->setShortName($options->get("short_name"));
    $OpenSearch->setDescription($options->get("description"));

    $OpenSearch -> addUrl ( get_bloginfo ( 'url' ) . '?s={searchTerms}' );
    $OpenSearch -> addUrl ( get_bloginfo ( 'url' ) . '?feed=rss2&s={searchTerms}', 'application/rss+xml' );
    $OpenSearch -> addUrl ( get_bloginfo ( 'url' ) . '?feed=atom&s={searchTerms}', 'application/atom+xml' );
    if($options->get('haveSsuggestion',false))
    {
        $OpenSearch->addUrl(get_bloginfo ( 'url' ).'/opensearchsuggest?s={searchTerms}', 'application/x-suggestions+json');
    }
    $OpenSearch->setLongName($options->get("long_name"));
    $OpenSearch->setExampleQuery($options->get("example_query"));
    $OpenSearch->setContact($options->get("contact"));
    $OpenSearch->setTags($options->get("tags"));
    $OpenSearch->setDeveloper("WP OpenSearch - Sen Viet");
    $OpenSearch->setAttribution($options->get("attribution"));
    $OpenSearch->setSyndicationRight($options->get("syndication_right"));
    $OpenSearch->setAdultContent($options->get("adult_content"));
    $OpenSearch->setInputEncoding(get_bloginfo('charset'));
    $OpenSearch->setOutputEncoding(get_bloginfo('charset'));
    $OpenSearch->setSmallIcon($options->get("image"));
    $OpenSearch->setLanguage(get_bloginfo('language'));

    echo $OpenSearch->toXML();
    die();
}

public function getOpensearchSuggest()
{
    header("Content-type: application/x-suggestions+json");
    $data = array();
    $titles = array();
    $urls = array();
    $resultCounts = array();
    $options = new scbOptions('wpos_options', __FILE__);
    if($options->get("haveSsuggestion",false)) {

        if (isset($_GET['s']) and strlen($_GET['s'])){
            $keyword = esc_attr($_GET['s']);
            $args = array('s' => $keyword);
            $query = new WP_Query($args);
            if($query->have_posts()){
                while($query->have_posts()){
                    $query->the_post();
                    $titles[] = get_the_title();
                    $resultCounts[] = 1;
                    $urls[] = get_permalink();
                }
                $data[] = $keyword;
                $data[] = $titles;
                $data[] = $resultCounts;
                $data[] = $urls;
            }
        }
    }
    echo json_encode($data);
    die();
}

public function wpos_activate()
{
    add_rewrite_rule('opensearch.xml', '/wp-admin/admin-ajax.php?action=opensearch_description', 'top');
    add_rewrite_rule('opensearchsuggest', '/wp-admin/admin-ajax.php?action=opensearch_suggest', 'top');
    flush_rewrite_rules();
}

public function wpos_deactivate()
{
    flush_rewrite_rules();
}
}