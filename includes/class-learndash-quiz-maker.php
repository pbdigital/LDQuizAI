<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class LearnDash_Quiz_Maker {

    /**
     * The single instance of the class.
     *
     * @var LearnDash_Quiz_Maker
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main LearnDash_Quiz_Maker Instance.
     *
     * Ensures only one instance of LearnDash_Quiz_Maker is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see LearnDash_Quiz_Maker()
     * @return LearnDash_Quiz_Maker - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * LearnDash_Quiz_Maker Constructor.
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Hook into actions and filters.
     * @since  1.0.0
     */
    private function init_hooks() {
        // add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'admin_menu', array( $this, 'create_settings_page' ) );
        //ajax
        add_action( 'wp_ajax_set_chatgpt_token', array( $this, 'set_chatgpt_token' ) );
        add_action( 'wp_ajax_ldm_create_quiz', array( $this, 'create_quiz' ) );
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        // include_once dirname( __FILE__ ) . '/class-learndash-quiz-maker-post-type.php';
        // include_once dirname( __FILE__ ) . '/class-learndash-quiz-maker-shortcode.php';
        // include_once dirname( __FILE__ ) . '/class-learndash-quiz-maker-quiz.php';
        // include_once dirname( __FILE__ ) . '/class-learndash-quiz-maker-question.php';
        // include_once dirname( __FILE__ ) . '/class-learndash-quiz-maker-answer.php';
    }

    /**
     * Fired when the plugin is activated.
     *
     * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public static function activate( $network_wide ) {
        
    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public static function deactivate( $network_wide ) {
        
    }

    /**
     * Create a settings page for the plugin.
     */
    public function create_settings_page() {
        // add_options_page( 'LearnDash Quiz Maker', 'LearnDash Quiz Maker', 'manage_options', 'learndash-quiz-maker', array( $this, 'settings_page' ) );
        // create own settings menu
        add_menu_page( 'LearnDash Quiz Maker', 'LearnDash Quiz Maker', 'manage_options', 'learndash-quiz-maker', array( $this, 'settings_page' ), 'dashicons-welcome-learn-more', 6 );
    }

    /**
     * Display the plugin settings page.
     */
    public function settings_page() {
        // call file that contains the markup for the plugin settings page LDM_PLUGIN_DIR
        include_once LDM_PLUGIN_DIR . '/views/settings-page.php';
    }

    public function set_chatgpt_token() {
        $token = $_POST['token'];
        update_option('chatgpt_token', $token);
        echo 'success';
        wp_die();
    }

    public function create_quiz() {
        if (!function_exists('learndash_get_post_type_slug')) {
            return false;
        }
    
        $quiz_title = $_POST['quiz_title'];
        $quiz_settings = $_POST['quiz_settings'];
        $questions = $_POST['questions'];

        // Prepare the quiz post data
        $quiz_post_data = array(
            'post_title'   => wp_strip_all_tags($quiz_title),
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => learndash_get_post_type_slug('quiz'),
        );

        // Insert the quiz post
        $quiz_id = wp_insert_post($quiz_post_data);

        if (!$quiz_id || is_wp_error($quiz_id)) {
            return false;
        }

        // Set default quiz settings
        $default_quiz_settings = array(
            'course'                       => 0,
            'lesson'                       => 0,
            'topic'                        => 0,
            'show_points'                  => 'on',
            'btn_view_question'            => 'on',
            'btn_restart_quiz'             => 'on',
            'btn_continue'                 => 'on',
            'time_limit_enabled'           => '',
            'time_limit_time'              => 60,
            'prerequisite_enabled'         => '',
            'prerequisiteList'             => array(),
            'quiz_pro'                     => 0,
            'repeats'                      => '',
            'template_load'                => 0,
            'template_save'                => 0,
        );

        // Merge default and provided settings
        $merged_quiz_settings = wp_parse_args($quiz_settings, $default_quiz_settings);

        // Update quiz settings
        foreach ($merged_quiz_settings as $setting_key => $setting_value) {
            update_post_meta($quiz_id, $setting_key, $setting_value);
        }
        $quiz_post_data     = array();
        $quiz_post_data['form'] = array();
        $quiz_post_data['post_ID'] = $quiz_id;
        $pro_quiz = new WpProQuiz_Controller_Quiz();
        $pro_quiz->route(
            array(
                'action'  => 'addUpdateQuiz',
                'quizId'  => 0,
                'post_id' => $quiz_id,
            ),
            $quiz_post_data
        );
        $quiz_pro_id = get_post_meta($quiz_id, 'quiz_pro_id', true);

        foreach ($questions as $question) {
            $question_title = $question['question'];
            $question_content = '';

            $quiz_post_data = array(
                'post_title'   => $question_title,
                'post_content' => $question_content,
                'post_status'  => 'publish',
                'post_type'    => 'sfwd-question',
            );

            // Insert the quiz post
            $question_id = wp_insert_post($quiz_post_data);
            update_post_meta($question_id, 'quiz_id', $quiz_id);

            //Sync with PRO Quiz	
            $quiz_post_data['action'] = "new_step";

            $question_pro_id = learndash_update_pro_question(0, $quiz_post_data);
            if (!empty($question_pro_id)) {
                update_post_meta($question_id, 'question_pro_id', absint($question_pro_id));
                learndash_proquiz_sync_question_fields($question_id, $question_pro_id);
            }

            $answers = $question['choices'];
            $params = array();
            $answer_keys = ['A', 'B', 'C', 'D', 'E'];
            // get array index of correctAnswer
            $correctAnswer = array_search($question['correctAnswer'], $answer_keys);
            foreach ($answers as $key => $answer) {
                $params['_answerData'][] = array(
                    '_answer' => $answer['text'],
                    '_points' => false,
                    '_correct' => $answer['isCorrect'] == 'true' ? true : false,
                );
            }

            $question_mapper = new WpProQuiz_Model_QuestionMapper();
            $proquiz_controller_question = new WpProQuiz_Controller_Question();
            $question_model  = $proquiz_controller_question->getPostQuestionModel(0, $question_pro_id);
            $question_model->setTitle($question_title);

            $question_model->setQuestion($question_title);
            $question_model->setQuizId($quiz_pro_id);
            $question_model->set_array_to_object($params);
            $question = $question_mapper->save($question_model, true);
            $question_pro_id = $question->getId();
            // var_dump($question);
            update_post_meta($question_id, 'question_pro_id', $question_pro_id);
        }

        echo json_encode([
            'success' => true,
            'quiz_link' => get_edit_post_link($quiz_id),
        ]);
        die();
    }



}