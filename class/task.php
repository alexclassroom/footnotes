<?php
/**
 * Includes the core function of the Plugin - Search and Replace the Footnotes.
 *
 * @filesource
 * @author Stefan Herndler
 * @since 1.5.0
 * 
 * Edited for v2.0.0 and following.
 * 
 * Edited for v2.0.5: Autoload / infinite scroll support added thanks to code from
 * @docteurfitness <https://wordpress.org/support/topic/auto-load-post-compatibility-update/>
 * 
 * Last modified   2020-11-06T1344+0100
 */

// If called directly, abort:
defined( 'ABSPATH' ) or die;

/**
 * Looks for Footnotes short codes and replaces them. Also displays the Reference Container.
 *
 * @author Stefan Herndler
 * @since 1.5.0
 */
class MCI_Footnotes_Task {

    /**
     * Contains all footnotes found on current public page.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @var array
     */
    public static $a_arr_Footnotes = array();

    /**
     * Flag if the display of 'LOVE FOOTNOTES' is allowed on the current public page.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @var bool
     */
    public static $a_bool_AllowLoveMe = true;

    /**
     * Prefix for the Footnote html element ID.
     *
     * @author Stefan Herndler
     * @since 1.5.8
     * @var string
     */
    public static $a_str_Prefix = "";

    /**
     * Register WordPress Hooks to replace Footnotes in the content of a public page.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * 
     * Edited for v2.0.5 through v2.0.7   2020-11-02T0330+0100..2020-11-06T1344+0100
     * 
     * Explicitly setting all priority to (default) "10" instead of lowest "PHP_INT_MAX", 
     * especially for the_content, makes the footnotes reference container display
     * beneath the content and above other features added by other plugins.
     * Requested by users: <https://wordpress.org/support/topic/change-the-position-5/>
     * Documentation: <https://codex.wordpress.org/Plugin_API/#Hook_in_your_Filter>
     */
    public function registerHooks() {
        // append custom css to the header
        add_filter('wp_head', array($this, "wp_head"), 10);

        // append the love and share me slug to the footer
        add_filter('wp_footer', array($this, "wp_footer"), 10);

        if (MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_EXPERT_LOOKUP_THE_TITLE))) {
            add_filter('the_title', array($this, "the_title"), 10);
        }
        if (MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_EXPERT_LOOKUP_THE_CONTENT))) {
            add_filter('the_content', array($this, "the_content"), 10);
        }
        if (MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_EXPERT_LOOKUP_THE_EXCERPT))) {
             add_filter('the_excerpt', array($this, "the_excerpt"), 10);
        }
        if (MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_EXPERT_LOOKUP_WIDGET_TITLE))) {
            add_filter('widget_title', array($this, "widget_title"), 10);
        }
        if (MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_EXPERT_LOOKUP_WIDGET_TEXT))) {
            add_filter('widget_text', array($this, "widget_text"), 10);
        }
        if (MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_EXPERT_LOOKUP_THE_POST))) {
            add_filter('the_post', array($this, "the_post"), 10);
        }
        // reset stored footnotes when displaying the header
        self::$a_arr_Footnotes = array();
        self::$a_bool_AllowLoveMe = true;
    }

    /**
     * Outputs the custom css to the header of the public page.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     */
    public function wp_head() {
        $l_str_Color = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_MOUSE_OVER_BOX_COLOR);
        $l_str_Background = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_MOUSE_OVER_BOX_BACKGROUND);
        $l_int_BorderWidth = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_INT_FOOTNOTES_MOUSE_OVER_BOX_BORDER_WIDTH);
        $l_str_BorderColor = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_MOUSE_OVER_BOX_BORDER_COLOR);
        $l_int_BorderRadius = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_INT_FOOTNOTES_MOUSE_OVER_BOX_BORDER_RADIUS);
        $l_int_MaxWidth = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_INT_FOOTNOTES_MOUSE_OVER_BOX_MAX_WIDTH);
        $l_str_BoxShadowColor = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_MOUSE_OVER_BOX_SHADOW_COLOR);
        ?>
        <style type="text/css" media="screen">
            <?php
            echo MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_CUSTOM_CSS);
            echo '.footnote_tooltip { display: none; padding: 12px; font-size: 13px;';
            if (!empty($l_str_Color)) {
                printf(" color: %s;", $l_str_Color);
            }
            if (!empty($l_str_Background)) {
                printf(" background-color: %s;", $l_str_Background);
            }
            if (!empty($l_int_BorderWidth) && intval($l_int_BorderWidth) > 0) {
                printf(" border-width: %dpx; border-style: solid;", $l_int_BorderWidth);
            }
            if (!empty($l_str_BorderColor)) {
                printf(" border-color: %s;", $l_str_BorderColor);
            }
            if (!empty($l_int_BorderRadius) && intval($l_int_BorderRadius) > 0) {
                printf(" border-radius: %dpx;", $l_int_BorderRadius);
            }
            if (!empty($l_int_MaxWidth) && intval($l_int_MaxWidth) > 0) {
                printf(" max-width: %dpx;", $l_int_MaxWidth);
            }
            if (!empty($l_str_BoxShadowColor)) {
                printf(" -webkit-box-shadow: 2px 2px 11px %s;", $l_str_BoxShadowColor);
                printf(" -moz-box-shadow: 2px 2px 11px %s;", $l_str_BoxShadowColor);
                printf(" box-shadow: 2px 2px 11px %s;", $l_str_BoxShadowColor);
            }
            echo '}';
            ?>
        </style>
        <?php
    }

    /**
     * Displays the 'LOVE FOOTNOTES' slug if enabled.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     */
    public function wp_footer() {
        if (MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_REFERENCE_CONTAINER_POSITION) == "footer") {
            echo $this->ReferenceContainer();
        }
        // get setting for love and share this plugin
        $l_str_LoveMeIndex = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_LOVE);
        // check if the admin allows to add a link to the footer
        if (empty($l_str_LoveMeIndex) || strtolower($l_str_LoveMeIndex) == "no" || !self::$a_bool_AllowLoveMe) {
            return;
        }
        // set a hyperlink to the word "footnotes" in the Love slug
        $l_str_LinkedName = sprintf('<a href="http://wordpress.org/plugins/footnotes/" target="_blank" style="text-decoration:none;">%s</a>',MCI_Footnotes_Config::C_STR_PLUGIN_PUBLIC_NAME);
        // get random love me text
        if (strtolower($l_str_LoveMeIndex) == "random") {
            $l_str_LoveMeIndex = "text-" . rand(1,3);
        }
        switch ($l_str_LoveMeIndex) {
            case "text-1":
                $l_str_LoveMeText = sprintf(__('I %s %s', MCI_Footnotes_Config::C_STR_PLUGIN_NAME), MCI_Footnotes_Config::C_STR_LOVE_SYMBOL, $l_str_LinkedName);
                break;
            case "text-2":
                $l_str_LoveMeText = sprintf(__('this site uses the awesome %s Plugin', MCI_Footnotes_Config::C_STR_PLUGIN_NAME), $l_str_LinkedName);
                break;
            case "text-3":
            default:
                $l_str_LoveMeText = sprintf(__('extra smooth %s', MCI_Footnotes_Config::C_STR_PLUGIN_NAME), $l_str_LinkedName);
                break;
        }
        echo sprintf('<div style="text-align:center; color:#acacac;">%s</div>', $l_str_LoveMeText);
    }

    /**
     * Replaces footnotes in the post/page title.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @param string $p_str_Content Widget content.
     * @return string Content with replaced footnotes.
     */
    public function the_title($p_str_Content) {
        // appends the reference container if set to "post_end"
        return $this->exec($p_str_Content, false);
    }

    /**
     * Replaces footnotes in the content of the current page/post.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @param string $p_str_Content Page/Post content.
     * @return string Content with replaced footnotes.
     */
    public function the_content($p_str_Content) {
        // appends the reference container if set to "post_end"
        return $this->exec($p_str_Content, MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_REFERENCE_CONTAINER_POSITION) == "post_end" ? true : false);
    }

    /**
     * Replaces footnotes in the excerpt of the current page/post.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @param string $p_str_Content Page/Post content.
     * @return string Content with replaced footnotes.
     */
    public function the_excerpt($p_str_Content) {
        return $this->exec($p_str_Content, false, !MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_FOOTNOTES_IN_EXCERPT)));
    }

    /**
     * Replaces footnotes in the widget title.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @param string $p_str_Content Widget content.
     * @return string Content with replaced footnotes.
     */
    public function widget_title($p_str_Content) {
        // appends the reference container if set to "post_end"
        return $this->exec($p_str_Content, false);
    }

    /**
     * Replaces footnotes in the content of the current widget.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @param string $p_str_Content Widget content.
     * @return string Content with replaced footnotes.
     */
    public function widget_text($p_str_Content) {
        // appends the reference container if set to "post_end"
        return $this->exec($p_str_Content, MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_REFERENCE_CONTAINER_POSITION) == "post_end" ? true : false);
    }

    /**
     * Replaces footnotes in each Content var of the current Post object.
     *
     * @author Stefan Herndler
     * @since 1.5.4
     * @param array|WP_Post $p_mixed_Posts
     */
    public function the_post(&$p_mixed_Posts) {
        // single WP_Post object received
        if (!is_array($p_mixed_Posts)) {
            $p_mixed_Posts = $this->replacePostObject($p_mixed_Posts);
            return;
        }
        // array of WP_Post objects received
        for($l_int_Index = 0; $l_int_Index < count($p_mixed_Posts); $l_int_Index++) {
            $p_mixed_Posts[$l_int_Index] = $this->replacePostObject($p_mixed_Posts[$l_int_Index]);
        }
    }

    /**
     * Replace all Footnotes in a WP_Post object.
     *
     * @author Stefan Herndler
     * @since 1.5.6
     * @param WP_Post $p_obj_Post
     * @return WP_Post
     */
    private function replacePostObject($p_obj_Post) {
        //MCI_Footnotes_Convert::debug($p_obj_Post);
        $p_obj_Post->post_content = $this->exec($p_obj_Post->post_content);
        $p_obj_Post->post_content_filtered = $this->exec($p_obj_Post->post_content_filtered);
        $p_obj_Post->post_excerpt = $this->exec($p_obj_Post->post_excerpt);
        return $p_obj_Post;
    }

    /**
     * Replaces all footnotes that occur in the given content.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @param string $p_str_Content Any string that may contain footnotes to be replaced.
     * @param bool $p_bool_OutputReferences Appends the Reference Container to the output if set to true, default true.
     * @param bool $p_bool_HideFootnotesText Hide footnotes found in the string.
     * @return string
     */
    public function exec($p_str_Content, $p_bool_OutputReferences = false, $p_bool_HideFootnotesText = false) {
        // replace all footnotes in the content, settings are converted to html characters
        $p_str_Content = $this->search($p_str_Content, true, $p_bool_HideFootnotesText);
        // replace all footnotes in the content, settings are NOT converted to html characters
        $p_str_Content = $this->search($p_str_Content, false, $p_bool_HideFootnotesText);

        // append the reference container
        if ($p_bool_OutputReferences) {
            $p_str_Content = $p_str_Content . $this->ReferenceContainer();
        }

        // take a look if the LOVE ME slug should NOT be displayed on this page/post, remove the short code if found
        if (strpos($p_str_Content, MCI_Footnotes_Config::C_STR_NO_LOVE_SLUG) !== false) {
            self::$a_bool_AllowLoveMe = false;
            $p_str_Content = str_replace(MCI_Footnotes_Config::C_STR_NO_LOVE_SLUG, "", $p_str_Content);
        }
        // return the content with replaced footnotes and optional reference container append
        return $p_str_Content;
    }

    /**
     * Replaces all footnotes in the given content and appends them to the static property.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @param string $p_str_Content Content to be searched for footnotes.
     * @param bool $p_bool_ConvertHtmlChars html encode settings, default true.
     * @param bool $p_bool_HideFootnotesText Hide footnotes found in the string.
     * @return string
     */
    public function search($p_str_Content, $p_bool_ConvertHtmlChars, $p_bool_HideFootnotesText) {
        // post ID to make everything unique wrt archive view and infinite scroll
        global $l_int_PostID;
        $l_int_PostID = get_the_id();
        // contains the index for the next footnote on this page
        $l_int_FootnoteIndex = count(self::$a_arr_Footnotes) + 1;
        // contains the starting position for the lookup of a footnote
        $l_int_PosStart = 0;
        // get start and end tag for the footnotes short code
        $l_str_StartingTag = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_SHORT_CODE_START);
        $l_str_EndingTag = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_SHORT_CODE_END);
        if ($l_str_StartingTag == "userdefined" || $l_str_EndingTag == "userdefined") {
            $l_str_StartingTag = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_SHORT_CODE_START_USER_DEFINED);
            $l_str_EndingTag = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_SHORT_CODE_END_USER_DEFINED);
        }
        // decode html special chars
        if ($p_bool_ConvertHtmlChars) {
            $l_str_StartingTag = htmlspecialchars($l_str_StartingTag);
            $l_str_EndingTag = htmlspecialchars($l_str_EndingTag);
        }
        // if footnotes short code is empty, return the content without changes
        if (empty($l_str_StartingTag) || empty($l_str_EndingTag)) {
            return $p_str_Content;
        }

        if (!$p_bool_HideFootnotesText) {
            // load template file
            $l_obj_Template = new MCI_Footnotes_Template(MCI_Footnotes_Template::C_STR_PUBLIC, "footnote");
            $l_obj_TemplateTooltip = new MCI_Footnotes_Template(MCI_Footnotes_Template::C_STR_PUBLIC, "tooltip");
        } else {
            $l_obj_Template = null;
            $l_obj_TemplateTooltip = null;
        }

        // search footnotes short codes in the content
        do {
            // get first occurrence of the footnote short code [start]
            $i_int_len_Content = strlen($p_str_Content);
            if ($l_int_PosStart > $i_int_len_Content) $l_int_PosStart = $i_int_len_Content;
            $l_int_PosStart = strpos($p_str_Content, $l_str_StartingTag, $l_int_PosStart);
            // no short code found, stop here
            if ($l_int_PosStart === false) {
                break;
            }
            // get first occurrence of a footnote short code [end]
            $l_int_PosEnd = strpos($p_str_Content, $l_str_EndingTag, $l_int_PosStart);
            // no short code found, stop here
            if ($l_int_PosEnd === false) {
                break;
            }
            // calculate the length of the footnote
            $l_int_Length = $l_int_PosEnd - $l_int_PosStart;
            // get footnote text
            $l_str_FootnoteText = substr($p_str_Content, $l_int_PosStart + strlen($l_str_StartingTag), $l_int_Length - strlen($l_str_StartingTag));
            // Text to be displayed instead of the footnote
            $l_str_FootnoteReplaceText = "";
            // display the footnote as mouse-over box
            if (!$p_bool_HideFootnotesText) {
                $l_str_Index = MCI_Footnotes_Convert::Index($l_int_FootnoteIndex, MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_COUNTER_STYLE));

                // display only an excerpt of the footnotes text if enabled
                $l_str_ExcerptText = $l_str_FootnoteText;
                $l_bool_EnableExcerpt = MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_FOOTNOTES_MOUSE_OVER_BOX_EXCERPT_ENABLED));
                $l_int_MaxLength = intval(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_INT_FOOTNOTES_MOUSE_OVER_BOX_EXCERPT_LENGTH));
                if ($l_bool_EnableExcerpt) {
                    $l_str_DummyText = strip_tags($l_str_FootnoteText);
                    if (is_int($l_int_MaxLength) && strlen($l_str_DummyText) > $l_int_MaxLength) {
                        $l_str_ExcerptText = substr($l_str_DummyText, 0, $l_int_MaxLength);
                        $l_str_ExcerptText = substr($l_str_ExcerptText, 0, strrpos($l_str_ExcerptText, ' '));
                        // Removed hyperlink navigation on user request, but left <a> element for style.
                        $l_str_ExcerptText .= '&nbsp;&#x2026; ' . sprintf(__("%scontinue%s", MCI_Footnotes_Config::C_STR_PLUGIN_NAME), '<a class="continue" onclick="footnote_moveToAnchor_' . $l_int_PostID . '(\'footnote_plugin_reference_' . $l_int_PostID . '_' . $l_str_Index . '\');">', '</a>');
                    }
                }

                // fill the footnotes template  templates/public/footnote.html
                $l_obj_Template->replace(
                    array(
                        "post_id" => $l_int_PostID,
                        "id"      => $l_str_Index,
                        "before"  => MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_STYLING_BEFORE),
                        "index"   => $l_str_Index,
                        "after"   => MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_STYLING_AFTER),
                        "text"    => MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_FOOTNOTES_MOUSE_OVER_BOX_ENABLED)) ? $l_str_ExcerptText : "",
                    )
                );
                $l_str_FootnoteReplaceText = $l_obj_Template->getContent();
                
                // reset the template
                $l_obj_Template->reload();
                if (MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_FOOTNOTES_MOUSE_OVER_BOX_ENABLED))) {
                    $l_int_OffsetY = intval(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_INT_FOOTNOTES_MOUSE_OVER_BOX_OFFSET_Y));
                    $l_int_OffsetX = intval(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_INT_FOOTNOTES_MOUSE_OVER_BOX_OFFSET_X));
                    
                    // fill in the tooltip template  templates/public/tooltip.html
                    $l_obj_TemplateTooltip->replace(
                        array(
                            "post_id"  => $l_int_PostID,
                            "id"       => $l_str_Index,
                            "position" => MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_MOUSE_OVER_BOX_POSITION),
                            "offset-y" => !empty($l_int_OffsetY) ? $l_int_OffsetY : 0,
                            "offset-x" => !empty($l_int_OffsetX) ? $l_int_OffsetX : 0
                        )
                    );
                    $l_str_FootnoteReplaceText .= $l_obj_TemplateTooltip->getContent();
                    $l_obj_TemplateTooltip->reload();
                }
            }
            // replace the footnote with the template
            $p_str_Content = substr_replace($p_str_Content, $l_str_FootnoteReplaceText, $l_int_PosStart, $l_int_Length + strlen($l_str_EndingTag));
            // add footnote only if not empty
            if (!empty($l_str_FootnoteText)) {
                // set footnote to the output box at the end
                self::$a_arr_Footnotes[] = $l_str_FootnoteText;
                // increase footnote index
                $l_int_FootnoteIndex++;
            }
            // add offset to the new starting position
            $l_int_PosStart += $l_int_Length + strlen($l_str_EndingTag);
            $l_int_PosStart = $l_int_Length + strlen($l_str_FootnoteReplaceText);
        } while (true);

        // return content
        return $p_str_Content;
    }

    /**
     * Generates the reference container.
     *
     * @author Stefan Herndler
     * @since 1.5.0
     * @return string
     */
    public function ReferenceContainer() {
        // post ID to make everything unique wrt archive view and infinite scroll
        global $l_int_PostID;
        $l_int_PostID = get_the_id();
        // no footnotes has been replaced on this page
        if (empty(self::$a_arr_Footnotes)) {
            return "";
        }
        // get html arrow
        $l_str_Arrow = MCI_Footnotes_Convert::getArrow(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_HYPERLINK_ARROW));
        // set html arrow to the first one if invalid index defined
        if (is_array($l_str_Arrow)) {
            $l_str_Arrow = MCI_Footnotes_Convert::getArrow(0);
        }
        // get user defined arrow
        $l_str_ArrowUserDefined = MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_HYPERLINK_ARROW_USER_DEFINED);
        if (!empty($l_str_ArrowUserDefined)) {
            $l_str_Arrow = $l_str_ArrowUserDefined;
        }

        // load template file
        $l_str_Body = "";
        $l_obj_Template = new MCI_Footnotes_Template(MCI_Footnotes_Template::C_STR_PUBLIC, "reference-container-body");

        // loop through all footnotes found in the page
        for ($l_str_Index = 0; $l_str_Index < count(self::$a_arr_Footnotes); $l_str_Index++) {
            // get footnote text
            $l_str_FootnoteText = self::$a_arr_Footnotes[$l_str_Index];
            // if footnote is empty, get to the next one
            if (empty($l_str_FootnoteText)) {
                continue;
            }
            // generate content of footnote index cell
			$l_str_FirstFootnoteIndex = ($l_str_Index + 1);
			// wrap each index # in a white-space:nowrap span
			$l_str_FootnoteArrowIndex  = '<span class="footnote_index_item">';
			// wrap the arrow in a @media print { display:hidden } span
			$l_str_FootnoteArrowIndex .= '<span class="footnote_index_arrow">' . $l_str_Arrow . '&#x200A;</span>';
			// get the index; add support for legacy index placeholder:
            $l_str_FootnoteArrowIndex .= MCI_Footnotes_Convert::Index(($l_str_Index + 1),  MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_COUNTER_STYLE));
            $l_str_FootnoteIndex       = MCI_Footnotes_Convert::Index(($l_str_Index + 1),  MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_COUNTER_STYLE));

            // check if it isn't the last footnote in the array
            if ($l_str_FirstFootnoteIndex < count(self::$a_arr_Footnotes) && MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_COMBINE_IDENTICAL_FOOTNOTES))) {
                // get all footnotes that I haven't passed yet
                for ($l_str_CheckIndex = $l_str_FirstFootnoteIndex; $l_str_CheckIndex < count(self::$a_arr_Footnotes); $l_str_CheckIndex++) {
                    // check if a further footnote is the same as the actual one
                    if ($l_str_FootnoteText == self::$a_arr_Footnotes[$l_str_CheckIndex]) {
                        // set the further footnote as empty so it won't be displayed later
                        self::$a_arr_Footnotes[$l_str_CheckIndex] = "";
                        // add the footnote index to the actual index
                        $l_str_FootnoteArrowIndex .= ',</span> <span class="footnote_index_item">' . MCI_Footnotes_Convert::Index(($l_str_CheckIndex + 1), MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_COUNTER_STYLE));
                        $l_str_FootnoteIndex      .= ', ' . MCI_Footnotes_Convert::Index(($l_str_CheckIndex + 1), MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_COUNTER_STYLE));
                    }
                }
            }
            
            $l_str_FootnoteArrowIndex .= '</span>';
            
			// replace all placeholders in the template  templates/public/reference-container-body.html
			// The individual arrow and index placeholders are for backcompat
            $l_obj_Template->replace(
                array(
                    "post_id"     => $l_int_PostID,
                    "id"          => MCI_Footnotes_Convert::Index($l_str_FirstFootnoteIndex, MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_FOOTNOTES_COUNTER_STYLE)),
                    "arrow"       => $l_str_Arrow,
                    "index"       => $l_str_FootnoteIndex,
                    "arrow-index" => $l_str_FootnoteArrowIndex,
                    "text"        => $l_str_FootnoteText
                )
            );
            // extra line breaks for page source legibility:
            $footnote_item_temp = $l_obj_Template->getContent();
            $footnote_item_temp .= "\r\n\r\n";
            $l_str_Body .= $footnote_item_temp;
            $l_obj_Template->reload();
        }

        // load template file  templates/public/reference-container.html
        $l_obj_TemplateContainer = new MCI_Footnotes_Template(MCI_Footnotes_Template::C_STR_PUBLIC, "reference-container");
        $l_obj_TemplateContainer->replace(
            array(
                "post_id"      => $l_int_PostID,
                "label"        => MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_STR_REFERENCE_CONTAINER_NAME),
                "button-style" => !MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_REFERENCE_CONTAINER_COLLAPSE)) ? 'display: none;' : '',
                "style"        =>  MCI_Footnotes_Convert::toBool(MCI_Footnotes_Settings::instance()->get(MCI_Footnotes_Settings::C_BOOL_REFERENCE_CONTAINER_COLLAPSE)) ? 'display: none;' : '',
                "content"      => $l_str_Body
            )
        );

        // free all found footnotes if reference container will be displayed
        self::$a_arr_Footnotes = array();
        
        return $l_obj_TemplateContainer->getContent();
    }
}
