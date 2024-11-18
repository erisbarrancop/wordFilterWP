<?php 

/*

    Plugin Name: Word Filter Plugin
    Description: Replaces a list of words.
    Version: 1.0
    Author: eris
    Author URI: #

*/

if ( ! defined('ABSPATH') ) exit;

class WordFilterPlugin {

    function __construct() {
        add_action('admin_menu', array( $this,'ourMenu') );
        add_action('admin_init',array($this, 'ourSettings')); 
        if(get_option('plugin_words_to_filter')) add_filter('the_content',array($this, 'filterLogic'));
    }
    
    function ourSettings(){
        add_settings_section('replacement-text-section', null, null, 'word-filter-options');
        register_setting('replacementFields','replacementText');
        add_settings_field('replacement-text', 'Filtered Text', array($this, 'replacementFieldHTML'),'word-filter-options', 'replacement-text-section');
    }

    function replacementFieldHTML(){ ?>

        <input type="text" name="replacementText" value="<?php echo esc_attr(get_option('replacementText', '***')) ?>">
        <p class="description">Leave blank to remove the words instead!</p>

    <?php }

    function filterLogic($content){
        $badWords = explode(',', get_option('plugin_words_to_filter'));
        $badWordsTrimmed = array_map('trim', $badWords);
        return str_ireplace($badWordsTrimmed, esc_html(get_option('replacementText', '****')), $content);
    }

    function ourMenu(){
        $mainPageHook = add_menu_page('Words to Filter','Word Filter','manage_options','ourwordfilter',array( $this,'wordFilterPage'), 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IS0tIFVwbG9hZGVkIHRvOiBTVkcgUmVwbywgd3d3LnN2Z3JlcG8uY29tLCBHZW5lcmF0b3I6IFNWRyBSZXBvIE1peGVyIFRvb2xzIC0tPgo8c3ZnIGZpbGw9IiMwMDAwMDAiIHdpZHRoPSI4MDBweCIgaGVpZ2h0PSI4MDBweCIgdmlld0JveD0iMCAwIDM2IDM2IiB2ZXJzaW9uPSIxLjEiICBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWlkWU1pZCBtZWV0IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4KICAgIDx0aXRsZT5maWx0ZXItb2ZmLXNvbGlkPC90aXRsZT4KICAgIDxwYXRoIGQ9Ik0yMy45LDE4LjZMMTAuMyw1LjFoMjIuMkMzMy4zLDUsMzQsNS42LDM0LDYuNGMwLDAsMCwwLDAsMC4xdjEuMWMwLDAuNS0wLjIsMS0wLjYsMS40TDIzLjksMTguNnoiIGNsYXNzPSJjbHItaS1zb2xpZCBjbHItaS1zb2xpZC1wYXRoLTEiPjwvcGF0aD48cGF0aCBkPSJNMzMuNSwzMUw0LjEsMS42TDIuNiwzbDIuMSwyLjFIMy41QzIuNyw1LDIsNS42LDIsNi40YzAsMCwwLDAsMCwwLjF2MS4xYzAsMC41LDAuMiwxLDAuNiwxLjRMMTQsMjAuNXYxMC4xbDgsMy40VjIyLjQKCWwxMC4xLDEwLjFMMzMuNSwzMXoiIGNsYXNzPSJjbHItaS1zb2xpZCBjbHItaS1zb2xpZC1wYXRoLTIiPjwvcGF0aD4KICAgIDxyZWN0IHg9IjAiIHk9IjAiIHdpZHRoPSIzNiIgaGVpZ2h0PSIzNiIgZmlsbC1vcGFjaXR5PSIwIi8+Cjwvc3ZnPg==', 100);
        add_submenu_page('ourwordfilter','Words to Filter','Words List','manage_options','ourwordfilter',array( $this,'wordFilterPage') );
        add_submenu_page('ourwordfilter','Word Filter Options','Options','manage_options','word-filter-options',array( $this,'optionsSubPage') );
        add_action("load-{$mainPageHook}", array($this, 'mainPageAssets'));
    }

    function mainPageAssets(){
        wp_enqueue_style('filterAdminCss', plugin_dir_url(__FILE__) . 'styles.css');
    }

    function handleForm(){
        
        if(wp_verify_nonce($_POST['ourNonce'], 'saveFilterWords') AND current_user_can('manage_options')){
            update_option('plugin_words_to_filter', sanitize_text_field($_POST['plugin_words_to_filter'])); ?>
            <div class="updated">
                <p>Your filtered words were saved.</p>
            </div>
        <?php }else{ ?>
            <div class="error">
                <p>Sorry you don't have permission to do that. </p>
            </div>
        <?php }

    }

    function wordFilterPage(){ ?>
        <div class="wrap">
            <h1>Word Filter</h1>
            <?php if (isset($_POST['justsubmitted']) == "true") $this->handleForm() ?>
            <form method="POST">
                <input type="hidden" name="justsubmitted" value="true">
                <?php wp_nonce_field('saveFilterWords', 'ourNonce') ?>
                <label for="plugin_words_to_filter"><p>Enter a <strong>comma-separated</strong> list of words to filter from your site's content.</p></label>
                <div class="word-filter__flex-container">
                    <textarea name="plugin_words_to_filter" id="plugin_words_to_filter" placeholder="bad, mean, awful, ugly"><?php echo esc_textarea(get_option('plugin_words_to_filter')) ?></textarea>
                </div>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </form>
        </div>
    <?php }

    function optionsSubPage(){?>
        <div class="wrap">
            <h1>Word Filter Options</h1>
            <form action="options.php" method="POST">
                <?php 
                    settings_errors();
                    settings_fields('replacementFields');
                    do_settings_sections('word-filter-options');
                    submit_button();
                ?>
            </form>
        </div>
    <?php }

}

$wordFilterPlugin = new WordFilterPlugin();