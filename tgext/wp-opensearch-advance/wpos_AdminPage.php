<?php
/**
 * Project : 355_wp_Opensearch
 * User: thuytien
 * Date: 8/31/2014
 * Time: 2:55 PM
 */
class WP_OS_Admin_Page extends scbBoxesPage {
    private $settings_fields;
    function setup() {
$this->args = array(
    'page_title' => 'OpenSearch Setting',
    'menu_title' => 'OpenSearch',
    'action_link' => 'Setting'
);
$this->boxes = array(
    // id, title, column
    array('settings', 'Settings Box', 'normal'),
    array('right1', 'Hướng dẫn lập trình', 'side'),
    array('right2', 'Hướngd ẫn lập trình', 'side'),
);
$this->settings_fields = array(
    array(
        'title' => 'Short name',
        'name' => 'short_name',
        'type' => 'text',
        'desc' => "<br>". __( 'Contains a brief human-readable title that identifies this search engine.', $this->textdomain )
    ),
    array(
        'title' => 'Long name',
        'name' => 'long_name',
        'type' => 'text',
        'desc' => "<br>". __( 'Contains an extended human-readable title that identifies this search engine.', $this->textdomain )
    ),
    array(
        'title' => 'Tags',
        'name' => 'tags',
        'type' => 'text',
        'desc' => "<br>". __( 'Contains a set of words that are used as keywords to identify and categorize this search content. Tags must be a single word and are delimited by the space character.', $this->textdomain )
    ),
    array(
        'title' => 'Description',
        'name' => 'description',
        'type' => 'text',
        'desc' => "<br>". __( 'Contains a human-readable text description of the search engine.', $this->textdomain )
    ),
    array(
        'title' => 'Image',
        'name' => 'image',
        'type' => 'text',
        'desc' => "<br>". __( 'Contains a URL that identifies the location of an image that can be used in association with this search content.16x16 image of type "image/x-icon" or "image/vnd.microsoft.icon" and a 64x64 image of type "image/jpeg" or "image/png".', $this->textdomain )
    ),
    array(
        'title' => 'Example Query',
        'name' => 'example_query',
        'type' => 'text',
        'desc' => "<br>". __( 'Defines a search query that can be performed by search clients, should include at least one Query element of role="example" that is expected to return search results. Search clients may use this example query to validate that the search engine is working properly.', $this->textdomain )
    ),
    array(
        'title' => 'Attribution',
        'name' => 'attribution',
        'type' => 'text',
        'desc' => "<br>". __( 'Contains a list of all sources or entities that should be credited for the content contained in the search feed.', $this->textdomain )
    ),
    array(
        'title' => 'Contact',
        'name' => 'contact',
        'type' => 'text',
        'desc' => "<br>". __( 'Contains an email address at which the maintainer of the description document can be reached.', $this->textdomain )
    ),
    array(
        'title' => 'Syndication Right',
        'name' => 'syndication_right',
        'type' => 'select',
        'value' => array('open', 'limited', 'private', 'closed'),
        'desc' => "<br>". __( 'Contains a value that indicates the degree to which the search results provided by this search engine can be queried, displayed, and redistributed.', $this->textdomain )
    ),
    array(
        'title' => 'Suggess',
        'name' => 'haveSsuggestion',
        'type' => 'checkbox',
        'desc' => "<br>". __( 'If it checked, that you allow user to get suggest whent they type a words.', $this->textdomain )
    ),
    array(
        'title' => 'Adult Content',
        'name' => 'adult_content',
        'type' => 'checkbox',
        'desc' => "<br>". __( 'Contains a boolean value that should be set to true if the search results may contain material intended only for adults.', $this->textdomain )
    ),
    array(
        'title' => 'Autodiscovery',
        'name' => 'autodiscovery',
        'type' => 'checkbox',
        'desc' => "<br>". __( 'Search engines that publish OpenSearch description documents can assist search clients in the discovery of OpenSearch interfaces through the use of "link" elements. Search engines that support OpenSearch should include a reference to the related OpenSearch description document on each page of search results.', $this->textdomain )
    )
);
    }
function settings_handler()
{
    $to_update = scbForms::validate_post_data($this->settings_fields);
    $this->options->update($to_update);
    add_action( 'admin_notices', array( $this, 'admin_msg' ) );
    return true;
}
function settings_box()
{
    $out = $this->table( $this->settings_fields);
    echo $this->form_wrap($out, 'Save changes', 'save_changes');
}
    function right1_box()
    {
        echo '<div class="rss-widget">';
        echo '<p>If you are still not clear about the settings, please visit <a href="http://www.opensearch.org/Home"> OpenSearch</a> document for more detailed information.</p><p>To get support, you can send a stick here : <a href="http://laptrinh.senviet.org/wordpress-plugin/viet-plugin-th…-vao-wordpress/ ‎">VIẾT PLUGIN THÊM OPENSEARCH VÀO WORDPRESS</a> </p>';
        echo '</div>';
    }

    function right2_box()
    {
        echo '<div class="rss-widget">';
        wp_widget_rss_output("http://laptrinh.senviet.org/feed", array('items' => 3, 'show_author' => 0, 'show_date' => 0, 'show_summary' => 1));
        echo '</div>';
    }
}